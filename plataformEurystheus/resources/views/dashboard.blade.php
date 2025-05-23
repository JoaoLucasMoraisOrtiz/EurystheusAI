<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Eurystheus</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        .header h1 { color: #2c3e50; }
        .user-info { background: #ffffff; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .user-info h3 { margin-top: 0; color: #3498db; }
        .role { padding: 6px 14px; border-radius: 20px; font-size: 0.85em; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .role.admin { background: #e74c3c; color: white; }
        .role.payed_user { background: #2ecc71; color: white; }
        .role.free_user { background: #95a5a6; color: white; }
        .logout-btn { background: #e74c3c; color: white; padding: 10px 18px; border: none; border-radius: 5px; cursor: pointer; font-size: 0.9em; transition: background-color 0.3s; }
        .logout-btn:hover { background: #c0392b; }
        .admin-link { background: #3498db; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-size: 0.9em; transition: background-color 0.3s; }
        .admin-link:hover { background: #2980b9; }
        .content-section { background: #ffffff; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .content-section h3 { color: #3498db; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .prompt-log-item { border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 20px; border-radius: 6px; background-color: #f9f9f9; }
        .prompt-log-item h4 { margin-top: 0; color: #555; }
        .prompt-log-item p { font-size: 0.95em; line-height: 1.6; }
        .generated-prompt { background-color: #e9f5ff; padding: 15px; margin-top: 15px; border-radius: 4px; border-left: 3px solid #3498db; }
        .generated-prompt code { display: block; white-space: pre-wrap; background: #f0f0f0; padding: 10px; border-radius: 4px; font-size: 0.9em; }
        .placeholder-input { width: calc(100% - 22px); padding: 10px; margin-top: 5px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .execute-btn { background: #2ecc71; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; transition: background-color 0.3s; }
        .execute-btn:hover { background: #27ae60; }
        .execution-result { margin-top: 20px; padding: 15px; border-radius: 4px; background-color: #e8f6f3; border: 1px solid #d0e9e4; }
        .execution-result h5 { margin-top: 0; color: #1abc9c; }
        .execution-result pre { white-space: pre-wrap; word-wrap: break-word; background: #fdfefe; padding: 10px; border-radius: 3px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard</h1>
            <div>
                @if($user->isAdmin())
                    <a href="{{ route('admin.index') }}" class="admin-link">Admin Panel</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        <div class="user-info">
            <h3>Welcome, {{ $user->name }}!</h3>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> <span class="role {{ str_replace('_', '-', $user->role->value) }}">{{ $user->role->label() }}</span></p>
            <p><strong>Member since:</strong> {{ $user->created_at->format('M d, Y') }}</p>
        </div>

        @if($user->isPayed() || $user->isAdmin())
        <div class="content-section">
            <h3>Create New Prompt Blueprint</h3>
            <form id="paidAgentForm" method="POST" action="{{ route('free.dashboard.prompt') }}">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label for="scope_objective">Objective:</label>
                    <input type="text" id="scope_objective" name="scope[objective]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.objective') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="scope_constraints">Constraints/Limitations:</label>
                    <input type="text" id="scope_constraints" name="scope[constraints]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.constraints') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="scope_data">Available Data/Information:</label>
                    <input type="text" id="scope_data" name="scope[data]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.data') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="scope_audience">Target Audience/Stakeholders:</label>
                    <input type="text" id="scope_audience" name="scope[audience]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.audience') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="scope_output_format">Desired Output Format/Result:</label>
                    <input type="text" id="scope_output_format" name="scope[output_format]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.output_format') }}">
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="scope_deadlines">Deadlines/Success Metrics:</label>
                    <input type="text" id="scope_deadlines" name="scope[deadlines]" class="placeholder-input" style="width:100%;" required value="{{ old('scope.deadlines') }}">
                </div>
                <button type="submit" class="execute-btn" style="background-color: #3498db;">Generate Blueprint</button>
            </form>
        </div>
        @endif
        
        <div class="content-section">
            @if($user->isAdmin())
                <h3>Admin Features</h3>
                <p>You have administrative privileges. You can manage users and system settings.</p>
                {{-- Admins might also see paid user features --}}
            @endif

            @if($user->isPayed() || $user->isAdmin()) 
                <h3>Your Prompt Blueprints & Execution</h3>
                @if(isset($promptLogs) && $promptLogs->count() > 0)
                    @foreach($promptLogs as $log)
                        <div class="prompt-log-item">
                            <h4>Prompt Scope: {{ $log->user_scope }}</h4>
                            <p><small>Created: {{ $log->created_at->format('M d, Y H:i') }}</small></p>
                            @if($log->llmResponse && $log->llmResponse->generated_prompts)
                                @php
                                    $generatedPrompts = json_decode($log->llmResponse->generated_prompts, true);
                                @endphp
                                @if(is_array($generatedPrompts) && count($generatedPrompts) > 0)
                                    <p><strong>Generated Prompt Blueprints:</strong></p>
                                    @foreach($generatedPrompts as $index => $promptText)
                                        <div class="generated-prompt">
                                            <code>{{ $promptText }}</code>
                                            <form method="POST" action="{{ route('paid.dashboard.execute.prompt') }}" style="margin-top: 15px;">
                                                @csrf
                                                <input type="hidden" name="llm_response_id" value="{{ $log->llmResponse->id }}">
                                                <input type="hidden" name="prompt_index" value="{{ $index }}">
                                                
                                                @php
                                                    preg_match_all('/\{\{(.*?)\}\}/', $promptText, $matches);
                                                    $placeholders = array_map('trim', array_unique($matches[1]));
                                                @endphp

                                                @if(count($placeholders) > 0)
                                                    <p><strong>Fill in the placeholders:</strong></p>
                                                    @foreach($placeholders as $placeholder)
                                                        <div>
                                                            <label for="placeholder_{{ $log->id }}_{{ $index }}_{{ $placeholder }}">{{ ucfirst(str_replace('_', ' ', $placeholder)) }}:</label>
                                                            <input type="text" 
                                                                   id="placeholder_{{ $log->id }}_{{ $index }}_{{ $placeholder }}" 
                                                                   name="placeholders[{{ $placeholder }}]" 
                                                                   class="placeholder-input"
                                                                   value="{{ old('placeholders.'.$placeholder, session("executed_llm_response_id") == $log->llmResponse->id && session("executed_prompt_index") == $index && old('placeholders.'.$placeholder) === null ? '' : old('placeholders.'.$placeholder)) }}"
                                                                   required>
                                                        </div>
                                                    @endforeach
                                                @else
                                                     <p><small>This prompt has no placeholders to fill.</small></p>
                                                @endif
                                                <button type="submit" class="execute-btn">Execute Prompt</button>
                                            </form>

                                            @if(session('executed_llm_response_id') == $log->llmResponse->id && session('executed_prompt_index') == $index && session('execution_result'))
                                                <div class="execution-result">
                                                    <h5>Execution Result:</h5>
                                                    <pre>{{ session('execution_result') }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p>No generated prompts found for this log, or there was an issue decoding them.</p>
                                @endif
                            @else
                                <p>No LLM response recorded for this prompt log yet.</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p>You haven't created any prompt blueprints yet. Free users can create blueprints, and paid users can execute them here.</p>
                @endif
            @elseif($user->isFree())
                <h3>Free User Dashboard</h3>
                <p>Welcome to the free tier. You can use the <a href="{{ route('free.dashboard.prompt') }}">Prompt Blueprint Generator</a> to create new prompt templates.</p>
                <p>Upgrade to a paid plan to execute your generated prompts and access other premium features!</p>
                 {{-- Link to FreeDashboardController@show if it has a separate view, or include its form here --}}
                 {{-- For now, assuming the prompt creation is handled by FreeDashboardController and its associated routes --}}
                 <p>To create a new prompt blueprint, you would typically have a form here or on a dedicated page that posts to `{{ route('free.dashboard.prompt') }}`.</p>
            @endif
        </div>
    </div>
</body>
</html>