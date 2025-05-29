<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('marketing.page_title') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Removed link to marketing.css as styles are incorporated or assumed to be in app.css --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> {{-- Added Alpine.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure Tailwind dark mode is activated if not by OS preference */
        /* html.dark { } */

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }

            100% {
                background-position: calc(200px + 100%) 0;
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .shimmer {
            background: linear-gradient(90deg,
                    rgba(255, 255, 255, 0) 0%,
                    rgba(255, 255, 255, 0.2) 20%,
                    rgba(255, 255, 255, 0.5) 60%,
                    rgba(255, 255, 255, 0));
            animation: shimmer 2s infinite;
            background-size: 200px 100%;
        }

        /* Updated hero-bg for the dark theme with gold/orange/yellow accents */
        .hero-bg-custom {
            background: linear-gradient(135deg, #0D0D0D 0%, #201500 15%, #332200 30%, #FFBF00 45%, #FFA500 55%, #FF8C00 65%, #4D2600 80%, #1A1200 90%, #0D0D0D 100%);
            background-size: 300% 300%;
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Additional style for golden text shadow to make text pop on dark backgrounds */
        .text-gold-glow {
            text-shadow: 0 0 8px rgba(255, 193, 7, 0.5), 0 0 12px rgba(255, 165, 0, 0.3);
        }

        .dark .dark\:text-glow {
            /* Apply this class for glowing text in dark mode */
            text-shadow: 0 0 5px theme('colors.amber.300'), 0 0 10px theme('colors.amber.500 / 0.5');
        }

        .dark .dark\:button-glow {
            box-shadow: 0 0 15px 0px theme('colors.amber.500 / 0.6'), 0 0 5px 0px theme('colors.orange.500 / 0.4');
        }

        .dark .dark\:card-glow {
            box-shadow: 0 0 20px 0px theme('colors.amber.600 / 0.3'), 0 2px 10px 0px theme('colors.orange.700 / 0.2');
            border-color: theme('colors.amber.500 / 0.6');
        }
    </style>
</head>

<body class="font-sans antialiased dark"> {{-- Applied 'dark' class to body to activate dark mode --}}
    <div class="min-h-screen bg-gradient-to-b from-neutral-950 to-neutral-900 dark:from-neutral-950 dark:to-black">
        <header class="bg-neutral-900/90 dark:bg-black/80 shadow-lg backdrop-blur-sm sticky top-0 z-50 border-b border-neutral-700/50 dark:border-amber-600/30">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div>
                    <a href="{{ route('marketing.home') }}" class="text-xl font-bold text-amber-400 dark:text-amber-400 hover:text-amber-300 dark:hover:text-amber-300 transition-colors dark:text-glow">
                        🏛️ EurystheusAI
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="{{ route('login') }}" onclick="trackButtonClick('header_login')" class="text-gray-300 dark:text-gray-300 hover:text-amber-400 dark:hover:text-amber-300 transition-colors">{{ __('auth.login') }}</a>
                    <a href="{{ route('register') }}" onclick="trackButtonClick('header_register')" class="bg-amber-500 hover:bg-amber-600 text-black px-4 py-2 rounded-lg font-medium transition-colors shadow-md dark:button-glow">
                        {{ __('auth.register') }}
                    </a>
                    @include('components.language-switcher')
                </div>
            </nav>
        </header>

        <main>
            <section class="hero-bg-custom py-20 md:py-32 text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-black/40"></div> {{-- Slightly stronger overlay for contrast --}}
                <div class="container mx-auto px-6 relative z-10">
                    <div class="max-w-4xl mx-auto text-center">
                        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight text-gold-glow">
                            {{ __('marketing.hero_title') }}
                        </h1>
                        <p class="text-xl md:text-2xl mb-8 opacity-90">
                            {{ __('marketing.hero_subtitle') }}
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-amber-400 text-xl">✓</span>
                                <span class="text-lg">{{ __('marketing.hero_feature_1') }}</span>
                            </div>
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-amber-400 text-xl">✓</span>
                                <span class="text-lg">{{ __('marketing.hero_feature_2') }}</span>
                            </div>
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-amber-400 text-xl">✓</span>
                                <span class="text-lg">{{ __('marketing.hero_feature_3') }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            <a href="{{ route('register') }}"
                                onclick="trackButtonClick('hero_try_free')"
                                class="border-2 border-amber-400 text-amber-400 font-semibold py-4 px-8 rounded-lg text-lg hover:bg-amber-400 hover:text-black transition-all duration-200">
                                {{ __('marketing.hero_button_try_free') }}
                            </a>
                            <a href="{{ route('marketing.sales') }}"
                                onclick="trackButtonClick('hero_see_plans')"
                                class="border-2 border-amber-400 from-amber-400 to-orange-500 text-amber-400 font-bold py-4 px-8 rounded-lg text-lg shadow-xl hover:shadow-2xl transform transition-all duration-200 hover:scale-105 shimmer dark:button-glow">
                                {{ __('marketing.hero_button_see_plans') }}
                            </a>
                        </div>
                        <p class="text-sm opacity-75 mt-4">
                            {{ __('marketing.hero_sub_cta') }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="py-16 bg-neutral-900 dark:bg-neutral-900">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-8">
                        {{ __('marketing.trusted_by_heading') }}
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-amber-400 dark:text-amber-400 dark:text-glow">10k+</div>
                            <div class="text-sm text-gray-300 dark:text-gray-400">{{ __('marketing.stats_prompts_generated_label') }}</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-amber-400 dark:text-amber-400 dark:text-glow">500+</div>
                            <div class="text-sm text-gray-300 dark:text-gray-400">{{ __('marketing.stats_active_users_label') }}</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-amber-400 dark:text-amber-400 dark:text-glow">98%</div>
                            <div class="text-sm text-gray-300 dark:text-gray-400">{{ __('marketing.stats_satisfaction_label') }}</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-amber-400 dark:text-amber-400 dark:text-glow">24/7</div>
                            <div class="text-sm text-gray-300 dark:text-gray-400">{{ __('marketing.stats_availability_label') }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-20 bg-neutral-950 dark:bg-neutral-950">
                <div class="container mx-auto px-6">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-bold text-white dark:text-white mb-4 dark:text-glow">
                            {{ __('marketing.why_title') }}
                        </h2>
                        <p class="text-xl text-gray-300 dark:text-gray-400 max-w-2xl mx-auto">
                            {{ __('marketing.why_subtitle') }}
                        </p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                        <div class="text-center p-8 rounded-xl bg-neutral-800/50 dark:bg-neutral-850/40 float-animation border border-amber-500/30 dark:card-glow">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-xl font-bold text-white dark:text-white mb-3">{{ __('marketing.why_card_1_title') }}</h3>
                            <p class="text-gray-300 dark:text-gray-400">
                                {{ __('marketing.why_card_1_desc') }}
                            </p>
                        </div>
                        <div class="text-center p-8 rounded-xl bg-neutral-800/50 dark:bg-neutral-850/40 float-animation border border-amber-500/30 dark:card-glow" style="animation-delay: 0.5s;">
                            <div class="text-5xl mb-4">⚡</div>
                            <h3 class="text-xl font-bold text-white dark:text-white mb-3">{{ __('marketing.why_card_2_title') }}</h3>
                            <p class="text-gray-300 dark:text-gray-400">
                                {{ __('marketing.why_card_2_desc') }}
                            </p>
                        </div>
                        <div class="text-center p-8 rounded-xl bg-neutral-800/50 dark:bg-neutral-850/40 float-animation border border-amber-500/30 dark:card-glow" style="animation-delay: 1s;">
                            <div class="text-5xl mb-4">🏆</div>
                            <h3 class="text-xl font-bold text-white dark:text-white mb-3">{{ __('marketing.why_card_3_title') }}</h3>
                            <p class="text-gray-300 dark:text-gray-400">
                                {{ __('marketing.why_card_3_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-20 bg-neutral-900 dark:bg-neutral-900 text-white">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6 dark:text-glow">
                        {{ __('marketing.final_cta_heading') }}
                    </h2>
                    <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
                        {{ __('marketing.final_cta_subheading') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="{{ route('register') }}"
                            onclick="trackButtonClick('final_cta_free')"
                            class="bg-amber-500 text-black font-bold py-4 px-8 rounded-lg text-lg shadow-xl hover:bg-amber-600 hover:shadow-2xl transform transition-all duration-200 hover:scale-105 dark:button-glow">
                            {{ __('marketing.final_cta_button_start_free') }}
                        </a>
                        <a href="{{ route('marketing.sales') }}"
                            onclick="trackButtonClick('final_cta_premium')"
                            class="border-2 border-amber-500 text-amber-500 font-semibold py-4 px-8 rounded-lg text-lg hover:bg-amber-500 hover:text-black transition-all duration-200">
                            {{ __('marketing.final_cta_button_go_premium') }}
                        </a>
                    </div>
                    <p class="text-sm opacity-75 mt-6">
                        {{ __('marketing.final_cta_guarantee_info') }}
                    </p>
                </div>
            </section>
        </main>

        <footer class="bg-neutral-900/90 dark:bg-black/80">
            <div class="container mx-auto px-6 py-8">
                <div class="text-center">
                    <p class="text-gray-400 dark:text-gray-400">&copy; {{ date('Y') }} EurystheusAI. {{ __('marketing.footer_copyright') }}</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">{{ __('marketing.footer_tagline_text') }}</p>
                </div>
            </div>
        </footer>
    </div>

    {{-- Analytics and Tracking JavaScript --}}
    <script>
        // Track page view on load
        document.addEventListener('DOMContentLoaded', function() {
            trackPageView('home');
        });

        // Function to track page views
        function trackPageView(page) {
            fetch('/api/analytics/page-view', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    page: page,
                    timestamp: new Date().toISOString()
                })
            }).catch(error => console.log('Analytics error:', error));
        }

        // Function to track button clicks
        function trackButtonClick(element) {
            fetch('/api/analytics/button-click', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    element: element,
                    page: 'home',
                    timestamp: new Date().toISOString()
                })
            }).catch(error => console.log('Analytics error:', error));
        }

        // Track scroll depth
        let maxScrollDepth = 0;
        window.addEventListener('scroll', function() {
            const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
            if (scrollPercent > maxScrollDepth) {
                maxScrollDepth = scrollPercent;
                if (maxScrollDepth >= 25 && maxScrollDepth < 50) {
                    // Track only once per threshold
                    if (!window.scrolled25) {
                        trackButtonClick('scroll_25_percent');
                        window.scrolled25 = true;
                    }
                } else if (maxScrollDepth >= 50 && maxScrollDepth < 75) {
                    if (!window.scrolled50) {
                        trackButtonClick('scroll_50_percent');
                        window.scrolled50 = true;
                    }
                } else if (maxScrollDepth >= 75) {
                    if (!window.scrolled75) {
                        trackButtonClick('scroll_75_percent');
                        window.scrolled75 = true;
                    }
                }
            }
        });
    </script>

    {{-- app.js is now loaded via @vite in the head --}}
    {{-- <script src="{{ asset('js/marketing.js') }}"></script> --}} {{-- Assuming this is part of Vite bundle or not strictly needed for this visual pass --}}
</body>

</html>