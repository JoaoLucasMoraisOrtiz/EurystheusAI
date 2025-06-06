<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\PromptLog;
use App\Models\LlmResponse;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Str; // Import Str facade

class PromptChatController extends Controller
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function index()
    {
        Session::forget([
            'chat_messages', 
            'conversation_state', 
            'collected_data_for_prompt', 
            'prompt_plan', 
            'final_dossier_content',
            'generated_prompt_id' // Manter para compatibilidade com getSavedPrompt se necessário
        ]);
        Session::put('conversation_state', 'gathering_info');

        $initial_system_prompt = <<<PROMPT
Você é o Assistente Dinâmico de Prompt do EurystheusAI. Sua principal missão é conversar com usuários, muitos dos quais podem não ter experiência com IA, para entender profundamente os problemas que eles querem resolver ou as tarefas que desejam realizar com o auxílio de uma IA.

Seu processo é:
1.  **Conversa Amigável e Investigativa:** Faça perguntas claras e simples para extrair todos os detalhes relevantes. Não presuma conhecimento técnico. Explore o objetivo do usuário, o público-alvo, o formato de saída desejado, o tom/estilo, o contexto de uso, exemplos, casos específicos, exceções e qualquer outra informação que possa ser crucial.
2.  **Coleta de Dados:** Reúna todas essas informações.
3.  **Sem Geração Prematura:** NÃO tente criar o prompt final ou um plano de prompts até que você tenha certeza absoluta de que coletou informações suficientes.
4.  **Validação do Entendimento:** Quando sentir que tem um entendimento completo, você deve APRESENTAR um resumo detalhado de TUDO que você entendeu e PERGUNTAR explicitamente ao usuário se o seu resumo está correto e completo.
5.  **Sinalização de Conclusão da Coleta:** Somente APÓS o usuário confirmar verbalmente que seu resumo está correto e completo, sua PRÓXIMA resposta DEVE SER APENAS e EXATAMENTE: "USER_CONFIRMED_SUMMARY: [Aqui você insere o resumo completo que o usuário acabou de confirmar]". Não use esta frase em nenhuma outra situação.

Comece a conversa se apresentando brevemente e fazendo uma pergunta aberta para o usuário descrever o problema ou a tarefa que ele tem em mente. Por exemplo: "Olá! Sou seu assistente especializado em criar soluções com IA. Para começarmos, poderia me contar um pouco sobre o desafio que você gostaria de resolver ou a tarefa que precisa automatizar?"
PROMPT;

        $firstAIMessage = "";
        try {
            $firstAIMessage = $this->geminiService->chatWithPrompt($initial_system_prompt);
        } catch (\Exception $e) {
            Log::error("GeminiService error on initial prompt: " . $e->getMessage());
            // Fallback message in case of API error
        }
        
        $chatHistory = [];
        if (!empty(trim($firstAIMessage))) {
            $chatHistory[] = ['role' => 'assistant', 'content' => trim($firstAIMessage)];
        } else {
            $fallbackMessage = "Olá! Sou seu assistente de IA. Poderia me descrever o problema que você gostaria de resolver ou a tarefa que precisa de ajuda para criar um prompt?";
            $chatHistory[] = ['role' => 'assistant', 'content' => $fallbackMessage];
        }
        Session::put('chat_messages', $chatHistory);

        return view('prompt.chat', [
            'chatHistory' => $chatHistory,
            'dossier' => null,
            'currentStep' => 1, // Represents conversation turn
            'collectedInfo' => [] 
        ]);
    }

    public function message(Request $request)
    {
        $userMessageContent = trim($request->input('message', ''));

        if (empty($userMessageContent)) {
            return response()->json(['error' => 'A mensagem não pode estar vazia.'], 400);
        }

        $chatMessages = Session::get('chat_messages', []);
        $chatMessages[] = ['role' => 'user', 'content' => $userMessageContent];
        // Session::put('chat_messages', $chatMessages); // Save later after AI response

        $conversationState = Session::get('conversation_state', 'gathering_info');
        
        $llmConversationHistory = "";
        // Include up to last N messages to keep prompt size manageable if necessary, or full history
        $historyToConsider = $chatMessages; // array_slice($chatMessages, -10); 
        foreach ($historyToConsider as $msg) {
            $roleName = $msg['role'] === 'user' ? 'Usuário' : 'Assistente';
            $llmConversationHistory .= $roleName . ": " . $msg['content'] . "\n";
        }

        if ($conversationState === 'gathering_info') {
            $promptForAI = <<<PROMPT
Você é o Assistente Dinâmico de Prompt do EurystheusAI. Sua tarefa é continuar a conversa com o usuário para entender completamente o problema dele.

Histórico da Conversa Atual:
{$llmConversationHistory}

Instruções para o Assistente:
1.  Analise a última mensagem do usuário.
2.  Se você precisar de mais informações para entender completamente o problema, formule uma pergunta clara e concisa para o usuário. Continue a conversa de forma natural e investigativa.
3.  Se você acredita que já coletou TODAS as informações necessárias e tem um entendimento COMPLETO do problema do usuário:
    a.  Primeiro, APRESENTE um resumo detalhado de TUDO que você entendeu sobre as necessidades do usuário (objetivo, público, formato, tom, contexto, exemplos, exceções, etc.).
    b.  Depois, PERGUNTE explicitamente ao usuário se o seu resumo está correto e completo. Ex: "Entendi corretamente suas necessidades? Há algo a adicionar ou corrigir?".
    c.  Se o usuário confirmar que seu resumo está correto e completo (ex: "sim", "está perfeito", "correto"), sua PRÓXIMA resposta DEVE SER APENAS e EXATAMENTE: "USER_CONFIRMED_SUMMARY: [Aqui você insere o resumo completo que o usuário acabou de confirmar]".
4.  NÃO use o texto "USER_CONFIRMED_SUMMARY:" em nenhuma outra situação. Apenas após o usuário confirmar seu resumo final.

Qual sua resposta ou próxima pergunta para o usuário?
PROMPT;
            $aiResponseContent = "";
            try {
                $aiResponseContent = trim($this->geminiService->chatWithPrompt($promptForAI));
            } catch (\Exception $e) {
                Log::error("GeminiService error during conversation: " . $e->getMessage());
                $aiResponseContent = "Desculpe, ocorreu um erro de comunicação com meus sistemas. Poderia tentar novamente?";
            }

            if (empty($aiResponseContent)) {
                 $aiResponseContent = "Não consegui processar sua última mensagem. Poderia reformular ou tentar novamente?";
            }

            if (strpos($aiResponseContent, "USER_CONFIRMED_SUMMARY:") === 0) {
                $confirmedSummary = trim(str_replace("USER_CONFIRMED_SUMMARY:", "", $aiResponseContent));
                
                $chatMessages[] = ['role' => 'assistant', 'content' => "Ótimo! Entendido. Agora vou preparar seu prompt otimizado."]; // User-facing ack
                Session::put('chat_messages', $chatMessages);
                Session::put('collected_data_for_prompt', ['summary' => $confirmedSummary, 'full_conversation_log' => $llmConversationHistory]);
                Session::put('conversation_state', 'generating_plan');

                Log::info("User confirmed summary. Summary: " . $confirmedSummary);

                // Trigger planning and generation
                try {
                    $planningResultJson = $this->createRobustPlan(
                        Session::get('collected_data_for_prompt'),
                        $llmConversationHistory
                    );
                    $planningResult = json_decode($planningResultJson, true);

                    if (!$planningResult || !isset($planningResult['plan']) || !isset($planningResult['analysis'])) {
                        Log::error("Invalid planning result structure: " . $planningResultJson);
                        throw new \Exception("Formato de plano inválido recebido da IA.");
                    }
                    Session::put('prompt_plan', $planningResult);

                    $promptsResult = $this->generatePromptsChain($planningResult, Session::get('collected_data_for_prompt'));
                    $finalUserResponse = $this->buildFinalDossierResponse($planningResult, $promptsResult, Session::get('collected_data_for_prompt'));

                    // Add the final dossier to chat history for the user to see
                    $chatMessages[] = ['role' => 'assistant', 'content' => $finalUserResponse];
                    Session::put('chat_messages', $chatMessages); // Update with final response
                    Session::put('final_dossier_content', $finalUserResponse);

                    $primaryPromptToSave = "";
                    if (isset($promptsResult['prompts'])) {
                        if (is_array($promptsResult['prompts']) && count($promptsResult['prompts']) > 0) {
                            // Check if prompts are stored as an array of strings or array of arrays (with 'prompt' key)
                            if (isset($promptsResult['prompts'][0]['prompt'])) {
                                $primaryPromptToSave = $promptsResult['prompts'][0]['prompt'];
                            } elseif (is_string($promptsResult['prompts'][0])) {
                                $primaryPromptToSave = $promptsResult['prompts'][0];
                            }
                        } elseif (is_string($promptsResult['prompts'])) {
                            $primaryPromptToSave = $promptsResult['prompts'];
                        }
                    }
                    $this->savePromptToDatabase(Session::get('collected_data_for_prompt'), $primaryPromptToSave, $finalUserResponse, $planningResult);
                    
                    return response()->json([
                        'message' => $finalUserResponse, // This is the dossier itself
                        'history' => $chatMessages, // Full history including the dossier
                        'done' => true,
                        'dossier' => $this->generateFinalDossierDisplay($planningResult, $promptsResult)
                    ]);

                } catch (\Exception $e) {
                    Log::error("Error during prompt generation phase: " . $e->getMessage());
                    $errorMessage = "Desculpe, encontrei um problema ao gerar seu prompt final. Detalhes: " . $e->getMessage() . ". Por favor, tente iniciar uma nova conversa.";
                    $chatMessages[] = ['role' => 'assistant', 'content' => $errorMessage];
                    Session::put('chat_messages', $chatMessages);
                    Session::put('conversation_state', 'gathering_info'); // Revert state
                    return response()->json(['message' => $errorMessage, 'history' => $chatMessages, 'done' => false, 'error' => true]);
                }

            } else {
                // Regular conversation turn
                $chatMessages[] = ['role' => 'assistant', 'content' => $aiResponseContent];
                Session::put('chat_messages', $chatMessages);
                return response()->json(['message' => $aiResponseContent, 'history' => $chatMessages, 'done' => false]);
            }

        } elseif (in_array($conversationState, ['generating_plan', 'generating_prompts'])) {
            // User should ideally not be able to send messages in this state
            $chatMessages[] = ['role' => 'assistant', 'content' => 'Estou processando a criação do seu prompt. Por favor, aguarde um momento.'];
            Session::put('chat_messages', $chatMessages);
            return response()->json([
                'message' => 'Estou processando a criação do seu prompt. Por favor, aguarde um momento.',
                'history' => $chatMessages,
                'done' => false,
                'processing' => true
            ]);
        }
        
        // Fallback for unknown state
        Log::warning("Unknown conversation state: " . $conversationState);
        $chatMessages[] = ['role' => 'assistant', 'content' => 'Ocorreu um erro inesperado com o estado da nossa conversa. Sugiro recomeçar.'];
        Session::put('chat_messages', $chatMessages);
        return response()->json(['error' => 'Estado desconhecido da conversa.', 'history' => $chatMessages], 500);
    }

    private function generateFinalDossierDisplay($planningResult, $promptsResult)
    {
        $dossier = [];
        $dossier["Análise Inteligente"] = $planningResult['analysis'] ?? 'N/A';
        
        $approach = "Prompt Único";
        $promptsCount = 0;
        if (isset($promptsResult['details']) && is_array($promptsResult['details'])) {
            $promptsCount = count($promptsResult['details']);
        } elseif (isset($promptsResult['prompts']) && is_string($promptsResult['prompts'])) {
            $promptsCount = 1;
        }

        if (($planningResult['approach'] ?? 'single_prompt') === 'chain_of_prompts') {
            $countToDisplay = $promptsCount > 0 ? $promptsCount : ($planningResult['prompts_needed'] ?? 0);
            $approach = "Cadeia de Prompts ({$countToDisplay})";
        }
        $dossier["Estratégia Adotada"] = $approach;
        $dossier["Raciocínio Técnico"] = $planningResult['reasoning'] ?? 'N/A';

        if (isset($promptsResult['details']) && is_array($promptsResult['details'])) {
            foreach ($promptsResult['details'] as $idx => $pData) {
                $promptContent = is_array($pData) ? ($pData['prompt'] ?? json_encode($pData)) : $pData;
                $dossier["Prompt " . ($idx + 1)] = $promptContent;
            }
        } elseif (isset($promptsResult['prompts']) && is_string($promptsResult['prompts'])) { // Single prompt scenario
                 $dossier["Prompt Gerado"] = $promptsResult['prompts'];
        }
        return $dossier;
    }
    
    // ETAPA 1: Criar plano robusto para cadeia de pensamento
    private function createRobustPlan($collectedInfo, $conversationHistory)
    {
        // $collectedInfo now contains ['summary' => "...", 'full_conversation_log' => "..."]
        $userSummary = $collectedInfo['summary'] ?? 'Nenhum resumo disponível.';
        // $conversationHistory is the raw log passed

        $prompt = <<<PROMPT
CONTEXTO DA CONVERSA:
O usuário teve uma conversa detalhada com um assistente de IA para definir suas necessidades para a criação de um ou mais prompts de IA.
O resumo das informações coletadas diretamente do usuário é:
{$userSummary}

Você também pode consultar o LOG COMPLETO DA CONVERSA se precisar de mais detalhes contextuais:
{$conversationHistory}

INSTRUÇÕES PARA VOCÊ (GERADOR DE PLANO):
Baseando-se PRINCIPALMENTE no RESUMO FORNECIDO e utilizando o LOG COMPLETO apenas para esclarecimentos se estritamente necessário:
1.  Analise profundamente o que o usuário precisa, conforme descrito no resumo.
2.  Determine se a necessidade do usuário é melhor atendida por um único prompt abrangente ou por uma cadeia de prompts menores e interdependentes.
3.  Crie um plano detalhado explicando sua abordagem. Seja específico sobre o que cada prompt (se mais de um) fará.
4.  Defina quantos prompts serão necessários (mínimo 1, máximo 5).
5.  Forneça um raciocínio claro para sua escolha de abordagem (prompt único vs. cadeia).

FORMATO DA RESPOSTA (ESTRITAMENTE JSON):
```json
{
    "analysis": "Análise detalhada das necessidades do usuário, baseada no resumo fornecido.",
    "approach": "single_prompt|chain_of_prompts",
    "reasoning": "Explicação detalhada do raciocínio e estratégia adotada (por que single ou chain).",
    "prompts_needed": <number_of_prompts>,
    "plan": [
        {
            "step": 1,
            "description": "Descrição concisa do que este prompt fará e qual seu objetivo específico.",
            "output_connects_to_next": "Se 'chain_of_prompts', descreva como a saída deste prompt alimenta o próximo, ou 'N/A' se for o último ou único."
        }
        // Adicionar mais objetos ao array 'plan' se 'prompts_needed' > 1
    ]
}
```

Responda APENAS com o JSON válido, sem texto introdutório ou explicativo adicional.
PROMPT;

        try {
            $response = $this->geminiService->chatWithPrompt($prompt);
            $response = preg_replace('/```json\s*|\s*```/', '', $response); // Clean markdown
            return trim($response);
        } catch (\Exception $e) {
            Log::error("GeminiService error in createRobustPlan: " . $e->getMessage());
            throw new \Exception("Falha ao criar plano robusto com IA: " . $e->getMessage());
        }
    }

    // ETAPA 2-3: Gerar cadeia de prompts baseada no plano
    private function generatePromptsChain($planningResult, $collectedInfo)
    {
        $userSummary = $collectedInfo['summary'] ?? 'Informação não coletada.';
        $prompts = [];
        $generatedPromptsDetails = []; // To store description and prompt

        if (!isset($planningResult['plan']) || !is_array($planningResult['plan'])) {
            Log::error("generatePromptsChain: planningResult[plan] is not set or not an array.", ['plan' => $planningResult]);
            throw new \Exception("O plano de prompts está malformado.");
        }

        $previousPromptsOutputs = []; // Store outputs if needed for context, or just the prompts themselves

        foreach ($planningResult['plan'] as $index => $promptStep) {
            $promptDescription = $promptStep['description'] ?? 'Descrição não fornecida.';
            $isFirst = ($index === 0);
            $isLast = ($index === (count($planningResult['plan']) - 1));
            
            $generationInstructionPrompt = $this->buildPromptGenerationPrompt(
                $userSummary, 
                $planningResult, 
                $promptDescription, 
                $index + 1, 
                $planningResult['prompts_needed'], 
                $isFirst, 
                $isLast,
                $previousPromptsOutputs // Pass outputs of previously generated prompts
            );

            try {
                $generatedSinglePrompt = $this->geminiService->chatWithPrompt($generationInstructionPrompt);
                $cleanPrompt = $this->extractPromptFromResponse($generatedSinglePrompt);
                $prompts[] = $cleanPrompt; // Store only the clean prompt for simple use
                $generatedPromptsDetails[] = [ // Store details for richer dossier
                    'description' => $promptDescription,
                    'prompt' => $cleanPrompt
                ];
                $previousPromptsOutputs[] = $cleanPrompt; // For now, assume output is the prompt itself for context
            } catch (\Exception $e) {
                Log::error("GeminiService error in generatePromptsChain for step " . ($index+1) . ": " . $e->getMessage());
                // Fallback for this specific prompt
                $fallbackCleanPrompt = $this->generateFallbackPrompt($userSummary, $promptDescription);
                $prompts[] = $fallbackCleanPrompt;
                $generatedPromptsDetails[] = [
                    'description' => $promptDescription . " (Fallback Gerado)",
                    'prompt' => $fallbackCleanPrompt
                ];
                 $previousPromptsOutputs[] = $fallbackCleanPrompt;
            }
        }
        
        // If single prompt expected, return string, else array of detailed prompts
        if ($planningResult['approach'] === 'single_prompt' && count($generatedPromptsDetails) === 1) {
            return ['prompts' => $generatedPromptsDetails[0]['prompt'], 'total_prompts' => 1, 'details' => $generatedPromptsDetails];
        }
        return ['prompts' => $generatedPromptsDetails, 'total_prompts' => count($generatedPromptsDetails), 'details' => $generatedPromptsDetails];
    }

    // Constrói prompt para gerar um prompt específico da cadeia
    private function buildPromptGenerationPrompt($userSummary, $planningResult, $promptDescription, $currentStepNum, $totalSteps, $isFirst, $isLast, $previousPromptsOutputs)
    {
        $context = "CONTEXTO GERAL FORNECIDO PELO USUÁRIO (RESUMO):\n" . $userSummary . "\n\n";
        $context .= "PLANO DE PROMPTS DEFINIDO PELA IA:\n" . ($planningResult['reasoning'] ?? 'Raciocínio não detalhado.') . "\n";
        foreach($planningResult['plan'] as $idx => $stepDetail) {
            $context .= "Passo " . ($idx+1) . " do plano: " . ($stepDetail['description'] ?? '') . "\n";
        }
        $context .= "\nPROMPT ATUAL A SER GERADO: Passo {$currentStepNum} de {$totalSteps}\n";
        $context .= "DESCRIÇÃO DESTE PROMPT ESPECÍFICO (DO PLANO): {$promptDescription}\n\n";

        if (!empty($previousPromptsOutputs)) {
            $context .= "PROMPTS ANTERIORES GERADOS NESTA CADEIA (para contexto, se relevante):\n";
            foreach($previousPromptsOutputs as $idx => $prevPrompt) {
                $context .= "Prompt Anterior " . ($idx+1) . ":\n\"" . (is_array($prevPrompt) ? json_encode($prevPrompt) : $prevPrompt) . "\"\n\n";
            }
        }

        $instructions = "Com base em TODO o contexto fornecido (resumo do usuário, plano da IA, e descrição específica deste prompt), sua tarefa é GERAR O TEXTO FINAL E OTIMIZADO para este prompt específico.";
        if ($totalSteps > 1) {
            if (!$isLast) {
                $instructions .= " Este prompt faz parte de uma cadeia. Certifique-se de que sua saída possa ser usada pelo próximo prompt, conforme o plano.";
            }
            if (!$isFirst) {
                $instructions .= " Considere que este prompt pode receber informações de um prompt anterior.";
            }
        }

        return $context . "INSTRUÇÕES PARA VOCÊ (GERADOR DE PROMPT INDIVIDUAL):\n{$instructions}\n\nO prompt que você gerar deve:\n1. Ser claro, específico, técnico e diretamente utilizável em qualquer modelo de IA moderno.\n2. Incluir todo o contexto necessário para seu funcionamento independente ou como parte da cadeia descrita.\n3. Ter instruções precisas e, se aplicável, estruturadas (ex: usando markdown para listas, seções).\n4. Usar linguagem técnica apropriada para o domínio do problema do usuário, se conhecido.\n5. Ser projetado para produzir resultados de alta qualidade e relevância para o objetivo descrito.\n\nIMPORTANTE: Responda APENAS com o texto do prompt final que você criou. Sem introduções, sem explicações, sem comentários, sem markdown de formatação de código (```). O prompt deve começar imediatamente.";
    }

    // Extrai o prompt limpo da resposta do LLM
    private function extractPromptFromResponse($response)
    {
        // Remove markdown code blocks if any
        $cleaned = preg_replace('/^```[a-zA-Z]*\s*|\s*```$/m', '', $response);
        return trim($cleaned);
    }

    // Gera um prompt de fallback se a API falhar
    private function generateFallbackPrompt($userSummary, $descriptionForThisPrompt)
    {
        // Basic fallback, could be more sophisticated
        return "INSTRUÇÃO: Com base no seguinte resumo das necessidades do usuário: '{$userSummary}', execute a tarefa descrita como: '{$descriptionForThisPrompt}'. Forneça uma resposta detalhada e útil.";
    }

    // ETAPA 4: Constrói resposta final amigável para o usuário
    private function buildFinalDossierResponse($planningResult, $promptsResult, $collectedInfo)
    {
        $userSummary = $collectedInfo['summary'] ?? 'Não foi possível recuperar o resumo da conversa.';
        $finalResponse = "Com base na nossa conversa, aqui está o que preparei para você:\n\n";
        $finalResponse .= "**📝 RESUMO DO QUE VOCÊ PRECISA (CONFORME NOSSA CONVERSA):**\n" . nl2br(htmlspecialchars($userSummary)) . "\n\n";
        
        $finalResponse .= "**🎯 MINHA ANÁLISE E ESTRATÉGIA:**\n";
        $finalResponse .= htmlspecialchars($planningResult['analysis'] ?? 'Análise não disponível.') . "\n";
        
        $promptsCount = 0;
        if (isset($promptsResult['details']) && is_array($promptsResult['details'])) {
            $promptsCount = count($promptsResult['details']);
        } elseif (isset($promptsResult['prompts']) && is_string($promptsResult['prompts'])) {
            $promptsCount = 1;
        }
        
        $approach = ($planningResult['approach'] ?? 'single_prompt') === 'chain_of_prompts' ? 
            "Cadeia de Prompts (total de " . ($promptsCount > 0 ? $promptsCount : ($planningResult['prompts_needed'] ?? 0)) . " prompts)" :
            "Prompt Único";
        $finalResponse .= "Estratégia Adotada: " . htmlspecialchars($approach) . "\n";
        $finalResponse .= "Raciocínio: " . htmlspecialchars($planningResult['reasoning'] ?? 'Raciocínio não disponível.') . "\n\n";

        $finalResponse .= "**🚀 SEU(S) PROMPT(S) OTIMIZADO(S):**\n\n";

        if (isset($promptsResult['details']) && is_array($promptsResult['details']) && count($promptsResult['details']) > 0) { // Chain of prompts with details
            foreach ($promptsResult['details'] as $index => $promptData) {
                $finalResponse .= "**Prompt " . ($index + 1) . " de " . count($promptsResult['details']) . ":** ";
                $finalResponse .= htmlspecialchars($promptData['description'] ?? '') . "\n";
                $finalResponse .= "```text\n" . htmlspecialchars(trim($promptData['prompt'] ?? '')) . "\n```\n\n";
            }
        } elseif (isset($promptsResult['prompts']) && is_string($promptsResult['prompts'])) { // Single prompt as string
            $finalResponse .= "**Prompt Gerado:**\n";
            $finalResponse .= "```text\n" . htmlspecialchars(trim($promptsResult['prompts'])) . "\n```\n\n";
        } else {
            $finalResponse .= "Não foi possível gerar os prompts desta vez. Por favor, tente novamente.\n";
        }

        $finalResponse .= "**💡 DICAS DE USO:**\n";
        if (isset($promptsResult['details']) && is_array($promptsResult['details']) && count($promptsResult['details']) > 1) {
            $finalResponse .= "- Cada prompt foi projetado para uma etapa específica. Para melhores resultados, use-os na sequência indicada (1 → 2 → ...).\n";
            $finalResponse .= "- A saída de um prompt pode ser a entrada (ou parte dela) para o próximo.\n";
        } else {
            $finalResponse .= "- Este prompt foi otimizado para suas necessidades. Teste-o em sua ferramenta de IA preferida.\n";
        }
        $finalResponse .= "- Sinta-se à vontade para adaptar ou refinar os prompts conforme necessário.\n";
        $finalResponse .= "- Experimente diferentes variações para ver o que funciona melhor para seu caso específico.\n\n";
        $finalResponse .= "---\n*Assistente Dinâmico de Prompt EurystheusAI - Transformando suas ideias em realidade com IA!*";

        return $finalResponse;
    }

    // Salva o prompt gerado na base de dados
    private function savePromptToDatabase($collectedInfo, $primaryPrompt, $finalDossierText, $planningResult)
    {
        try {
            $user = Auth::user(); // Corrected: Use Auth facade
            if (!$user) {
                Log::warning("savePromptToDatabase: User not authenticated. Skipping save.");
                return null;
            }

            $userSummary = $collectedInfo['summary'] ?? 'N/A';
            // Create a concise title
            $title = "Prompt Gerado: " . Str::limit(explode("\n", $userSummary)[0], 100);
            if (isset($planningResult['plan'][0]['description'])) {
                 $title = Str::limit($planningResult['plan'][0]['description'], 150);
            }


            $promptLog = PromptLog::create([
                'user_id' => $user->id,
                'title' => $title,
                'content' => $primaryPrompt, // O primeiro ou único prompt gerado
                'context_data' => json_encode(['user_summary' => $userSummary, 'full_conversation' => $collectedInfo['full_conversation_log'] ?? null]),
                'llm_model_used' => config('gemini.model'), // Assumindo que você tem isso no config
                'version' => '1.0', // Ou alguma lógica de versionamento
                'status' => 'completed',
            ]);
            
            Session::put('generated_prompt_id', $promptLog->id);

            // Salvar a resposta completa do LLM (o dossier)
            LlmResponse::create([
                'prompt_log_id' => $promptLog->id,
                'response_text' => $finalDossierText, // O dossier completo
                'reasoning' => json_encode([ // O plano e raciocínio da IA
                    'analysis' => $planningResult['analysis'] ?? null,
                    'approach' => $planningResult['approach'] ?? null,
                    'reasoning' => $planningResult['reasoning'] ?? null,
                    'plan_details' => $planningResult['plan'] ?? null,
                ]),
                'metadata' => json_encode(['type' => 'final_dossier_and_plan'])
            ]);
            
            return $promptLog;

        } catch (\Exception $e) {
            Log::error("Failed to save prompt to database: " . $e->getMessage(), [
                'user_id' => Auth::id(), // Corrected: Use Auth facade
                'summary_length' => strlen($collectedInfo['summary'] ?? ''),
            ]);
            return null;
        }
    }
    
    // Método para recuperar prompt salvo (para visualização no dashboard, por exemplo)
    public function getSavedPrompt(Request $request)
    {
        $promptId = Session::get('generated_prompt_id'); // Ou passado por request se for de um histórico
        
        if (!$promptId && $request->has('id')) {
            $promptId = $request->input('id');
        }

        if (!$promptId) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum ID de prompt fornecido ou encontrado na sessão.'
            ], 400);
        }
        
        try {
            $promptLog = PromptLog::with('llmResponses')->find($promptId);
            $currentUser = Auth::user(); // Corrected: Use Auth facade

            if (!$promptLog || ($promptLog->user_id !== $currentUser->id && !$currentUser->isAdmin())) { // Security check
                return response()->json([
                    'success' => false,
                    'message' => 'Prompt não encontrado ou acesso não autorizado.'
                ], 404);
            }
            
            // O dossier completo está em LlmResponse
            $dossierResponse = $promptLog->llmResponses()->whereJsonContains('metadata->type', 'final_dossier_and_plan')->first();
            $promptContentForClipboard = $promptLog->content; // O prompt principal

            // Se for uma cadeia, talvez concatenar ou oferecer um zip? Por ora, o primeiro.
            if ($dossierResponse) {
                 $reasoningData = json_decode($dossierResponse->reasoning, true);
                 if (isset($reasoningData['approach']) && $reasoningData['approach'] === 'chain_of_prompts' && isset($reasoningData['plan_details'])) {
                     // $allPromptsText = ""; // Logic for concatenating prompts can be complex here
                     // foreach($reasoningData['plan_details'] as $idx => $step) {
                         // This part needs careful implementation if all chained prompts are to be copied.
                         // For now, clipboard gets the primary prompt.
                     // }
                 }
            }


            return response()->json([
                'success' => true,
                'prompt' => $promptContentForClipboard, // O prompt principal para copiar
                'full_dossier' => $dossierResponse ? $dossierResponse->response_text : 'Dossier não disponível.',
                'title' => $promptLog->title,
                'created_at' => $promptLog->created_at->format('d/m/Y H:i')
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error retrieving saved prompt: " . $e->getMessage(), ['prompt_id' => $promptId]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao recuperar prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reset()
    {
        Session::forget([
            'chat_messages', 
            'conversation_state', 
            'collected_data_for_prompt', 
            'prompt_plan', 
            'final_dossier_content',
            'generated_prompt_id'
        ]);
        return redirect()->route('prompt.chat');
    }
}
