<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Free - EurystheusAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
</head>
<body class="font-sans antialiased theme-dark bg-gray-900 text-gray-100 min-h-screen flex flex-col">
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="text-white text-lg font-semibold">Processando...</div>
    </div>
    <!-- Header -->
    <header class="bg-gray-800 shadow-md">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-100">Dashboard Free</a>
            <form method="POST" action="{{ route('logout') }}" class="">
                @csrf
                <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                    Sair
                </button>
            </form>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-10 flex-grow">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form Card -->
            <div class="bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-100 mb-6">Novo Agente</h2>
                <form id="agentForm" method="POST" action="{{ route('free.dashboard.prompt') }}" class="space-y-4">
                    @csrf
                    @php
                    $fields = [
                        'objective' => 'Objetivo Principal',
                        'constraints' => 'Restrições',
                        'data' => 'Dados Disponíveis',
                        'audience' => 'Público-Alvo',
                        'output_format' => 'Formato de Resposta',
                        'deadlines' => 'Prazos / Métricas',
                    ];
                    @endphp
                    @foreach($fields as $key => $label)
                        <div>
                            <label for="scope_{{ $key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                            <input id="scope_{{ $key }}" name="scope[{{ $key }}]" type="text" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-600 rounded-lg bg-gray-700 text-gray-100 focus:ring-2 focus:ring-yellow-400 focus:outline-none">
                        </div>
                    @endforeach
                    <button type="submit" class="w-full flex justify-center items-center mt-4 bg-gradient-to-r from-orange-500 to-orange-600 dark:from-yellow-400 dark:to-yellow-500 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-yellow-400">
                        Gerar Prompt
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>

            <!-- Recent Prompts & User Info -->
            <div class="space-y-8">
                <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
                    <h2 class="text-2xl font-semibold text-gray-100 mb-4">Últimos Prompts</h2>
                    @if($recentPrompts->isEmpty())
                        <p class="text-gray-400">Nenhum prompt gerado ainda.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($recentPrompts as $prompt)
                                @php
                                    $promptInputData = json_decode($prompt->content, true);
                                    $buttonTitle = 'Ver Detalhes'; // Default
                                    if (is_array($promptInputData) && isset($promptInputData['objective']) && is_string($promptInputData['objective'])) {
                                        $buttonTitle = $promptInputData['objective'];
                                    } elseif (is_array($promptInputData) && !empty($promptInputData)) {
                                        $firstValue = reset($promptInputData);
                                        if (is_string($firstValue)) {
                                            $buttonTitle = $firstValue;
                                        }
                                    }

                                    // Prepare data for the modal in a structured way
                                    $modalData = [
                                        'chain_of_thought' => null,
                                        'generated_prompts_array' => [],
                                        'raw_reasoning_fallback' => null,
                                        'error_message' => null
                                    ];

                                    if ($prompt->relationLoaded('llmResponse') && $prompt->llmResponse) {
                                        $chainOfThought = $prompt->llmResponse->llm_reasoning;
                                        if (!empty(trim($chainOfThought))) {
                                            $modalData['chain_of_thought'] = trim($chainOfThought);
                                        }

                                        $generatedPromptsJson = $prompt->llmResponse->generated_prompts;
                                        if (!empty(trim($generatedPromptsJson))) {
                                            $cleanedPromptsJson = preg_replace('/^```json\\\\s*|\\\\s*```$/s', '', $generatedPromptsJson);
                                            $promptsArray = json_decode($cleanedPromptsJson, true);

                                            if (json_last_error() === JSON_ERROR_NONE && is_array($promptsArray)) {
                                                if (!empty($promptsArray)) {
                                                    foreach ($promptsArray as $gp) {
                                                        $modalData['generated_prompts_array'][] = is_string($gp) ? trim($gp) : '(Conteúdo do prompt inválido)';
                                                    }
                                                }
                                                // If $promptsArray is empty (e.g., from '[]'), generated_prompts_array will remain empty, which is fine.
                                            } else {
                                                // Invalid JSON for prompts, store it as a fallback
                                                $modalData['raw_reasoning_fallback'] = "**Prompts Gerados (formato inesperado):**\\n" . trim($generatedPromptsJson);
                                            }
                                        }

                                        if (empty($modalData['chain_of_thought']) && empty($modalData['generated_prompts_array']) && empty($modalData['raw_reasoning_fallback'])) {
                                            $modalData['error_message'] = 'Nenhuma informação (raciocínio ou prompts) registrada para esta resposta do modelo.';
                                        }

                                    } else {
                                        $modalData['error_message'] = 'Detalhes da resposta do modelo não carregados ou ausentes.';
                                    }
                                @endphp
                                <button type="button"
                                    class="w-full text-left px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-md prompt-button truncate"
                                    title="{{ e($buttonTitle) }}"
                                    data-modal-info='@json($modalData)'>
                                    {{ \Illuminate\Support\Str::limit($buttonTitle, 35) }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Prompt Details -->
    <div id="promptModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 hidden z-50 p-4">
        <div class="bg-gray-800 text-gray-100 rounded-lg shadow-xl w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 md:p-5 border-b border-gray-700 sticky top-0 bg-gray-800 z-10">
                <h3 class="text-lg md:text-xl font-semibold">Detalhes do Prompt Gerado</h3>
                <button id="modalClose" class="text-gray-400 hover:text-gray-200 text-3xl leading-none p-1">&times;</button>
            </div>
            <div id="modalDynamicContent" class="p-4 md:p-6 overflow-y-auto space-y-6">
                {{-- JS will populate this --}}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 py-4">
        <div class="container mx-auto px-6 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} EurystheusAI. Todos os direitos reservados.
        </div>
    </footer>
    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('agentForm');
            const submitBtn = form.querySelector('button[type=submit]');
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Carregando...';
            });
            
            const modal = document.getElementById('promptModal');
            const modalDynamicContentElement = document.getElementById('modalDynamicContent');
            const closeBtn = document.getElementById('modalClose');
            
            document.querySelectorAll('.prompt-button').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modalInfoRaw = btn.getAttribute('data-modal-info');
                    let modalData;
                    try {
                        // Parse JSON from data-modal-info attribute
                        modalData = JSON.parse(modalInfoRaw);
                    } catch (e) {
                        console.error('Error parsing modal data:', e);
                        modalDynamicContentElement.innerHTML = '<p class="text-red-400">Erro ao carregar os detalhes. Tente novamente.</p>';
                        modal.classList.remove('hidden');
                        return;
                    }

                    let htmlContent = '';

                    if (modalData.error_message) {
                        htmlContent += `<p class="text-orange-400">${modalData.error_message}</p>`;
                    }

                    if (modalData.chain_of_thought) {
                        htmlContent += `
                            <div>
                                <h4 class="text-md font-semibold text-gray-300 mb-2">Explicação do Raciocínio (Chain of Thought):</h4>
                                <pre class="bg-gray-900 p-3 rounded-md whitespace-pre-wrap break-words text-sm border border-gray-700">${escapeHtml(modalData.chain_of_thought)}</pre>
                            </div>
                        `;
                    }

                    if (modalData.generated_prompts_array && modalData.generated_prompts_array.length > 0) {
                        htmlContent += '<div><h4 class="text-md font-semibold text-gray-300 mb-3">Prompts Gerados:</h4><div class="space-y-4">';
                        modalData.generated_prompts_array.forEach((promptText, index) => {
                            const promptId = `prompt-text-${Date.now()}-${index}`;
                            htmlContent += `
                                <div class="bg-gray-750 p-1 rounded-md border border-gray-650 shadow">
                                    <div class="flex justify-between items-center p-2 bg-gray-700 rounded-t-md">
                                        <span class="font-medium text-gray-200">Prompt ${index + 1} (Etapa ${index + 1})</span>
                                        <button 
                                            type="button" 
                                            class="copy-button bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-1 px-3 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400"
                                            data-clipboard-target="#${promptId}"
                                            title="Copiar Prompt ${index + 1}"
                                        >
                                            Copiar
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor"><path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"/><path d="M3 5a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2-1a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1V5a1 1 0 00-1-1H5z"/></svg>
                                        </button>
                                    </div>
                                    <pre id="${promptId}" class="p-3 whitespace-pre-wrap break-words text-sm text-gray-200">${escapeHtml(promptText)}</pre>
                                </div>
                            `;
                        });
                        htmlContent += '</div></div>';
                    }

                    if (modalData.raw_reasoning_fallback) {
                         htmlContent += `
                            <div>
                                <h4 class="text-md font-semibold text-gray-300 mb-2">Dados Brutos (Fallback):</h4>
                                <pre class="bg-gray-900 p-3 rounded-md whitespace-pre-wrap break-words text-sm border border-gray-700">${escapeHtml(modalData.raw_reasoning_fallback)}</pre>
                            </div>
                        `;
                    }
                    
                    if (!htmlContent) {
                        htmlContent = '<p class="text-gray-400">Nenhum conteúdo para exibir.</p>';
                    }

                    modalDynamicContentElement.innerHTML = htmlContent;
                    initializeClipboard(); // Initialize clipboard for new buttons
                    modal.classList.remove('hidden');
                });
            });
            
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modalDynamicContentElement.innerHTML = ''; // Clear content on close
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modalDynamicContentElement.innerHTML = ''; // Clear content on close
                }
            });

            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function initializeClipboard() {
                document.querySelectorAll('.copy-button').forEach(button => {
                    // Remove any existing listeners to prevent multiple initializations
                    const newButton = button.cloneNode(true);
                    button.parentNode.replaceChild(newButton, button);

                    newButton.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-clipboard-target');
                        const textToCopy = document.querySelector(targetId).innerText;
                        navigator.clipboard.writeText(textToCopy).then(() => {
                            const originalText = this.innerHTML;
                            this.innerHTML = 'Copiado! <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                            setTimeout(() => {
                                this.innerHTML = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('Falha ao copiar texto: ', err);
                            alert('Falha ao copiar texto.');
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>
