<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PromptLog;
use App\Models\LlmResponse;
use App\Services\GeminiService;

class PromptController extends Controller
{
    private function getConversationPrompts()
    {
        return [
            'system' => "Você é um assistente especializado em criação de prompts de IA, com foco em aplicações jurídicas e profissionais. Seu objetivo é conduzir uma conversa estruturada e guiada para coletar informações e criar o prompt perfeito.

PRINCÍPIOS FUNDAMENTAIS:
- SEMPRE confirme o que entendeu da resposta do usuário
- SEMPRE direcione claramente qual é o próximo passo
- Use linguagem clara, direta e acolhedora
- Forneça exemplos práticos para guiar as respostas
- Nunca deixe o usuário sem saber o que fazer a seguir

ESTRUTURA DE CADA RESPOSTA:
1. Reconhecimento: confirme que entendeu a resposta
2. Direcionamento: explique claramente o próximo passo
3. Pergunta específica: faça UMA pergunta por vez
4. Orientação: dê exemplos de como responder

FLUXO DA CONVERSA:
1. OBJETIVO: O que o usuário quer criar?
2. CONTEXTO: Em que situação será usado?
3. PÚBLICO-ALVO: Para quem é destinado?
4. DETALHES: Requisitos específicos e formato
5. FINALIZAÇÃO: Confirmação e criação dos prompts

REGRAS OBRIGATÓRIAS:
- Sempre responda em português brasileiro
- Seja específico e direto em cada pergunta
- Nunca termine uma resposta sem uma pergunta clara
- Sempre explique por que está fazendo a pergunta",

            'welcome' => "Olá! 👋 Sou seu assistente especializado em criação de prompts.

Vou te guiar através de uma conversa simples para criar prompts personalizados que funcionem perfeitamente com IAs.

**Vamos começar!** 

Me conte: **o que você precisa criar ou resolver hoje?**

Por exemplo:
• \"Preciso escrever uma petição inicial\"
• \"Quero criar conteúdo para redes sociais\"
• \"Preciso responder emails de clientes\"
• \"Quero elaborar um contrato\"

Pode ser bem específico ou geral - vamos refinar juntos! 😊",

            'step_1_to_2' => "✅ **Perfeito! Entendi que você quer: {{user_input}}**

Agora preciso entender melhor o contexto para criar o prompt ideal.

**Por favor, me diga em que situação específica você irá usar isso.**

Por exemplo:
• \"É para um caso de direito trabalhista\"
• \"É para uma campanha no Instagram da minha empresa\"
• \"É para responder clientes no WhatsApp do escritório\"
• \"É para apresentar em uma reunião executiva\"

**Por que pergunto isso?** O contexto me ajuda a ajustar a linguagem e o formato certos! 🎯",

            'step_2_to_3' => "✅ **Ótimo! Agora entendo o contexto: {{user_input}}**

Vamos para o próximo passo: definir seu público-alvo.

**Agora me diga:** **Para quem** é destinado o que você vai criar?

Por exemplo:
• \"Para juízes em processos trabalhistas\"
• \"Para jovens de 18-25 anos interessados em tech\"
• \"Para executivos de empresas médias\"
• \"Para minha equipe interna\"
• \"Para clientes que não entendem termos técnicos\"

**Por que isso é importante?** Saber quem vai ler/usar o resultado me permite ajustar o tom, linguagem e nível de detalhamento perfeitos! 👥",

            'step_3_to_4' => "✅ **Excelente! Agora sei que o público-alvo é: {{user_input}}**

Estamos quase lá! Vamos definir os detalhes específicos.

**Próximo passo:** Me conte sobre **requisitos específicos** que você tem.

Pense em:
• **Formato:** Tem algum modelo obrigatório? (ofício, email, post, etc.)
• **Tom:** Formal, informal, técnico, simples?
• **Tamanho:** Curto, médio, detalhado?
• **Prazo:** É urgente? Tem deadline?
• **Restrições:** Algo que NÃO pode faltar ou que deve evitar?

**Por que pergunto isso?** Esses detalhes garantem que o prompt vai gerar exatamente o que você precisa, no formato certo! ⚙️",

            'step_4_to_5' => "✅ **Perfeito! Tenho todos os detalhes importantes: {{user_input}}**

Última etapa antes de criar seus prompts!

**Para finalizar:** Você tem algum **exemplo ou referência** do que considera ideal?

Por exemplo:
• \"Quero algo como [exemplo específico]\"
• \"Gosto do estilo formal mas acessível\"
• \"Nada muito técnico, linguagem simples\"
• \"Algo parecido com [referência]\"
• \"Definitivamente NÃO quero [algo específico]\"

**Se não tiver exemplos, sem problemas!** Já tenho informações suficientes para criar algo excelente.

**Por que isso ajuda?** Exemplos me permitem capturar exatamente o estilo que você prefere! ✨",

            'finalization' => "🎉 **Excelente! Tenho todas as informações necessárias!**

**Resumo do que vamos criar:**
• **Objetivo:** {{objective}}
• **Contexto:** {{context}}
• **Público:** {{audience}}
• **Detalhes:** {{details}}
• **Referências:** {{examples}}

**Agora vou criar 3 prompts otimizados** para você, cada um com uma abordagem diferente:

1. **Prompt Detalhado** - Estruturado e completo
2. **Prompt Criativo** - Flexível e adaptável  
3. **Prompt Direto** - Objetivo e eficiente

**Preparando seus prompts personalizados...** ⚡

*Isso levará apenas alguns segundos!*"
        ];
    }

