<?php

namespace hexa_package_openai\Services;

use hexa_core\AI\Contracts\AiTransactionRecorder;
use hexa_core\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * WhisperService -- handles audio transcription via OpenAI Whisper API.
 */
class WhisperService
{
    private const MODEL = 'whisper-1';

    private const PRICE_PER_MINUTE_USD = 0.006;

    /**
     * Transcribe an audio file using OpenAI Whisper API.
     *
     * @param  string  $filePath  Absolute path to the audio file.
     * @param  string  $language  Language code (default: en).
     * @return array{success: bool, text: string|null, error: string|null}
     */
    public function transcribe(string $filePath, string $language = 'en'): array
    {
        $apiKey = Setting::getValue('openai_api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'text' => null, 'error' => 'OpenAI API key not configured.'];
        }

        if (! file_exists($filePath)) {
            return ['success' => false, 'text' => null, 'error' => 'Audio file not found.'];
        }

        return $this->requestTranscription(
            (string) file_get_contents($filePath),
            basename($filePath),
            $language,
            (string) $apiKey,
            'file'
        );
    }

    /**
     * Transcribe audio from raw binary data.
     *
     * @param  string  $audioData  Raw audio binary data.
     * @param  string  $filename  Filename with extension (e.g. recording.webm).
     * @param  string  $language  Language code (default: en).
     * @return array{success: bool, text: string|null, error: string|null}
     */
    public function transcribeFromData(string $audioData, string $filename = 'recording.webm', string $language = 'en'): array
    {
        $apiKey = Setting::getValue('openai_api_key');

        if (empty($apiKey)) {
            return ['success' => false, 'text' => null, 'error' => 'OpenAI API key not configured.'];
        }

        return $this->requestTranscription($audioData, $filename, $language, (string) $apiKey, 'memory');
    }

    /**
     * @return array{success: bool, text: string|null, error: string|null}
     */
    private function requestTranscription(
        string $audioData,
        string $filename,
        string $language,
        string $apiKey,
        string $source
    ): array {
        $span = app(AiTransactionRecorder::class)->start([
            'provider' => 'openai',
            'package' => 'hexawebsystems/laravel-hexa-package-openai',
            'model' => self::MODEL,
            'operation' => 'audio.transcriptions.create',
            'endpoint' => '/v1/audio/transcriptions',
            'request_metadata' => [
                'audio_bytes' => strlen($audioData),
                'audio_extension' => strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)),
                'language' => $language,
                'source' => $source,
                'response_format' => 'verbose_json',
            ],
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
            ])->attach(
                'file', $audioData, $filename
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => self::MODEL,
                'language' => $language,
                'response_format' => 'verbose_json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];
                $seconds = max(0.0, (float) ($usage['seconds'] ?? $data['duration'] ?? 0));
                if ($seconds > 0) {
                    $usage['audio_duration_seconds'] = $seconds;
                    $usage['estimated_cost_usd'] = round(($seconds / 60) * self::PRICE_PER_MINUTE_USD, 10);
                }

                $span->succeed([
                    'provider_request_id' => $response->header('x-request-id'),
                    'model' => self::MODEL,
                    'http_status' => $response->status(),
                    'finish_reason' => 'transcribed',
                    'usage' => $usage,
                    'response_metadata' => [
                        'audio_duration_seconds' => $seconds ?: null,
                        'transcript_characters' => mb_strlen((string) ($data['text'] ?? '')),
                        'detected_language' => $data['language'] ?? null,
                    ],
                ]);

                return ['success' => true, 'text' => $data['text'] ?? '', 'error' => null];
            }

            $body = $response->json();
            $errorMsg = $body['error']['message'] ?? ('HTTP '.$response->status());
            $span->fail($errorMsg, [
                'provider_request_id' => $response->header('x-request-id'),
                'model' => self::MODEL,
                'http_status' => $response->status(),
                'error_type' => $body['error']['type'] ?? 'openai_http_error',
                'error_code' => $body['error']['code'] ?? null,
                'usage' => is_array($body['usage'] ?? null) ? $body['usage'] : [],
            ]);

            return ['success' => false, 'text' => null, 'error' => $errorMsg];
        } catch (\Throwable $e) {
            $span->fail($e, ['model' => self::MODEL]);

            return ['success' => false, 'text' => null, 'error' => 'Whisper request failed: '.$e->getMessage()];
        }
    }
}
