<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check authentication
        if (!\Illuminate\Support\Facades\Auth::check()) {
            $this->logUnauthorizedAccess($request, 'not_authenticated');
            return redirect()->route('login');
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Validate user account status
        if (!$user->email_verified_at && !$request->is('email/verify*')) {
            return redirect()->route('verification.notice');
        }

        // Check role authorization
        $allowedRoles = array_map(fn($role) => UserRole::from($role), $roles);

        if (!in_array($user->role, $allowedRoles)) {
            $this->logUnauthorizedAccess($request, 'insufficient_privileges');
            
            // Rate limit unauthorized access attempts
            $key = $user->id . '|unauthorized_access';
            RateLimiter::hit($key, 300); // 5 minutes
            
            if (RateLimiter::tooManyAttempts($key, 10)) {
                \Log::alert('Repeated unauthorized access attempts', [
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                    'attempts' => RateLimiter::attempts($key),
                    'route' => $request->route()?->getName(),
                ]);
            }
            
            abort(403, 'Unauthorized access. This incident has been logged.');
        }

        // Log admin actions for audit
        if ($user->role === UserRole::ADMIN && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logAdminAction($request, $user);
        }

        return $next($request);
    }

    private function logUnauthorizedAccess(Request $request, string $reason): void
    {
        \Log::warning('Unauthorized access attempt', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'timestamp' => now(),
        ]);
    }

    private function logAdminAction(Request $request, $user): void
    {
        \Log::info('Admin action performed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $request->method(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'input_data' => $request->except(['password', 'password_confirmation', '_token']),
            'timestamp' => now(),
        ]);
    }
}
