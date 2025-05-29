<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PromptLog;
use App\Models\LlmResponse;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class TestUserWithPromptsSeeder extends Seeder
{
    public function run(): void
    {
        // Criar usuário de teste gratuito (ou usar existente)
        $freeUser = User::firstOrCreate([
            'email' => 'teste.gratuito@eurystheusai.com',
        ], [
            'name' => 'Usuário Teste Gratuito',
            'password' => Hash::make('password123'),
            'role' => UserRole::FREE_USER,
            'email_verified_at' => now(),
        ]);

        // Verificar quantos prompts o usuário já possui
        $existingPrompts = PromptLog::where('anonymous_user', $freeUser->id)->count();
        $promptsToCreate = max(0, 12 - $existingPrompts);

        // Criar prompts adicionais se necessário
        for ($i = $existingPrompts + 1; $i <= $existingPrompts + $promptsToCreate; $i++) {
            $promptLog = PromptLog::create([
                'anonymous_user' => $freeUser->id,
                'content' => json_encode([
                    'objective' => "Objetivo de teste #{$i}",
                    'constraints' => "Restrições de teste #{$i}",
                    'data' => "Dados de teste #{$i}",
                    'audience' => "Audiência de teste #{$i}",
                    'output_format' => "Formato de saída #{$i}",
                    'deadlines' => "Prazo de teste #{$i}",
                ]),
            ]);

            // Criar resposta LLM para cada prompt
            LlmResponse::create([
                'prompt_log_id' => $promptLog->id,
                'llm_reasoning' => "Explicação do raciocínio para prompt #{$i}",
                'generated_prompts' => json_encode([
                    "Prompt gerado #{$i}.1: Este é um exemplo de prompt para testar o sistema.",
                    "Prompt gerado #{$i}.2: Este é outro exemplo de prompt para validar a funcionalidade.",
                ]),
            ]);
        }

        // Criar usuário de teste pago (ou usar existente)
        $paidUser = User::firstOrCreate([
            'email' => 'teste.pago@eurystheusai.com',
        ], [
            'name' => 'Usuário Teste Pago',
            'password' => Hash::make('password123'),
            'role' => UserRole::PAYED_USER,
            'email_verified_at' => now(),
        ]);

        // Verificar se já tem prompts e criar alguns se necessário
        $existingPaidPrompts = PromptLog::where('anonymous_user', $paidUser->id)->count();
        if ($existingPaidPrompts < 5) {
            for ($i = $existingPaidPrompts + 1; $i <= 5; $i++) {
                $promptLog = PromptLog::create([
                    'anonymous_user' => $paidUser->id,
                    'content' => json_encode([
                        'objective' => "Objetivo pago #{$i}",
                        'constraints' => "Restrições pago #{$i}",
                        'data' => "Dados pago #{$i}",
                        'audience' => "Audiência pago #{$i}",
                        'output_format' => "Formato pago #{$i}",
                        'deadlines' => "Prazo pago #{$i}",
                    ]),
                ]);

                LlmResponse::create([
                    'prompt_log_id' => $promptLog->id,
                    'llm_reasoning' => "Explicação paga #{$i}",
                    'generated_prompts' => json_encode([
                        "Prompt pago #{$i}.1: Prompt avançado para usuário premium.",
                        "Prompt pago #{$i}.2: Outro prompt premium com recursos avançados.",
                    ]),
                ]);
            }
        }

        $currentFreePrompts = PromptLog::where('anonymous_user', $freeUser->id)->count();
        $this->command->info('Usuários de teste atualizados:');
        $this->command->info("- Usuário Gratuito: teste.gratuito@eurystheusai.com ({$currentFreePrompts}/15 prompts usados)");
        $this->command->info("- Usuário Pago: teste.pago@eurystheusai.com (sem limite)");
        $this->command->info("- Senha para ambos: password123");
    }
}
