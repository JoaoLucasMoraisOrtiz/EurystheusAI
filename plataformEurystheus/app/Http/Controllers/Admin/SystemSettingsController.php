<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('key')->get()->groupBy(function ($setting) {
            // Group settings by prefix (e.g., 'free_user_', 'site_', etc.)
            $parts = explode('_', $setting->key);
            return $parts[0] . '_' . ($parts[1] ?? 'general');
        });

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        foreach ($request->input('settings', []) as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            
            if ($setting) {
                // Cast the value appropriately based on the setting type
                $castedValue = match ($setting->type) {
                    'integer' => (int) $value,
                    'boolean' => (bool) $value,
                    'json' => is_array($value) ? $value : json_decode($value, true),
                    default => $value,
                };

                SystemSetting::set($key, $castedValue, $setting->type, $setting->description);
            }
        }

        // Clear all system setting caches
        Cache::flush();

        return back()->with('success', 'System settings updated successfully!');
    }

    public function show($key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        return response()->json([
            'key' => $setting->key,
            'value' => SystemSetting::get($key),
            'type' => $setting->type,
            'description' => $setting->description,
        ]);
    }
}
