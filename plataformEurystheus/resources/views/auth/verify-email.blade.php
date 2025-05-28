<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.verify_email') }} - EurystheusAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased theme-light bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white/90 dark:bg-gray-900/90 shadow-md backdrop-blur-sm">
        <nav class="container mx-auto px-6 py-4">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('marketing.home') }}" class="inline-flex items-center text-gray-700 dark:text-gray-300 hover:text-orange-500 dark:hover:text-yellow-400 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('general.back') }} {{ __('general.home') }}
                </a>
                @include('components.language-switcher')
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('auth.verify_title') }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ __('auth.verify_subtitle') }}</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/70 backdrop-blur-md rounded-xl shadow-xl p-8 border border-gray-200/50 dark:border-gray-700/50">
                @if (session('status') === 'verification-link-sent')
                    <div class="mb-4 text-sm text-green-600 dark:text-green-400">
                        {{ __('auth.verification_sent') }}
                    </div>
                @endif
                <p class="mb-4 text-gray-700 dark:text-gray-300">{{ __('auth.not_receive_email_instruction') }}</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex justify-center items-center bg-gradient-to-r from-orange-500 to-orange-600 dark:from-yellow-400 dark:to-yellow-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        {{ __('auth.resend_verification') }}
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
                <p class="mt-6 text-sm text-gray-600 dark:text-gray-400 text-center">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-orange-500 dark:text-yellow-400 hover:underline focus:outline-none">
                            {{ __('auth.logout') }}
                        </button>
                    </form>
                </p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white/80 dark:bg-gray-900/80 border-t border-gray-200 dark:border-gray-700 py-4 backdrop-blur-sm">
        <div class="container mx-auto px-6">
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                &copy; {{ date('Y') }} EurystheusAI. {{ __('general.all_rights_reserved') }}
            </p>
        </div>
    </footer>
</body>
</html>
