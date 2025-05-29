<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('marketing.sales_page_title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link href="{{ asset('css/marketing.css') }}" rel="stylesheet"> --}} {{-- Assuming styles will be handled by app.css or inline --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-orbitron {
            font-family: 'Orbitron', sans-serif;
        }
        .dark .dark\:bg-black {
            background-color: #000;
        }
        .dark .dark\:bg-gray-900 {
            background-color: #111827; /* Tailwind gray-900 */
        }
        .dark .dark\:bg-gray-800 {
            background-color: #1f2937; /* Tailwind gray-800 */
        }
        .dark .dark\:text-white {
            color: #fff;
        }
        .dark .dark\:text-gray-300 {
            color: #d1d5db; /* Tailwind gray-300 */
        }
        .dark .dark\:text-gray-400 {
            color: #9ca3af; /* Tailwind gray-400 */
        }
        .dark .dark\:text-amber-400 {
            color: #fbbF24; /* Tailwind amber-400 */
        }
        .dark .dark\:border-gray-700 {
            border-color: #374151; /* Tailwind gray-700 */
        }
        .dark .dark\:hover\:text-amber-300 {
            color: #fcd34d; /* Tailwind amber-300 */
        }
         .dark .dark\:hover\:bg-gray-600 {
            background-color: #4b5563; /* Tailwind gray-600 */
        }
        .dark .dark\:focus\:ring-amber-400 {
            --tw-ring-color: #fbbF24;
        }
        .dark .dark\:bg-yellow-400 {
            background-color: #fbbF24;
        }
        .dark .dark\:text-gray-900 {
            color: #111827;
        }
         .dark .dark\:border-yellow-500 {
            border-color: #f59e0b; /* Tailwind yellow-500 */
        }
        .dark .dark\:hover\:bg-yellow-500 {
            background-color: #f59e0b;
        }
         .dark .dark\:hover\:bg-black {
            background-color: #000;
        }
    </style>
