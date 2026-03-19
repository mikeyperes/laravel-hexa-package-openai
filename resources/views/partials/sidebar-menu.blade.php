@if(\hexa_core\Models\Setting::isPackageEnabled('hexawebsystems/laravel-hexa-package-openai'))
@if(auth()->check())

@php
    $openaiPageEnabled = fn($page) => \hexa_core\Models\Setting::getValue('openai_page_' . $page, '1') === '1';
@endphp

@if($openaiPageEnabled('settings'))
<a href="{{ route('settings.openai') }}"
   class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->is('settings/openai*') ? 'sidebar-active' : 'sidebar-hover' }}">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
    </svg>
    OpenAI
</a>
@endif

@endif
@endif
