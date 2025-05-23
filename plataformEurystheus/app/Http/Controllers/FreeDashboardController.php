<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use App\Models\LlmResponse; // Adicionar import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException; // Added for error handling
use Illuminate\Support\Facades\Log; // Added for logging

class FreeDashboardController extends Controller
{
    private function extractJsonFromMarkdown($text)
    {
        if (preg_match('/```(?:json)?\\s*(\\{.*\\})\\s*```/is', $text, $matches)) {
            return $matches[1];
        }
        return $text;
    }
    
    public function show()
    {
        // ...existing code...
        return view('free.dashboard', [
            'recentPrompts' => PromptLog::where('anonymous_user', Auth::id())
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function storePrompt(Request $request)
    {
        $validated = $request->validate([
            'scope.objective' => 'required|string|max:1000',
            'scope.constraints' => 'required|string|max:1000',
            'scope.data' => 'required|string|max:1000',
            'scope.audience' => 'required|string|max:1000',
            'scope.output_format' => 'required|string|max:1000',
            'scope.deadlines' => 'required|string|max:1000',
        ]);

        // Verificar limite de 20 registros
        $count = PromptLog::where('anonymous_user', '=', Auth::id())->count();
        if ($count >= 20 && !Auth::user()->isPayed() && !Auth::user()->isAdmin()) { // Allow paid users and admins to exceed this limit or apply different limits
            return response()->json(['error' => 'Limite de 20 agentes atingido para usuários gratuitos.'], 403);
        }

        // Salvar dados anonimizados (sem armazenar email ou dados pessoais)
        $promptLog = PromptLog::create([
            'anonymous_user' => Auth::id(),
            'content' => json_encode($validated['scope'], JSON_UNESCAPED_UNICODE), // Storing the original user scope
        ]);

        // Construct a detailed instructional prompt for Gemini
        $userObjective = $validated['scope']['objective'];
        $userConstraints = $validated['scope']['constraints'];
        $userData = $validated['scope']['data'];
        $userAudience = $validated['scope']['audience'];
        $userOutputFormat = $validated['scope']['output_format'];
        $userDeadlines = $validated['scope']['deadlines'];

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
        Input: {{example_input_1}}
        Output: {{example_output_1}}
        Input: {{user_actual_input}}
        Output:

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

Example of a prompt you might generate for the Target AI (for illustration only, adapt to the user's actual problem):
"Act as a seasoned market research analyst. Based on the following user data: {{user_data_snippet}}, your objective is to {{sub_objective}}.
1. First, identify key trends in the provided data.
2. Second, explain the potential implications of these trends for {{user_audience}}.
3. Finally, summarize your findings in a bullet-point list, followed by a concise recommendation.
Show your reasoning for each step."

Design the prompt(s) for the Target AI now, based on the user's problem scope provided above. Ensure all placeholders are in the format `{{placeholder_name}}`.
PROMPT;

        $client = new Client();
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY not found in .env file.');
            return response()->json(['error' => 'API key configuration error.'], 500);
        }
        try {
            $response = $client->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
                [
                    'json' => [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $instructionalPrompt]
                                ]
                            ]
                        ]
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 60, // Increased timeout for potentially complex generation
                ]
            );

            $body = $response->getBody()->getContents();
            //Log::info("Gemini API Raw Response: {$body}"); // Log raw response

            $geminiResponseData = json_decode($body, true);

            // Extract the text content from Gemini's response
            // The actual generated content is usually within candidates -> content -> parts -> text
            $generatedText = $geminiResponseData['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$generatedText) {
                Log::error('Failed to extract generated text from Gemini response.', ['response' => $geminiResponseData]);
                return response()->json(['error' => 'Error processing LLM response. No text content found.'], 500);
            }

            // Attempt to parse the generated text as JSON (as per our instruction to Gemini)
            // $parsedLLMOutput = json_decode($generatedText, true); // Original attempt
            if (is_array($generatedText)) { // Should not happen with current Gemini API structure but good for robustness
                $parsedLLMOutput = $generatedText;
            } else {
                $cleanJson = $this->extractJsonFromMarkdown($generatedText);
                $parsedLLMOutput = json_decode($cleanJson, true);
            }

            if (!is_array($parsedLLMOutput) || json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse LLM generated text as JSON.', [
                    'generated_text' => $generatedText,
                    'json_error' => json_last_error_msg()
                ]);
                // Fallback: return the raw text if it's not valid JSON, or handle as an error
                $llmResult = [
                    'chain_of_thought_explanation' => 'LLM did not return valid JSON. Raw output: ' . $generatedText,
                    'generated_prompts' => ['Error: Could not parse LLM output.'],
                ];
            } else {
                $llmResult = [
                    'chain_of_thought_explanation' => $parsedLLMOutput['chain_of_thought_explanation'] ?? 'No explanation provided.',
                    'generated_prompts' => $parsedLLMOutput['generated_prompts'] ?? ['No prompts generated.'],
                ];
            }

            LlmResponse::create([
                'prompt_log_id'     => $promptLog->id,
                'llm_reasoning'     => $llmResult['chain_of_thought_explanation'],
                'generated_prompts' => json_encode($llmResult['generated_prompts'], JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'prompt_log_id' => $promptLog->id,
            ], 200);

        } catch (RequestException $e) {
            Log::error("Gemini API Request Exception: " . $e->getMessage(), [
                'request' => $e->getRequest(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return response()->json([
                'error' => 'Failed to communicate with LLM service.',
                'details' => $e->getMessage()
            ], 503);
        } catch (\Exception $e) {
            Log::error("General Exception in storePrompt: " . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}