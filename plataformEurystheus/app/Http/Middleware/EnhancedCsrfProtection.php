<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnhancedCsrfProtection
{
    /**
     * CSRF protection configurations
     */
    private array $protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
    private array $excludedRoutes = [
        'api/*',           // API routes typically use token authentication
        'webhooks/*',      // Webhook endpoints
        'health-check',    // Health check endpoint
        'prompt/chat/message', // Chat message endpoint
        'login',           // Login endpoint
        'register',        // Register endpoint
        'password/*',      // Password reset endpoints
        'logout'
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF for excluded routes
        if ($this->shouldSkipCsrfCheck($request)) {
            return $next($request);
        }

        // Only check CSRF for state-changing methods
        if (in_array($request->method(), $this->protectedMethods)) {
            $this->validateCsrfToken($request);
            $this->validateRequestOrigin($request);
            $this->validateSessionIntegrity($request);
            $this->checkDoubleSubmitCookie($request);
        }

        $response = $next($request);

        // Add additional security headers
        $this->addCsrfSecurityHeaders($response);

        return $response;
    }

    private function shouldSkipCsrfCheck(Request $request): bool
    {
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    private function validateCsrfToken(Request $request): void
    {
        $token = $this->getTokenFromRequest($request);
        $sessionToken = session()->token();

        if (!$token) {
            $this->logCsrfViolation($request, 'MISSING_CSRF_TOKEN');
            abort(419, 'CSRF token missing');
        }

        if (!hash_equals($sessionToken, $token)) {
            $this->logCsrfViolation($request, 'INVALID_CSRF_TOKEN');
            abort(419, 'CSRF token mismatch');
        }

        // Check if token has been used recently (replay attack protection)
        $this->checkTokenReuse($request, $token);
    }

    private function validateRequestOrigin(Request $request): void
    {
        $origin = $request->header('Origin');
        $referer = $request->header('Referer');
        $host = $request->getHost();

        // Check Origin header
        if ($origin) {
            $originHost = parse_url($origin, PHP_URL_HOST);
            if ($originHost !== $host) {
                $this->logCsrfViolation($request, 'INVALID_ORIGIN', [
                    'expected_host' => $host,
                    'actual_origin' => $origin,
                ]);
                abort(403, 'Invalid request origin');
            }
        }

        // Check Referer header as fallback
        if (!$origin && $referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            if ($refererHost !== $host) {
                $this->logCsrfViolation($request, 'INVALID_REFERER', [
                    'expected_host' => $host,
                    'actual_referer' => $referer,
                ]);
                abort(403, 'Invalid request referer');
            }
        }

        // If neither Origin nor Referer is present, it's suspicious
        if (!$origin && !$referer) {
            $this->logCsrfViolation($request, 'MISSING_ORIGIN_AND_REFERER');
            // Don't abort here as some legitimate requests might not have these headers
        }
    }

    private function validateSessionIntegrity(Request $request): void
    {
        $sessionId = session()->getId();
        $userId = auth()->id();

        if (!$sessionId) {
            $this->logCsrfViolation($request, 'MISSING_SESSION_ID');
            abort(419, 'Session not found');
        }

        // Check for session fixation attacks
        $this->checkSessionFixation($request);

        // Validate session fingerprint
        if ($userId) {
            $this->validateSessionFingerprint($request);
        }

        // Check session timeout
        $this->checkSessionTimeout($request);
    }

    private function checkDoubleSubmitCookie(Request $request): void
    {
        // Implement double-submit cookie pattern for enhanced CSRF protection
        $cookieToken = $request->cookie('csrf_cookie');
        $headerToken = $request->header('X-CSRF-TOKEN');

        if ($cookieToken && $headerToken) {
            if (!hash_equals($cookieToken, $headerToken)) {
                $this->logCsrfViolation($request, 'DOUBLE_SUBMIT_COOKIE_MISMATCH');
                abort(419, 'CSRF cookie mismatch');
            }
        }
    }

    private function checkTokenReuse(Request $request, string $token): void
    {
        // Skip token reuse check for dashboard form submissions to avoid blocking legitimate usage
        $path = $request->path();
        if (str_contains($path, 'dashboard/prompt') || str_contains($path, 'dashboard')) {
            Log::info('Skipping CSRF token reuse check for dashboard route: ' . $path);
            return;
        }
        
        $tokenKey = 'csrf_token_used:' . hash('sha256', $token);
        
        if (Cache::has($tokenKey)) {
            $this->logCsrfViolation($request, 'CSRF_TOKEN_REUSE');
            abort(419, 'CSRF token already used');
        }

        // Mark token as used for 2 minutes only (reduced from 5 minutes)
        Cache::put($tokenKey, true, now()->addMinutes(2));
    }

    private function checkSessionFixation(Request $request): void
    {
        $sessionId = session()->getId();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        $sessionKey = "session_info:{$sessionId}";
        $storedInfo = Cache::get($sessionKey);

        $currentInfo = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ];

        if (!$storedInfo) {
            Cache::put($sessionKey, $currentInfo, now()->addHours(2));
        } else {
            // Check for IP change (possible session hijacking)
            if ($storedInfo['ip'] !== $ip) {
                $this->logCsrfViolation($request, 'SESSION_IP_CHANGE', [
                    'original_ip' => $storedInfo['ip'],
                    'current_ip' => $ip,
                ]);
                
                // Regenerate session for security
                session()->regenerate(true);
            }

            // Check for user agent change
            if ($storedInfo['user_agent'] !== $userAgent) {
                $this->logCsrfViolation($request, 'SESSION_USER_AGENT_CHANGE', [
                    'original_ua' => $storedInfo['user_agent'],
                    'current_ua' => $userAgent,
                ]);
            }
        }
    }

    private function validateSessionFingerprint(Request $request): void
    {
        $expectedFingerprint = $this->generateSessionFingerprint($request);
        $storedFingerprint = session('session_fingerprint');

        if (!$storedFingerprint) {
            session(['session_fingerprint' => $expectedFingerprint]);
        } elseif (!hash_equals($storedFingerprint, $expectedFingerprint)) {
            $this->logCsrfViolation($request, 'SESSION_FINGERPRINT_MISMATCH');
            
            // Force logout and session regeneration
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            
            abort(419, 'Session security violation');
        }
    }

    private function checkSessionTimeout(Request $request): void
    {
        $lastActivity = session('last_activity');
        $maxInactiveTime = config('session.lifetime') * 60; // Convert minutes to seconds

        if ($lastActivity && (time() - $lastActivity) > $maxInactiveTime) {
            $this->logCsrfViolation($request, 'SESSION_TIMEOUT');
            
            session()->invalidate();
            session()->regenerateToken();
            
            abort(419, 'Session expired');
        }

        session(['last_activity' => time()]);
    }

    private function generateSessionFingerprint(Request $request): string
    {
        return hash('sha256', 
            $request->userAgent() . 
            $request->ip() . 
            auth()->user()->password . 
            config('app.key')
        );
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        // Check multiple places for CSRF token
        return $request->input('_token') ?: 
               $request->header('X-CSRF-TOKEN') ?: 
               $request->header('X-XSRF-TOKEN');
    }

    private function addCsrfSecurityHeaders(Response $response): void
    {
        // Add SameSite cookie attribute
        $response->headers->set('Set-Cookie', 'SameSite=Strict', false);
        
        // Add additional security headers
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy to prevent CSRF
        $csp = "default-src 'self'; " .
               "form-action 'self'; " .
               "frame-ancestors 'self'; " .
               "base-uri 'self';";
        
        $response->headers->set('Content-Security-Policy', $csp);
    }

    private function logCsrfViolation(Request $request, string $violationType, array $extra = []): void
    {
        \Log::warning("CSRF Protection Violation: {$violationType}", array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'referer' => $request->header('Referer'),
            'origin' => $request->header('Origin'),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'timestamp' => now(),
        ], $extra));

        // Track CSRF violations per IP
        $violationKey = 'csrf_violations:' . $request->ip();
        $violations = Cache::increment($violationKey, 1);
        Cache::put($violationKey, $violations, now()->addHour());

        // Block IP if too many violations
        if ($violations > 10) {
            $blockKey = 'blocked_ip:' . $request->ip();
            Cache::put($blockKey, true, now()->addHours(24));
            
            \Log::alert('IP blocked due to repeated CSRF violations', [
                'ip' => $request->ip(),
                'violations' => $violations,
            ]);
        }
    }
}
