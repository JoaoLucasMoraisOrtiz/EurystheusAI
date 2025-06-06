<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityMonitoring
{
    /**
     * Security events to monitor
     */
    private array $criticalEvents = [
        'login_failure',
        'privilege_escalation_attempt',
        'sql_injection_attempt',
        'xss_attempt',
        'csrf_token_mismatch',
        'session_hijacking',
        'brute_force_attack',
        'dos_attack',
        'malicious_file_upload',
        'unauthorized_access',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip all security monitoring in local environment
        if (app()->environment('local')) {
            return $next($request);
        }
        
        $startTime = microtime(true);
        
        // Capture request details
        $requestData = $this->captureRequestData($request);
        
        // Monitor for real-time threats
        $this->monitorRealTimeThreats($request);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Monitor response and log security events
        $this->monitorResponse($request, $response, $responseTime, $requestData);
        
        return $response;
    }

    private function captureRequestData(Request $request): array
    {
        return [
            'timestamp' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'referer' => $request->header('Referer'),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'request_size' => $request->header('Content-Length', 0),
            'is_ajax' => $request->ajax(),
            'is_secure' => $request->secure(),
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'real_ip' => $request->header('X-Real-IP'),
        ];
    }

    private function monitorRealTimeThreats(Request $request): void
    {
        // Skip ALL threat monitoring for dashboard routes to prevent false positives
        if ($request->is('dashboard*') || 
            $request->is('free/*') || 
            $request->is('api/generate-prompt') ||
            $request->is('prompt/*') ||
            $request->is('prompt/chat*') ||
            str_contains($request->path(), 'dashboard') ||
            str_contains($request->path(), 'prompt') ||
            $request->path() === 'dashboard/prompt') {
            Log::info('Skipping threat monitoring for dashboard route: ' . $request->path());
            return;
        }
        
        // Check for known malicious IPs
        if ($this->isKnownMaliciousIp($request->ip())) {
            $this->logSecurityAlert('KNOWN_MALICIOUS_IP', $request, [
                'threat_level' => 'HIGH',
                'action' => 'BLOCKED',
            ]);
            abort(403, 'Access denied');
        }
        
        // Monitor for suspicious patterns (only on non-dashboard routes)
        $this->checkSuspiciousPatterns($request);
        
        // Check for anomalous behavior
        $this->detectAnomalousBehavior($request);
        
        // Monitor for credential stuffing
        if ($request->is('login') && $request->isMethod('POST')) {
            $this->detectCredentialStuffing($request);
        }
        
        // Check for insider threats
        if (Auth::check()) {
            $this->monitorInsiderThreats($request);
        }
    }

    private function monitorResponse(Request $request, Response $response, float $responseTime, array $requestData): void
    {
        $responseData = [
            'status_code' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent()),
            'response_time' => $responseTime,
            'headers' => $response->headers->all(),
        ];
        
        // Log all requests for audit trail
        $this->logRequestResponse($requestData, $responseData);
        
        // Check for security indicators in response
        $this->analyzeSecurityIndicators($request, $response, $requestData, $responseData);
        
        // Monitor for data exfiltration
        if ($responseData['response_size'] > 10 * 1024 * 1024) { // 10MB
            $this->logSecurityAlert('LARGE_DATA_RESPONSE', $request, [
                'response_size' => $responseData['response_size'],
                'threat_level' => 'MEDIUM',
            ]);
        }
        
        // Monitor error rates
        if ($response->getStatusCode() >= 400) {
            $this->monitorErrorRates($request);
        }
    }

    private function isKnownMaliciousIp(string $ip): bool
    {
        // Check against cache of known malicious IPs
        $maliciousIps = Cache::get('malicious_ips', []);
        
        if (in_array($ip, $maliciousIps)) {
            return true;
        }
        
        // Check against database of blocked IPs
        return DB::table('blocked_ips')->where('ip', $ip)->exists();
    }

    private function checkSuspiciousPatterns(Request $request): void
    {
        // Skip ALL security pattern checks for dashboard routes to avoid false positives
        if ($request->is('dashboard*') || 
            $request->is('free/*') || 
            $request->is('api/generate-prompt') ||
            $request->path() === 'dashboard/prompt' ||
            str_contains($request->path(), 'dashboard') ||
            str_contains($request->path(), 'prompt')) {
            Log::info('Skipping suspicious pattern checks for dashboard route: ' . $request->path());
            return;
        }
        
        // Only perform security checks on non-dashboard routes
        $input = json_encode($request->all());
        
        // Only check for very specific, high-confidence malicious patterns
        $suspiciousPatterns = [
            // SQL Injection patterns (very specific)
            '/union\s+select\s+[\w\*,\s]+\s+from\s+[\w_]+/i' => 'SQL_INJECTION',
            '/\'\s*or\s+1\s*=\s*1[\s\']/i' => 'SQL_INJECTION',
            '/drop\s+table\s+[\w_]+/i' => 'SQL_INJECTION',
            
            // XSS patterns (only obvious script injection)
            '/<script[^>]*>.*?<\/script>/i' => 'XSS',
            '/javascript:\s*(alert|confirm|prompt)\s*\(/i' => 'XSS',
            
            // Command injection (very specific)
            '/;\s*(rm\s+-rf\s+\/|cat\s+\/etc\/passwd)/i' => 'COMMAND_INJECTION',
            
            // Path traversal (multiple levels with system files)
            '/\.\.\/\.\.\/\.\.\/.*?(etc\/passwd|windows\/system32)/i' => 'PATH_TRAVERSAL',
        ];
        
        foreach ($suspiciousPatterns as $pattern => $threatType) {
            if (preg_match($pattern, $input)) {
                Log::warning($threatType . ' attempt detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'pattern' => $pattern,
                    'input_sample' => substr($input, 0, 200),
                ]);
                // Only log, don't block legitimate functionality
            }
        }
    }

    private function detectAnomalousBehavior(Request $request): void
    {
        $userId = Auth::id();
        $ip = $request->ip();
        
        if (!$userId) return;
        
        // Check for unusual access times
        $hour = now()->hour;
        if ($hour < 6 || $hour > 22) { // Outside normal hours
            $key = "unusual_access_time:user:{$userId}";
            $count = Cache::increment($key, 1);
            Cache::put($key, $count, now()->addHour());
            
            if ($count > 5) { // More than 5 requests outside normal hours
                $this->logSecurityAlert('UNUSUAL_ACCESS_TIME', $request, [
                    'hour' => $hour,
                    'threat_level' => 'MEDIUM',
                ]);
            }
        }
        
        // Check for multiple IP addresses for same user
        $ipKey = "user_ips:user:{$userId}";
        $userIps = Cache::get($ipKey, []);
        
        if (!in_array($ip, $userIps)) {
            $userIps[] = $ip;
            Cache::put($ipKey, $userIps, now()->addDay());
            
            if (count($userIps) > 3) { // More than 3 different IPs in a day
                $this->logSecurityAlert('MULTIPLE_IP_ADDRESSES', $request, [
                    'ip_count' => count($userIps),
                    'ips' => $userIps,
                    'threat_level' => 'MEDIUM',
                ]);
            }
        }
        
        // Check for rapid location changes (if we had geolocation)
        $this->checkLocationAnomalies($request);
    }

    private function detectCredentialStuffing(Request $request): void
    {
        $email = $request->input('email');
        $ip = $request->ip();
        
        if (!$email) return;
        
        // Monitor failed login attempts per IP
        $ipKey = "failed_logins:ip:{$ip}";
        $ipFailures = Cache::increment($ipKey, 1);
        Cache::put($ipKey, $ipFailures, now()->addHour());
        
        // Monitor failed attempts per email
        $emailKey = "failed_logins:email:" . hash('sha256', $email);
        $emailFailures = Cache::increment($emailKey, 1);
        Cache::put($emailKey, $emailFailures, now()->addHour());
        
        // Detect credential stuffing patterns
        if ($ipFailures > 20 || $emailFailures > 10) {
            $this->logSecurityAlert('CREDENTIAL_STUFFING_DETECTED', $request, [
                'ip_failures' => $ipFailures,
                'email_failures' => $emailFailures,
                'threat_level' => 'HIGH',
            ]);
            
            // Temporarily block IP if too many failures
            if ($ipFailures > 50) {
                $blockKey = "blocked_ip:{$ip}";
                Cache::put($blockKey, true, now()->addHours(24));
            }
        }
    }

    private function monitorInsiderThreats(Request $request): void
    {
        $user = Auth::user();
        
        // Monitor admin actions
        if ($user->role->value === 'admin') {
            $this->monitorAdminActions($request, $user);
        }
        
        // Check for privilege escalation attempts
        if ($request->has(['role', 'permissions', 'admin'])) {
            $this->logSecurityAlert('PRIVILEGE_ESCALATION_ATTEMPT', $request, [
                'user_role' => $user->role->value,
                'parameters' => $request->only(['role', 'permissions', 'admin']),
                'threat_level' => 'HIGH',
            ]);
        }
        
        // Monitor for bulk data access
        $this->monitorBulkDataAccess($request, $user);
    }

    private function monitorAdminActions(Request $request, $user): void
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logSecurityAlert('ADMIN_ACTION', $request, [
                'admin_id' => $user->id,
                'admin_email' => $user->email,
                'action' => $request->method(),
                'route' => $request->route()?->getName(),
                'parameters' => $request->except(['password', 'password_confirmation', '_token']),
                'threat_level' => 'INFO',
            ]);
        }
    }

    private function monitorBulkDataAccess(Request $request, $user): void
    {
        // Monitor for attempts to access large amounts of data
        if ($request->has(['limit', 'per_page']) || $request->is('*/export*')) {
            $limit = $request->input('limit', $request->input('per_page', 0));
            
            if ($limit > 1000) {
                $this->logSecurityAlert('BULK_DATA_ACCESS', $request, [
                    'user_id' => $user->id,
                    'requested_limit' => $limit,
                    'threat_level' => 'MEDIUM',
                ]);
            }
        }
    }

    private function checkLocationAnomalies(Request $request): void
    {
        // This would require a geolocation service
        // For now, we'll monitor IP changes as a proxy
        $userId = Auth::id();
        if (!$userId) return;
        
        $currentIp = $request->ip();
        $lastIpKey = "last_ip:user:{$userId}";
        $lastIp = Cache::get($lastIpKey);
        
        if ($lastIp && $lastIp !== $currentIp) {
            $this->logSecurityAlert('IP_CHANGE_DETECTED', $request, [
                'previous_ip' => $lastIp,
                'current_ip' => $currentIp,
                'threat_level' => 'LOW',
            ]);
        }
        
        Cache::put($lastIpKey, $currentIp, now()->addWeek());
    }

    private function analyzeSecurityIndicators(Request $request, Response $response, array $requestData, array $responseData): void
    {
        // Check for information disclosure
        $content = $response->getContent();
        
        if (is_string($content)) {
            $sensitivePatterns = [
                '/password\s*[:=]\s*[\'"][^\'"]+[\'"]/i' => 'PASSWORD_DISCLOSURE',
                '/api[_-]?key\s*[:=]\s*[\'"][^\'"]+[\'"]/i' => 'API_KEY_DISCLOSURE',
                '/secret\s*[:=]\s*[\'"][^\'"]+[\'"]/i' => 'SECRET_DISCLOSURE',
                '/token\s*[:=]\s*[\'"][^\'"]+[\'"]/i' => 'TOKEN_DISCLOSURE',
                '/sql\s+error/i' => 'SQL_ERROR_DISCLOSURE',
                '/stack\s+trace/i' => 'STACK_TRACE_DISCLOSURE',
            ];
            
            foreach ($sensitivePatterns as $pattern => $type) {
                if (preg_match($pattern, $content)) {
                    $this->logSecurityAlert($type, $request, [
                        'threat_level' => 'HIGH',
                        'response_contains_sensitive_data' => true,
                    ]);
                }
            }
        }
        
        // Monitor for timing attacks
        if ($responseData['response_time'] > 5000) { // More than 5 seconds
            $this->logSecurityAlert('SLOW_RESPONSE_TIME', $request, [
                'response_time' => $responseData['response_time'],
                'threat_level' => 'LOW',
            ]);
        }
    }

    private function monitorErrorRates(Request $request): void
    {
        $ip = $request->ip();
        $errorKey = "error_rate:ip:{$ip}";
        $errorCount = Cache::increment($errorKey, 1);
        Cache::put($errorKey, $errorCount, now()->addHour());
        
        if ($errorCount > 50) { // More than 50 errors per hour
            $this->logSecurityAlert('HIGH_ERROR_RATE', $request, [
                'error_count' => $errorCount,
                'threat_level' => 'MEDIUM',
            ]);
        }
    }

    private function logRequestResponse(array $requestData, array $responseData): void
    {
        // Store in database for audit trail
        try {
            DB::table('security_audit_log')->insert([
                'timestamp' => $requestData['timestamp'],
                'ip' => $requestData['ip'],
                'user_id' => $requestData['user_id'],
                'method' => $requestData['method'],
                'url' => $requestData['url'],
                'user_agent' => $requestData['user_agent'],
                'status_code' => $responseData['status_code'],
                'response_time' => $responseData['response_time'],
                'request_size' => $requestData['request_size'],
                'response_size' => $responseData['response_size'],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fallback to log file if database is unavailable
            Log::info('Security audit log entry', array_merge($requestData, $responseData));
        }
    }

    private function logSecurityAlert(string $eventType, Request $request, array $extra = []): void
    {
        $alertData = array_merge([
            'event_type' => $eventType,
            'timestamp' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ], $extra);
        
        // Log to security channel
        Log::channel('security')->alert("Security Alert: {$eventType}", $alertData);
        
        // Store in database for analysis
        try {
            DB::table('security_alerts')->insert([
                'event_type' => $eventType,
                'threat_level' => $extra['threat_level'] ?? 'UNKNOWN',
                'ip' => $request->ip(),
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'details' => json_encode($alertData),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store security alert in database', [
                'error' => $e->getMessage(),
                'alert_data' => $alertData,
            ]);
        }
        
        // Send real-time notifications for critical events
        if (in_array($eventType, $this->criticalEvents) || ($extra['threat_level'] ?? '') === 'HIGH') {
            $this->sendRealTimeAlert($eventType, $alertData);
        }
    }

    private function sendRealTimeAlert(string $eventType, array $alertData): void
    {
        // This would integrate with notification systems (email, Slack, etc.)
        Log::emergency("CRITICAL SECURITY ALERT: {$eventType}", $alertData);
        
        // Here you could add integrations with:
        // - Email notifications to security team
        // - Slack/Discord webhooks
        // - SMS alerts for critical events
        // - Integration with SIEM systems
    }
}
