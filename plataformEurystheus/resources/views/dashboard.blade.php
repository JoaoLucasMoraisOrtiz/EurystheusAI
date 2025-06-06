<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('dashboard.title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #334155 100%);
            min-height: 100vh;
            color: #E2E8F0; 
            line-height: 1.6;
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px;
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
            padding: 24px 32px; 
            background: rgba(30, 41, 59, 0.8);
            border-radius: 16px; 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .header h1 { 
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-info { 
            background: rgba(30, 41, 59, 0.8);
            padding: 32px; 
            border-radius: 16px; 
            margin-bottom: 40px; 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .user-info h3 { 
            margin-top: 0; 
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3B82F6 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }
        .user-info p {
            margin: 8px 0;
            color: #CBD5E1;
        }
        .role { 
            display: inline-flex;
            align-items: center;
            padding: 8px 16px; 
            border-radius: 20px; 
            font-size: 0.875rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .role.admin { 
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); 
            color: white; 
            box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.3);
        }
        .role.payed-user { 
            background: linear-gradient(135deg, #10B981 0%, #059669 100%); 
            color: white; 
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.3);
        }
        .role.free-user { 
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%); 
            color: white; 
            box-shadow: 0 4px 14px 0 rgba(107, 114, 128, 0.3);
        }
        .logout-btn { 
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 14px 0 rgba(239, 68, 68, 0.3);
        }
        .logout-btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(239, 68, 68, 0.4);
        }
        .admin-link { 
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            color: white; 
            padding: 12px 20px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.3);
        }
        .admin-link:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.4);
        }
        .content-section { 
            background: rgba(30, 41, 59, 0.8);
            padding: 32px; 
            border-radius: 16px; 
            margin-bottom: 32px; 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .content-section h3 { 
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3B82F6 0%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 0; 
            border-bottom: 1px solid rgba(148, 163, 184, 0.2); 
            padding-bottom: 16px; 
            margin-bottom: 24px; 
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #F1F5F9;
        }
        .prompt-log-item { 
            border: 1px solid rgba(148, 163, 184, 0.2); 
            padding: 24px; 
            margin-bottom: 24px; 
            border-radius: 12px; 
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
        }
        .prompt-log-item h4 { 
            margin-top: 0; 
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .prompt-log-item p { 
            font-size: 0.875rem; 
            color: #94A3B8; 
        }
        .generated-prompt { 
            background: rgba(15, 23, 42, 0.8);
            padding: 20px; 
            margin-top: 16px; 
            border-radius: 12px; 
            border-left: 4px solid #FBBF24;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .generated-prompt code { 
            display: block; 
            white-space: pre-wrap; 
            background: rgba(51, 65, 85, 0.6);
            padding: 16px; 
            border-radius: 8px; 
            font-size: 0.875rem;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .placeholder-input { 
            width: 100%; 
            padding: 12px 16px; 
            margin-top: 8px; 
            margin-bottom: 12px; 
            border: 1px solid rgba(148, 163, 184, 0.3); 
            background: rgba(30, 41, 59, 0.8);
            color: #F1F5F9; 
            border-radius: 8px; 
            font-size: 0.875rem;
            transition: all 0.2s;
            resize: vertical;
        }
        .placeholder-input:focus {
            outline: none;
            border-color: #3B82F6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .execute-btn { 
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
            color: #0F172A; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 4px 14px 0 rgba(245, 158, 11, 0.3);
        }
        .execute-btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px 0 rgba(245, 158, 11, 0.4);
        }
        .execution-result { 
            margin-top: 24px; 
            padding: 20px; 
            border-radius: 12px; 
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .execution-result h5 { 
            margin-top: 0; 
            font-weight: 600;
            color: #FBBF24; 
        }
        .execution-result pre { 
            white-space: pre-wrap; 
            word-wrap: break-word; 
            background: rgba(51, 65, 85, 0.6);
            padding: 16px; 
            border-radius: 8px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.875rem;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .alert { 
            padding: 16px 20px; 
            margin-bottom: 24px; 
            border-radius: 12px;
            font-weight: 500;
        }
        .alert-success { 
            background: rgba(16, 185, 129, 0.1);
            color: #6EE7B7; 
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .alert-error { 
            background: rgba(239, 68, 68, 0.1);
            color: #FCA5A5; 
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        /* Collapsible prompts & star prioritization */
        .prompt-header { 
            background: rgba(51, 65, 85, 0.8);
            padding: 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: all 0.2s;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .prompt-header:hover {
            background: rgba(51, 65, 85, 0.9);
            transform: translateY(-1px);
        }
        .prompt-header .prompt-title { 
            font-weight: 600; 
            color: #F1F5F9; 
        }
        .prompt-body { 
            margin-top: 12px; 
        }
        .hidden { 
            display: none; 
        }

        /* Styles for the Loading Overlay */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        #loadingOverlay.visible {
            display: flex;
        }

        .spinner {
            border: 4px solid rgba(148, 163, 184, 0.3);
            border-top: 4px solid #FBBF24;
            border-radius: 50%;
            width: 64px;
            height: 64px;
            animation: spin 1s linear infinite;
            box-shadow: 0 8px 32px rgba(251, 191, 36, 0.3);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Form improvements */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 25%, #FFD700 50%, #FFA500 75%, #FFD700 100%);
            background-size: 200% 200%;
            color: #0F172A;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 
                0 8px 32px rgba(255, 215, 0, 0.4),
                0 0 0 1px rgba(255, 215, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: goldShimmer 3s ease-in-out infinite;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                rgba(255, 255, 255, 0.3),
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: goldGleam 2s linear infinite;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 12px 48px rgba(255, 215, 0, 0.6),
                0 0 0 2px rgba(255, 215, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            animation-duration: 1.5s;
        }

        .btn-primary:active {
            transform: translateY(-1px) scale(1.01);
            box-shadow: 
                0 6px 24px rgba(255, 215, 0, 0.5),
                0 0 0 1px rgba(255, 215, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        @keyframes goldShimmer {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes goldGleam {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            50% {
                transform: translateX(0%) translateY(0%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }
            .header {
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }
            .header-actions {
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @include('prompt.chatHeader')
</head>
<body class="font-sans antialiased theme-light">
    <!-- Loading Overlay - visibility controlled by JS by adding/removing .visible class -->
    <div id="loadingOverlay">
        <div class="spinner"></div> <!-- Elemento do spinner -->
    </div>
    <div class="container">
        <div class="header">
            <h1>{{ __('general.dashboard') }}</h1>
            <div class="header-actions">
                @include('components.language-switcher')
                @if($user->isAdmin())
                    <a href="{{ route('admin.index') }}" class="admin-link">{{ __('general.admin') }} {{ __('dashboard.overview') }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">{{ __('general.logout') }}</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($user->isFree())
        <!-- Upgrade Banner for Free Users -->
        <div class="upgrade-banner" style="background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 50%, #FCD34D 100%); padding: 24px; border-radius: 16px; margin-bottom: 32px; border: 2px solid #F59E0B; box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent); transform: rotate(45deg); animation: shimmer 3s infinite;"></div>
            <div style="position: relative; z-index: 1;">
                <div style="display: flex; align-items: center; justify-content: between; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1; min-width: 300px;">
                        <h3 style="color: #0F172A; font-size: 1.5rem; font-weight: 800; margin: 0 0 8px 0;">🚀 {{ __('dashboard.upgrade_to_hero') }}</h3>
                        <p style="color: #0F172A; font-size: 1rem; margin: 0; opacity: 0.9;">{{ __('dashboard.unlock_unlimited_prompts') }}</p>
                    </div>
                    <div style="flex-shrink: 0;">
                        <a href="{{ route('marketing.sales') }}" 
                           style="background: #0F172A; color: #FBBF24; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.4);"
                           onmouseover="this.style.transform='translateY(-2px) scale(1.05)'; this.style.boxShadow='0 8px 25px rgba(15, 23, 42, 0.6)';"
                           onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 4px 14px rgba(15, 23, 42, 0.4)';">
                            <span>⚡</span>
                            {{ __('dashboard.upgrade_now') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style>
            @keyframes shimmer {
                0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                50% { transform: translateX(0%) translateY(0%) rotate(45deg); }
                100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            }
        </style>
        @endif
        
<!--         <div class="user-info">
            <h3>{{ __('dashboard.welcome_back') }}, {{ $user->name }}!</h3>
            <p><strong>{{ __('auth.email') }}:</strong> {{ $user->email }}</p>
            <p><strong>{{ __('general.role') }}:</strong> <span class="role {{ str_replace('_', '-', $user->role->value) }}">{{ $user->role->label() }}</span></p>
            <p><strong>{{ __('general.created_at') }}:</strong> {{ $user->created_at->format('M d, Y') }}</p>
        </div> -->


        @if($user->isPayed() || $user->isAdmin())
        <div class="content-section">
            <!-- <h3>{{ __('dashboard.create_prompt') }}</h3>
            <div class="mb-4">
                <a href="{{ route('prompt.chat') }}" class="btn-primary" style="display:inline-block;">Abrir Assistente de Criação de Prompt (Chat)</a>
            </div> -->
            @include('prompt.chat')
        </div>
        @endif
        
        <div class="content-section">
            @if($user->isAdmin())
                <h3>{{ __('dashboard.admin_features') }}</h3>
                <p>{{ __('dashboard.admin_privileges_message') }}</p>
                {{-- Admins might also see paid user features --}}
            @endif

            @if($user->isPayed() || $user->isAdmin()) 
                <h3>{{ __('dashboard.prompt_blueprints_execution') }}</h3>
                @if(isset($promptLogs) && $promptLogs->count() > 0)
                    @foreach($promptLogs as $log)
                        @php
                            // Decodificar o JSON do campo content para extrair o objective
                            $content = json_decode($log->content, true);
                            $objective = isset($content['objective']) ? $content['objective'] : __('dashboard.unnamed_problem');
                        @endphp
                        <div class="prompt-log-item" data-blueprint-key="{{ $log->id }}">
                            <div class="blueprint-header cursor-pointer" data-blueprint-key="{{ $log->id }}">
                                <h4>🎯 {{ $objective }}</h4>
                                <p><small>{{ __('general.created_at') }}: {{ $log->created_at->format('M d, Y H:i') }}</small></p>
                            </div>
                            <div class="blueprint-details hidden">
                            @if($log->llmResponse && $log->llmResponse->generated_prompts)
                                @php
                                    $generatedPrompts = json_decode($log->llmResponse->generated_prompts, true);
                                @endphp
                                @if(is_array($generatedPrompts) && count($generatedPrompts) > 0)
                                    <p><strong>{{ __('dashboard.generated_prompt_blueprints') }}:</strong></p>
                                    @foreach($generatedPrompts as $index => $promptText)
                                        @php
                                            $isExecutedPrompt = (session('executed_llm_response_id') == $log->llmResponse->id && session('executed_prompt_index') == $index);
                                            $promptKey = $log->id . '_' . $index;
                                        @endphp
                                        <div class="generated-prompt" 
                                             data-prompt-key="{{ $promptKey }}" 
                                             @if($isExecutedPrompt) id="executed_prompt_target" @endif>
                                            <div class="prompt-header">
                                                <span class="prompt-title">{{ __('dashboard.prompt') }} {{ $index + 1 }}</span>
                                            </div>
                                            <div class="prompt-body {{ $isExecutedPrompt ? '' : 'hidden' }}">
                                                <code>{{ $promptText }}</code>
                                                <form method="POST" action="{{ route('paid.dashboard.execute.prompt') }}" style="margin-top: 15px;">
                                                    @csrf
                                                    <input type="hidden" name="llm_response_id" value="{{ $log->llmResponse->id }}">
                                                    <input type="hidden" name="prompt_index" value="{{ $index }}">
                                                    <input type="hidden" name="scroll_to_id" value="executed_prompt_target"> {{-- Used to identify which prompt to scroll to --}}
                                                    
                                                    @php
                                                        preg_match_all('/\{\{(.*?)\}\}/', $promptText, $matches);
                                                        $placeholders = array_map('trim', array_unique($matches[1]));
                                                    @endphp

                                                    @if(count($placeholders) > 0)
                                                        <p><strong>🔧 Fill in the placeholders:</strong></p>
                                                        @foreach($placeholders as $placeholder)
                                                            <div class="form-group">
                                                                <label for="placeholder_{{ $log->id }}_{{ $index }}_{{ $placeholder }}">{{ ucfirst(str_replace('_', ' ', $placeholder)) }}:</label>
                                                            <textarea 
                                                                id="placeholder_{{ $log->id }}_{{ $index }}_{{ $placeholder }}" 
                                                                name="placeholders[{{ $placeholder }}]" 
                                                                class="placeholder-input"
                                                                rows="3"
                                                                placeholder="{{ __('dashboard.enter_placeholder', ['placeholder' => str_replace('_', ' ', $placeholder)]) }}..."
                                                                required>{{ old('placeholders.'.$placeholder, session("executed_llm_response_id") == $log->llmResponse->id && session("executed_prompt_index") == $index && old('placeholders.'.$placeholder) === null ? '' : old('placeholders.'.$placeholder)) }}</textarea>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                         <p class="text-sm text-gray-400">✅ {{ __('dashboard.no_placeholders_message') }}</p>
                                                    @endif
                                                    <button type="submit" class="execute-btn">🚀 {{ __('dashboard.execute_prompt') }}</button>
                                                </form>

                                                @if($isExecutedPrompt && session('execution_result'))
                                                    <div class="execution-result">
                                                        <h5>🎉 {{ __('dashboard.execution_result') }}:</h5>
                                                        <pre>{{ session('execution_result') }}</pre>
                                                    </div>
                                                @endif
                                            </div> <!-- .prompt-body -->
                                        </div> <!-- .generated-prompt -->
                                    @endforeach
                                @else
                                    <p>Não existem prompts automáticos disponíveis no momento. Verifique se a IA está configurada corretamente ou crie um novo prompt manualmente.</p>
                                @endif
                            @else
                                <p>Não foi possível obter resposta automática no momento. Tente novamente ou revise a configuração da IA.</p>
                            @endif
                        </div> <!-- .blueprint-details -->
                        </div> <!-- .prompt-log-item -->
                    @endforeach
                @else
                    <p>Nenhum blueprint encontrado. Crie novos prompts para prosseguir.</p>
                @endif
            @elseif($user->isFree())
                <h3>🆓 {{ __('dashboard.free_user_dashboard') }}</h3>
                <div style="background: rgba(59, 130, 246, 0.1); padding: 24px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.3); margin-bottom: 20px;">
                    <p style="margin-bottom: 16px; font-size: 1.1rem;">{{ __('dashboard.free_tier_welcome') }}</p>
                    <p style="margin-bottom: 16px;">{{ __('dashboard.free_tier_generator_info') }} <a href="{{ route('free.dashboard.prompt') }}" style="color: #60A5FA; text-decoration: none; font-weight: 600;">{{ __('dashboard.prompt_blueprint_generator') }}</a> {{ __('dashboard.create_new_templates') }}</p>
                    <p style="margin: 0; font-weight: 600; color: #FBBF24;">✨ {{ __('dashboard.upgrade_message') }}</p>
                </div>
            @endif
        </div>
    </div>
    <!-- Loading Behavior for Paid Dashboard -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');

            // Ensure the loading overlay is hidden when the page initially loads or reloads.
            if (loadingOverlay) {
                loadingOverlay.classList.remove('visible');
            }

            const paidForm = document.getElementById('paidAgentForm');
            if (paidForm) {
                paidForm.addEventListener('submit', function() {
                    if (loadingOverlay) {
                        loadingOverlay.classList.add('visible');
                    }
                });
            }
            // Show loading when executing a generated prompt
            document.querySelectorAll('.generated-prompt form').forEach(form => {
                form.addEventListener('submit', function() {
                    if (loadingOverlay) {
                        loadingOverlay.classList.add('visible');
                    }
                });
            });

            // --- Collapsible Prompt Bodies with localStorage Persistence ---
            function getOpenPrompts() {
                return JSON.parse(localStorage.getItem('openPrompts') || '[]');
            }
            function setOpenPrompts(list) {
                localStorage.setItem('openPrompts', JSON.stringify(list));
            }

            const executedPromptDiv = document.getElementById('executed_prompt_target');

            document.querySelectorAll('.prompt-header').forEach(header => {
                const promptDiv = header.closest('.generated-prompt');
                const body = promptDiv.querySelector('.prompt-body');
                const key = promptDiv.dataset.promptKey;
                let currentOpenPrompts = getOpenPrompts();

                // Initial state based on localStorage or if it's the executed prompt
                if (promptDiv.id === 'executed_prompt_target') {
                    body.classList.remove('hidden'); // Ensure executed prompt is open
                    if (!currentOpenPrompts.includes(key)) {
                        currentOpenPrompts.push(key);
                        setOpenPrompts(currentOpenPrompts);
                    }
                } else {
                    if (currentOpenPrompts.includes(key)) {
                        body.classList.remove('hidden');
                    } else {
                        body.classList.add('hidden'); // Ensure others are hidden if not in localStorage
                    }
                }

                header.addEventListener('click', () => {
                    body.classList.toggle('hidden');
                    currentOpenPrompts = getOpenPrompts(); // Get fresh list
                    if (body.classList.contains('hidden')) {
                        setOpenPrompts(currentOpenPrompts.filter(k => k !== key));
                    } else {
                        if (!currentOpenPrompts.includes(key)) {
                            currentOpenPrompts.push(key);
                            setOpenPrompts(currentOpenPrompts);
                        }
                    }
                });
            });

            // --- Scroll to Executed Prompt ---
            if (executedPromptDiv) {
                // Ensure its body is open (already handled by the logic above)
                const body = executedPromptDiv.querySelector('.prompt-body');
                if (body && body.classList.contains('hidden')) {
                     body.classList.remove('hidden'); // Failsafe
                }
                // Scroll after a brief delay to allow layout to settle
                setTimeout(() => {
                    executedPromptDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150); // Adjusted delay
            }
            // --- Collapsible Blueprints ---
            document.querySelectorAll('.blueprint-header').forEach(header => {
                const blueprintKey = header.dataset.blueprintKey;
                // Toggle details on header click
                header.addEventListener('click', () => {
                    const details = header.nextElementSibling;
                    if (details) {
                        details.classList.toggle('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>