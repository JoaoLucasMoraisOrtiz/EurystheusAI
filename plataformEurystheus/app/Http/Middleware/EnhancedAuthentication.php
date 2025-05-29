<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class EnhancedAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limiting for login attempts
        if ($request->is('login') && $request->isMethod('POST')) {
            $this->enforceRateLimit($request);
        }

        // Check for session hijacking
        if (auth()->check()) {
            $this->validateSession($request);
        }

        // Force HTTPS for authentication routes
        if (!$request->secure() && app()->environment('production')) {
            if ($request->is('login*') || $request->is('register*') || $request->is('admin*')) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }

    private function enforceRateLimit(Request $request): void
    {
        $key = $request->ip() . '|login';
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            \Log::warning('Rate limit exceeded for login attempts', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempts_remaining' => 0,
                'retry_after' => $seconds
            ]);
            
            abort(429, "Too many login attempts. Try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, 900); // 15 minutes window
    }

    private function validateSession(Request $request): void
    {
        $user = auth()->user();
        $sessionData = session()->all();
        
        // Check for session tampering
        $expectedFingerprint = hash('sha256', 
            $request->userAgent() . 
            $request->ip() . 
            $user->password . 
            config('app.key')
        );
        
        $storedFingerprint = session('user_fingerprint');
        
        if (!$storedFingerprint) {
            session(['user_fingerprint' => $expectedFingerprint]);
        } elseif ($storedFingerprint !== $expectedFingerprint) {
            \Log::alert('Potential session hijacking detected', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expected_fingerprint' => $expectedFingerprint,
                'stored_fingerprint' => $storedFingerprint
            ]);
            
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            
            abort(401, 'Session security violation detected.');
        }
    }
}
