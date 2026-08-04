<?php

namespace Tests\Feature;

use hexa_core\AI\Models\AiTransaction;
use hexa_core\Models\Setting;
use hexa_package_openai\Services\WhisperService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhisperTransactionLoggingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->requireInstalledPackage('hexawebsystems/laravel-hexa-package-openai', WhisperService::class);
        Artisan::call('migrate:fresh', ['--database' => 'sqlite', '--force' => true]);
        Setting::setValue('openai_api_key', 'openai-test-key');
    }

    public function test_transcription_logs_duration_and_provider_estimated_cost(): void
    {
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response([
                'task' => 'transcribe',
                'language' => 'english',
                'duration' => 30.0,
                'text' => 'A short test transcript.',
                'usage' => ['type' => 'duration', 'seconds' => 30],
            ], 200, ['x-request-id' => 'openai-transcription-1']),
        ]);

        $result = app(WhisperService::class)->transcribeFromData('test audio bytes', 'recording.webm');

        $this->assertTrue($result['success']);
        $transaction = AiTransaction::query()->sole();
        $this->assertSame('succeeded', $transaction->status);
        $this->assertSame('openai-transcription-1', $transaction->provider_request_id);
        $this->assertSame('whisper-1', $transaction->model);
        $this->assertSame('provider_estimated', $transaction->cost_status);
        $this->assertSame('0.0030000000', $transaction->total_cost_usd);
        $this->assertSame(30, $transaction->usage['audio_duration_seconds']);
        $this->assertSame(16, $transaction->request_metadata['audio_bytes']);
    }

    public function test_failed_transcription_logs_provider_error(): void
    {
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response([
                'error' => [
                    'type' => 'invalid_request_error',
                    'code' => 'invalid_audio',
                    'message' => 'Invalid audio file.',
                ],
            ], 400, ['x-request-id' => 'openai-transcription-2']),
        ]);

        $result = app(WhisperService::class)->transcribeFromData('invalid audio', 'recording.webm');

        $this->assertFalse($result['success']);
        $transaction = AiTransaction::query()->sole();
        $this->assertSame('failed', $transaction->status);
        $this->assertSame(400, $transaction->http_status);
        $this->assertSame('invalid_request_error', $transaction->error_type);
        $this->assertSame('invalid_audio', $transaction->error_code);
    }
}
