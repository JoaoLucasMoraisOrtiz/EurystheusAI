<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use App\Models\LlmResponse;
use App\Models\Promotion;
use App\Models\SystemSetting;
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
        Log::debug('extractJsonFromMarkdown: Input text preview (before sanitization)', [
            'text_preview' => substr($text, 0, 500),
            'original_length' => strlen($text)
        ]);

        // Sanitize non-printable ASCII characters, excluding tab, newline, carriage return
        // which are valid in JSON strings (\\t, \\n, \\r) and as whitespace.
        $sanitizedText = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/', '', $text);
        if (strlen($text) !== strlen($sanitizedText)) {
            Log::debug('extractJsonFromMarkdown: Text sanitized', [
                'original_length' => strlen($text),
                'sanitized_length' => strlen($sanitizedText),
                'sanitized_text_preview' => substr($sanitizedText, 0, 500)
            ]);
        } else {
            Log::debug('extractJsonFromMarkdown: No sanitization needed/performed for input text.');
        }
        $textToParse = $sanitizedText; // Use sanitized text for parsing

        // First try to extract JSON from markdown code blocks
        $markdownRegex = '/```(?:json)?\\s*(\\{.*\\})\\s*```/is';
        if (preg_match($markdownRegex, $textToParse, $matches)) {
            Log::debug('extractJsonFromMarkdown: Matched markdown block', [
                'extracted_length' => strlen($matches[1]),
                'match_preview' => substr($matches[1], 0, 200)
            ]);
            return $matches[1];
        }
        Log::debug('extractJsonFromMarkdown: Markdown block regex did NOT match.', [
            'regex' => $markdownRegex, 
            'text_preview_on_fail' => substr($textToParse, 0, 500) // Log text that failed to match
        ]);

        // Try to find JSON object directly in the text (without markdown)
        // This regex attempts to match a valid JSON object starting with { and ending with }
        // It tries to handle nested objects/arrays but might not be perfect for all edge cases.
        $directJsonRegex = '/^\\s*(\\{(?:[^{}]|\\\"|\\\\\\\\|\\\\b|\\\\f|\\\\n|\\\\r|\\\\t|\\\\u[0-9a-fA-F]{4}|\\{(?:[^{}]|\\\"|\\\\\\\\|\\\\b|\\\\f|\\\\n|\\\\r|\\\\t|\\\\u[0-9a-fA-F]{4})*\\})*\\})\\s*$/s';
        if (preg_match($directJsonRegex, $textToParse, $matches)) {
            Log::debug('extractJsonFromMarkdown: Matched direct JSON object (fallback)', [ 
                'extracted_length' => strlen($matches[1]),
                'match_preview' => substr($matches[1], 0, 200)
            ]);
            return $matches[1];
        }
        Log::debug('extractJsonFromMarkdown: Direct JSON object regex (fallback) did NOT match.', [
            'regex' => $directJsonRegex, 
            'text_preview_on_fail' => substr($textToParse, 0, 500) // Log text that failed to match
        ]);
        
        Log::warning('extractJsonFromMarkdown: No JSON structure found after all attempts, returning original (sanitized) text', [
            'text_preview' => substr($textToParse, 0, 500)
        ]);
        return $textToParse; // Return sanitized text if no JSON block is found
    }
    
    public function show()
    {
        $recentPrompts = PromptLog::with('llmResponse') // Eager load LlmResponse
            ->where('anonymous_user', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        // Get active promotion for banners
        $activePromotion = Promotion::getActivePromotion();

        // Get prompts usage for free users
        $promptsUsed = PromptLog::where('anonymous_user', Auth::id())->count();
        $promptsLimit = SystemSetting::get('free_user_prompt_limit', 15);
        $promptsRemaining = max(0, $promptsLimit - $promptsUsed);

        return view('free.dashboard', [
            'recentPrompts' => $recentPrompts,
            'activePromotion' => $activePromotion,
            'promptsUsed' => $promptsUsed,
            'promptsLimit' => $promptsLimit,
            'promptsRemaining' => $promptsRemaining,
        ]);
    }

    public function storePrompt(Request $request)
    {
        Log::info('=== INICIO STOREPROMPT ===', [
            'method' => $request->method(),
            'url' => $request->url(),
            'user_id' => Auth::id(),
            'timestamp' => now()->toDateTimeString(),
            'input_keys' => array_keys($request->all()),
            'input_size' => strlen(json_encode($request->all()))
        ]);

        // Log validation step
        Log::info('Starting validation...');
        
        try {
            $validated = $request->validate([
                'scope.objective' => 'required|string|max:200000', // Increased limit
                'scope.constraints' => 'nullable|string|max:5000000', // Increased limit
                'scope.data' => 'nullable|string|max:1000000', // Increased limit for potentially large data
                'scope.audience' => 'nullable|string|max:200000', // Increased limit
                'scope.output_format' => 'nullable|string|max:5000000', // Increased limit
                'scope.deadlines' => 'required|string|max:200000', // Increased limit
            ]);

            Log::info('Validation passed for PromptLog', [
                'validated_data' => $validated,
                'request_size' => strlen(json_encode($request->all()))
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Unexpected error during validation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erro interno. Tente novamente.'])->withInput();
        }

        Log::info('=== VALIDATION COMPLETED SUCCESSFULLY ===');

        // Log limit check
        Log::info('Checking user prompt limit...');
        $promptsLimit = SystemSetting::get('free_user_prompt_limit', 15); // Use SystemSetting
        $count = PromptLog::where('anonymous_user', Auth::id())->count();
        Log::info('Current user prompt count', ['count' => $count, 'limit' => $promptsLimit]);
        
        if ($count >= $promptsLimit) { // Use $promptsLimit
            Log::warning('User exceeded prompt limit', ['user_id' => Auth::id(), 'count' => $count]);
            return back()->withErrors(['limit' => 'Você atingiu o limite de 15 prompts para usuários gratuitos. Faça upgrade para continuar usando a plataforma!'])->withInput();
        }

        $userScope = $request->input('scope');
        $userObjective = $userScope['objective'] ?? '';
        $userConstraints = $userScope['constraints'] ?? '';
        $userData = $userScope['data'] ?? '';
        $userAudience = $userScope['audience'] ?? '';
        $userOutputFormat = $userScope['output_format'] ?? '';
        $userDeadlines = $userScope['deadlines'] ?? '';

        Log::info('User scope extracted', [
            'objective_length' => strlen($userObjective),
            'constraints_length' => strlen($userConstraints),
            'data_length' => strlen($userData),
            'audience_length' => strlen($userAudience),
            'output_format_length' => strlen($userOutputFormat),
            'deadlines_length' => strlen($userDeadlines)
        ]);

        // STEP 1: Analyze and infer advanced prompt engineering techniques
        $analysisPrompt = <<<PROMPT
You are an expert AI prompt engineer, acting as a "Prompt Architect." Your immediate task is to analyze a user's problem scope and infer which advanced prompt engineering techniques are most suitable for a "Target AI" to solve it.

You must deduce the need for specific techniques based *solely* on the provided problem description. Do not generate the final prompt(s) for the Target AI in this step.

**User's Problem Scope:**
- Objective: {$userObjective}
- Constraints/Limitations: {$userConstraints}
- Available Data/Information: {$userData}
- Target Audience/Stakeholders: {$userAudience}
- Desired Output Format/Result: {$userOutputFormat}
- Deadlines/Success Metrics: {$userDeadlines}

**Your Task (Step 1 of 2): Infer Advanced Prompting Techniques**

Analyze the 'User's Problem Scope' and infer the implied requirements. For each requirement, determine which advanced prompt engineering technique(s) from the list below would be most beneficial for the Target AI.

**Advanced Prompt Engineering Techniques to Consider for Inference:**
- **Decomposed Prompting (DecomP):** For complex objectives that can be broken into independent sub-tasks or require token-level processing.
- **Program-of-Thoughts (PoT):** For precise numerical calculations, logical operations, or when external, deterministic computation is needed.
- **Skeleton-of-Thought (SoT):** For long, structured outputs where reduced latency is critical and the structure can be planned upfront.
- **Tree-of-Thoughts (ToT):** For multi-step reasoning, exploration of multiple solution paths, lookahead, or backtracking (e.g., puzzles, complex strategic decisions).
- **Recursive Decomposition of Logical Thoughts (RDoLT):** For highly complex, multi-level reasoning, especially mathematical/logical tasks, benefiting from robust self-correction and learning from rejected paths.
- **Chain-of-Logic (CoL):** For rule-based reasoning, especially in domains like law, where composite rules need to be broken down, evaluated, and reassembled logically.
- **Automatic Reasoning and Tool-use (ART):** For tasks requiring external tools (search, code execution, databases) for accurate information or computation beyond the Target AI's internal knowledge.
- **Persona Assignment:** For when a specific domain expertise or tone is crucial.
- **Few-Shot Examples:** For tasks involving specific formats, styles, or patterns.
- **Chain-of-Thought (CoT) / Zero-Shot CoT:** For tasks benefiting from transparency in reasoning or complex problem-solving.
- **Contextualization:** Always necessary.
- **Output Structuring:** Always necessary.

**Your Output Format:**
Provide a JSON object with the following keys:
- "inferred_techniques": An array of strings, listing the names of the advanced prompt engineering techniques you infer as most suitable.
- "reasoning_for_inference": A string explaining briefly *why* each technique was chosen based on the 'User's Problem Scope'.
PROMPT;

        Log::info('Analysis prompt created', ['prompt_length' => strlen($analysisPrompt)]);

        $promptLog = null; // Initialize to null

        Log::info('Starting database transaction...');
        DB::beginTransaction();
        try {
            // 1. Save the initial PromptLog
            Log::info('Creating PromptLog record...');
            $promptLog = PromptLog::create([
                'anonymous_user' => Auth::id(),
                'content' => json_encode($userScope), // Save the original user input
            ]);
            Log::info('PromptLog created successfully', ['id' => $promptLog->id]);

            Log::info('Starting two-step prompt generation for PromptLog ID: ' . $promptLog->id);
            
            Log::info('Initializing Guzzle HTTP client...');
            $client = new Client(['timeout' => 60]); // Add timeout
            $apiKey = config('gemini.api_key');
            $geminiModel = config('gemini.model', 'gemini-2.0-flash'); // Fallback to default if not set
            $geminiApiUrl = config('gemini.url', 'https://generativelanguage.googleapis.com/v1beta/models/');

            if (!$apiKey || !$geminiModel || !$geminiApiUrl) {
                Log::error('Gemini API configuration missing in config/gemini.php or .env', [
                    'api_key_set' => !empty($apiKey),
                    'model_set' => !empty($geminiModel),
                    'url_set' => !empty($geminiApiUrl)
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro de configuração do serviço de IA. Verifique as configurações e tente novamente mais tarde.'])->withInput();
            }

            Log::info('Gemini API Key loaded successfully for PromptLog ID: ' . $promptLog->id . ' (length: ' . strlen($apiKey) . ')');
            Log::info('Gemini Model and URL loaded', ['model' => $geminiModel, 'url' => $geminiApiUrl]);

            // STEP 1: Call Gemini API for technique analysis
            Log::info('=== STEP 1: Calling Gemini API for technique analysis ===', [
                'prompt_log_id' => $promptLog->id,
                'timestamp' => now()->toDateTimeString(),
                'api_url' => rtrim($geminiApiUrl, '/') . '/' . $geminiModel . ':generateContent'
            ]);
            
            $analysisRequestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $analysisPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048,
                ]
            ];
            
            Log::info('API request data prepared', [
                'request_size' => strlen(json_encode($analysisRequestData)),
                'contents_count' => count($analysisRequestData['contents']),
                'max_tokens' => $analysisRequestData['generationConfig']['maxOutputTokens']
            ]);
            
            $step1ApiFullUrl = rtrim($geminiApiUrl, '/') . '/' . $geminiModel . ':generateContent?key=' . $apiKey;
            Log::info('Step 1 API Full URL: ' . $step1ApiFullUrl);
            $analysisResponse = $client->post($step1ApiFullUrl, [
                'json' => $analysisRequestData
            ]);
            
            // Check if the response is successful first
            if ($analysisResponse->getStatusCode() !== 200) {
                Log::error('Step 1 API call failed', [
                    'prompt_log_id' => $promptLog->id,
                    'status_code' => $analysisResponse->getStatusCode(),
                    'headers' => $analysisResponse->getHeaders()
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro ao processar análise de técnicas. Status: ' . $analysisResponse->getStatusCode()])->withInput();
            }

            // Get response body only once
            $analysisBody = $analysisResponse->getBody()->getContents();
            Log::info('Step 1 API response received', [
                'prompt_log_id' => $promptLog->id,
                'status_code' => $analysisResponse->getStatusCode(),
                'body_length' => strlen($analysisBody),
                'body_preview' => substr($analysisBody, 0, 200) . '...'
            ]);
            $analysisData = json_decode($analysisBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode analysis response JSON for PromptLog ID: ' . $promptLog->id, [
                    'error' => json_last_error_msg(), 
                    'body_preview' => substr($analysisBody, 0, 500)
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro ao processar análise de técnicas. Resposta inválida do serviço de IA (JSON decode failed).'])->withInput();
            }

            // Check for promptFeedback blockReason
            if (isset($analysisData['promptFeedback']['blockReason'])) {
                Log::error('Prompt blocked by API in Step 1 for PromptLog ID: ' . $promptLog->id, [
                    'blockReason' => $analysisData['promptFeedback']['blockReason'],
                    'safetyRatings' => $analysisData['promptFeedback']['safetyRatings'] ?? []
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Seu prompt foi bloqueado por questões de segurança na Etapa 1. Tente reformulá-lo.'])->withInput();
            }

            if (!isset($analysisData['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('No valid content found in API response for Step 1 for PromptLog ID: ' . $promptLog->id, [
                    'api_response_structure' => json_encode($analysisData, JSON_PRETTY_PRINT)
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Resposta da API (Etapa 1) inválida ou não contém o texto esperado.'])->withInput();
            }
            $mainAnalysisText = $analysisData['candidates'][0]['content']['parts'][0]['text'];

            Log::info('Main analysis text extracted', [
                'text_length' => strlen($mainAnalysisText),
                'text_preview' => substr($mainAnalysisText, 0, 300) . '...'
            ]);

            // Extract techniques from analysis
            $finalAnalysisJson = $this->extractJsonFromMarkdown($mainAnalysisText);
            Log::info('Techniques extraction attempted', [
                'extracted_json_length' => strlen($finalAnalysisJson),
                'json_preview' => substr($finalAnalysisJson, 0, 200) . '...'
            ]);
            
            $analysisResult = json_decode($finalAnalysisJson, true);
            
            $inferredTechniques = [];
            $reasoningForInference = '';
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($analysisResult)) {
                $inferredTechniques = $analysisResult['inferred_techniques'] ?? [];
                $reasoningForInference = $analysisResult['reasoning_for_inference'] ?? '';
                Log::info('Successfully extracted techniques for PromptLog ID: ' . $promptLog->id, [
                    'inferred_techniques' => $inferredTechniques,
                    'techniques_count' => count($inferredTechniques),
                    'reasoning_length' => strlen($reasoningForInference)
                ]);
            } else {
                Log::warning('Could not parse techniques from analysis for PromptLog ID: ' . $promptLog->id . '. Using fallback.', [
                    'json_error' => json_last_error_msg(),
                    'analysis_result_type' => gettype($analysisResult),
                    'raw_json_sample' => substr($finalAnalysisJson, 0, 500)
                ]);
                $inferredTechniques = ['Chain-of-Thought', 'Persona Assignment', 'Contextualization', 'Output Structuring'];
                $reasoningForInference = 'Fallback techniques used due to parsing error.';
            }

            Log::info('=== STEP 1 COMPLETED SUCCESSFULLY ===', [
                'prompt_log_id' => $promptLog->id,
                'techniques_extracted' => count($inferredTechniques),
                'proceeding_to_step_2' => true
            ]);

            // STEP 2: Generate optimized prompts using inferred techniques
            $techniquesString = json_encode($inferredTechniques);
            
            Log::info('=== STEP 2: Preparing prompt generation ===', [
                'prompt_log_id' => $promptLog->id,
                'techniques_to_use' => $inferredTechniques,
                'timestamp' => now()->toDateTimeString()
            ]);
            
            $generationPrompt = <<<PROMPT
You are an expert AI prompt engineer, acting as a "Prompt Architect." Your task is to design one or more highly effective and optimized prompts for a "Target AI" to solve a specific user problem. You will use the advanced prompt engineering techniques that were previously inferred and provided to you.

**User's Problem Scope (Original Context):**
- Objective: {$userObjective}
- Constraints/Limitations: {$userConstraints}
- Available Data/Information: {$userData}
- Target Audience/Stakeholders: {$userAudience}
- Desired Output Format/Result: {$userOutputFormat}
- Deadlines/Success Metrics: {$userDeadlines}

**Inferred Advanced Techniques (from previous step):**
{$techniquesString}

**Reasoning for Technique Selection:**
{$reasoningForInference}

**Your Task (Step 2 of 2): Generate Prompt(s) for Target AI**

Based on the 'User's Problem Scope' and the 'Inferred Advanced Techniques', construct the complete prompt(s) for the Target AI. Ensure all necessary context, persona, and instructions for applying the inferred techniques are embedded.

**Instructions for Generating Prompts for the Target AI:**

1.  **Clarity and Specificity:** Every instruction must be crystal clear and unambiguous.
2.  **Persona Assignment:** If "Persona Assignment" was inferred, assign a specific, relevant expert persona to the Target AI.
3.  **Task Decomposition & Step-by-Step Guidance:** If "Decomposed Prompting", "Tree-of-Thoughts", "Recursive Decomposition of Logical Thoughts", or "Chain-of-Logic" were inferred, break down the task into sub-tasks. Provide clear, numbered instructions if applicable.
4.  **Placeholder Usage:** Use `{{placeholder_name}}` for all variable data that the end-user will provide to the Target AI. Ensure names are descriptive.
5.  **Few-Shot Examples:** If "Few-Shot Examples" was inferred, integrate 1-3 high-quality examples.
6.  **Reasoning Instructions:**
    * If "Chain-of-Thought" was inferred, include "Let's think step by step." or similar.
    * If "Program-of-Thoughts" was inferred, instruct the Target AI to "Generate Python code for calculations" and indicate it will be executed externally.
    * If "Skeleton-of-Thought" was inferred, instruct the Target AI to "First, generate a concise outline or skeleton of the response, then expand each point."
    * If "Tree-of-Thoughts" was inferred, instruct the Target AI to "Explore multiple solution paths, evaluating each option. Be prepared to backtrack."
    * If "Recursive Decomposition of Logical Thoughts" was inferred, guide it to "Recursively decompose the problem into easy, intermediate, and final sub-tasks, evaluating solutions for Logical Validity, Coherence, Simplicity, and Adaptability."
    * If "Chain-of-Logic" was inferred, structure the prompt to follow a logical decomposition process.
    * If "Automatic Reasoning and Tool-use" was inferred, instruct the Target AI on how to use external tools.
7.  **Contextualization:** Ensure all necessary context from the 'User's Problem Scope' is clearly embedded or referenced.
8.  **Output Structuring:** Explicitly define the desired output format for the Target AI, directly referencing the 'Desired Output Format/Result'.

**Your Output Format:**
Provide a JSON object with the following keys:
- "chain_of_thought_explanation": A string explaining YOUR reasoning for the designed prompt(s). Detail which inferred techniques you incorporated and why they are suitable for the user's specific problem.
- "generated_prompts": An array of strings. Each string in the array is a complete, ready-to-use prompt designed for the Target AI. If only one prompt is needed, this should be an array with a single element. Ensure all variable inputs are represented by `{{placeholder_name}}`.
PROMPT;

            Log::info('Generation prompt created', [
                'prompt_length' => strlen($generationPrompt),
                'techniques_included' => count($inferredTechniques)
            ]);

            Log::info('=== STEP 2: Calling Gemini API for prompt generation ===', [
                'prompt_log_id' => $promptLog->id,
                'timestamp' => now()->toDateTimeString(),
                'api_url' => rtrim($geminiApiUrl, '/') . '/' . $geminiModel . ':generateContent'
            ]);

            $generationRequestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $generationPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8,
                    'maxOutputTokens' => 4096,
                ]
            ];
            
            Log::info('Generation API request data prepared', [
                'request_size' => strlen(json_encode($generationRequestData)),
                'max_tokens' => $generationRequestData['generationConfig']['maxOutputTokens']
            ]);

            Log::info('🔥 ABOUT TO CALL STEP 2 API 🔥', [
                'prompt_log_id' => $promptLog->id,
                'api_url' => rtrim($geminiApiUrl, '/') . '/' . $geminiModel . ':generateContent',
                'has_api_key' => !empty($apiKey),
                'request_ready' => true
            ]);

            $step2ApiFullUrl = rtrim($geminiApiUrl, '/') . '/' . $geminiModel . ':generateContent?key=' . $apiKey;
            Log::info('Step 2 API Full URL: ' . $step2ApiFullUrl);
            $generationResponse = $client->post($step2ApiFullUrl, [
                'json' => $generationRequestData
            ]);

            // Check if Step 2 response is successful first
            if ($generationResponse->getStatusCode() !== 200) {
                Log::error('Step 2 API call failed', [
                    'prompt_log_id' => $promptLog->id,
                    'status_code' => $generationResponse->getStatusCode(),
                    'headers' => $generationResponse->getHeaders()
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro ao processar geração de prompts. Status: ' . $generationResponse->getStatusCode()])->withInput();
            }

            // Get response body only once
            $generationBody = $generationResponse->getBody()->getContents();
            Log::info('Step 2 API response received', [
                'prompt_log_id' => $promptLog->id,
                'status_code' => $generationResponse->getStatusCode(),
                'body_length' => strlen($generationBody),
                'body_preview' => substr($generationBody, 0, 200) . '...'
            ]);
            $generationData = json_decode($generationBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to decode generation response JSON for PromptLog ID: ' . $promptLog->id, [
                    'error' => json_last_error_msg(), 
                    'body_preview' => substr($generationBody, 0, 500)
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Erro ao processar geração de prompts. Resposta inválida do serviço de IA (JSON decode failed).'])->withInput();
            }

            // Check for promptFeedback blockReason
            if (isset($generationData['promptFeedback']['blockReason'])) {
                Log::error('Prompt blocked by API in Step 2 for PromptLog ID: ' . $promptLog->id, [
                    'blockReason' => $generationData['promptFeedback']['blockReason'],
                    'safetyRatings' => $generationData['promptFeedback']['safetyRatings'] ?? []
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Seu prompt foi bloqueado por questões de segurança na Etapa 2. Tente reformulá-lo.'])->withInput();
            }

            if (!isset($generationData['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('No valid content found in API response for Step 2 for PromptLog ID: ' . $promptLog->id, [
                    'api_response_structure' => json_encode($generationData, JSON_PRETTY_PRINT)
                ]);
                DB::rollBack();
                return back()->withErrors(['api_error' => 'Resposta da API (Etapa 2) inválida ou não contém o texto esperado.'])->withInput();
            }
            $mainGenerationText = $generationData['candidates'][0]['content']['parts'][0]['text'];

            Log::info('Main generation text extracted', [
                'text_length' => strlen($mainGenerationText),
                'text_preview' => substr($mainGenerationText, 0, 300) . '...'
            ]);

            // Extract final prompts
            $finalGenerationJson = $this->extractJsonFromMarkdown($mainGenerationText);
            Log::info('Final prompts extraction attempted', [
                'prompt_log_id' => $promptLog->id, // Added prompt_log_id
                'extracted_json_length' => strlen($finalGenerationJson),
                // Increased preview and added ellipsis check
                'json_preview' => substr($finalGenerationJson, 0, 500) . (strlen($finalGenerationJson) > 500 ? '...' : '')
            ]);
            
            $generationResult = json_decode($finalGenerationJson, true);

            $llmReasoningToStore = $finalGenerationJson; // Default fallback for reasoning if parsing fails
            $generatedPromptsToStore = []; // Initialize as empty

            if (json_last_error() === JSON_ERROR_NONE && is_array($generationResult)) {
                Log::info('Successfully parsed final generation for PromptLog ID: ' . $promptLog->id, [
                    'result_keys' => array_keys($generationResult),
                    'has_generated_prompts' => isset($generationResult['generated_prompts']), // Added
                    'prompts_count' => isset($generationResult['generated_prompts']) ? count($generationResult['generated_prompts']) : 0,
                    'has_chain_of_thought' => isset($generationResult['chain_of_thought_explanation']), // Added
                ]);
                
                // Combine both reasoning steps for comprehensive explanation
                $step1Reasoning = "**Step 1 - Technique Analysis:**\n" . $reasoningForInference . "\n\n**Inferred Techniques:** " . implode(', ', $inferredTechniques) . "\n\n";
                $step2Reasoning = "**Step 2 - Prompt Generation:**\n" . ($generationResult['chain_of_thought_explanation'] ?? 'No explanation provided by model.');
                
                $llmReasoningToStore = $step1Reasoning . $step2Reasoning;
                $generatedPromptsToStore = $generationResult['generated_prompts'] ?? []; // Ensure this is an array

                if (empty($generatedPromptsToStore)) {
                    Log::warning('Parsed final generation successfully, but "generated_prompts" key was missing or empty in the response.', [
                        'prompt_log_id' => $promptLog->id,
                        'generation_result_keys' => array_keys($generationResult),
                        'raw_generation_result_preview' => substr(json_encode($generationResult), 0, 500) . (strlen(json_encode($generationResult)) > 500 ? '...' : '')
                    ]);
                }

            } else {
                Log::error('Failed to parse final generation JSON for PromptLog ID: ' . $promptLog->id . '. Error: ' . json_last_error_msg(), [
                    'json_error_code' => json_last_error(),
                    'generation_result_type' => gettype($generationResult), // Will be null if json_decode fails
                    // Log the string that failed to parse
                    'final_generation_json_to_parse_preview' => substr($finalGenerationJson, 0, 1000) . (strlen($finalGenerationJson) > 1000 ? '...' : ''),
                    // Log the original model output for comparison
                    'main_generation_text_preview' => substr($mainGenerationText, 0, 1000) . (strlen($mainGenerationText) > 1000 ? '...' : '')
                ]);
                // Refined fallback reasoning message
                $llmReasoningToStore = "**Analysis Techniques:** " . implode(', ', $inferredTechniques) . "\n\n**Reasoning:** " . $reasoningForInference . "\n\n**Raw Generation Response (that failed to parse as JSON):** " . $finalGenerationJson;
                // $generatedPromptsToStore remains []
            }

            Log::info('=== SAVING RESULTS ===', [
                'prompt_log_id' => $promptLog->id,
                'techniques_used' => $inferredTechniques,
                'generated_prompts_to_save_count' => count($generatedPromptsToStore), // Added
                'generated_prompts_to_save_preview' => substr(json_encode($generatedPromptsToStore), 0, 200) . (strlen(json_encode($generatedPromptsToStore)) > 200 ? '...' : ''), // Added
                'reasoning_length' => strlen($llmReasoningToStore),
                'timestamp' => now()->toDateTimeString()
            ]);

            $llmResponse = LlmResponse::create([
                'prompt_log_id' => $promptLog->id,
                'llm_reasoning' => $llmReasoningToStore,
                'generated_prompts' => json_encode($generatedPromptsToStore),
            ]);
            
            Log::info('LlmResponse created successfully', ['id' => $llmResponse->id]);

            DB::commit();
            Log::info('=== TRANSACTION COMMITTED SUCCESSFULLY ===', [
                'prompt_log_id' => $promptLog->id,
                'llm_response_id' => $llmResponse->id,
                'total_prompts' => count($generatedPromptsToStore),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return redirect()->route('dashboard')->with('success', 'Prompt avançado gerado com sucesso usando técnicas otimizadas!');

        } catch (RequestException $e) {
            DB::rollBack();
            Log::error('=== GUZZLE HTTP REQUEST FAILED ===', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'request_uri' => $e->getRequest() ? $e->getRequest()->getUri() : 'N/A',
                'request_body' => $e->getRequest() ? (string) $e->getRequest()->getBody() : 'N/A',
                'response_status' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'response_body' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'N/A',
                'prompt_log_id' => $promptLog ? $promptLog->id : 'null'
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
            Log::error('=== UNEXPECTED ERROR OCCURRED ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'prompt_log_id' => $promptLog ? $promptLog->id : 'null'
            ]);
            return back()->withErrors(['error' => 'Ocorreu um erro inesperado. Tente novamente.'])->withInput();
        }
    }
}