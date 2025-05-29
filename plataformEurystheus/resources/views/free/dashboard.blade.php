<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('dashboard.free_dashboard') }} - EurystheusAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased theme-dark bg-gray-900 text-gray-100 min-h-screen flex flex-col">
    <!-- Loading Overlay -->        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
            <div class="text-white text-lg font-semibold">{{ __('general.processing') }}...</div>
        </div>
    <!-- Header -->
    <header class="bg-gray-800 shadow-md">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-100">{{ __('dashboard.free_dashboard') }}</a>
            <div class="flex items-center space-x-4">
                @include('components.language-switcher')
                <form method="POST" action="{{ route('logout') }}" class="">
                    @csrf
                    <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors">
                        {{ __('general.logout') }}
                    </button>
                </form>
            </div>
        </nav>
    </header>

    <!-- Upgrade Banner for Free Users -->
    <div class="container mx-auto px-6 py-4">
        @if($activePromotion && $activePromotion->isValid())
        <div class="bg-gradient-to-r from-orange-500 via-yellow-500 to-orange-600 rounded-xl shadow-xl p-6 border-2 border-yellow-400 relative overflow-hidden">
            <!-- Animated background elements -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse"></div>
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-300/20 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-orange-300/20 rounded-full translate-y-12 -translate-x-12"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-2 drop-shadow-lg">
                        🚀 {{ __('dashboard.upgrade_banner_title') }}
                    </h3>
                    <p class="text-white/90 text-lg mb-3 drop-shadow">
                        {{ __('dashboard.upgrade_banner_subtitle') }}
                    </p>
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 text-sm text-white/80">
                        <div class="flex items-center gap-1">
                            <span class="text-white">✓</span>
                            <span>{{ __('dashboard.feature_unlimited_prompts') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-white">✓</span>
                            <span>{{ __('dashboard.feature_advanced_ai') }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-white">✓</span>
                            <span>{{ __('dashboard.feature_priority_support') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('marketing.sales') }}" 
                       class="inline-flex items-center gap-3 bg-white text-orange-600 font-bold py-4 px-8 rounded-xl text-lg shadow-xl hover:shadow-2xl transform transition-all duration-300 hover:scale-105 hover:bg-gray-50 group">
                        <span class="text-2xl group-hover:animate-bounce">⚡</span>
                        <div class="text-left">
                            <div class="font-extrabold">{{ __('dashboard.upgrade_now') }}</div>
                            <div class="text-sm font-normal text-orange-500">
                                <span class="line-through opacity-60">{{ $activePromotion->formatted_original_price }}</span>
                                <span class="ml-2">{{ $activePromotion->formatted_discounted_price }}</span>
                            </div>
                            <div class="text-xs text-orange-600 font-mono bg-orange-100 px-2 py-1 rounded mt-1">
                                Código: {{ $activePromotion->code }}
                            </div>
                        </div>
                        <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Special offer badge with countdown -->
            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full transform rotate-12 shadow-lg animate-pulse">
                {{ $activePromotion->discount_percentage }}% OFF
            </div>
            
            <!-- Countdown Timer -->
            @if($activePromotion->valid_until)
            <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-black/80 text-white px-4 py-2 rounded-lg text-sm font-mono"
                 x-data="{ 
                     time: '',
                     endDate: new Date('{{ $activePromotion->valid_until->toISOString() }}'),
                     countdown() {
                         const now = new Date();
                         const diff = this.endDate - now;
                         
                         if (diff <= 0) {
                             this.time = 'EXPIRADO';
                             return;
                         }
                         
                         const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                         const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                         const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                         const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                         
                         if (days > 0) {
                             this.time = `${days}d ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                         } else {
                             this.time = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                         }
                     }
                 }"
                 x-init="countdown(); setInterval(countdown, 1000)">
                <div class="flex items-center gap-2">
                    <span class="text-red-400">⏰</span>
                    <span>{{ __('dashboard.offer_ends_in') }}:</span>
                    <span x-text="time" class="font-bold text-yellow-400"></span>
                </div>
            </div>
            @endif
        </div>
        @else
        <!-- Fallback banner when no promotion is active -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-xl p-6 border-2 border-purple-400 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-2 drop-shadow-lg">
                        🚀 {{ __('dashboard.upgrade_banner_title') }}
                    </h3>
                    <p class="text-white/90 text-lg mb-3 drop-shadow">
                        {{ __('dashboard.upgrade_banner_subtitle') }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('marketing.sales') }}" 
                       class="inline-flex items-center gap-3 bg-white text-purple-600 font-bold py-4 px-8 rounded-xl text-lg shadow-xl hover:shadow-2xl transform transition-all duration-300 hover:scale-105 hover:bg-gray-50 group">
                        <span class="text-2xl group-hover:animate-bounce">⚡</span>
                        <div class="text-left">
                            <div class="font-extrabold">{{ __('dashboard.upgrade_now') }}</div>
                            <div class="text-sm font-normal text-purple-500">{{ __('dashboard.starting_at_price') }}</div>
                        </div>
                        <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Secondary Urgency Banner -->
        <!-- <div class="mt-4 bg-gradient-to-r from-red-600 to-pink-600 rounded-lg p-4 border border-red-400 relative overflow-hidden" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-2xl animate-pulse">🔥</div>
                    <div>
                        <div class="text-white font-bold text-sm">
                            {{ __('dashboard.special_launch_offer') }}
                        </div>
                        <div class="text-white/90 text-xs">
                            {{ __('dashboard.discount_expires_soon') }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-white text-right">
                        <div class="text-lg font-bold line-through opacity-60">R$ 59</div>
                        <div class="text-xl font-extrabold">R$ 29</div>
                    </div>
                    <button @click="show = false" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div> -->
    </div>

    <!-- Floating Upgrade Prompt -->
    <div class="fixed bottom-6 right-6 z-50" 
         x-data="{ showFloating: false }" 
         x-init="setTimeout(() => showFloating = true, 10000)"
         x-show="showFloating" 
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="transform translate-y-full opacity-0"
         x-transition:enter-end="transform translate-y-0 opacity-100">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl shadow-2xl p-4 max-w-sm border border-purple-400 relative">
            <button @click="showFloating = false" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors">
                ×
            </button>
            <div class="flex items-center gap-3">
                <div class="text-3xl animate-bounce">💎</div>
                <div>
                    <div class="text-white font-bold text-sm">
                        {{ __('dashboard.upgrade_reminder') }}
                    </div>
                    <div class="text-white/90 text-xs mb-2">
                        {{ __('dashboard.unlock_premium_features') }}
                    </div>
                    <a href="{{ route('marketing.sales') }}" 
                       class="inline-block bg-white text-purple-600 font-bold py-1 px-3 rounded-lg text-xs hover:bg-gray-100 transition-colors">
                        {{ __('dashboard.try_premium') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <main class="container mx-auto px-6 py-10 flex-grow">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Form Card -->
            <div class="bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-100 mb-6">{{ __('dashboard.new_agent') }}</h2>
                <form id="agentForm" method="POST" action="{{ route('free.dashboard.prompt') }}" class="space-y-4">
                    @csrf
                    @php
                    $fields = [
                        'objective' => __('dashboard.prompt_objective'),
                        'constraints' => __('dashboard.constraints'),
                        'data' => __('dashboard.available_data'),
                        'audience' => __('dashboard.target_audience'),
                        'output_format' => __('dashboard.output_format'),
                        'deadlines' => __('dashboard.deadlines'),
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
                        {{ __('dashboard.generate_prompt') }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>

            <!-- Recent Prompts & User Info -->
            <div class="space-y-8">
                <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
                    <h2 class="text-2xl font-semibold text-gray-100 mb-4">{{ __('dashboard.recent_prompts') }}</h2>
                    @if($recentPrompts->isEmpty())
                        <p class="text-gray-400">{{ __('dashboard.no_prompts_yet') }}</p>
                    @else
                        <div class="space-y-2">
                            @foreach($recentPrompts as $prompt)
                                @php
                                    $promptInputData = json_decode($prompt->content, true);
                                    $buttonTitle = __('dashboard.view_details'); // Default
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
                                                        $modalData['generated_prompts_array'][] = is_string($gp) ? trim($gp) : __('dashboard.invalid_prompt_content');
                                                    }
                                                }
                                                // If $promptsArray is empty (e.g., from '[]'), generated_prompts_array will remain empty, which is fine.
                                            } else {
                                                // Invalid JSON for prompts, store it as a fallback
                                                $modalData['raw_reasoning_fallback'] = "**" . __('dashboard.generated_prompts_unexpected_format') . ":**\\n" . trim($generatedPromptsJson);
                                            }
                                        }

                                        if (empty($modalData['chain_of_thought']) && empty($modalData['generated_prompts_array']) && empty($modalData['raw_reasoning_fallback'])) {
                                            $modalData['error_message'] = __('dashboard.no_model_info_recorded');
                                        }

                                    } else {
                                        $modalData['error_message'] = __('dashboard.model_response_not_loaded');
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
                <h3 class="text-lg md:text-xl font-semibold">{{ __('dashboard.prompt_details') }}</h3>
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
            &copy; {{ date('Y') }} EurystheusAI. {{ __('general.all_rights_reserved') }}
        </div>
    </footer>
    <!-- Scripts -->
    <script>
        // Translation variables for JavaScript
        const translations = {
            'error_loading_details': @json(__('dashboard.error_loading_details')),
            'chain_of_thought_explanation': @json(__('dashboard.chain_of_thought_explanation')),
            'generated_prompts': @json(__('dashboard.generated_prompts')),
            'prompt_step': @json(__('dashboard.prompt_step')),
            'copy_prompt': @json(__('dashboard.copy_prompt')),
            'copied': @json(__('general.copied')),
            'copy_failed': @json(__('dashboard.copy_failed')),
            'raw_data_fallback': @json(__('dashboard.raw_data_fallback')),
            'no_content_to_display': @json(__('dashboard.no_content_to_display'))
        };
            'no_content_to_display': @json(__('dashboard.no_content_to_display'))
        };

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('agentForm');
            const submitBtn = form.querySelector('button[type=submit]');
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>{{ __("general.loading") }}...';
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
                        modalDynamicContentElement.innerHTML = '<p class="text-red-400">' + translations.error_loading_details + '</p>';
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
                                <h4 class="text-md font-semibold text-gray-300 mb-2">${translations.chain_of_thought_explanation}:</h4>
                                <pre class="bg-gray-900 p-3 rounded-md whitespace-pre-wrap break-words text-sm border border-gray-700">${escapeHtml(modalData.chain_of_thought)}</pre>
                            </div>
                        `;
                    }

                    if (modalData.generated_prompts_array && modalData.generated_prompts_array.length > 0) {
                        htmlContent += '<div><h4 class="text-md font-semibold text-gray-300 mb-3">' + translations.generated_prompts + ':</h4><div class="space-y-4">';
                        modalData.generated_prompts_array.forEach((promptText, index) => {
                            const promptId = `prompt-text-${Date.now()}-${index}`;
                            htmlContent += `
                                <div class="bg-gray-750 p-1 rounded-md border border-gray-650 shadow">
                                    <div class="flex justify-between items-center p-2 bg-gray-700 rounded-t-md">
                                        <span class="font-medium text-gray-200">${translations.prompt_step} ${index + 1} (${translations.prompt_step} ${index + 1})</span>
                                        <button 
                                            type="button" 
                                            class="copy-button bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold py-1 px-3 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400"
                                            data-clipboard-target="#${promptId}"
                                            title="${translations.copy_prompt} ${index + 1}"
                                        >
                                            ${translations.copy_prompt}
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
                                <h4 class="text-md font-semibold text-gray-300 mb-2">${translations.raw_data_fallback}:</h4>
                                <pre class="bg-gray-900 p-3 rounded-md whitespace-pre-wrap break-words text-sm border border-gray-700">${escapeHtml(modalData.raw_reasoning_fallback)}</pre>
                            </div>
                        `;
                    }
                    
                    if (!htmlContent) {
                        htmlContent = '<p class="text-gray-400">' + translations.no_content_to_display + '</p>';
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
                            this.innerHTML = translations.copied + '! <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                            setTimeout(() => {
                                this.innerHTML = originalText;
                            }, 2000);
                        }).catch(err => {
                            console.error('Falha ao copiar texto: ', err);
                            alert(translations.copy_failed);
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>
