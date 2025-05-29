<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Enums\UserRole; // Assuming UserRole enum is here
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TranslationController extends Controller
{
    // Ensure only admins can access these methods
    public function __construct()
    {
        // This will be handled at the route level using middleware
        // For example: $this->middleware('auth'); $this->middleware('role:admin');
    }

    protected function getLocales()
    {
        // Define the locales you want to manage
        return ['en', 'pt_BR'];
    }

    protected function getTranslationFilePath($locale, $file)
    {
        // Ensure the $file variable doesn't have malicious paths.
        // For simplicity, we assume $file is a clean name like 'marketing' or 'auth'.
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $file); // Sanitize file name
        return lang_path($locale . '/' . $fileName . '.php');
    }

    public function index(Request $request, $file = 'marketing')
    {
        $locales = $this->getLocales();
        $translationsByLocale = [];
        $allKeys = [];

        foreach ($locales as $locale) {
            $filePath = $this->getTranslationFilePath($locale, $file);
            if (File::exists($filePath)) {
                $translationsByLocale[$locale] = include $filePath; // Load as array
                // Use Arr::dot to get all keys, including nested ones for comparison
                $allKeys = array_merge($allKeys, array_keys(Arr::dot($translationsByLocale[$locale])));
            } else {
                $translationsByLocale[$locale] = [];
            }
        }
        $allKeys = array_unique($allKeys);
        sort($allKeys);

        $groupedTranslations = [];
        foreach ($allKeys as $dottedKey) {
            // Determine the section (top-level key)
            $section = Str::before($dottedKey, '.');
            if (!Str::contains($dottedKey, '.')) {
                $section = 'general'; // Default section for non-dotted keys
            }

            // For each locale, get the value using the dotted key if available
            // If the original structure is nested, Arr::get will retrieve it.
            foreach ($locales as $locale) {
                // We need to provide the original (potentially nested) key to Arr::get
                // The $dottedKey is fine for identifying the leaf, but for display,
                // we want to group by the actual structure.
                // The current blade expects section -> key (can be nested) -> locale -> value
                // Let's simplify: the blade will handle displaying nested structures if we pass the full arrays.

                // The $groupedTranslations structure from the blade seems to be:
                // $groupedTranslations[SECTION_NAME][KEY_NAME (can be sub.key)][LOCALE] = VALUE
                // We will build this structure.
                $keyParts = explode('.', $dottedKey);
                $currentKey = array_shift($keyParts); // This is the main key for the section, or the key itself if not nested.
                                                    // If it's nested, the blade will need to handle it.
                                                    // For now, let's assume the blade iterates through $allKeys and uses $translationsByLocale.

                // The provided blade structure is:
                // foreach ($groupedTranslations as $section => $keys)
                //   foreach ($keys as $key => $translations)
                //     foreach ($locales as $locale)
                //       $translations[$locale]

                // Let's try to match this structure.
                // $key here is the full dotted key.
                $groupedTranslations[$section][$dottedKey][$locale] = Arr::get($translationsByLocale[$locale], $dottedKey, '');
            }
        }
        
        // The blade expects $groupedTranslations where the inner key is the full dotted key.
        // And $translations where the key is the full dotted key.
        // Let's ensure $translations is also structured with dotted keys for consistency in the view if needed.
        $flatTranslations = [];
        foreach ($allKeys as $dottedKey) {
            foreach ($locales as $locale) {
                $flatTranslations[$dottedKey][$locale] = Arr::get($translationsByLocale[$locale], $dottedKey, '');
            }
        }


        return view('admin.translations.index', [
            'file' => $file,
            'locales' => $locales,
            'translations' => $flatTranslations, // For direct access by dotted key
            'allKeys' => $allKeys, 
            'groupedTranslations' => $groupedTranslations // As expected by the blade's loops
        ]);
    }

    public function update(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Translation update request data:', [
            'file' => $request->input('file'),
            'translations_en_count' => count($request->input('translations_en', [])),
            'translations_pt_BR_count' => count($request->input('translations_pt_BR', [])),
            'sample_en_keys' => array_slice(array_keys($request->input('translations_en', [])), 0, 3),
            'sample_pt_BR_keys' => array_slice(array_keys($request->input('translations_pt_BR', [])), 0, 3),
        ]);

        $validated = $request->validate([
            'file' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/', // Sanitize file name
            'translations_en' => 'nullable|array',
            'translations_pt_BR' => 'nullable|array',
        ]);

        $file = $validated['file'];
        $locales = $this->getLocales();
        
        // Reorganize the input data to match our expected format
        $inputTranslations = [];
        if (isset($validated['translations_en'])) {
            foreach ($validated['translations_en'] as $key => $value) {
                $inputTranslations[$key]['en'] = $value;
            }
        }
        if (isset($validated['translations_pt_BR'])) {
            foreach ($validated['translations_pt_BR'] as $key => $value) {
                $inputTranslations[$key]['pt_BR'] = $value;
            }
        }

        // Debug: Log processed translations
        Log::info('Processed translations count:', [
            'total_keys' => count($inputTranslations),
            'sample_keys' => array_slice(array_keys($inputTranslations), 0, 5)
        ]);

        foreach ($locales as $locale) {
            $filePath = $this->getTranslationFilePath($locale, $file);
            
            // Build new translations for this locale
            $newLocaleTranslations = [];
            foreach ($inputTranslations as $dottedKey => $localeValues) {
                if (isset($localeValues[$locale]) && $localeValues[$locale] !== null) {
                    // Use Arr::set to reconstruct the nested array structure
                    Arr::set($newLocaleTranslations, $dottedKey, trim($localeValues[$locale]));
                }
            }

            // Debug: Log what we're about to save
            Log::info("Saving translations for locale {$locale}:", [
                'file_path' => $filePath,
                'keys_count' => count($newLocaleTranslations),
                'sample_data' => array_slice($newLocaleTranslations, 0, 3, true)
            ]);

            // Generate the content for the PHP file
            $content = "<?php\n\nreturn " . $this->varExportPretty($newLocaleTranslations) . ";\n";
            
            try {
                // Ensure directory exists
                $directory = dirname($filePath);
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                    Log::info("Created directory: {$directory}");
                }
                
                // Check if file is writable
                if (File::exists($filePath) && !is_writable($filePath)) {
                    throw new \Exception("File is not writable: {$filePath}");
                }
                
                // Write the file
                $result = File::put($filePath, $content);
                
                if ($result === false) {
                    throw new \Exception("Failed to write file: {$filePath}");
                }
                
                Log::info("Successfully saved translation file for {$locale}: {$filePath}");
                
            } catch (\Exception $e) {
                Log::error("Failed to save translations for {$locale}: " . $e->getMessage(), [
                    'file_path' => $filePath,
                    'exception' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()->with('error', 'Failed to save translations for ' . $locale . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.translations.index', ['file' => $file])
                         ->with('success', 'Translations updated successfully for file: ' . $file);
    }

    /**
     * Pretty print var_export output for better formatting
     */
    private function varExportPretty($var, $indent = 0)
    {
        $spaces = str_repeat('    ', $indent);
        
        if (is_array($var)) {
            $output = "[\n";
            foreach ($var as $key => $value) {
                $output .= $spaces . '    ' . var_export($key, true) . ' => ';
                if (is_array($value)) {
                    $output .= $this->varExportPretty($value, $indent + 1);
                } else {
                    $output .= var_export($value, true);
                }
                $output .= ",\n";
            }
            $output .= $spaces . ']';
            return $output;
        }
        
        return var_export($var, true);
    }
}