    private function analyzeConversationState($chatHistory)
    {
        $userMessages = array_filter($chatHistory, fn($msg) => $msg['role'] === 'user');
        $userCount = count($userMessages);
        
        if ($userCount === 0) {
            return ['step' => 'welcome', 'collected' => []];
        }
        
        // Extrair mensagens do usuário
        $userMessagesArray = array_values($userMessages);
        
        if ($userCount === 1) {
            return [
                'step' => 'step_1_to_2',
                'collected' => ['objective' => $userMessagesArray[0]['content']]
            ];
        }
        
        if ($userCount === 2) {
            return [
                'step' => 'step_2_to_3', 
                'collected' => [
                    'objective' => $userMessagesArray[0]['content'],
                    'context' => $userMessagesArray[1]['content']
                ]
            ];
        }
        
        if ($userCount === 3) {
            return [
                'step' => 'step_3_to_4',
                'collected' => [
                    'objective' => $userMessagesArray[0]['content'],
                    'context' => $userMessagesArray[1]['content'],
                    'audience' => $userMessagesArray[2]['content']
                ]
            ];
        }
        
        if ($userCount === 4) {
            return [
                'step' => 'step_4_to_5',
                'collected' => [
                    'objective' => $userMessagesArray[0]['content'],
                    'context' => $userMessagesArray[1]['content'], 
                    'audience' => $userMessagesArray[2]['content'],
                    'details' => $userMessagesArray[3]['content']
                ]
            ];
        }
        
        if ($userCount >= 5) {
            return [
                'step' => 'finalization',
                'collected' => [
                    'objective' => $userMessagesArray[0]['content'],
                    'context' => $userMessagesArray[1]['content'],
                    'audience' => $userMessagesArray[2]['content'], 
                    'details' => $userMessagesArray[3]['content'],
                    'examples' => $userMessagesArray[4]['content']
                ]
            ];
        }
        
        return ['step' => 'welcome', 'collected' => []];
    }

    private function generateContextualResponse($state, $userMessage = '')
    {
        $prompts = $this->getConversationPrompts();
        $step = $state['step'];
        $collected = $state['collected'];
        
        switch ($step) {
            case 'welcome':
                return $prompts['welcome'];
                
            case 'step_1_to_2':
                return str_replace('{{user_input}}', $collected['objective'] ?? $userMessage, $prompts['step_1_to_2']);
                
            case 'step_2_to_3':
                return str_replace('{{user_input}}', $userMessage, $prompts['step_2_to_3']);
                
            case 'step_3_to_4':
                return str_replace('{{user_input}}', $userMessage, $prompts['step_3_to_4']);
                
            case 'step_4_to_5':
                return str_replace('{{user_input}}', $userMessage, $prompts['step_4_to_5']);
                
            case 'finalization':
                $response = $prompts['finalization'];
                $response = str_replace('{{objective}}', $collected['objective'] ?? '', $response);
                $response = str_replace('{{context}}', $collected['context'] ?? '', $response);
                $response = str_replace('{{audience}}', $collected['audience'] ?? '', $response);
                $response = str_replace('{{details}}', $collected['details'] ?? '', $response);
                $response = str_replace('{{examples}}', $userMessage, $response);
                return $response;
                
            default:
                return "Hmm, parece que algo deu errado. Vamos recomeçar! Me conte: o que você precisa criar hoje?";
        }
    }

    private function generatePromptBlueprints($collectedData)
    {
        $objective = $collectedData['objective'] ?? '';
        $context = $collectedData['context'] ?? '';
        $audience = $collectedData['audience'] ?? '';
        $details = $collectedData['details'] ?? '';
        $examples = $collectedData['examples'] ?? '';

        $systemPrompt = "Você é um especialista em criação de prompts para IA. Com base nas informações coletadas, crie 3 prompts otimizados e distintos.

INFORMAÇÕES COLETADAS:
- Objetivo: {$objective}
- Contexto: {$context}  
- Público-alvo: {$audience}
- Detalhes específicos: {$details}
- Exemplos/Referências: {$examples}

INSTRUÇÕES PARA CRIAÇÃO:
1. Crie 3 prompts diferentes, cada um com uma abordagem única
2. Use placeholders {{variavel}} para informações que o usuário preencherá
3. Inclua contexto, role-play e instruções específicas
4. Otimize para clareza e resultados precisos
5. Adapte o tom para o público-alvo identificado

FORMATO DE RESPOSTA (JSON):
{
  \"prompts\": [
    \"Prompt 1: [DETALHADO E ESTRUTURADO]\",
    \"Prompt 2: [CRIATIVO E FLEXÍVEL]\", 
    \"Prompt 3: [DIRETO E EFICIENTE]\"
  ]
}

Foque na qualidade e aplicabilidade prática.";

