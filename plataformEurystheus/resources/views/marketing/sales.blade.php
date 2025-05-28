<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EurystheusAI - Pricing Plans</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
    {{-- Add any specific fonts or icons here --}}
</head>
<body class="font-sans antialiased theme-light">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 shadow-md">
            <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
                <div>
                    <a href="{{ route('marketing.home') }}" class="text-xl font-bold text-gray-800 dark:text-white">EurystheusAI</a>
                </div>
                <div class="flex items-center">
                    <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-yellow-400 mx-2">Login</a>
                    <a href="{{ route('register') }}" class="text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-yellow-400 mx-2">Register</a>
                    <button id="theme-toggle" class="ml-4 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-yellow-400">
                        <svg id="theme-toggle-dark-icon" class="hidden h-6 w-6 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon" class="hidden h-6 w-6 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 5.05A1 1 0 003.636 6.464l.707.707a1 1 0 001.414-1.414l-.707-.707zm1.414 10.607a1 1 0 010-1.414l.707.707a1 1 0 111.414 1.414l-.707.707a1 1 0 01-1.414 0zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                    </button>
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-6 py-12">
            <section class="text-center mb-16">
                <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white">
                    O Poder Certo Para Cada <span class="text-orange-500 dark:text-yellow-400">Desafio</span>.
                </h1>
                <p class="mt-6 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Do seu primeiro experimento à automação em escala, temos o plano perfeito para transformar seus desafios em vitórias. Todos os planos pagos incluem uma <strong class="font-semibold">garantia de 14 dias de satisfação ou seu dinheiro de volta.</strong>
                </p>
            </section>

            <section class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                {{-- Free Plan --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Apprentice</h2>
                    <p class="text-orange-500 dark:text-yellow-400 text-4xl font-extrabold mb-4">Free</p>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Perfeito para explorar o poder da engenharia de prompt automatizada. Dê o primeiro passo na sua jornada hercúlea.</p>
                    <ul class="text-gray-700 dark:text-gray-300 space-y-2 mb-8 flex-grow">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> 10 prompts por dia</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Acesso aos blueprints básicos</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Suporte da comunidade</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Testes limitados de execução</li>
                    </ul>
                    <a href="{{ route('register') }}?plan=free" onclick="trackButtonClick('sales_free_plan')" class="mt-auto w-full text-center bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-3 px-6 rounded-lg transition duration-150">
                        Começar Grátis
                    </a>
                </div>

                {{-- Paid Plan (Most Popular) --}}
                <div class="bg-orange-500 dark:bg-yellow-400 text-white dark:text-gray-900 p-8 rounded-lg shadow-2xl border-2 border-orange-600 dark:border-yellow-500 relative flex flex-col">
                    <span class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-gray-800 text-orange-500 dark:text-yellow-400 px-3 py-1 text-sm font-semibold rounded-full shadow">Most Popular</span>
                    <h2 class="text-2xl font-bold mb-2">Hero</h2>
                    <div class="mb-4">
                        <span class="text-4xl font-extrabold">R$ 97</span>
                        <span class="text-lg opacity-90">/mês</span>
                    </div>
                    <p class="opacity-90 mb-6">Para profissionais e criadores que não têm tempo a perder. Libere todo o potencial da IA para resultados ilimitados.</p>
                    <ul class="space-y-2 mb-8 flex-grow">
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> <strong>Prompts ilimitados</strong></li>
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> Todos os blueprints premium</li>
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> Execução de prompts ilimitada</li>
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> Suporte prioritário</li>
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> Novos recursos em primeira mão</li>
                        <li class="flex items-center"><span class="text-green-300 mr-2">✓</span> Templates exclusivos</li>
                    </ul>
                    <a href="{{ route('register') }}?plan=hero" onclick="trackButtonClick('sales_hero_plan')" class="mt-auto w-full text-center bg-white hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-black text-orange-500 dark:text-yellow-400 font-semibold py-3 px-6 rounded-lg transition duration-150 shadow-md">
                        🚀 Escolher Hero
                    </a>
                </div>

                {{-- Enterprise/Custom Plan --}}
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 flex flex-col">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Titan</h2>
                     <p class="text-orange-500 dark:text-yellow-400 text-4xl font-extrabold mb-4">Custom</p>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Soluções sob medida para equipes e empresas que buscam integrar o poder da EurystheusAI em sua escala.</p>
                    <ul class="text-gray-700 dark:text-gray-300 space-y-2 mb-8 flex-grow">
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Tudo do Hero</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> API dedicada</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Blueprints customizados</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Suporte 24/7</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> Treinamento da equipe</li>
                        <li class="flex items-center"><span class="text-green-500 mr-2">✓</span> SLA garantido</li>
                    </ul>
                    <a href="mailto:contato@eurystheusai.com?subject=Interesse no Plano Titan" onclick="trackButtonClick('sales_titan_plan')" class="mt-auto w-full text-center bg-orange-500 hover:bg-orange-600 dark:bg-yellow-400 dark:hover:bg-yellow-500 text-white dark:text-gray-900 font-semibold py-3 px-6 rounded-lg transition duration-150">
                        Falar com Vendas
                    </a>
                </div>
            </section>

            {{-- Social Proof Section --}}
            <section class="mt-20">
                <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-10">O Que Nossos Heróis Estão Dizendo</h2>
                <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-8">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"Consegui triplicar a qualidade dos meus prompts em apenas uma semana. O ROI foi imediato!"</p>
                        <div class="font-semibold text-gray-800 dark:text-white">
                            Marina S. - Content Creator
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">"Economizo 3+ horas por dia com prompts que realmente funcionam. Revolucionou meu workflow!"</p>
                        <div class="font-semibold text-gray-800 dark:text-white">
                            Carlos R. - Empresário
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-20 text-center">
                 <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Frequently Asked Questions</h2>
                 <div class="max-w-3xl mx-auto text-left space-y-4">
                     <details class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                         <summary class="font-semibold text-gray-800 dark:text-white cursor-pointer">Como funciona a garantia de 14 dias?</summary>
                         <p class="text-gray-600 dark:text-gray-300 mt-3">Se não ficar 100% satisfeito com os resultados, devolvemos seu dinheiro sem perguntas.</p>
                     </details>
                     <details class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                         <summary class="font-semibold text-gray-800 dark:text-white cursor-pointer">Posso cancelar a qualquer momento?</summary>
                         <p class="text-gray-600 dark:text-gray-300 mt-3">Sim! Não há contratos ou taxas de cancelamento. Você pode cancelar com um clique.</p>
                     </details>
                     <details class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                         <summary class="font-semibold text-gray-800 dark:text-white cursor-pointer">Que modelos de IA são suportados?</summary>
                         <p class="text-gray-600 dark:text-gray-300 mt-3">Nossos prompts funcionam com ChatGPT, Claude, Gemini, e todos os principais modelos de IA.</p>
                     </details>
                 </div>
            </section>
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-20">
            <div class="container mx-auto px-6 py-8 text-center text-gray-600 dark:text-gray-300">
                <p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>
                 <p class="mt-1">Empowering Your AI Labors.</p>
            </div>
        </footer>
    </div>
    
    {{-- Analytics and Tracking JavaScript --}}
    <script>
        // Track page view on load
        document.addEventListener('DOMContentLoaded', function() {
            trackPageView('sales');
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
                    page: 'sales',
                    timestamp: new Date().toISOString()
                })
            }).catch(error => console.log('Analytics error:', error));
        }
    </script>
    
    {{-- app.js is now loaded via @vite in the head --}}
    <script src="{{ asset('js/marketing.js') }}"></script>
</body>
</html>
