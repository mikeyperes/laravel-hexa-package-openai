@extends('layouts.app')
@section('title', 'OpenAI Settings')

@section('content')
<div class="max-w-4xl mx-auto" x-data="openaiSettings()">

    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('settings.index') }}" class="hover:text-purple-600 transition-colors">Settings</a></li>
            <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
            <li class="text-gray-900 font-medium">OpenAI</li>
        </ol>
    </nav>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">OpenAI Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Configure your OpenAI API key for Whisper audio transcription.</p>
    </div>

    {{-- Setup Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">Setup Instructions</h3>
        <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
            <li>Go to <a href="https://platform.openai.com/api-keys" target="_blank" class="underline font-medium">platform.openai.com/api-keys</a> <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></li>
            <li>Click "Create new secret key"</li>
            <li>Copy the key and paste it below</li>
            <li>Click Save, then Test to verify</li>
        </ol>
        <p class="mt-2 text-xs text-blue-600">Whisper transcription costs ~$0.006/minute of audio.</p>
    </div>

    {{-- API Key Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">API Key</h2>
        </div>
        <div class="p-6 space-y-6">

            {{-- API Key Field --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">OpenAI API Key</label>
                <template x-if="!editKey">
                    <div class="flex items-center gap-3">
                        @if($hasApiKey)
                            <input type="text" disabled value="••••••••" class="flex-1 px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm font-mono text-gray-500 cursor-not-allowed">
                        @else
                            <input type="text" disabled placeholder="Not set" class="flex-1 px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm font-mono text-gray-400 cursor-not-allowed">
                        @endif
                        <button type="button" @click="editKey = true"
                            class="px-4 py-2.5 text-sm font-medium text-purple-600 border border-purple-300 rounded-lg hover:bg-purple-50 transition-colors">
                            Change
                        </button>
                    </div>
                </template>
                <template x-if="editKey">
                    <div class="flex items-center gap-3">
                        <input type="text" x-model="apiKey"
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="sk-...">
                        <button type="button" @click="editKey = false; apiKey = ''"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </template>
            </div>

            {{-- Save + Test Buttons --}}
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="button" @click="saveKey()" :disabled="saving"
                    class="inline-flex items-center px-6 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors">
                    <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="saving ? 'Saving...' : 'Save'"></span>
                </button>
                <button type="button" @click="testKey()" :disabled="testing"
                    class="inline-flex items-center px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 transition-colors">
                    <svg x-show="testing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="testing ? 'Testing...' : 'Test API Key'"></span>
                </button>
            </div>

            {{-- Result Banner --}}
            <template x-if="result.message">
                <div class="rounded-lg px-4 py-3 text-sm font-medium flex items-center gap-2"
                    :class="result.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'">
                    <template x-if="result.type === 'success'">
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="result.type === 'error'">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <span x-text="result.message"></span>
                </div>
            </template>

        </div>
    </div>

</div>

@push('scripts')
<script>
function openaiSettings() {
    return {
        apiKey: '',
        editKey: false,
        saving: false,
        testing: false,
        result: { type: '', message: '' },

        async saveKey() {
            this.saving = true;
            this.result = { type: '', message: '' };
            try {
                const resp = await fetch('{{ route("settings.openai.save") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ openai_api_key: this.apiKey }),
                });
                const data = await resp.json();
                this.result = { type: data.success ? 'success' : 'error', message: data.message };
                if (data.success) { this.editKey = false; this.apiKey = ''; }
            } catch (e) {
                this.result = { type: 'error', message: 'Network error: ' + e.message };
            }
            this.saving = false;
        },

        async testKey() {
            this.testing = true;
            this.result = { type: '', message: '' };
            try {
                const resp = await fetch('{{ route("settings.openai.test") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await resp.json();
                this.result = { type: data.success ? 'success' : 'error', message: data.message };
            } catch (e) {
                this.result = { type: 'error', message: 'Network error: ' + e.message };
            }
            this.testing = false;
        },
    };
}
</script>
@endpush
@endsection
