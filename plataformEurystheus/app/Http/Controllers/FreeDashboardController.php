<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreeDashboardController extends Controller
{
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
            'scope.objective' => 'required|string',
            'scope.constraints' => 'required|string',
            'scope.data' => 'required|string',
            'scope.audience' => 'required|string',
            'scope.output_format' => 'required|string',
            'scope.deadlines' => 'required|string',
        ]);

        // Verificar limite de 20 registros
        $count = PromptLog::where('anonymous_user', '=', Auth::id())->count();
        if ($count >= 20) {
            return response()->json(['error' => 'Limite de 20 agentes atingido.'], 403);
        }

        // Salvar dados anonimizados (sem armazenar email ou dados pessoais)
        PromptLog::create([
            'anonymous_user' => Auth::id(),
            'content' => json_encode($validated['scope']),
        ]);

        // Montar prompt composto (apenas exemplo)
        $composedPrompt = "Crie um prompt para resolver: Objetivo: {$validated['scope']['objective']}; "
            ."Restrições: {$validated['scope']['constraints']}; "
            ."Dados: {$validated['scope']['data']}; "
            ."Público: {$validated['scope']['audience']}; "
            ."Formato: {$validated['scope']['output_format']}; "
            ."Prazos: {$validated['scope']['deadlines']}.";

        // Chamaria API do LLM aqui (placeholder)
        $generated = [
            'chain_of_thought' => 'LLM explicando reasoning passo a passo...',
            'options' => [
                'Prompt 1...',
                'Prompt 2...',
                'Prompt 3...',
            ],
        ];

        return response()->json([
            'prompt' => $composedPrompt,
            'llm_response' => $generated,
        ]);    }}