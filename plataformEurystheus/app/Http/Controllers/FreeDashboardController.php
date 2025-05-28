<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use App\Models\LlmResponse; // Adicionar import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException; // Added for error handling
use Illuminate\Support\Facades\Log; // Added for logging
use Illuminate\Support\Facades\DB; // Added for database transactions

class FreeDashboardController extends Controller
{
    private function extractJsonFromMarkdown($text)
    {
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $text, $matches)) {
            return $matches[1];
        }
        return $text; // Return original text if no JSON block is found
    }
    
    public function show()
    {
        $recentPrompts = PromptLog::with('llmResponse') // Eager load LlmResponse
            ->where('anonymous_user', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('free.dashboard', [
            'recentPrompts' => $recentPrompts,
        ]);
    }

    public function storePrompt(Request $request)
    {
        $validated = $request->validate([
            'scope.objective' => 'required|string|max:1000',
            'scope.constraints' => 'nullable|string|max:1000',
            'scope.data' => 'nullable|string|max:1000',
            'scope.audience' => 'nullable|string|max:1000',
            'scope.output_format' => 'nullable|string|max:1000',
            'scope.deadlines' => 'required|string|max:1000',
        ]);

        // Verificar limite de 20 registros
        $count = PromptLog::where('anonymous_user', Auth::id())->count();
        if ($count >= 20) {
            return back()->withErrors(['limit' => 'Você atingiu o limite de 20 prompts para usuários gratuitos.'])->withInput();
        }

        $userScope = $request->input('scope');
        $userObjective = $userScope['objective'] ?? '';
        $userConstraints = $userScope['constraints'] ?? '';
        $userData = $userScope['data'] ?? '';
        $userAudience = $userScope['audience'] ?? '';
        $userOutputFormat = $userScope['output_format'] ?? '';
        $userDeadlines = $userScope['deadlines'] ?? '';

        $instructionalPrompt = <<<PROMPT
You are an expert AI prompt engineer, acting as a "Prompt Architect." Your primary task is to design one or more highly effective prompts based on the user's problem description below. These prompts will be used to guide another Large Language Model (the "Target AI") to solve the user's problem thoroughly and efficiently.

User's Problem Scope:
- Objective: {$userObjective}
- Constraints/Limitations: {$userConstraints}
- Available Data/Information: {$userData}
- Target Audience/Stakeholders: {$userAudience}
- Desired Output Format/Result: {$userOutputFormat}
- Deadlines/Success Metrics: {$userDeadlines}

Instructions for designing the prompt(s) for the Target AI:
1.  **Clarity and Specificity (Fundamental):**
    *   Ensure each generated prompt is crystal clear, unambiguous, and highly specific. Avoid vague language.
    *   The Target AI should know exactly what is expected of it.

2.  **Persona Assignment (For the Target AI):**
    *   If beneficial, assign a specific expert persona to the Target AI within the prompt(s) you generate (e.g., "You are a senior financial analyst," "You are a creative content writer specializing in marketing copy"). This helps set the tone, style, and depth of the Target AI's response.

3.  **Task Decomposition & Step-by-Step Guidance (For Complex Problems):**
    *   If the user's objective is complex, break it down into smaller, manageable sub-tasks.
    *   Design a sequence of chained prompts if necessary, where the output of one prompt logically feeds into the next.
    *   For each step or sub-task, provide clear, numbered instructions within the prompt for the Target AI.

4.  **Placeholder Usage (Crucial for Dynamic Data):**
    *   Identify any parts of the user's problem scope or the desired task for the Target AI that represent variable data that the end-user will provide at execution time.
    *   For each such variable piece of information, use a placeholder in the format `{{placeholder_name}}`. For example, if the user needs to analyze customer feedback, a placeholder might be `{{customer_feedback_text}}`. If the prompt needs a specific document, it might be `{{document_content}}`.
    *   Ensure placeholder names are descriptive (e.g., `{{target_audience_segment}}` instead of `{{data1}}`).

5.  **Few-Shot Examples (Where Appropriate):**
    *   If the task involves a specific format, style, or pattern recognition, consider incorporating 1-3 high-quality examples directly into the prompt(s) you design for the Target AI.
    *   If these examples themselves contain variable data, use the `{{placeholder_name}}` format within the examples too.
    *   Example structure for few-shot:

6.  **Reasoning and Chain-of-Thought (CoT) for the Target AI:**
    *   Instruct the Target AI to explain its reasoning, show its work, or provide a step-by-step thought process if the task benefits from transparency or requires complex problem-solving.

7.  **Contextualization:**
    *   Ensure all necessary context from the user's scope is embedded or clearly referenced in the prompts for the Target AI. Use placeholders like `{{relevant_context_data}}` if this context is variable.

8.  **Output Structuring (For the Target AI):**
    *   If the user specified a desired output format, or if a structured output would be beneficial, instruct the Target AI explicitly on how to format its response (e.g., JSON, markdown table, bullet points, specific headings).

9.  **Iterative Refinement (For Chained Prompts):**
    *   If generating a sequence of prompts, ensure a smooth flow of information and context between them.

Your Output Format (As the Prompt Architect):
Please structure YOUR response (as the Prompt Architect) as a JSON object with the following keys:
    -   "chain_of_thought_explanation": A string explaining YOUR reasoning for the designed prompt(s). Detail which techniques (Clarity, Persona, Task Decomposition, Placeholders, Few-Shot, CoT, etc.) you incorporated into the generated prompts and why they are suitable for the user's specific problem.
    -   "generated_prompts": An array of strings. Each string in the array is a complete, ready-to-use prompt designed for the Target AI. If only one prompt is needed, this should be an array with a single element. Ensure all variable inputs are represented by `{{placeholder_name}}`.

Example of a prompt you might generate for the Target AI (for illustration only, adapt to the user\'s actual problem):
"Act as a seasoned market research analyst. Based on the following user data: {{user_data_snippet}}, your objective is to {{sub_objective}}.
1. First, identify key trends in the provided data.
2. Second, explain the potential implications of these trends for {{user_audience}}.
3. Finally, summarize your findings in a bullet-point list, followed by a concise recommendation.
Show your reasoning for each step."

Design the prompt(s) for the Target AI now, based on the user\'s problem scope provided above. Ensure all placeholders are in the format `{{placeholder_name}}`.
PROMPT;

        $promptLog = null; // Initialize to null

        DB::beginTransaction();
        try {
            // 1. Save the initial PromptLog
            $promptLog = PromptLog::create([
                'anonymous_user' => Auth::id(),
                'content' => json_encode($userScope), // Save the original user input
            ]);

            Log::info('Attempting to call Gemini API for PromptLog ID: ' . $promptLog->id);

            $client = new Client();
            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                Log::error('GEMINI_API_KEY not set.');
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro de configuração do serviço de IA. Tente novamente mais tarde.'])->withInput();
            }

            // Changed model from gemini-pro to gemini-1.0-pro
            $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $instructionalPrompt]
                            ]
                        ]
                    ],
                    // Optional: Add generationConfig if needed
                    // 'generationConfig' => [
                    //     'temperature' => 0.7,
                    //     'maxOutputTokens' => 1024,
                    // ]
                ]
            ]);

            $body = $response->getBody()->getContents();
            Log::info('Gemini API call successful for PromptLog ID: ' . $promptLog->id . '. Status: ' . $response->getStatusCode(), [
                'response_body_snippet' => substr($body, 0, 500)
            ]);

            $cleanedApiResponseBody = $this->extractJsonFromMarkdown($body); // Clean the entire API response first
            Log::info('Cleaned API response body (after first extractJsonFromMarkdown) for PromptLog ID: ' . $promptLog->id, ['cleaned_api_response_body_snippet' => substr($cleanedApiResponseBody, 0, 500)]);

            $responseData = json_decode($cleanedApiResponseBody, true); // Decode the Gemini API structure

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode JSON structure from Gemini API for PromptLog ID: ' . $promptLog->id . '. Error: ' . json_last_error_msg(), ['original_body' => $body, 'cleaned_api_response_body' => $cleanedApiResponseBody]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro ao processar a resposta do serviço de IA. Resposta da API inválida.'])->withInput();
            }
            Log::info('Successfully decoded JSON structure from Gemini API for PromptLog ID: ' . $promptLog->id, ['decoded_api_structure_snippet' => substr(print_r($responseData, true), 0, 500)]);

            // Access the main response text part from the Gemini API structure
            $mainApiResponseText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$mainApiResponseText) {
                Log::error('No main text part found in Gemini response for PromptLog ID: ' . $promptLog->id, ['response_data' => $responseData]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Resposta do serviço de IA não contém a parte de texto esperada.'])->withInput();
            }
            Log::info('Main API response text extracted for PromptLog ID: ' . $promptLog->id, ['main_api_response_text_snippet' => substr($mainApiResponseText, 0, 500)]);

            // Attempt to clean markdown from the actual LLM-generated content string
            $finalJsonToDecode = $this->extractJsonFromMarkdown($mainApiResponseText);
            Log::info('Final JSON string (LLM content, after second extractJsonFromMarkdown) to decode for LlmResponse for PromptLog ID: ' . $promptLog->id, ['final_json_to_decode_snippet' => substr($finalJsonToDecode, 0, 500)]);

            $llmStructuredResponse = json_decode($finalJsonToDecode, true);

            $llmReasoningToStore = $finalJsonToDecode; // Default to the cleaned JSON string
            $generatedPromptsToStore = [];

            if (json_last_error() === JSON_ERROR_NONE && is_array($llmStructuredResponse)) {
                Log::info('Successfully decoded structured JSON from main API response text for PromptLog ID: ' . $promptLog->id);
                // If decoding is successful, extract specific parts
                $llmReasoningToStore = $llmStructuredResponse['chain_of_thought_explanation'] ?? $finalJsonToDecode; // Use explanation or fallback to the full cleaned JSON
                $generatedPromptsToStore = $llmStructuredResponse['generated_prompts'] ?? [];
            } else {
                Log::error('Failed to decode the structured JSON from the main API response text for PromptLog ID: ' . $promptLog->id . '. Error: ' . json_last_error_msg(), [
                    'original_main_text_from_llm' => $mainApiResponseText,
                    'attempted_clean_json_for_decode' => $finalJsonToDecode
                ]);
                // $llmReasoningToStore is already set to $finalJsonToDecode (the cleaned string)
                // $generatedPromptsToStore is already set to []
            }

            Log::info('Preparing to save LlmResponse for PromptLog ID: ' . $promptLog->id, [
                'llm_reasoning_to_store' => $llmReasoningToStore,
                'generated_prompts_to_store' => $generatedPromptsToStore
            ]);

            LlmResponse::create([
                'prompt_log_id' => $promptLog->id,
                'llm_reasoning' => $llmReasoningToStore,
                'generated_prompts' => json_encode($generatedPromptsToStore), // Always encode array to JSON for DB
            ]);

            DB::commit();
            Log::info('Successfully processed and saved prompt and LLM response for PromptLog ID: ' . $promptLog->id);
            return redirect()->route('free.dashboard')->with('success', 'Prompt gerado e salvo com sucesso!');

        } catch (RequestException $e) {
            DB::rollBack();
            Log::error('Guzzle HTTP request failed: ' . $e->getMessage(), [
                'request' => $e->getRequest() ? (string) $e->getRequest()->getBody() : 'N/A',
                'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'N/A',
            ]);
            $errorMessage = 'Erro ao comunicar com o serviço de IA.';
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                if ($statusCode >= 400 && $statusCode < 500) {
                    $errorMessage .= ' Verifique os dados enviados.';
                } elseif ($statusCode >= 500) {
                    $errorMessage .= ' Tente novamente mais tarde.';
                }
            }
            return back()->withErrors(['api_error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('An unexpected error occurred: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Ocorreu um erro inesperado. Tente novamente.'])->withInput();
        }
    }
}