</head>
<body class="font-sans antialiased dark bg-black text-white">
    <div class="min-h-screen">
        <header class="bg-gray-900 shadow-md sticky top-0 z-50">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div>
                    <a href="{{ route('marketing.home') }}" class="text-2xl font-orbitron font-bold text-amber-400 hover:text-amber-300 transition-colors">
                        {{ __('marketing.sales_header_brand') }}
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-amber-400 transition-colors">{{ __('marketing.nav_login') }}</a>
                    <a href="{{ route('register') }}" class="bg-amber-400 hover:bg-amber-500 text-black font-semibold py-2 px-4 rounded-md transition-colors">{{ __('marketing.nav_signup') }}</a>
                    <x-language-switcher />
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-6 py-12">
            <section class="text-center mb-16">
                <h1 class="text-5xl font-orbitron font-extrabold text-white">
                    {{ __('marketing.sales_main_heading_1') }} <span class="text-amber-400">{{ __('marketing.sales_main_heading_2') }}</span>.
                </h1>
                <p class="mt-6 text-xl text-gray-300 max-w-3xl mx-auto">
                    {!! __('marketing.sales_main_subheading') !!}
                </p>
            </section>

            <section class="grid md:grid-cols-3 gap-8 max-w-7xl mx-auto">
                {{-- Free Plan --}}
                <div class="bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700 flex flex-col transform hover:scale-105 transition-transform duration-300">
                    <h2 class="text-3xl font-orbitron font-bold text-white mb-2">{{ __('marketing.sales_plan_apprentice_title') }}</h2>
                    <p class="text-amber-400 text-5xl font-extrabold mb-4">{{ __('marketing.sales_plan_apprentice_price') }}</p>
                    <p class="text-gray-400 mb-6 text-sm">{{ __('marketing.sales_plan_apprentice_desc') }}</p>
                    <ul class="text-gray-300 space-y-3 mb-8 flex-grow">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_apprentice_feat1') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_apprentice_feat2') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_apprentice_feat3') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_apprentice_feat4') }}</li>
                    </ul>
                    <a href="{{ route('register') }}?plan=free" onclick="trackButtonClick('sales_free_plan')" class="mt-auto w-full text-center bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-150">
                        {{ __('marketing.sales_plan_apprentice_cta') }}
                    </a>
                </div>

                {{-- Paid Plan (Most Popular) --}}
                <div class="bg-amber-400 text-gray-900 p-8 rounded-lg shadow-2xl border-2 border-amber-500 relative flex flex-col transform scale-105 hover:scale-110 transition-transform duration-300">
                    <span class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-900 text-amber-400 px-4 py-1 text-sm font-semibold rounded-full shadow-lg">{{ __('marketing.sales_plan_hero_badge') }}</span>
                    <h2 class="text-3xl font-orbitron font-bold mb-2">{{ __('marketing.sales_plan_hero_title') }}</h2>
                    <div class="mb-4">
                        <span class="text-5xl font-extrabold">{{ __('marketing.sales_plan_hero_price') }}</span>
                        <span class="text-lg opacity-90">{{ __('marketing.sales_plan_hero_period') }}</span>
                    </div>
                    <p class="opacity-90 mb-6 text-sm">{{ __('marketing.sales_plan_hero_desc') }}</p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {!! __('marketing.sales_plan_hero_feat1') !!}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_hero_feat2') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_hero_feat3') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_hero_feat4') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_hero_feat5') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-700 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_hero_feat6') }}</li>
                    </ul>
                    <a href="{{ route('register') }}?plan=hero" onclick="trackButtonClick('sales_hero_plan')" class="mt-auto w-full text-center bg-gray-900 hover:bg-black text-amber-400 font-semibold py-3 px-6 rounded-lg transition duration-150 shadow-md">
                        {{ __('marketing.sales_plan_hero_cta') }}
                    </a>
                </div>

                {{-- Enterprise/Custom Plan --}}
                <div class="bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700 flex flex-col transform hover:scale-105 transition-transform duration-300">
                    <h2 class="text-3xl font-orbitron font-bold text-white mb-2">{{ __('marketing.sales_plan_titan_title') }}</h2>
                     <p class="text-amber-400 text-5xl font-extrabold mb-4">{{ __('marketing.sales_plan_titan_price') }}</p>
                    <p class="text-gray-400 mb-6 text-sm">{{ __('marketing.sales_plan_titan_desc') }}</p>
                    <ul class="text-gray-300 space-y-3 mb-8 flex-grow">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat1') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat2') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat3') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat4') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat5') }}</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg> {{ __('marketing.sales_plan_titan_feat6') }}</li>
                    </ul>
                    <a href="mailto:contato@eurystheusai.com?subject={{ rawurlencode(__('marketing.sales_plan_titan_title')) }}" onclick="trackButtonClick('sales_titan_plan')" class="mt-auto w-full text-center bg-amber-400 hover:bg-amber-500 text-gray-900 font-semibold py-3 px-6 rounded-lg transition duration-150">
                        {{ __('marketing.sales_plan_titan_cta') }}
                    </a>
                </div>
            </section>

            {{-- Social Proof Section --}}
            <section class="mt-24">
                <h2 class="text-4xl font-orbitron font-bold text-center text-white mb-12">{{ __('marketing.sales_testimonials_heading') }}</h2>
                <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-10">
                    <div class="bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700">
                        <div class="flex items-center mb-4">
                            <div class="text-amber-400 text-2xl">⭐⭐⭐⭐⭐</div>
                        </div>
                        <p class="text-gray-300 mb-5 text-lg italic">"{{ __('marketing.sales_testimonial_1_text') }}"</p>
                        <div class="font-semibold text-white text-right">
                            - {{ __('marketing.sales_testimonial_1_author') }}
                        </div>
                    </div>
                    <div class="bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700">
                        <div class="flex items-center mb-4">
                            <div class="text-amber-400 text-2xl">⭐⭐⭐⭐⭐</div>
                        </div>
                        <p class="text-gray-300 mb-5 text-lg italic">"{{ __('marketing.sales_testimonial_2_text') }}"</p>
                        <div class="font-semibold text-white text-right">
                            - {{ __('marketing.sales_testimonial_2_author') }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-24 text-center">
                 <h2 class="text-4xl font-orbitron font-bold text-white mb-12">{{ __('marketing.sales_faq_heading') }}</h2>
                 <div class="max-w-4xl mx-auto text-left space-y-6">
                     <details class="bg-gray-800 p-6 rounded-lg shadow-xl border border-gray-700 group">
                         <summary class="font-semibold text-lg text-white cursor-pointer list-none flex justify-between items-center group-open:text-amber-400 transition-colors">
                            {{ __('marketing.sales_faq_q1') }}
                            <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </summary>
                         <p class="text-gray-300 mt-4">{{ __('marketing.sales_faq_a1') }}</p>
                     </details>
                     <details class="bg-gray-800 p-6 rounded-lg shadow-xl border border-gray-700 group">
                         <summary class="font-semibold text-lg text-white cursor-pointer list-none flex justify-between items-center group-open:text-amber-400 transition-colors">
                            {{ __('marketing.sales_faq_q2') }}
                            <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </summary>
                         <p class="text-gray-300 mt-4">{{ __('marketing.sales_faq_a2') }}</p>
                     </details>
                     <details class="bg-gray-800 p-6 rounded-lg shadow-xl border border-gray-700 group">
                         <summary class="font-semibold text-lg text-white cursor-pointer list-none flex justify-between items-center group-open:text-amber-400 transition-colors">
                            {{ __('marketing.sales_faq_q3') }}
                            <svg class="w-5 h-5 transform group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </summary>
                         <p class="text-gray-300 mt-4">{{ __('marketing.sales_faq_a3') }}</p>
                     </details>
                 </div>
            </section>
        </main>

        <footer class="bg-gray-900 border-t border-gray-700 mt-24">
            <div class="container mx-auto px-6 py-10 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ __('marketing.sales_header_brand') }}. {{ __('marketing.sales_footer_rights') }}</p>
                 <p class="mt-2 text-sm font-orbitron">{{ __('marketing.sales_footer_tagline') }}</p>
            </div>
        </footer>
    </div>
    
    {{-- Analytics and Tracking JavaScript --}}
    <script>
        // Track page view on load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof trackPageView === 'function') {
                trackPageView('sales');
            }
        });

        // Function to track page views (ensure this is defined or loaded globally if needed)
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
            }).catch(error => console.warn('Analytics page view error:', error));
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
                    page: 'sales',
                    timestamp: new Date().toISOString()
                })
            }).catch(error => console.warn('Analytics button click error:', error));
        }
    </script>
    
    {{-- marketing.js is not explicitly included as its functionality (theme toggle) is removed. 
         If it contains other essential JS, it should be reviewed and potentially included via @vite. --}}
    {{-- <script src="{{ asset('js/marketing.js') }}"></script> --}}
</body>
</html>
