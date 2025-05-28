<!-- filepath: /home/joao/Documentos/EurystheusAI/plataformEurystheus/resources/views/auth/register.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.register') }} - EurystheusAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="{{ asset('css/marketing.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased theme-light bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 min-h-screen flex flex-col">
    <!-- Header with back button -->
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

    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <!-- Logo/Branding -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('auth.register_title') }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ __('auth.register_subtitle') }}</p>
            </div>

            <!-- Registration Form -->
            <div class="bg-gray-50 dark:bg-gray-900/70 backdrop-blur-md rounded-xl shadow-xl p-8 border border-gray-200/50 dark:border-gray-700/50">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.name') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:border-transparent transition-colors"
                                placeholder="{{ __('auth.name_placeholder') }}">
                        </div>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.email') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:border-transparent transition-colors"
                                placeholder="{{ __('auth.email_placeholder') }}">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.password') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required
                                class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:border-transparent transition-colors"
                                placeholder="{{ __('auth.password_min_8') }}">
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.confirm_password') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:border-transparent transition-colors"
                                placeholder="{{ __('auth.confirm_password_placeholder') }}">
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full flex justify-center items-center bg-gradient-to-r from-orange-500 to-orange-600 dark:from-yellow-400 dark:to-orange-400 text-white font-semibold py-3 px-4 rounded-lg shadow-lg transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-yellow-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <span>{{ __('auth.create_account') }}</span>
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                        {{ __('auth.agree_terms_text') }}
                        <a href="{{ url('/terms') }}" class="text-orange-500 dark:text-yellow-400 hover:underline">{{ __('auth.terms_of_service') }}</a> {{ __('general.and') }}
                        <a href="{{ url('/privacy') }}" class="text-orange-500 dark:text-yellow-400 hover:underline">{{ __('auth.privacy_policy') }}</a>.
                    </p>
                </form>
            </div>

            <!-- Login Link -->
            <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ __('auth.already_have_account') }}
                <a href="{{ route('login') }}" class="font-medium text-orange-500 dark:text-yellow-400 hover:underline">
                    {{ __('auth.sign_in_here') }}
                </a>
            </p>
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