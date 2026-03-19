@if(Route::has('settings.openai'))
<a href="{{ route('settings.openai') }}" class="group block bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-purple-300 transition-all duration-200">
    <div class="flex items-start justify-between">
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
        </div>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">v{{ config('openai.version', '?') }}</span>
    </div>
    <h3 class="mt-4 text-lg font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">OpenAI</h3>
    <p class="mt-1 text-sm text-gray-500">Whisper audio transcription and OpenAI API integration.</p>
</a>
@endif
