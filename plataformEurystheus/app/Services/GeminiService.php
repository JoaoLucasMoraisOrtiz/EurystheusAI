<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function chat($messages)
    {
        $apiKey = config('services.gemini.api_key');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        // Monta o payload no formato esperado pela API Gemini
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                'parts' => [
                    ['text' => $msg['content']]
                ]
            ];
        }

        $payload = [
            'contents' => $contents
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            throw new \Exception('Erro ao chamar Gemini: ' . $response->body());
        }

        $data = $response->json();
        // Extrai a resposta do modelo
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        return $text;
    }

    public function chatWithPrompt($prompt)
    {
        $apiKey = config('services.gemini.api_key');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if ($response->failed()) {
            throw new \Exception('Erro ao chamar Gemini: ' . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        return $text;
    }
}
