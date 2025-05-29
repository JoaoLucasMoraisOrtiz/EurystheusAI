@php
    $currentLocale = app()->getLocale();
    $availableLocales = [
        'pt_BR' => ['name' => 'Português', 'flag' => '🇧🇷'],
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
    ];
@endphp

<div x-data="{ open: false }" class="relative inline-block text-left">
    <div>
        <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-700 shadow-sm px-3 py-1.5 bg-gray-800 text-sm font-medium text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-yellow-500" id="options-menu" aria-haspopup="true" :aria-expanded="open.toString()">
            <span class="mr-2">{{ $availableLocales[$currentLocale]['flag'] }}</span>
            {{ $availableLocales[$currentLocale]['name'] }}
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-auto min-w-[12rem] rounded-md shadow-lg bg-gray-800 ring-1 ring-gray-700 focus:outline-none z-50"
         role="menu"
         aria-orientation="vertical" 
         aria-labelledby="options-menu" 
         tabindex="-1">
        <div class="py-1" role="none">
            @foreach($availableLocales as $locale => $data)
                <a href="{{ route('language.switch', $locale) }}" 
                   class="group flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ $currentLocale === $locale ? 'bg-gray-700 font-semibold' : '' }}"
                   role="menuitem" 
                   tabindex="-1">
                    <span class="mr-3">{{ $data['flag'] }}</span>
                    {{ $data['name'] }}
                    @if($currentLocale === $locale)
                        <svg class="ml-auto h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
