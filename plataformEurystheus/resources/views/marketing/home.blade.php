<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EurystheusAI - Unleash Your AI Potential</title>
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
                        <svg id="theme-toggle-light-icon" class="hidden h-6 w-6 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 5.05A1 1 0 003.636 6.464l.707.707a1 1 0 001.414-1.414l-.707-.707zm1.414 10.607a1 1 0 010-1.414l.707-.707a1 1 0 111.414 1.414l-.707.707a1 1 0 01-1.414 0zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                    </button>
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-6 py-12">
            <section class="flex flex-col md:flex-row items-center justify-between py-16 md:py-24">
                <!-- Left Column: Text and Call-to-Action -->
                <div class="md:w-1/2 text-left">
                    <h1 class="text-5xl font-extrabold text-gray-900 dark:text-white leading-tight">
                        Forje Prompts <span class="text-orange-500 dark:text-yellow-400">Lendários.</span>
                    </h1>
                    <p class="mt-6 text-xl text-gray-600 dark:text-gray-300">
                        Descreva seu desafio. Nossa IA constrói a "cadeia de pensamento" que extrai resultados extraordinários de qualquer IA. O trabalho pesado é nosso. A glória é sua.
                    </p>
                    <div class="mt-8 flex">
                        <input type="text" placeholder="Descreva seu problema ou ideia..." class="flex-1 min-w-0 px-4 py-3 rounded-l-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 placeholder-gray-500 dark:placeholder-gray-400">
                        <button class="bg-orange-500 hover:bg-orange-600 dark:bg-yellow-400 dark:hover:bg-yellow-500 text-white dark:text-gray-900 font-semibold py-3 px-6 rounded-r-lg shadow-lg transition-transform duration-150 hover:scale-105 flex-shrink-0">
                            Start for Free
                        </button>
                    </div>
                </div>

                <!-- Right Column: AI Visualization -->
                <div class="md:w-1/2 flex justify-center items-center mt-12 md:mt-0">
                    <div class="relative w-80 h-80 sm:w-96 sm:h-96 md:w-112 md:h-112">
                        <!-- Image clipped inside the circle -->
                        <div class="absolute inset-4 rounded-full overflow-hidden">
                            <img src="{{ asset('img/eurystheus.png') }}" alt="Image of Eurystheus" class="w-full h-full object-cover">
                        </div>
                        <!-- Gradient overlay on top -->
                        <div class="absolute inset-0 rounded-full radial-fade-circle pointer-events-none"></div>
                    </div>
                </div>
            </section>

            <section class="mt-20 text-center">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Pronto Para Conquistar Seus 12 Trabalhos?</h2>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">Junte-se a milhares de criadores que já transformaram seus maiores desafios em conquistas épicas com a EurystheusAI.</p>
                <a href="{{ route('register') }}" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-8 rounded-lg text-lg shadow-lg transform transition-transform duration-150 hover:scale-105">
                    Criar Minha Conta Grátis
                </a>
            </section>
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-20">
            <div class="container mx-auto px-6 py-8 text-center text-gray-600 dark:text-gray-300">
                <p>&copy; {{ date('Y') }} EurystheusAI. All rights reserved.</p>
                <p class="mt-1">Harnessing AI, Honoring Legend.</p>
            </div>
        </footer>
    </div>
    {{-- app.js is now loaded via @vite in the head --}}
    <script src="{{ asset('js/marketing.js') }}"></script>
</body>
</html>