        try {
            $response = Http::timeout(30)->post(config('services.openai.endpoint'), [
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => 'Crie os 3 prompts otimizados baseados nas informações fornecidas.']
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ], [
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                
                // Try to extract JSON from the response
                if (preg_match('/\{.*\}/s', $content, $matches)) {
                    $jsonContent = $matches[0];
                    $decoded = json_decode($jsonContent, true);
                    
                    if (isset($decoded['prompts']) && is_array($decoded['prompts'])) {
                        return $decoded['prompts'];
                    }
                }
                
                // Fallback: try to extract prompts manually
                preg_match_all('/Prompt \d+[:\-]\s*(.+?)(?=Prompt \d+|$)/s', $content, $matches);
                if (!empty($matches[1])) {
                    return array_map('trim', $matches[1]);
                }
                
                // Final fallback
                return [trim($content)];
            }
            
            throw new \Exception('Failed to generate prompts: ' . $response->status());
            
        } catch (\Exception $e) {
            Log::error('Error generating prompts: ' . $e->getMessage());
            
            // Return fallback prompts
            return [
                "Você é um especialista em {$context}. Sua tarefa é {$objective} para {$audience}.\n\nInstruções específicas:\n- {$details}\n- Use um tom adequado ao público-alvo\n- Seja claro e objetivo\n\nInformações necessárias: {{informacoes_especificas}}\n\nCrie o conteúdo solicitado seguindo as melhores práticas da área.",
                
                "Atue como um profissional especializado que precisa {$objective}.\n\nContexto: {$context}\nPúblico: {$audience}\nRequisitos: {$details}\n\nVocê deve:\n1. Analisar o contexto fornecido\n2. Adaptar a linguagem ao público\n3. Incluir todos os elementos necessários\n4. Garantir qualidade profissional\n\nDados para processamento: {{dados_entrada}}\n\nFaça o trabalho de forma excepcional.",
                
                "Preciso que você {$objective} considerando:\n\n- Contexto: {$context}\n- Destinatário: {$audience} \n- Especificações: {$details}\n\nInput: {{entrada}}\n\nOutput esperado: Resultado profissional, bem estruturado e adequado ao contexto apresentado."
            ];
        }
    }

    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function sendMessage(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $chatHistory = session('chat_history', []);
            
            // Add user message to history
            $chatHistory[] = ['role' => 'user', 'content' => $userMessage];
            
            // Analyze conversation state
            $state = $this->analyzeConversationState($chatHistory);
            
            // Check if we should generate final prompts
            if ($state['step'] === 'finalization' && count(array_filter($chatHistory, fn($msg) => $msg['role'] === 'user')) >= 5) {
                // Generate the final response message
                $responseMessage = $this->generateContextualResponse($state, $userMessage);
                $chatHistory[] = ['role' => 'assistant', 'content' => $responseMessage];
                
                // Save conversation and generate prompts
                $promptLog = PromptLog::create([
                    'user_id' => auth()->id(),
                    'content' => json_encode($state['collected']),
                    'status' => 'completed'
                ]);
                
                $generatedPrompts = $this->generatePromptBlueprints($state['collected']);
                
                $llmResponse = LlmResponse::create([
                    'prompt_log_id' => $promptLog->id,
                    'generated_prompts' => json_encode($generatedPrompts),
                    'raw_response' => json_encode($generatedPrompts)
                ]);
                
                session(['chat_history' => $chatHistory]);
                session(['prompt_created' => true]);
                
                return response()->json([
                    'message' => $responseMessage,
                    'done' => true,
                    'prompts_generated' => true,
                    'step' => $state['step']
                ]);
            }
            
            // Generate contextual response for current step
            $responseMessage = $this->generateContextualResponse($state, $userMessage);
            $chatHistory[] = ['role' => 'assistant', 'content' => $responseMessage];
            
            session(['chat_history' => $chatHistory]);
            
            return response()->json([
                'message' => $responseMessage,
                'step' => $state['step'],
                'collected' => $state['collected'],
                'done' => false
            ]);
            
        } catch (\Exception $e) {
            Log::error('Chat message error: ' . $e->getMessage());
            
            return response()->json([
                'message' => '❌ Desculpe, ocorreu um erro. Vamos tentar novamente?',
                'error' => true
            ], 500);
        }
    }
}