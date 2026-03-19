<?php

namespace hexa_package_openai\Services;

use hexa_core\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * WhisperService -- handles audio transcription via OpenAI Whisper API.
 */
class WhisperService
{
    /**
     * Transcribe an audio file using OpenAI Whisper API.
     *
     * @param string $filePath Absolute path to the audio file.
     * @param string $language Language code (default: en).
     * @return array{success: bool, text: string|null, error: string|null}
     */
    public function transcribe(string $filePath, string $language = 'en'): array
    {
        $apiKey = Setting::getValue('openai_api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'text' => null, 'error' => 'OpenAI API key not configured.'];
        }

        if (!file_exists($filePath)) {
            return ['success' => false, 'text' => null, 'error' => 'Audio file not found.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->attach(
                'file', file_get_contents($filePath), basename($filePath)
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model'    => 'whisper-1',
                'language' => $language,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => true, 'text' => $data['text'] ?? '', 'error' => null];
            }

            $body = $response->json();
            $errorMsg = $body['error']['message'] ?? ('HTTP ' . $response->status());

            return ['success' => false, 'text' => null, 'error' => $errorMsg];
        } catch (\Exception $e) {
            return ['success' => false, 'text' => null, 'error' => 'Whisper request failed: ' . $e->getMessage()];
        }
    }

    /**
     * Transcribe audio from raw binary data.
     *
     * @param string $audioData Raw audio binary data.
     * @param string $filename Filename with extension (e.g. recording.webm).
     * @param string $language Language code (default: en).
     * @return array{success: bool, text: string|null, error: string|null}
     */
    public function transcribeFromData(string $audioData, string $filename = 'recording.webm', string $language = 'en'): array
    {
        $apiKey = Setting::getValue('openai_api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'text' => null, 'error' => 'OpenAI API key not configured.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->attach(
                'file', $audioData, $filename
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model'    => 'whisper-1',
                'language' => $language,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => true, 'text' => $data['text'] ?? '', 'error' => null];
            }

            $body = $response->json();
            $errorMsg = $body['error']['message'] ?? ('HTTP ' . $response->status());

            return ['success' => false, 'text' => null, 'error' => $errorMsg];
        } catch (\Exception $e) {
            return ['success' => false, 'text' => null, 'error' => 'Whisper request failed: ' . $e->getMessage()];
        }
    }
}
