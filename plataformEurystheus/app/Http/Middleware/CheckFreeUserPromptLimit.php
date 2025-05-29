<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PromptLog;
use App\Models\User;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class CheckFreeUserPromptLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Se o usuário não está autenticado ou não é um usuário gratuito, permita a requisição
        if (!$user || !$user->isFree()) {
            return $next($request);
        }
        
        // Obter limite dinâmico de prompts do sistema
        $promptLimit = SystemSetting::get('free_user_prompt_limit', 15);
        
        // Verificar se o usuário atingiu o limite de prompts
        $promptCount = PromptLog::where('anonymous_user', $user->id)->count();
        
        if ($promptCount >= $promptLimit) {
            return back()->withErrors([
                'limit' => "Você atingiu o limite de {$promptLimit} prompts para usuários gratuitos. Faça upgrade para continuar usando a plataforma!"
            ])->withInput();
        }
        
        return $next($request);
    }
}
