<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Settings - Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { color: #2c3e50; margin: 0; }
        .back-btn { background: #6c757d; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; font-size: 0.9em; transition: background-color 0.3s; }
        .back-btn:hover { background: #5a6268; }
        
        .settings-section { background: #ffffff; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .settings-section h2 { color: #3498db; margin-top: 0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; text-transform: capitalize; }
        
        .setting-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid #f0f0f0; }
        .setting-item:last-child { border-bottom: none; }
        .setting-info { flex: 1; }
        .setting-label { font-weight: 600; color: #2c3e50; margin-bottom: 5px; }
        .setting-description { font-size: 0.9em; color: #7f8c8d; margin: 0; }
        .setting-key { font-size: 0.8em; color: #95a5a6; font-family: monospace; }
        
        .setting-input { flex: 0 0 200px; margin-left: 20px; }
        .setting-input input, .setting-input select { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9em; }
        .setting-input input[type="checkbox"] { width: auto; transform: scale(1.2); }
        
        .save-btn { background: #27ae60; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 1em; cursor: pointer; transition: background-color 0.3s; }
        .save-btn:hover { background: #229954; }
        .save-btn:disabled { background: #95a5a6; cursor: not-allowed; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .form-actions { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        
        .highlight-setting { background-color: #fff3cd; border-left: 4px solid #ffc107; padding-left: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ System Settings</h1>
            <a href="{{ route('admin.index') }}" class="back-btn">← Back to Admin</a>
        </div>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')
            
            @foreach($settings as $groupName => $groupSettings)
                <div class="settings-section">
                    <h2>{{ str_replace('_', ' ', $groupName) }}</h2>
                    
                    @foreach($groupSettings as $setting)
                        <div class="setting-item {{ $setting->key === 'free_user_prompt_limit' ? 'highlight-setting' : '' }}">
                            <div class="setting-info">
                                <div class="setting-label">
                                    {{ ucwords(str_replace('_', ' ', explode('_', $setting->key, 3)[2] ?? $setting->key)) }}
                                    @if($setting->key === 'free_user_prompt_limit')
                                        <span style="background: #e74c3c; color: white; padding: 2px 6px; border-radius: 3px; font-size: 0.7em; margin-left: 5px;">IMPORTANT</span>
                                    @endif
                                </div>
                                @if($setting->description)
                                    <p class="setting-description">{{ $setting->description }}</p>
                                @endif
                                <div class="setting-key">{{ $setting->key }}</div>
                            </div>
                            <div class="setting-input">
                                @if($setting->type === 'boolean')
                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                        <input type="checkbox" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="1"
                                               {{ \App\Models\SystemSetting::get($setting->key) ? 'checked' : '' }}>
                                        <span style="margin-left: 8px; font-size: 0.9em;">
                                            {{ \App\Models\SystemSetting::get($setting->key) ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </label>
                                @elseif($setting->type === 'integer')
                                    @if($setting->key === 'free_user_prompt_limit')
                                        <select name="settings[{{ $setting->key }}]" style="background: #fff3cd;">
                                            @foreach([5, 10, 15, 20, 25, 30, 50, 100] as $limit)
                                                <option value="{{ $limit }}" 
                                                        {{ \App\Models\SystemSetting::get($setting->key) == $limit ? 'selected' : '' }}>
                                                    {{ $limit }} prompts
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="number" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="{{ \App\Models\SystemSetting::get($setting->key) }}"
                                               min="0">
                                    @endif
                                @else
                                    <input type="text" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ \App\Models\SystemSetting::get($setting->key) }}">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            
            <div class="form-actions">
                <button type="submit" class="save-btn">💾 Save All Settings</button>
            </div>
        </form>
    </div>

    <script>
        // Add confirmation for important settings
        document.querySelector('form').addEventListener('submit', function(e) {
            const limitSetting = document.querySelector('select[name="settings[free_user_prompt_limit]"]');
            if (limitSetting && limitSetting.value != limitSetting.defaultValue) {
                if (!confirm('You are changing the free user prompt limit. This will affect all free users immediately. Continue?')) {
                    e.preventDefault();
                }
            }
        });

        // Update checkbox labels dynamically
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.parentElement.querySelector('span');
                if (label) {
                    label.textContent = this.checked ? 'Enabled' : 'Disabled';
                }
            });
        });
    </script>
</body>
</html>
