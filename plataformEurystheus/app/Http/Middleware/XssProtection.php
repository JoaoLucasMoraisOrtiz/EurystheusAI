<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssProtection
{
    /**
     * XSS patterns to detect and block
     */
    private array $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/<embed[^>]*>.*?<\/embed>/is',
        '/<applet[^>]*>.*?<\/applet>/is',
        '/javascript:/i',
        '/vbscript:/i',
        '/on\w+\s*=/i',
        '/<img[^>]+src[^>]*=.*javascript:/i',
        '/<img[^>]+src[^>]*=.*vbscript:/i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input data
        $this->sanitizeRequest($request);
        
        $response = $next($request);
        
        // Add security headers
        $this->addSecurityHeaders($response);
        
        return $response;
    }

    private function sanitizeRequest(Request $request): void
    {
        $inputs = $request->all();
        $sanitized = $this->sanitizeArray($inputs, $request);
        $request->merge($sanitized);
    }

    private function sanitizeArray(array $data, Request $request): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->sanitizeString($value, $key, $request);
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value, $request);
            }
        }
        
        return $data;
    }

    private function sanitizeString(string $value, string $key, Request $request): string
    {
        // Detect potential XSS
        foreach ($this->xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->logXssAttempt($request, $key, $value);
                // Instead of blocking, sanitize the content
                $value = strip_tags($value);
                break;
            }
        }

        // HTML encode special characters
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function addSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy with Vite support for development
        $isDev = app()->environment('local');
        $viteConnect = $isDev ? ' http://127.0.0.1:5173 ws://127.0.0.1:5173' : '';
        
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.tailwindcss.com" . ($isDev ? " http://127.0.0.1:5173" : "") . "; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com" . ($isDev ? " http://127.0.0.1:5173" : "") . "; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self'" . $viteConnect . "; " .
               "frame-ancestors 'none';";
               
        $response->headers->set('Content-Security-Policy', $csp);
    }

    private function logXssAttempt(Request $request, string $key, string $value): void
    {
        \Log::warning('XSS attempt detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'parameter' => $key,
            'suspicious_value' => $value,
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }
}
