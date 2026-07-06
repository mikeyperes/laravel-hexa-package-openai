<?php

namespace hexa_package_openai\Http\Controllers;

use hexa_core\Http\Controllers\Controller;
use hexa_core\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * OpenaiSettingController -- manages OpenAI integration settings.
 */
class OpenaiSettingController extends Controller
{
    /**
     * Display the OpenAI settings page.
     *
     * @return View
     */
    public function index(): View
    {
        $hasApiKey = (bool) Setting::getValue('openai_api_key');

        return view('openai::settings.index', compact('hasApiKey'));
    }

    /**
     * Save the OpenAI API key.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request): JsonResponse
    {
        if ($request->filled('openai_api_key')) {
            Setting::setValue('openai_api_key', $request->input('openai_api_key'));
        }

        return response()->json(['success' => true, 'message' => 'OpenAI settings saved.']);
    }

    /**
     * Test the OpenAI API key by listing models.
     *
     * @return JsonResponse
     */
    public function test(): JsonResponse
    {
        $apiKey = Setting::getValue('openai_api_key');

        if (empty($apiKey)) {
            return response()->json(['success' => false, 'message' => 'No API key configured.']);
        }

        $result = app(\hexa_package_openai\Services\OpenAiApiService::class)->testConnection((string) $apiKey);

        if ($result["success"] ?? false) {
            return response()->json(["success" => true, "message" => "API key is valid."]);
        }

        return response()->json(["success" => false, "message" => "Validation failed: " . (string) ($result["message"] ?? "OpenAI validation failed.")]);

    }
}
