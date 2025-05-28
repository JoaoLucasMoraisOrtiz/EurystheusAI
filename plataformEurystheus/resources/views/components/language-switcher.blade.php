@php
    $currentLocale = app()->getLocale();
    $availableLocales = [
        'pt_BR' => ['name' => 'Português', 'flag' => '🇧🇷'],
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
    ];
@endphp

<div class="relative inline-block text-left">
    <div>
        <button type="button" 
                class="inline-flex items-center justify-center w-auto rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-yellow-400 transition-colors"
                id="language-menu-button" 
                aria-expanded="true" 
                aria-haspopup="true"
                onclick="toggleLanguageMenu()">
            <span class="mr-2">{{ $availableLocales[$currentLocale]['flag'] }}</span>
            {{ $availableLocales[$currentLocale]['name'] }}
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="origin-top-right absolute right-0 mt-2 min-w-max rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 hidden"
         id="language-menu"
         role="menu" 
         aria-orientation="vertical" 
         aria-labelledby="language-menu-button" 
         tabindex="-1">
        <div class="py-1" role="none">
            @foreach($availableLocales as $locale => $data)
                <a href="{{ route('language.switch', $locale) }}" 
                   class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors {{ $currentLocale === $locale ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}"
                   role="menuitem" 
                   tabindex="-1">
                    <span class="mr-3">{{ $data['flag'] }}</span>
                    {{ $data['name'] }}
                    @if($currentLocale === $locale)
                        <svg class="ml-auto h-5 w-5 text-orange-500 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

<script>
function toggleLanguageMenu() {
    const menu = document.getElementById('language-menu');
    menu.classList.toggle('hidden');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const button = document.getElementById('language-menu-button');
    const menu = document.getElementById('language-menu');
    
    if (!button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});
</script>
