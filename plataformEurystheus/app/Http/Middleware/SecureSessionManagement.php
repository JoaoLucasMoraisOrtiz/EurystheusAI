<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SecureSessionManagement
{
    /**
     * Session security configuration
     */
    private const MAX_SESSION_LIFETIME = 3600; // 1 hour
    private const SESSION_REGENERATE_INTERVAL = 900; // 15 minutes
    private const MAX_CONCURRENT_SESSIONS = 3;
    private const SESSION_TIMEOUT_WARNING = 300; // 5 minutes

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip all session security checks in local environment
        if (app()->environment('local')) {
            return $next($request);
        }

        // Garantir que $session esteja sempre definido
        $session = $request->session();

        // Validate session security
        $this->validateSessionSecurity($request);

        // Check for session hijacking
        $this->detectSessionHijacking($request);

        // Regenerate session ID periodically
        $this->handleSessionRegeneration($request);

        // Enforce session timeout
        $this->enforceSessionTimeout($request);

        // Limit concurrent sessions
        $this->limitConcurrentSessions($request);

        // Track session activity
        $this->trackSessionActivity($request);

        $response = $next($request);

        // Add session security headers
        return $this->addSessionSecurityHeaders($response);
    }

    /**
     * Validate session security configuration
     */
    private function validateSessionSecurity(Request $request): void
    {
        $session = $request->session();

        // Force proper session cookie settings
        $sessionConfig = config('session');
        $isSecure = $request->secure();

        // Set session cookie parameters before checking
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] * 60, // Convert minutes to seconds
            'path' => $sessionConfig['path'],
            'domain' => $sessionConfig['domain'],
            'secure' => $isSecure,
            'httponly' => true, // Always set to true for security
            'samesite' => 'lax' // Always set to lax for compatibility
        ]);

        // Check if session is properly configured
        if (!$session->isStarted()) {
            Log::warning('Session not started for authenticated request', [
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return;
        }

        // Validate session cookie settings after configuration
        $cookieParams = session_get_cookie_params();

        $securityIssues = [];

        if (!$cookieParams['secure'] && $request->secure()) {
            $securityIssues[] = 'Session cookie not marked as secure';
        }

        if (!$cookieParams['httponly']) {
            $securityIssues[] = 'Session cookie not marked as HTTP-only';
        }

        if ($cookieParams['samesite'] !== 'strict' && $cookieParams['samesite'] !== 'lax') {
            $securityIssues[] = 'Session cookie SameSite not properly configured';
        }

        // Only log remaining issues as info, not warnings
        if (!empty($securityIssues)) {
            Log::info('Session security status', [
                'cookie_params' => $cookieParams,
                'url' => $request->url(),
                'remaining_issues' => $securityIssues,
            ]);
        }
    }

    /**
     * Detect potential session hijacking
     */
    private function detectSessionHijacking(Request $request): void
    {
        $session = $request->session();

        if (!$session->has('_authenticated') || !auth()->check()) {
            return;
        }

        $currentFingerprint = $this->generateSessionFingerprint($request);
        $storedFingerprint = $session->get('_session_fingerprint');

        // First time setting fingerprint
        if (!$storedFingerprint) {
            $session->put('_session_fingerprint', $currentFingerprint);
            $session->put('_session_created_at', time());
            $session->put('_session_ip', $request->ip());
            return;
        }

        // Check for fingerprint mismatch
        if ($currentFingerprint !== $storedFingerprint) {
            Log::error('Potential session hijacking detected', [
                'user_id' => auth()->id(),
                'current_fingerprint' => $currentFingerprint,
                'stored_fingerprint' => $storedFingerprint,
                'current_ip' => $request->ip(),
                'stored_ip' => $session->get('_session_ip'),
                'user_agent' => $request->userAgent(),
                'url' => $request->url(),
                'session_id' => $session->getId(),
            ]);

            // Invalidate session and force re-authentication
            $this->invalidateSession($request, 'Session hijacking detected');
            abort(401, 'Session security violation detected');
        }

        // Check for IP address changes (allow some flexibility for mobile networks)
        $storedIp = $session->get('_session_ip');
        $currentIp = $request->ip();

        if ($storedIp && $currentIp !== $storedIp) {
            // Log IP change but don't immediately invalidate (could be legitimate)
            Log::warning('Session IP address changed', [
                'user_id' => auth()->id(),
                'old_ip' => $storedIp,
                'new_ip' => $currentIp,
                'user_agent' => $request->userAgent(),
                'session_id' => $session->getId(),
            ]);

            // Update stored IP
            $session->put('_session_ip', $currentIp);

            // Store IP change for monitoring
            $this->recordIpChange($request, $storedIp, $currentIp);
        }
    }

    /**
     * Handle periodic session regeneration
     */
    private function handleSessionRegeneration(Request $request): void
    {
        $session = $request->session();

        if (!auth()->check()) {
            return;
        }

        $lastRegeneration = $session->get('_last_regeneration', 0);
        $currentTime = time();

        // Regenerate session ID periodically
        if ($currentTime - $lastRegeneration > self::SESSION_REGENERATE_INTERVAL) {
            $oldSessionId = $session->getId();

            $session->regenerate(true); // Delete old session
            $session->put('_last_regeneration', $currentTime);

            Log::info('Session regenerated', [
                'user_id' => auth()->id(),
                'old_session_id' => $oldSessionId,
                'new_session_id' => $session->getId(),
                'ip' => $request->ip(),
            ]);
        }
    }

    /**
     * Enforce session timeout
     */
    private function enforceSessionTimeout(Request $request): void
    {
        $session = $request->session();

        if (!auth()->check()) {
            return;
        }

        $lastActivity = $session->get('_last_activity', time());
        $sessionCreated = $session->get('_session_created_at', time());
        $currentTime = time();

        // Check absolute session lifetime
        if ($currentTime - $sessionCreated > self::MAX_SESSION_LIFETIME) {
            Log::info('Session expired due to maximum lifetime', [
                'user_id' => auth()->id(),
                'session_age' => $currentTime - $sessionCreated,
                'max_lifetime' => self::MAX_SESSION_LIFETIME,
            ]);

            $this->invalidateSession($request, 'Session expired');
            abort(401, 'Session expired - please log in again');
        }

        // Check for session inactivity
        $inactivityPeriod = $currentTime - $lastActivity;
        $maxInactivity = config('session.lifetime') * 60; // Convert minutes to seconds

        if ($inactivityPeriod > $maxInactivity) {
            Log::info('Session expired due to inactivity', [
                'user_id' => auth()->id(),
                'inactive_for' => $inactivityPeriod,
                'max_inactivity' => $maxInactivity,
            ]);

            $this->invalidateSession($request, 'Session expired due to inactivity');
            abort(401, 'Session expired due to inactivity');
        }

        // Update last activity
        $session->put('_last_activity', $currentTime);

        // Warn about upcoming timeout
        if ($maxInactivity - $inactivityPeriod < self::SESSION_TIMEOUT_WARNING) {
            $session->flash('session_timeout_warning', true);
        }
    }

    /**
     * Limit concurrent sessions per user
     */
    private function limitConcurrentSessions(Request $request): void
    {
        if (!auth()->check()) {
            return;
        }

        $userId = auth()->id();
        $currentSessionId = $request->session()->getId();
        $cacheKey = "user_sessions_{$userId}";

        $activeSessions = Cache::get($cacheKey, []);

        // Clean up expired sessions
        $activeSessions = array_filter($activeSessions, function ($sessionData) {
            return time() - $sessionData['last_activity'] < config('session.lifetime') * 60;
        });

        // Add or update current session
        $activeSessions[$currentSessionId] = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => time(),
            'created_at' => $activeSessions[$currentSessionId]['created_at'] ?? time(),
        ];

        // Check if we exceed the limit
        if (count($activeSessions) > self::MAX_CONCURRENT_SESSIONS) {
            // Sort by last activity and remove oldest sessions
            uasort($activeSessions, function ($a, $b) {
                return $a['last_activity'] <=> $b['last_activity'];
            });

            $sessionsToKeep = array_slice($activeSessions, -self::MAX_CONCURRENT_SESSIONS, null, true);
            $sessionsToRemove = array_diff_key($activeSessions, $sessionsToKeep);

            foreach ($sessionsToRemove as $sessionId => $sessionData) {
                Log::info('Concurrent session limit exceeded, removing old session', [
                    'user_id' => $userId,
                    'removed_session_id' => $sessionId,
                    'session_ip' => $sessionData['ip'],
                    'current_session_id' => $currentSessionId,
                ]);

                // Invalidate old session
                $this->invalidateSessionById($sessionId);
            }

            $activeSessions = $sessionsToKeep;
        }

        // Store updated session list
        Cache::put($cacheKey, $activeSessions, config('session.lifetime') * 60);
    }

    /**
     * Track session activity for security monitoring
     */
    private function trackSessionActivity(Request $request): void
    {
        if (!auth()->check()) {
            return;
        }

        $userId = auth()->id();
        $activityData = [
            'user_id' => $userId,
            'session_id' => $request->session()->getId(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
            'method' => $request->method(),
            'timestamp' => time(),
        ];

        // Store in cache for recent activity monitoring
        $recentActivityKey = "recent_activity_{$userId}";
        $recentActivity = Cache::get($recentActivityKey, []);

        // Keep only last 10 activities
        array_unshift($recentActivity, $activityData);
        $recentActivity = array_slice($recentActivity, 0, 10);

        Cache::put($recentActivityKey, $recentActivity, 3600);

        // Detect suspicious patterns
        $this->detectSuspiciousSessionActivity($recentActivity);
    }

    /**
     * Detect suspicious session activity patterns
     */
    private function detectSuspiciousSessionActivity(array $recentActivity): void
    {
        if (count($recentActivity) < 3) {
            return;
        }

        $ipAddresses = array_column($recentActivity, 'ip');
        $userAgents = array_column($recentActivity, 'user_agent');
        $timestamps = array_column($recentActivity, 'timestamp');

        // Check for rapid location changes
        $uniqueIps = array_unique($ipAddresses);
        if (count($uniqueIps) > 2) {
            Log::warning('Multiple IP addresses detected in session', [
                'user_id' => $recentActivity[0]['user_id'],
                'ip_addresses' => $uniqueIps,
                'session_id' => $recentActivity[0]['session_id'],
            ]);
        }

        // Check for rapid user agent changes
        $uniqueUserAgents = array_unique($userAgents);
        if (count($uniqueUserAgents) > 1) {
            Log::warning('Multiple user agents detected in session', [
                'user_id' => $recentActivity[0]['user_id'],
                'user_agents' => $uniqueUserAgents,
                'session_id' => $recentActivity[0]['session_id'],
            ]);
        }

        // Check for rapid requests (potential automation)
        if (count($timestamps) >= 3) {
            $timeDiffs = [];
            for ($i = 0; $i < count($timestamps) - 1; $i++) {
                $timeDiffs[] = $timestamps[$i] - $timestamps[$i + 1];
            }

            $avgTimeDiff = array_sum($timeDiffs) / count($timeDiffs);
            if ($avgTimeDiff < 2) { // Less than 2 seconds between requests
                Log::warning('Rapid requests detected - possible automation', [
                    'user_id' => $recentActivity[0]['user_id'],
                    'avg_time_diff' => $avgTimeDiff,
                    'request_count' => count($timestamps),
                    'session_id' => $recentActivity[0]['session_id'],
                ]);
            }
        }
    }

    /**
     * Generate session fingerprint
     */
    private function generateSessionFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
            // Don't include IP as it can change legitimately
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Invalidate current session
     */
    private function invalidateSession(Request $request, string $reason): void
    {
        $userId = auth()->id();
        $sessionId = $request->session()->getId();

        Log::info('Session invalidated', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'reason' => $reason,
            'ip' => $request->ip(),
        ]);

        // Clear user's active sessions from cache
        if ($userId) {
            Cache::forget("user_sessions_{$userId}");
            Cache::forget("recent_activity_{$userId}");
        }

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        auth()->logout();
    }

    /**
     * Invalidate session by ID
     */
    private function invalidateSessionById(string $sessionId): void
    {
        // This would require a custom session handler or database sessions
        // For file-based sessions, you'd need to delete the session file
        $sessionPath = storage_path('framework/sessions/' . $sessionId);
        if (file_exists($sessionPath)) {
            unlink($sessionPath);
        }
    }

    /**
     * Record IP address change for monitoring
     */
    private function recordIpChange(Request $request, string $oldIp, string $newIp): void
    {
        try {
            \DB::table('security_audit_log')->insert([
                'user_id' => auth()->id(),
                'event_type' => 'ip_change',
                'ip_address' => $newIp,
                'user_agent' => $request->userAgent(),
                'url' => $request->url(),
                'details' => json_encode([
                    'old_ip' => $oldIp,
                    'new_ip' => $newIp,
                    'session_id' => $request->session()->getId(),
                ]),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record IP change', [
                'error' => $e->getMessage(),
                'old_ip' => $oldIp,
                'new_ip' => $newIp,
            ]);
        }
    }

    /**
     * Add session security headers to response
     */
    private function addSessionSecurityHeaders($response)
    {
        // Session timeout warning header
        if (session()->has('session_timeout_warning')) {
            $response->header('X-Session-Timeout-Warning', 'true');
        }

        // Session security headers
        $response->header('X-Session-Security', 'enforced');

        return $response;
    }
}
