<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Translations - {{ $file }}.php</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f1f1f1; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.13); }
        
        .header { background: #d97706; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #fff; font-size: 1.6em; margin: 0; }
        .back-btn { background: #f59e0b; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px; font-size: 0.9em; }
        .back-btn:hover { background: #d97706; }
        
        .nav-tabs { background: #f9f9f9; border-bottom: 1px solid #ccc; padding: 0; margin: 0; display: flex; }
        .nav-tab { background: transparent; border: none; padding: 12px 20px; cursor: pointer; font-size: 14px; border-bottom: 4px solid transparent; }
        .nav-tab.active { background: #fff; border-bottom-color: #f59e0b; color: #f59e0b; font-weight: 600; }
        .nav-tab:hover { background: #fff; }
        
        .tab-content { display: none; padding: 20px; }
        .tab-content.active { display: block; }
        
        .form-table { width: 100%; border-collapse: collapse; }
        .form-table th { background: #f9f9f9; padding: 12px 15px; text-align: left; font-weight: 600; width: 200px; vertical-align: top; border-bottom: 1px solid #e1e1e1; }
        .form-table td { padding: 12px 15px; border-bottom: 1px solid #e1e1e1; }
        .form-table tr:hover { background: #f9f9f9; }
        
        .translation-key { font-family: monospace; background: #f5f5f5; padding: 4px 8px; border-radius: 3px; font-size: 0.9em; color: #666; margin-bottom: 5px; display: block; }
        .translation-input { 
            width: 100%; 
            padding: 8px 12px; 
            border: 1px solid #ddd; 
            border-radius: 3px; 
            font-size: 14px; 
            resize: vertical; 
            min-height: 40px; 
            transition: all 0.3s ease;
        }
        .translation-input:focus { 
            border-color: #f59e0b; 
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2); 
            outline: none; 
        }
        
        /* Visual editing enhancements */
        .translation-input[data-rich="true"] {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            min-height: 60px;
        }
        
        .rich-editor-toolbar {
            display: flex;
            gap: 5px;
            margin-bottom: 5px;
            padding: 5px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 3px 3px 0 0;
        }
        
        .rich-btn {
            padding: 4px 8px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        
        .rich-btn:hover {
            background: #f59e0b;
            color: white;
        }
        
        .preview-mode {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            background: #f8f9fa;
            min-height: 40px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .locale-columns { display: flex; gap: 20px; }
        .locale-column { flex: 1; }
        .locale-header { background: #0073aa; color: white; padding: 10px 15px; text-align: center; font-weight: bold; margin-bottom: 0; }
        .locale-header.en { background: #d63384; }
        .locale-header.pt { background: #198754; }
        
        .translation-pair { display: flex; gap: 15px; margin-bottom: 15px; padding: 15px; border: 1px solid #e1e1e1; border-radius: 5px; background: #fafafa; }
        .translation-side { flex: 1; }
        .locale-label { font-weight: bold; margin-bottom: 8px; padding: 5px 10px; border-radius: 3px; color: white; text-align: center; font-size: 0.9em; }
        .locale-label.en { background: #d63384; }
        .locale-label.pt { background: #198754; }
        
        .sync-button { background: #6c757d; color: white; border: none; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; cursor: pointer; margin-left: 5px; }
        .sync-button:hover { background: #545b62; }
        
        .submit-section { background: #f9f9f9; padding: 20px; border-top: 1px solid #e1e1e1; }
        .btn-primary { background: #f59e0b; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 3px; cursor: pointer; }
        .btn-primary:hover { background: #d97706; }
        
        .alert { padding: 15px; margin: 20px; border-left: 4px solid; }
        .alert.success { background: #dff0d8; border-color: #d6e9c6; color: #3c763d; }
        .alert.error { background: #f2dede; border-color: #ebccd1; color: #a94442; }
        
        .section-description { background: #e7f3ff; padding: 12px; margin-bottom: 15px; border-left: 4px solid #0073aa; font-style: italic; color: #555; }
        
        .char-count { font-size: 0.8em; color: #666; margin-top: 5px; }
        
        .search-box { padding: 15px 20px; background: #f9f9f9; border-bottom: 1px solid #e1e1e1; }
        .search-input { width: 300px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Translation Manager - {{ ucfirst($file) }}.php</h1>
            <a href="{{ route('admin.index') }}" class="back-btn">← Back to Admin</a>
        </div>

        @if(session('success'))
            <div class="alert success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error">❌ {{ session('error') }}</div>
        @endif

        <div class="search-box">
            <input type="text" class="search-input" placeholder="Search translations..." id="searchInput">
        </div>

        <form method="POST" action="{{ route('admin.translations.update') }}">
            @csrf
            <input type="hidden" name="file" value="{{ $file }}">

            <div class="nav-tabs">
                @foreach($groupedTranslations as $sectionName => $translations)
                    <button type="button" class="nav-tab {{ $loop->first ? 'active' : '' }}" onclick="switchTab('{{ $loop->index }}')">
                        {{ $sectionName }} ({{ count($translations) }})
                    </button>
                @endforeach
            </div>

            @foreach($groupedTranslations as $sectionName => $translations)
                <div class="tab-content {{ $loop->first ? 'active' : '' }}" id="tab-{{ $loop->index }}">
                    <div class="section-description">
                        <strong>{{ $sectionName }}</strong> - 
                        @switch($sectionName)
                            @case('Hero Section')
                                Main landing page content, headlines, and primary call-to-action buttons
                                @break
                            @case('Navigation')
                                Menu items, navigation links, and site structure labels
                                @break
                            @case('Features')
                                Product features, benefits descriptions, and feature highlights
                                @break
                            @case('Pricing')
                                Pricing plans, costs, and subscription-related content
                                @break
                            @case('Login')
                                Login form, authentication prompts, and sign-in related text
                                @break
                            @case('Register')
                                Registration form, account creation, and signup process
                                @break
                            @default
                                Various translations for this section
                        @endswitch
                    </div>
                    
                    @foreach($translations as $key => $localeData)
                        <div class="translation-pair" data-key="{{ $key }}" data-value="{{ strtolower($localeData['en'] . ' ' . $localeData['pt_BR']) }}">
                            <div class="translation-key">
                                <strong>{{ $key }}</strong>
                                <div style="font-size: 0.8em; color: #666; margin-top: 5px;">
                                    @if(str_contains($key, 'title'))
                                        📋 Title/Heading
                                    @elseif(str_contains($key, 'button') || str_contains($key, 'cta'))
                                        🔘 Button/Action
                                    @elseif(str_contains($key, 'desc') || str_contains($key, 'subtitle'))
                                        📝 Description
                                    @elseif(str_contains($key, 'nav') || str_contains($key, 'menu'))
                                        🧭 Navigation
                                    @elseif(str_contains($key, 'error') || str_contains($key, 'failed'))
                                        ⚠️ Error Message
                                    @elseif(str_contains($key, 'success'))
                                        ✅ Success Message
                                    @else
                                        💬 Text
                                    @endif
                                </div>
                            </div>
                            
                            <div class="locale-columns">
                                <!-- English Column -->
                                <div class="locale-column">
                                    <div class="locale-label en">🇺🇸 English</div>
                                    @if(strlen($localeData['en']) > 100)
                                        <textarea name="translations_en[{{ $key }}]" class="translation-input" rows="3" placeholder="English translation...">{{ old('translations_en.'.$key, $localeData['en']) }}</textarea>
                                    @else
                                        <input type="text" name="translations_en[{{ $key }}]" class="translation-input" value="{{ old('translations_en.'.$key, $localeData['en']) }}" placeholder="English translation...">
                                    @endif
                                    <div class="char-count">{{ strlen($localeData['en']) }} characters</div>
                                </div>
                                
                                <!-- Portuguese Column -->
                                <div class="locale-column">
                                    <div class="locale-label pt">🇧🇷 Português (BR)</div>
                                    @if(strlen($localeData['pt_BR']) > 100)
                                        <textarea name="translations_pt_BR[{{ $key }}]" class="translation-input" rows="3" placeholder="Tradução em português...">{{ old('translations_pt_BR.'.$key, $localeData['pt_BR']) }}</textarea>
                                    @else
                                        <input type="text" name="translations_pt_BR[{{ $key }}]" class="translation-input" value="{{ old('translations_pt_BR.'.$key, $localeData['pt_BR']) }}" placeholder="Tradução em português...">
                                    @endif
                                    <div class="char-count">{{ strlen($localeData['pt_BR']) }} characters</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="submit-section">
                <button type="submit" class="btn-primary">💾 Save All Translations (EN + PT-BR)</button>
                <span style="margin-left: 15px; color: #666; font-size: 0.9em;">
                    Both language files will be validated before saving
                </span>
            </div>
        </form>
    </div>

    <script>
        function switchTab(tabIndex) {
            // Remove active class from all tabs and content
            document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to selected tab and content
            document.querySelectorAll('.nav-tab')[tabIndex].classList.add('active');
            document.getElementById('tab-' + tabIndex).classList.add('active');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.translation-pair');
            
            rows.forEach(row => {
                const key = row.dataset.key.toLowerCase();
                const value = row.dataset.value;
                
                if (key.includes(searchTerm) || value.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Character count update
        document.querySelectorAll('.translation-input').forEach(input => {
            input.addEventListener('input', function() {
                const charCount = this.nextElementSibling;
                if (charCount && charCount.classList.contains('char-count')) {
                    charCount.textContent = this.value.length + ' characters';
                }
            });
        });

        // Enhanced visual editing features
        function addRichEditing() {
            document.querySelectorAll('textarea.translation-input').forEach(textarea => {
                // Create toolbar
                const toolbar = document.createElement('div');
                toolbar.className = 'rich-editor-toolbar';
                toolbar.innerHTML = `
                    <button type="button" class="rich-btn" onclick="insertText(this, '**', '**')" title="Bold">B</button>
                    <button type="button" class="rich-btn" onclick="insertText(this, '*', '*')" title="Italic">I</button>
                    <button type="button" class="rich-btn" onclick="insertText(this, '🚀 ', '')" title="Rocket">🚀</button>
                    <button type="button" class="rich-btn" onclick="insertText(this, '💎 ', '')" title="Diamond">💎</button>
                    <button type="button" class="rich-btn" onclick="insertText(this, '⚡ ', '')" title="Lightning">⚡</button>
                    <button type="button" class="rich-btn" onclick="insertText(this, '🎯 ', '')" title="Target">🎯</button>
                    <button type="button" class="rich-btn" onclick="togglePreview(this)" title="Preview">👁️</button>
                `;
                
                textarea.parentNode.insertBefore(toolbar, textarea);
                textarea.dataset.rich = "true";
            });
        }

        function insertText(button, before, after) {
            const textarea = button.parentNode.nextElementSibling;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);
            
            textarea.value = text.substring(0, start) + before + selectedText + after + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + before.length, start + before.length + selectedText.length);
        }

        function togglePreview(button) {
            const textarea = button.parentNode.nextElementSibling;
            const isPreview = textarea.style.display === 'none';
            
            if (isPreview) {
                // Show textarea, hide preview
                textarea.style.display = '';
                textarea.nextElementSibling.style.display = 'none';
                button.textContent = '👁️';
                button.title = 'Preview';
            } else {
                // Show preview, hide textarea
                const preview = document.createElement('div');
                preview.className = 'preview-mode';
                preview.innerHTML = textarea.value
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/\n/g, '<br>');
                
                if (textarea.nextElementSibling && textarea.nextElementSibling.classList.contains('preview-mode')) {
                    textarea.nextElementSibling.innerHTML = preview.innerHTML;
                } else {
                    textarea.parentNode.insertBefore(preview, textarea.nextElementSibling);
                }
                
                textarea.style.display = 'none';
                preview.style.display = 'block';
                button.textContent = '✏️';
                button.title = 'Edit';
            }
        }

        // Initialize rich editing when page loads
        document.addEventListener('DOMContentLoaded', addRichEditing);

        // Auto-save functionality (every 30 seconds)
        let autoSaveTimer;
        function enableAutoSave() {
            autoSaveTimer = setInterval(() => {
                const form = document.querySelector('form');
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(response => {
                    if (response.ok) {
                        // Show subtle indication of auto-save
                        const indicator = document.createElement('div');
                        indicator.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:8px 16px;border-radius:4px;z-index:1000;';
                        indicator.textContent = '✅ Auto-saved';
                        document.body.appendChild(indicator);
                        setTimeout(() => indicator.remove(), 2000);
                    }
                }).catch(() => {
                    // Silent fail for auto-save
                });
            }, 30000);
        }

        // Start auto-save
        enableAutoSave();
    </script>
</body>
</html>
