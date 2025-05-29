<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SqlInjectionProtection
{
    /**
     * SQL Injection patterns to detect
     */
    private array $sqlPatterns = [
        '/(\s*(union|select|insert|update|delete|drop|create|alter|exec|execute)\s+)/i',
        '/(\s*(or|and)\s+[0-9]+\s*=\s*[0-9]+)/i',
        '/(\s*[\'\"]\s*(or|and)\s+[\'\"]\s*=\s*[\'\"])/i',
        '/(\s*--\s*)/i',
        '/(\s*\/\*.*?\*\/\s*)/i',
        '/(\s*;\s*(shutdown|xp_cmdshell|sp_configure)\s*)/i',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Check all input parameters
        $inputs = array_merge(
            $request->all(),
            $request->query->all(),
            $request->request->all()
        );

        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $this->detectSqlInjection($key, $value, $request);
            } elseif (is_array($value)) {
                $this->checkArrayForSqlInjection($key, $value, $request);
            }
        }

        return $next($request);
    }

    private function detectSqlInjection(string $key, string $value, Request $request): void
    {
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->logSuspiciousActivity($request, $key, $value, 'SQL_INJECTION_ATTEMPT');
                abort(400, 'Invalid input detected');
            }
        }
    }

    private function checkArrayForSqlInjection(string $key, array $array, Request $request): void
    {
        foreach ($array as $subKey => $value) {
            if (is_string($value)) {
                $this->detectSqlInjection("{$key}.{$subKey}", $value, $request);
            } elseif (is_array($value)) {
                $this->checkArrayForSqlInjection("{$key}.{$subKey}", $value, $request);
            }
        }
    }

    private function logSuspiciousActivity(Request $request, string $key, string $value, string $type): void
    {
        \Log::warning("Security threat detected: {$type}", [
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
