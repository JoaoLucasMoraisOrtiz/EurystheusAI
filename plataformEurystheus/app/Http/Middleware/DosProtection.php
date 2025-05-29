<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DosProtection
{
    /**
     * Rate limiting configurations
     */
    private array $rateLimits = [
        'api' => [
            'requests' => 100,      // requests per minute
            'window' => 60,         // in seconds
        ],
        'web' => [
            'requests' => 300,      // requests per 5 minutes
            'window' => 300,        // in seconds
        ],
        'admin' => [
            'requests' => 60,       // requests per 5 minutes (more restrictive)
            'window' => 300,        // in seconds
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Determine rate limit type based on route
        $limitType = $this->determineLimitType($request);
        
        // Apply rate limiting
        $this->enforceRateLimit($request, $limitType);
        
        // Check for suspicious patterns
        $this->detectSuspiciousActivity($request);
        
        // Monitor response size
        $response = $next($request);
        $this->monitorResponse($request, $response);
        
        return $response;
    }

    private function determineLimitType(Request $request): string
    {
        if ($request->is('api/*')) {
            return 'api';
        } elseif ($request->is('admin/*')) {
            return 'admin';
        }
        
        return 'web';
    }

    private function enforceRateLimit(Request $request, string $limitType): void
    {
        $config = $this->rateLimits[$limitType];
        $key = $this->getRateLimitKey($request, $limitType);
        
        if (RateLimiter::tooManyAttempts($key, $config['requests'])) {
            $retryAfter = RateLimiter::availableIn($key);
            
            $this->logDosAttempt($request, $limitType, 'RATE_LIMIT_EXCEEDED');
            
            // Escalate response for repeated violations
            $violationKey = $request->ip() . '|violations';
            $violations = Cache::increment($violationKey, 1);
            Cache::put($violationKey, $violations, now()->addHour());
            
            if ($violations > 10) {
                // Temporary IP ban
                $banKey = $request->ip() . '|banned';
                Cache::put($banKey, true, now()->addHours(24));
                
                \Log::emergency('IP banned due to repeated DoS attempts', [
                    'ip' => $request->ip(),
                    'violations' => $violations,
                    'user_agent' => $request->userAgent(),
                ]);
                
                abort(403, 'IP temporarily banned due to excessive requests');
            }
            
            abort(429, "Too many requests. Retry after {$retryAfter} seconds.");
        }
        
        RateLimiter::hit($key, $config['window']);
    }

    private function detectSuspiciousActivity(Request $request): void
    {
        // Check if IP is banned
        $banKey = $request->ip() . '|banned';
        if (Cache::has($banKey)) {
            abort(403, 'Access denied');
        }
        
        // Check for rapid successive requests from same IP
        $rapidKey = $request->ip() . '|rapid_requests';
        $rapidCount = Cache::increment($rapidKey, 1);
        
        if ($rapidCount === 1) {
            Cache::put($rapidKey, 1, now()->addSeconds(10));
        }
        
        // More than 20 requests in 10 seconds is suspicious
        if ($rapidCount > 20) {
            $this->logDosAttempt($request, 'rapid', 'RAPID_REQUESTS');
            
            // Apply temporary throttling
            $throttleKey = $request->ip() . '|throttled';
            Cache::put($throttleKey, true, now()->addMinutes(5));
        }
        
        // Check if IP is throttled
        if (Cache::has($request->ip() . '|throttled')) {
            sleep(2); // Add delay for throttled IPs
        }
        
        // Check for request size abuse
        $contentLength = $request->header('Content-Length', 0);
        if ($contentLength > 50 * 1024 * 1024) { // 50MB
            $this->logDosAttempt($request, 'size', 'LARGE_REQUEST');
            abort(413, 'Request too large');
        }
        
        // Check for unusual user agents
        $userAgent = $request->userAgent();
        if ($this->isSuspiciousUserAgent($userAgent)) {
            $this->logDosAttempt($request, 'user_agent', 'SUSPICIOUS_USER_AGENT');
        }
    }

    private function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) return true;
        
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/java/i',
            '/go-http/i',
            '/scanner/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }

    private function monitorResponse(Request $request, Response $response): void
    {
        $responseSize = strlen($response->getContent());
        
        // Log large responses that might indicate data exfiltration
        if ($responseSize > 10 * 1024 * 1024) { // 10MB
            \Log::warning('Large response detected', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'response_size' => $responseSize,
                'user_id' => auth()->id(),
                'timestamp' => now(),
            ]);
        }
    }

    private function getRateLimitKey(Request $request, string $limitType): string
    {
        // Use IP + User ID (if authenticated) for more accurate limiting
        $identifier = $request->ip();
        
        if (auth()->check()) {
            $identifier .= '|user:' . auth()->id();
        }
        
        return "{$identifier}|{$limitType}";
    }

    private function logDosAttempt(Request $request, string $type, string $reason): void
    {
        \Log::warning("DoS/DDoS attempt detected: {$reason}", [
            'type' => $type,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'referer' => $request->header('Referer'),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }
}
