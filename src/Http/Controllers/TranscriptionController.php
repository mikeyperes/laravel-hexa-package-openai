<?php

namespace hexa_package_openai\Http\Controllers;

use hexa_core\Http\Controllers\Controller;
use hexa_package_openai\Services\WhisperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TranscriptionController -- receives audio from browser and returns transcribed text.
 */
class TranscriptionController extends Controller
{
    /**
     * Transcribe an uploaded audio blob via Whisper.
     *
     * @param Request $request
     * @param WhisperService $whisper
     * @return JsonResponse
     */
    public function transcribe(Request $request, WhisperService $whisper): JsonResponse
    {
        if (!$request->hasFile('audio')) {
            return response()->json(['success' => false, 'error' => 'No audio file received.'], 422);
        }

        $file = $request->file('audio');
        $language = $request->input('language', 'en');

        $result = $whisper->transcribe($file->getRealPath(), $language);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'text'    => $result['text'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => $result['error'],
        ], 422);
    }
}
