<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Termos de Serviço - EurystheusAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
</head>
<body class="font-sans antialiased theme-light">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="bg-white dark:bg-gray-800 shadow-md">
            <nav class="container mx-auto px-6 py-3 flex justify-between items-center">
                <a href="{{ route('marketing.home') }}" class="text-xl font-bold text-gray-800 dark:text-white">EurystheusAI</a>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('marketing.terms') }}" class="text-gray-600 dark:text-gray-300 font-semibold">Termos</a>
                    <a href="{{ route('marketing.privacy') }}" class="text-gray-600 dark:text-gray-300 font-semibold">Privacidade</a>
                    <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-yellow-400">Login</a>
                </div>
            </nav>
        </header>

        <main class="container mx-auto px-6 py-12">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Termos de Serviço</h1>
            <div class="prose dark:prose-invert max-w-none">
                <p>Aqui você pode inserir o conteúdo completo dos Termos de Serviço da EurystheusAI.</p>
                <!-- Adicione o texto dos termos aqui -->
            </div>
        </main>

        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
            <div class="container mx-auto px-6 py-4 text-center text-gray-600 dark:text-gray-300">
                &copy; {{ date('Y') }} EurystheusAI. Todos os direitos reservados.
            </div>
        </footer>
    </div>
</body>
</html>
