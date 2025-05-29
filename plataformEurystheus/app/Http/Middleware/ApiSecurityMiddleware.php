<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurityMiddleware
{
    /**
     * API security configurations
     */
    private array $securityConfig = [
        'max_requests_per_minute' => 60,
        'max_requests_per_hour' => 1000,
        'max_payload_size' => 5 * 1024 * 1024, // 5MB
        'allowed_content_types' => [
            'application/json',
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Validate API authentication
        $this->validateApiAuthentication($request);
        
        // Apply API rate limiting
        $this->enforceApiRateLimit($request);
        
        // Validate request payload
        $this->validateRequestPayload($request);
        
        // Check for API abuse patterns
        $this->detectApiAbuse($request);
        
        $response = $next($request);
        
        // Add API security headers
        $this->addApiSecurityHeaders($response);
        
        // Log API usage
        $this->logApiUsage($request, $response);
        
        return $response;
    }

    private function validateApiAuthentication(Request $request): void
    {
        // Check for API key in headers
        $apiKey = $request->header('X-API-Key');
        $authHeader = $request->header('Authorization');
        
        if (!$apiKey && !$authHeader) {
            $this->logSecurityEvent($request, 'MISSING_API_AUTHENTICATION');
            abort(401, 'API authentication required');
        }
        
        // Validate API key format
        if ($apiKey && !$this->isValidApiKeyFormat($apiKey)) {
            $this->logSecurityEvent($request, 'INVALID_API_KEY_FORMAT');
            abort(401, 'Invalid API key format');
        }
        
        // Check for suspicious authentication patterns
        if ($this->hasSuspiciousAuthPattern($request)) {
            $this->logSecurityEvent($request, 'SUSPICIOUS_AUTH_PATTERN');
            abort(401, 'Suspicious authentication pattern detected');
        }
    }

    private function enforceApiRateLimit(Request $request): void
    {
        $identifier = $this->getApiIdentifier($request);
        
        // Per-minute rate limiting
        $minuteKey = "api_rate_limit:minute:{$identifier}";
        if (RateLimiter::tooManyAttempts($minuteKey, $this->securityConfig['max_requests_per_minute'])) {
            $this->logSecurityEvent($request, 'API_RATE_LIMIT_EXCEEDED_MINUTE');
            abort(429, 'API rate limit exceeded (per minute)');
        }
        
        // Per-hour rate limiting
        $hourKey = "api_rate_limit:hour:{$identifier}";
        if (RateLimiter::tooManyAttempts($hourKey, $this->securityConfig['max_requests_per_hour'])) {
            $this->logSecurityEvent($request, 'API_RATE_LIMIT_EXCEEDED_HOUR');
            abort(429, 'API rate limit exceeded (per hour)');
        }
        
        RateLimiter::hit($minuteKey, 60); // 1 minute window
        RateLimiter::hit($hourKey, 3600); // 1 hour window
    }

    private function validateRequestPayload(Request $request): void
    {
        // Check content type
        $contentType = $request->header('Content-Type');
        if ($contentType && !$this->isAllowedContentType($contentType)) {
            $this->logSecurityEvent($request, 'INVALID_CONTENT_TYPE');
            abort(415, 'Unsupported content type');
        }
        
        // Check payload size
        $contentLength = $request->header('Content-Length', 0);
        if ($contentLength > $this->securityConfig['max_payload_size']) {
            $this->logSecurityEvent($request, 'PAYLOAD_TOO_LARGE');
            abort(413, 'Payload too large');
        }
        
        // Validate JSON payload
        if ($request->isJson()) {
            $this->validateJsonPayload($request);
        }
        
        // Check for potentially dangerous parameters
        $this->validateRequestParameters($request);
    }

    private function detectApiAbuse(Request $request): void
    {
        $identifier = $this->getApiIdentifier($request);
        
        // Check for rapid fire requests
        $rapidFireKey = "api_rapid_fire:{$identifier}";
        $rapidFireCount = \Illuminate\Support\Facades\Cache::increment($rapidFireKey, 1);
        
        if ($rapidFireCount === 1) {
            \Illuminate\Support\Facades\Cache::put($rapidFireKey, 1, now()->addSeconds(5));
        }
        
        if ($rapidFireCount > 10) { // More than 10 requests in 5 seconds
            $this->logSecurityEvent($request, 'API_RAPID_FIRE_DETECTED');
            abort(429, 'Rapid fire requests detected');
        }
        
        // Check for error rate abuse
        $errorKey = "api_errors:{$identifier}";
        $errorCount = \Illuminate\Support\Facades\Cache::get($errorKey, 0);
        
        if ($errorCount > 20) { // More than 20 errors in last hour
            $this->logSecurityEvent($request, 'HIGH_ERROR_RATE');
            abort(429, 'High error rate detected');
        }
        
        // Check for automated requests
        if ($this->isAutomatedRequest($request)) {
            $this->logSecurityEvent($request, 'AUTOMATED_REQUEST_DETECTED');
        }
    }

    private function validateJsonPayload(Request $request): void
    {
        try {
            $payload = $request->json()->all();
            
            // Check for deeply nested objects (potential DoS)
            if ($this->hasDeepNesting($payload, 0, 10)) {
                $this->logSecurityEvent($request, 'DEEP_JSON_NESTING');
                abort(400, 'JSON payload too deeply nested');
            }
            
            // Check for excessively large arrays
            if ($this->hasLargeArrays($payload)) {
                $this->logSecurityEvent($request, 'LARGE_JSON_ARRAYS');
                abort(400, 'JSON arrays too large');
            }
            
        } catch (\Exception $e) {
            $this->logSecurityEvent($request, 'INVALID_JSON_PAYLOAD');
            abort(400, 'Invalid JSON payload');
        }
    }

    private function validateRequestParameters(Request $request): void
    {
        $dangerousParams = [
            'eval', 'exec', 'system', 'shell_exec', 'passthru',
            'file_get_contents', 'file_put_contents', 'fopen',
            'include', 'require', 'include_once', 'require_once',
        ];
        
        foreach ($request->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($dangerousParams as $param) {
                    if (stripos($value, $param) !== false) {
                        $this->logSecurityEvent($request, 'DANGEROUS_PARAMETER_DETECTED', [
                            'parameter' => $key,
                            'value' => $value,
                        ]);
                        abort(400, 'Potentially dangerous parameter detected');
                    }
                }
            }
        }
    }

    private function addApiSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-API-Version', '1.0');
        $response->headers->set('X-Rate-Limit-Remaining', '100');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
    }

    private function isValidApiKeyFormat(string $apiKey): bool
    {
        // API key should be at least 32 characters and alphanumeric
        return preg_match('/^[a-zA-Z0-9]{32,}$/', $apiKey);
    }

    private function hasSuspiciousAuthPattern(Request $request): bool
    {
        $userAgent = $request->userAgent();
        $authHeader = $request->header('Authorization');
        
        // Check for common attack tools
        $suspiciousAgents = ['sqlmap', 'nikto', 'burp', 'owasp', 'nmap'];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }
        
        // Check for malformed authorization headers
        if ($authHeader && !preg_match('/^(Bearer|Basic|Digest)\s+.+$/i', $authHeader)) {
            return true;
        }
        
        return false;
    }

    private function isAllowedContentType(string $contentType): bool
    {
        foreach ($this->securityConfig['allowed_content_types'] as $allowed) {
            if (stripos($contentType, $allowed) === 0) {
                return true;
            }
        }
        
        return false;
    }

    private function hasDeepNesting(array $data, int $depth, int $maxDepth): bool
    {
        if ($depth > $maxDepth) {
            return true;
        }
        
        foreach ($data as $value) {
            if (is_array($value)) {
                if ($this->hasDeepNesting($value, $depth + 1, $maxDepth)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function hasLargeArrays(array $data): bool
    {
        foreach ($data as $value) {
            if (is_array($value)) {
                if (count($value) > 1000) { // Arrays with more than 1000 elements
                    return true;
                }
                
                if ($this->hasLargeArrays($value)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function isAutomatedRequest(Request $request): bool
    {
        // Check for missing or suspicious headers that indicate automation
        $requiredHeaders = ['User-Agent', 'Accept', 'Accept-Language'];
        
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return true;
            }
        }
        
        // Check for consistent timing patterns (too regular)
        $identifier = $this->getApiIdentifier($request);
        $timingKey = "api_timing:{$identifier}";
        $lastRequest = \Illuminate\Support\Facades\Cache::get($timingKey);
        
        if ($lastRequest) {
            $timeDiff = now()->diffInSeconds($lastRequest);
            if ($timeDiff < 1) { // Less than 1 second between requests
                return true;
            }
        }
        
        \Illuminate\Support\Facades\Cache::put($timingKey, now(), now()->addMinutes(5));
        
        return false;
    }

    private function getApiIdentifier(Request $request): string
    {
        // Use API key if available, otherwise fall back to IP + User Agent
        $apiKey = $request->header('X-API-Key');
        
        if ($apiKey) {
            return 'api_key:' . substr($apiKey, 0, 8);
        }
        
        return 'ip:' . $request->ip() . '|ua:' . md5($request->userAgent() ?? '');
    }

    private function logApiUsage(Request $request, Response $response): void
    {
        \Log::info('API request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_status' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent()),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }

    private function logSecurityEvent(Request $request, string $event, array $extra = []): void
    {
        \Log::warning("API security event: {$event}", array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ], $extra));
    }
}
