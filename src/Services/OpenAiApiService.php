<?php

namespace hexa_package_openai\Services;

use hexa_core\Models\Setting;
use Illuminate\Support\Facades\Http;

class OpenAiApiService
{
    private const API_BASE = "https://api.openai.com/v1";

    public function getApiKey(): ?string
    {
        $key = Setting::getValue("openai_api_key");
        return is_string($key) && trim($key) !== "" ? trim($key) : null;
    }

    public function listModels(?string $token = null): array
    {
        $token = trim((string) ($token ?? $this->getApiKey() ?? ""));
        if ($token === "") {
            return ["success" => false, "message" => "No OpenAI token configured.", "models" => []];
        }

        try {
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $token,
                "Accept" => "application/json",
            ])->timeout(20)->get(self::API_BASE . "/models");

            if (!$response->successful()) {
                return [
                    "success" => false,
                    "message" => "OpenAI returned HTTP " . $response->status() . ": " . (string) ($response->json("error.message") ?? "Unknown error"),
                    "models" => [],
                ];
            }

            $models = array_values(array_filter((array) $response->json("data", []), "is_array"));
            return ["success" => true, "message" => count($models) . " model(s) accessible.", "models" => $models];
        } catch (\Throwable $e) {
            return ["success" => false, "message" => "OpenAI connection failed: " . $e->getMessage(), "models" => []];
        }
    }

    public function testConnection(?string $token = null): array
    {
        $models = $this->listModels($token);
        return [
            "success" => (bool) ($models["success"] ?? false),
            "message" => (string) ($models["message"] ?? "OpenAI test failed."),
            "count" => count((array) ($models["models"] ?? [])),
        ];
    }
}
