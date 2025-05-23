<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromptLog;
use App\Models\LlmResponse;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient; // Standard Google API Client
use Google\Service\VertexAI; // Vertex AI Service
use Google\Service\VertexAI\GenerateContentRequest;
use Google\Service\VertexAI\Content;
use Google\Service\VertexAI\Part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // HTTP client for direct API call if SDK setup is complex

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $promptLogs = collect();
        $llmResponses = collect();

        if ($user->isPayed()) {
            // Eager load LlmResponse with PromptLog
            $promptLogs = $user->promptLogs()->with('llmResponse')->latest()->get();
        }
        
        return view('dashboard', compact('user', 'promptLogs'));
    }

    public function executePrompt(Request $request)
    {
        $request->validate([
            'llm_response_id' => 'required|exists:llm_responses,id',
            'prompt_index' => 'required|integer|min:0',
            'placeholders' => 'nullable|array',
        ]);

        $llmResponseId = $request->input('llm_response_id');
        $promptIndex = $request->input('prompt_index');
        
        $llmResponse = LlmResponse::findOrFail($llmResponseId);
        $user = Auth::user();

        if ($llmResponse->promptLog->anonymous_user !== $user->id) { // Corrected: Changed user_id to anonymous_user
            return back()->with('error', 'Unauthorized action.')->withInput();
        }
        
        if (!$user->isPayed()) {
            return back()->with('error', 'This feature is for paid users only.')->withInput();
        }

        $generatedPrompts = json_decode($llmResponse->generated_prompts, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($generatedPrompts) || !isset($generatedPrompts[$promptIndex])) {
            return back()->with('error', 'Invalid prompt selected or error decoding prompts.')->withInput();
        }

        $selectedPrompt = $generatedPrompts[$promptIndex];
        $finalPrompt = $selectedPrompt;

        if ($request->filled('placeholders')) {
            foreach ($request->input('placeholders', []) as $key => $value) {
                $finalPrompt = str_replace("{{{$key}}}", $value, $finalPrompt);
                $finalPrompt = str_replace("{{ {$key} }}", $value, $finalPrompt); // Handle with spaces
            }
        }
        
        // Ensure there are no remaining placeholders
        if (preg_match('/\{\{.*?\}\}/', $finalPrompt)) {
            return back()->with('error', 'All placeholders must be filled before execution.')->withInput($request->all());
        }

        try {
            $apiKey = env('GEMINI_API_KEY');
            $geminiApiUrl = env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent');

            if (!$apiKey) {
                Log::error('GEMINI_API_KEY not set.');
                return back()->with('error', 'AI service configuration error. Missing API Key.')->withInput();
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$geminiApiUrl}?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $finalPrompt]
                        ]
                    ]
                ],
                // Optional: Add generationConfig if needed
                // 'generationConfig' => [
                //     'temperature' => 0.7,
                //     'maxOutputTokens' => 1000,
                // ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'prompt' => $finalPrompt
                ]);
                return back()->with('error', 'Failed to execute prompt with AI. Status: ' . $response->status())->withInput();
            }

            $responseData = $response->json();
            
            $executionResult = '';
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $executionResult = $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                Log::warning('Gemini API response format unexpected.', ['response' => $responseData]);
                $executionResult = 'Could not extract text from AI response. The prompt was sent.';
            }
            
            // Store the result or pass it back
            // For now, passing back to the view via session flash data
            return back()
                ->withInput($request->except('placeholders'))
                ->with('execution_result', $executionResult)
                ->with('executed_llm_response_id', $llmResponse->id) // for identifying which prompt's result is shown
                ->with('executed_prompt_index', $promptIndex); // for identifying which prompt's result is shown

        } catch (\Exception $e) {
            Log::error('Gemini API call failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to execute prompt with AI: ' . $e->getMessage())->withInput();
        }
    }
}
