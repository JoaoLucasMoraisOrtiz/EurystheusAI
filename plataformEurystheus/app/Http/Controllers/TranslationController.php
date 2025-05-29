<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
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
        $validated = $request->validate([
            'file' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/', // Sanitize file name
            'translations' => 'required|array',
            // Further validation for translations structure can be added if needed
            // e.g., 'translations.*.*' => 'nullable|string'
        ]);

        $file = $validated['file'];
        $locales = $this->getLocales();
        $inputTranslations = $validated['translations']; // These are [dotted.key][locale] = value

        foreach ($locales as $locale) {
            $filePath = $this->getTranslationFilePath($locale, $file);
            $currentTranslations = [];
            if (File::exists($filePath)) {
                $currentTranslations = include $filePath;
                if (!is_array($currentTranslations)) {
                    $currentTranslations = [];
                }
            }

            // Rebuild the translations for this locale from the flat input
            $newLocaleTranslations = [];
            foreach ($inputTranslations as $dottedKey => $localeValues) {
                if (isset($localeValues[$locale])) {
                    // Use Arr::set to reconstruct the nested array structure
                    Arr::set($newLocaleTranslations, $dottedKey, $localeValues[$locale]);
                } else {
                    // If a key is not submitted for a locale, we might want to keep the old value or set it to empty.
                    // For now, if it's not in the input, it means it might have been deleted or was empty.
                    // To preserve keys that were not submitted at all (e.g. entire sections not in the form),
                    // we should merge with existing. But Arr::set will create new keys.

                    // Let's ensure we only update keys that were present in the form for this locale.
                    // If a key is missing for a locale in the form, it implies it should be empty or removed.
                    // The Arr::set will handle creating the structure. If a value is empty string, it will be saved as such.
                }
            }
            
            // To handle removal of keys, we should start with $newLocaleTranslations
            // and then merge existing values for keys *not* present in the form's $allKeys (if we had that).
            // Simpler: the form should submit all keys. If a value is empty, it's empty.
            // If a key is entirely removed from the form (not possible with current blade), then it's different.

            // The current approach: $newLocaleTranslations will only contain keys submitted for this locale.
            // This means if a key was previously set but now its input is empty, it will be saved as empty.
            // If a key existed but is not part of the submitted $inputTranslations at all, it will be removed from the file.
            // This might be too destructive if the form doesn't render all possible keys.
            // The blade renders based on $allKeys, so all known keys should be submitted.

            $content = "<?php\n\nreturn " . var_export($newLocaleTranslations, true) . ";\n";
            
            try {
                File::put($filePath, $content);
            } catch (\Exception $e) {
                // Log error or return with an error message
                return redirect()->back()->with('error', 'Failed to save translations for ' . $locale . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.translations.index', ['file' => $file])
                         ->with('success', 'Translations updated successfully for file: ' . $file);
    }
}