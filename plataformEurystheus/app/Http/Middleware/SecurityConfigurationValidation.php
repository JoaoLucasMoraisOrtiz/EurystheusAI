<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SecurityConfigurationValidation
{
    /**
     * Security configuration requirements
     */
    private array $requiredSecurityConfigs = [
        'app.env' => ['production', 'staging'],
        'app.debug' => [false],
        'session.secure' => [true],
        'session.http_only' => [true],
        'session.same_site' => ['strict', 'lax'],
        'session.encrypt' => [true],
        'app.force_https' => [true],
        'hashing.driver' => ['bcrypt', 'argon2id'],
        'auth.password_timeout' => ['<=', 900], // 15 minutes max
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip security configuration validation in local environment
        if (app()->environment('local')) {
            return $next($request);
        }
        
        // Check configuration only once per hour to avoid performance impact
        $cacheKey = 'security_config_validated';
        if (!Cache::has($cacheKey)) {
            $this->validateSecurityConfiguration();
            Cache::put($cacheKey, true, 3600); // Cache for 1 hour
        }

        // Validate SSL/TLS configuration
        $this->validateSslConfiguration($request);

        // Check for insecure headers
        $this->validateRequestHeaders($request);

        return $next($request);
    }

    /**
     * Validate security configuration settings
     */
    private function validateSecurityConfiguration(): void
    {
        $violations = [];

        foreach ($this->requiredSecurityConfigs as $configKey => $allowedValues) {
            $currentValue = Config::get($configKey);
            
            if (is_array($allowedValues) && isset($allowedValues[0]) && is_string($allowedValues[0])) {
                // Handle comparison operators
                if (in_array($allowedValues[0], ['<=', '>=', '<', '>', '==', '!='])) {
                    $operator = $allowedValues[0];
                    $compareValue = $allowedValues[1] ?? null;
                    
                    if (!$this->compareValues($currentValue, $operator, $compareValue)) {
                        $violations[] = "Configuration '{$configKey}' value '{$currentValue}' does not meet requirement '{$operator} {$compareValue}'";
                    }
                    continue;
                }
            }

            if (!in_array($currentValue, $allowedValues)) {
                $violations[] = "Configuration '{$configKey}' has insecure value: '{$currentValue}'. Allowed: " . implode(', ', $allowedValues);
            }
        }

        // Check for missing security headers configuration
        $this->validateSecurityHeadersConfig($violations);

        // Check database security
        $this->validateDatabaseSecurity($violations);

        // Log violations
        if (!empty($violations)) {
            Log::warning('Security configuration violations detected:', [
                'violations' => $violations,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ]);

            // In production, you might want to send alerts
            if (app()->environment('production')) {
                $this->sendSecurityAlert($violations);
            }
        }
    }

    /**
     * Compare values with operators
     */
    private function compareValues($value, string $operator, $compareValue): bool
    {
        switch ($operator) {
            case '<=':
                return $value <= $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<':
                return $value < $compareValue;
            case '>':
                return $value > $compareValue;
            case '==':
                return $value == $compareValue;
            case '!=':
                return $value != $compareValue;
            default:
                return false;
        }
    }

    /**
     * Validate SSL/TLS configuration
     */
    private function validateSslConfiguration(Request $request): void
    {
        // Check if HTTPS is enforced in production
        if (app()->environment('production') && !$request->secure()) {
            Log::warning('Insecure HTTP request in production environment', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Check for weak TLS versions (if available in headers)
        $sslProtocol = $request->header('X-Forwarded-Proto-Version');
        if ($sslProtocol && version_compare($sslProtocol, '1.2', '<')) {
            Log::warning('Weak TLS version detected', [
                'version' => $sslProtocol,
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);
        }
    }

    /**
     * Validate request headers for security issues
     */
    private function validateRequestHeaders(Request $request): void
    {
        $suspiciousHeaders = [];

        // Check for potentially dangerous headers
        $dangerousHeaders = [
            'X-Forwarded-Host',
            'X-Original-URL',
            'X-Rewrite-URL',
        ];

        foreach ($dangerousHeaders as $header) {
            if ($request->hasHeader($header)) {
                $suspiciousHeaders[] = $header . ': ' . $request->header($header);
            }
        }

        // Check for suspicious User-Agent patterns
        $userAgent = $request->userAgent();
        $suspiciousPatterns = [
            '/sqlmap/i',
            '/nmap/i',
            '/nikto/i',
            '/burp/i',
            '/w3af/i',
            '/havij/i',
            '/python-requests/i',
            '/curl\/[0-9]/i',
            '/wget/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $suspiciousHeaders[] = 'Suspicious User-Agent: ' . $userAgent;
                break;
            }
        }

        if (!empty($suspiciousHeaders)) {
            Log::warning('Suspicious request headers detected', [
                'headers' => $suspiciousHeaders,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'timestamp' => now(),
            ]);
        }
    }

    /**
     * Validate security headers configuration
     */
    private function validateSecurityHeadersConfig(array &$violations): void
    {
        // Check if security headers are properly configured
        $requiredHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Referrer-Policy',
            'Content-Security-Policy',
        ];

        // This would typically check your middleware configuration
        // For now, we'll just ensure they're not disabled
        if (Config::get('app.disable_security_headers', false)) {
            $violations[] = "Security headers are disabled - this is a critical security risk";
        }
    }

    /**
     * Validate database security configuration
     */
    private function validateDatabaseSecurity(array &$violations): void
    {
        $dbConfig = Config::get('database.connections.' . Config::get('database.default'));

        // Check for insecure database configurations
        if (isset($dbConfig['password']) && empty($dbConfig['password'])) {
            $violations[] = "Database password is empty - this is a critical security risk";
        }

        if (isset($dbConfig['username']) && in_array($dbConfig['username'], ['root', 'admin', 'sa'])) {
            $violations[] = "Database username '{$dbConfig['username']}' is potentially insecure";
        }

        // Check for localhost-only connections in production
        if (app()->environment('production') && isset($dbConfig['host'])) {
            if (in_array($dbConfig['host'], ['127.0.0.1', 'localhost', '::1'])) {
                Log::info('Database connection is localhost-only in production', [
                    'host' => $dbConfig['host'],
                ]);
            }
        }
    }

    /**
     * Send security alert for configuration violations
     */
    private function sendSecurityAlert(array $violations): void
    {
        // You can implement email alerts, Slack notifications, etc.
        // For now, we'll use Laravel's notification system or log
        
        Log::critical('Security configuration violations require immediate attention', [
            'violations' => $violations,
            'environment' => app()->environment(),
            'timestamp' => now(),
            'server' => gethostname(),
        ]);

        // If you have admin notification system, send alert here
        // \Notification::route('mail', config('app.admin_email'))
        //     ->notify(new SecurityConfigurationAlert($violations));
    }
}
