<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('marketing.page_title') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes shimmer {
            0% { background-position: -200px 0; }
            100% { background-position: calc(200px + 100%) 0; }
        }
        .float-animation { animation: float 3s ease-in-out infinite; }
        .shimmer {
            background: linear-gradient(
                90deg,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.2) 20%,
                rgba(255,255,255,0.5) 60%,
                rgba(255,255,255,0)
            );
            animation: shimmer 2s infinite;
            background-size: 200px 100%;
        }
        .hero-bg {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 25%, #ea580c 50%, #dc2626 75%, #b91c1c 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="font-sans antialiased theme-light">
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
        <header class="bg-white/95 dark:bg-gray-900/95 shadow-lg backdrop-blur-sm sticky top-0 z-50 border-b border-gray-200/50 dark:border-gray-700/50">
            <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
                <div>
                    <a href="{{ route('marketing.home') }}" class="text-xl font-bold text-gray-800 dark:text-white hover:text-orange-500 dark:hover:text-yellow-400 transition-colors">
                        🏛️ EurystheusAI
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="{{ route('login') }}" onclick="trackButtonClick('header_login')" class="text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-yellow-400 transition-colors">{{ __('auth.login') }}</a>
                    <a href="{{ route('register') }}" onclick="trackButtonClick('header_register')" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-md">{{ __('auth.register') }}</a>
                    @include('components.language-switcher')
                </div>
            </nav>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="hero-bg py-20 md:py-32 text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-black/20"></div>
                <div class="container mx-auto px-6 relative z-10">
                    <div class="max-w-4xl mx-auto text-center">
                        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
                            {{ __('marketing.hero_title') }}
                        </h1>
                        <p class="text-xl md:text-2xl mb-8 opacity-90">
                            {{ __('marketing.hero_subtitle') }}
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-green-400 text-xl">✓</span>
                                <span class="text-lg">Prompts profissionais em segundos</span>
                            </div>
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-green-400 text-xl">✓</span>
                                <span class="text-lg">Resultados 10x melhores com IA</span>
                            </div>
                            <div class="flex items-center justify-center space-x-3">
                                <span class="text-green-400 text-xl">✓</span>
                                <span class="text-lg">Economia de horas todo dia</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            <a href="{{ route('register') }}" 
                               onclick="trackButtonClick('hero_try_free')"
                               class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold py-4 px-8 rounded-lg text-lg shadow-xl hover:shadow-2xl transform transition-all duration-200 hover:scale-105 shimmer">
                                🚀 Experimentar Grátis
                            </a>
                            <a href="{{ route('marketing.sales') }}" 
                               onclick="trackButtonClick('hero_see_plans')"
                               class="border-2 border-yellow-400 text-yellow-400 font-semibold py-4 px-8 rounded-lg text-lg hover:bg-yellow-400 hover:text-white transition-all duration-200">
                                💎 Ver Planos
                            </a>
                        </div>
                        <p class="text-sm opacity-75 mt-4">
                            Sem cartão de crédito • Começe em 30 segundos
                        </p>
                    </div>
                </div>
            </section>

            <!-- Social Proof Section -->
            <section class="py-16 bg-gray-50 dark:bg-gray-800">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-8">
                        Confiado por criadores em todo o mundo
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-orange-500 dark:text-yellow-400">10k+</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Prompts Gerados</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-orange-500 dark:text-yellow-400">500+</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Usuários Ativos</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-orange-500 dark:text-yellow-400">98%</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Satisfação</div>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="text-3xl font-bold text-orange-500 dark:text-yellow-400">24/7</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Disponível</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-20 bg-white dark:bg-gray-900">
                <div class="container mx-auto px-6">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                            Por que EurystheusAI?
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Transforme sua relação com IA. De iniciante a expert em minutos.
                        </p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                        <div class="text-center p-8 rounded-xl bg-gradient-to-br from-yellow-50 to-orange-100 dark:from-yellow-900/20 dark:to-orange-900/20 float-animation border border-yellow-200/50">
                            <div class="text-5xl mb-4">🎯</div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Prompts Precision</h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                Nossos prompts são testados e otimizados para entregar resultados excepcionais em qualquer modelo de IA.
                            </p>
                        </div>
                        <div class="text-center p-8 rounded-xl bg-gradient-to-br from-orange-50 to-amber-100 dark:from-orange-900/20 dark:to-amber-900/20 float-animation border border-orange-200/50" style="animation-delay: 0.5s;">
                            <div class="text-5xl mb-4">⚡</div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Velocidade Épica</h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                De ideias vagas a prompts profissionais em segundos. Economize horas todo dia.
                            </p>
                        </div>
                        <div class="text-center p-8 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-100 dark:from-amber-900/20 dark:to-yellow-900/20 float-animation border border-amber-200/50" style="animation-delay: 1s;">
                            <div class="text-5xl mb-4">🏆</div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Resultados Garantidos</h3>
                            <p class="text-gray-600 dark:text-gray-300">
                                14 dias de garantia. Se não revolucionar seu workflow, devolvemos seu dinheiro.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA Section -->
            <section class="py-20 bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500 dark:from-yellow-400 dark:via-orange-400 dark:to-red-400 text-white">
                <div class="container mx-auto px-6 text-center">
                    <h2 class="text-4xl md:text-5xl font-bold mb-6">
                        Pronto Para Conquistar Seus 12 Trabalhos?
                    </h2>
                    <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
                        Junte-se a milhares de criadores que já transformaram seus maiores desafios em conquistas épicas com a EurystheusAI.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="{{ route('register') }}" 
                           onclick="trackButtonClick('final_cta_free')"
                           class="bg-white text-orange-600 font-bold py-4 px-8 rounded-lg text-lg shadow-xl hover:shadow-2xl transform transition-all duration-200 hover:scale-105">
                            🎯 Começar Gratuitamente
                        </a>
                        <a href="{{ route('marketing.sales') }}" 
                           onclick="trackButtonClick('final_cta_premium')"
                           class="border-2 border-white text-white font-semibold py-4 px-8 rounded-lg text-lg hover:bg-white hover:text-orange-600 transition-all duration-200">
                            ⚡ Ir Direto Pro Premium
                        </a>
                    </div>
                    <p class="text-sm opacity-75 mt-6">
                        💝 Garantia de 14 dias • 🚀 Setup em 30 segundos • ⭐ Suporte premium
                    </p>
                </div>
            </section>
        </main>

        <footer class="bg-white/80 dark:bg-gray-900/80 border-t border-gray-200 dark:border-gray-700 mt-20 backdrop-blur-sm">
            <div class="container mx-auto px-6 py-8">
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-400">&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Harnessing AI, Honoring Legend.</p>
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
                    trackButtonClick('scroll_25_percent');
                } else if (maxScrollDepth >= 50 && maxScrollDepth < 75) {
                    trackButtonClick('scroll_50_percent');
                } else if (maxScrollDepth >= 75) {
                    trackButtonClick('scroll_75_percent');
                }
            }
        });
    </script>
    
    {{-- app.js is now loaded via @vite in the head --}}
    <script src="{{ asset('js/marketing.js') }}"></script>
</body>
</html>
