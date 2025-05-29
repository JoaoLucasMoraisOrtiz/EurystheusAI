<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SecurityAlertService
{
    protected array $criticalThresholds = [
        'attacks_per_minute' => 10,
        'failed_logins_per_hour' => 50,
        'blocked_ips_per_hour' => 20,
        'sql_injection_attempts_per_hour' => 5,
        'xss_attempts_per_hour' => 10,
        'dos_attacks_per_hour' => 5,
    ];

    protected array $notificationChannels = [
        'email' => true,
        'slack' => false, // Set to true if you configure Slack webhook
        'database' => true,
        'log' => true,
    ];

    /**
     * Check for security anomalies and trigger alerts
     */
    public function checkSecurityAnomalies(): void
    {
        try {
            $this->checkAttackPatterns();
            $this->checkFailedLoginSpikes();
            $this->checkBlockedIPSpikes();
            $this->checkSQLInjectionAttempts();
            $this->checkXSSAttempts();
            $this->checkDOSAttacks();
            $this->checkSuspiciousPatterns();
            $this->checkSystemIntegrity();
        } catch (\Exception $e) {
            Log::error('Security anomaly check failed: ' . $e->getMessage());
        }
    }

    /**
     * Check for attack patterns
     */
    protected function checkAttackPatterns(): void
    {
        $now = Carbon::now();
        $lastMinute = $now->copy()->subMinute();

        $attacksCount = DB::table('security_audit_log')
            ->where('created_at', '>', $lastMinute)
            ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack', 'csrf_attack'])
            ->count();

        if ($attacksCount >= $this->criticalThresholds['attacks_per_minute']) {
            $this->triggerAlert(
                'HIGH_ATTACK_VOLUME',
                "High attack volume detected: {$attacksCount} attacks in the last minute",
                'critical',
                [
                    'attack_count' => $attacksCount,
                    'timeframe' => '1 minute',
                    'threshold' => $this->criticalThresholds['attacks_per_minute']
                ]
            );
        }
    }

    /**
     * Check for failed login spikes
     */
    protected function checkFailedLoginSpikes(): void
    {
        $lastHour = Carbon::now()->subHour();

        $failedLogins = DB::table('security_audit_log')
            ->where('created_at', '>', $lastHour)
            ->where('event_type', 'authentication_failure')
            ->count();

        if ($failedLogins >= $this->criticalThresholds['failed_logins_per_hour']) {
            $this->triggerAlert(
                'FAILED_LOGIN_SPIKE',
                "High number of failed login attempts: {$failedLogins} in the last hour",
                'high',
                [
                    'failed_login_count' => $failedLogins,
                    'timeframe' => '1 hour',
                    'threshold' => $this->criticalThresholds['failed_logins_per_hour']
                ]
            );
        }
    }

    /**
     * Check for blocked IP spikes
     */
    protected function checkBlockedIPSpikes(): void
    {
        $lastHour = Carbon::now()->subHour();

        $blockedIPs = DB::table('blocked_ips')
            ->where('created_at', '>', $lastHour)
            ->count();

        if ($blockedIPs >= $this->criticalThresholds['blocked_ips_per_hour']) {
            $this->triggerAlert(
                'IP_BLOCKING_SPIKE',
                "High number of IPs blocked: {$blockedIPs} in the last hour",
                'medium',
                [
                    'blocked_ip_count' => $blockedIPs,
                    'timeframe' => '1 hour',
                    'threshold' => $this->criticalThresholds['blocked_ips_per_hour']
                ]
            );
        }
    }

    /**
     * Check for SQL injection attempts
     */
    protected function checkSQLInjectionAttempts(): void
    {
        $lastHour = Carbon::now()->subHour();

        $sqlInjectionAttempts = DB::table('security_audit_log')
            ->where('created_at', '>', $lastHour)
            ->where('event_type', 'sql_injection')
            ->count();

        if ($sqlInjectionAttempts >= $this->criticalThresholds['sql_injection_attempts_per_hour']) {
            $this->triggerAlert(
                'SQL_INJECTION_SPIKE',
                "Multiple SQL injection attempts detected: {$sqlInjectionAttempts} in the last hour",
                'critical',
                [
                    'attempt_count' => $sqlInjectionAttempts,
                    'timeframe' => '1 hour',
                    'threshold' => $this->criticalThresholds['sql_injection_attempts_per_hour']
                ]
            );
        }
    }

    /**
     * Check for XSS attempts
     */
    protected function checkXSSAttempts(): void
    {
        $lastHour = Carbon::now()->subHour();

        $xssAttempts = DB::table('security_audit_log')
            ->where('created_at', '>', $lastHour)
            ->where('event_type', 'xss_attack')
            ->count();

        if ($xssAttempts >= $this->criticalThresholds['xss_attempts_per_hour']) {
            $this->triggerAlert(
                'XSS_ATTACK_SPIKE',
                "Multiple XSS attack attempts detected: {$xssAttempts} in the last hour",
                'high',
                [
                    'attempt_count' => $xssAttempts,
                    'timeframe' => '1 hour',
                    'threshold' => $this->criticalThresholds['xss_attempts_per_hour']
                ]
            );
        }
    }

    /**
     * Check for DOS attacks
     */
    protected function checkDOSAttacks(): void
    {
        $lastHour = Carbon::now()->subHour();

        $dosAttempts = DB::table('security_audit_log')
            ->where('created_at', '>', $lastHour)
            ->where('event_type', 'dos_attack')
            ->count();

        if ($dosAttempts >= $this->criticalThresholds['dos_attacks_per_hour']) {
            $this->triggerAlert(
                'DOS_ATTACK_SPIKE',
                "DoS attack pattern detected: {$dosAttempts} attempts in the last hour",
                'critical',
                [
                    'attempt_count' => $dosAttempts,
                    'timeframe' => '1 hour',
                    'threshold' => $this->criticalThresholds['dos_attacks_per_hour']
                ]
            );
        }
    }

    /**
     * Check for suspicious patterns
     */
    protected function checkSuspiciousPatterns(): void
    {
        // Check for coordinated attacks from multiple IPs
        $lastHour = Carbon::now()->subHour();

        $coordinatedAttacks = DB::table('security_audit_log')
            ->select('ip_address', DB::raw('COUNT(*) as attack_count'))
            ->where('created_at', '>', $lastHour)
            ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack'])
            ->groupBy('ip_address')
            ->having('attack_count', '>=', 5)
            ->count();

        if ($coordinatedAttacks >= 3) {
            $this->triggerAlert(
                'COORDINATED_ATTACK',
                "Potential coordinated attack detected from {$coordinatedAttacks} different IPs",
                'critical',
                [
                    'attacking_ips' => $coordinatedAttacks,
                    'timeframe' => '1 hour'
                ]
            );
        }

        // Check for unusual geographic patterns
        $this->checkGeographicAnomalies();
    }

    /**
     * Check for geographic anomalies
     */
    protected function checkGeographicAnomalies(): void
    {
        $lastHour = Carbon::now()->subHour();

        // Get unique countries from recent attacks
        $attackingCountries = DB::table('security_audit_log')
            ->select('country', DB::raw('COUNT(*) as attack_count'))
            ->where('created_at', '>', $lastHour)
            ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack'])
            ->whereNotNull('country')
            ->groupBy('country')
            ->having('attack_count', '>=', 5)
            ->count();

        if ($attackingCountries >= 5) {
            $this->triggerAlert(
                'GEOGRAPHIC_ANOMALY',
                "Attacks detected from {$attackingCountries} different countries in the last hour",
                'medium',
                [
                    'attacking_countries' => $attackingCountries,
                    'timeframe' => '1 hour'
                ]
            );
        }
    }

    /**
     * Check system integrity
     */
    protected function checkSystemIntegrity(): void
    {
        // Check if security middleware is functioning
        $recentMiddlewareActivity = DB::table('security_audit_log')
            ->where('created_at', '>', Carbon::now()->subMinutes(30))
            ->where('event_type', 'middleware_check')
            ->exists();

        if (!$recentMiddlewareActivity) {
            $this->triggerAlert(
                'MIDDLEWARE_INACTIVE',
                'Security middleware appears to be inactive - no recent activity detected',
                'critical',
                [
                    'last_activity_check' => '30 minutes ago'
                ]
            );
        }

        // Check database health
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->triggerAlert(
                'DATABASE_CONNECTION_FAILED',
                'Database connection failed during security check',
                'critical',
                [
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Trigger a security alert
     */
    public function triggerAlert(string $alertType, string $message, string $severity, array $metadata = []): void
    {
        $alertId = $this->createAlertRecord($alertType, $message, $severity, $metadata);

        if ($this->notificationChannels['email']) {
            $this->sendEmailAlert($alertType, $message, $severity, $metadata, $alertId);
        }

        if ($this->notificationChannels['slack']) {
            $this->sendSlackAlert($alertType, $message, $severity, $metadata);
        }

        if ($this->notificationChannels['log']) {
            $this->logAlert($alertType, $message, $severity, $metadata);
        }
    }

    /**
     * Create alert record in database
     */
    protected function createAlertRecord(string $alertType, string $message, string $severity, array $metadata): int
    {
        if (!$this->notificationChannels['database']) {
            return 0;
        }

        return DB::table('security_alerts')->insertGetId([
            'alert_type' => $alertType,
            'message' => $message,
            'severity' => $severity,
            'metadata' => json_encode($metadata),
            'status' => 'active',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Send email alert to administrators
     */
    protected function sendEmailAlert(string $alertType, string $message, string $severity, array $metadata, int $alertId): void
    {
        try {
            // Get admin email addresses
            $adminEmails = DB::table('users')
                ->where('role', 'admin')
                ->pluck('email')
                ->toArray();

            if (empty($adminEmails)) {
                Log::warning('No admin emails found for security alert notification');
                return;
            }

            // Check if we've already sent this type of alert recently to avoid spam
            $cacheKey = "security_alert_sent_{$alertType}";
            if (Cache::has($cacheKey) && $severity !== 'critical') {
                return; // Don't send duplicate alerts for non-critical events
            }

            // Send email to each admin
            foreach ($adminEmails as $email) {
                Mail::raw($this->formatEmailMessage($alertType, $message, $severity, $metadata, $alertId), function ($mail) use ($email, $alertType, $severity) {
                    $mail->to($email)
                         ->subject("🛡️ Security Alert [{$severity}]: {$alertType}")
                         ->from(config('mail.from.address'), 'Eurystheus Security System');
                });
            }

            // Set cache to prevent spam (except for critical alerts)
            if ($severity !== 'critical') {
                Cache::put($cacheKey, true, 300); // 5 minutes
            }

        } catch (\Exception $e) {
            Log::error('Failed to send security email alert: ' . $e->getMessage());
        }
    }

    /**
     * Format email message
     */
    protected function formatEmailMessage(string $alertType, string $message, string $severity, array $metadata, int $alertId): string
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i:s T');
        
        $email = "🛡️ EURYSTHEUS SECURITY ALERT\n\n";
        $email .= "Alert ID: #{$alertId}\n";
        $email .= "Type: {$alertType}\n";
        $email .= "Severity: " . strtoupper($severity) . "\n";
        $email .= "Time: {$timestamp}\n\n";
        $email .= "Description:\n{$message}\n\n";
        
        if (!empty($metadata)) {
            $email .= "Additional Details:\n";
            foreach ($metadata as $key => $value) {
                $email .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
            }
            $email .= "\n";
        }
        
        $email .= "Dashboard: " . route('admin.security.dashboard') . "\n";
        $email .= "Alert Management: " . route('admin.security.alerts') . "\n\n";
        $email .= "This is an automated security notification from Eurystheus AI Platform.\n";
        $email .= "Please review and take appropriate action if necessary.";
        
        return $email;
    }

    /**
     * Send Slack alert
     */
    protected function sendSlackAlert(string $alertType, string $message, string $severity, array $metadata): void
    {
        $webhookUrl = config('services.slack.webhook_url');
        
        if (!$webhookUrl) {
            return;
        }

        try {
            $color = $this->getSeverityColor($severity);
            
            $payload = [
                'text' => "🛡️ Security Alert: {$alertType}",
                'attachments' => [
                    [
                        'color' => $color,
                        'title' => $alertType,
                        'text' => $message,
                        'fields' => [
                            [
                                'title' => 'Severity',
                                'value' => strtoupper($severity),
                                'short' => true
                            ],
                            [
                                'title' => 'Time',
                                'value' => Carbon::now()->format('Y-m-d H:i:s T'),
                                'short' => true
                            ]
                        ],
                        'footer' => 'Eurystheus Security System',
                        'ts' => Carbon::now()->timestamp
                    ]
                ]
            ];

            Http::post($webhookUrl, $payload);

        } catch (\Exception $e) {
            Log::error('Failed to send Slack security alert: ' . $e->getMessage());
        }
    }

    /**
     * Log security alert
     */
    protected function logAlert(string $alertType, string $message, string $severity, array $metadata): void
    {
        $logMessage = "Security Alert [{$severity}] {$alertType}: {$message}";
        
        if (!empty($metadata)) {
            $logMessage .= ' | Metadata: ' . json_encode($metadata);
        }

        switch ($severity) {
            case 'critical':
                Log::critical($logMessage);
                break;
            case 'high':
                Log::error($logMessage);
                break;
            case 'medium':
                Log::warning($logMessage);
                break;
            default:
                Log::info($logMessage);
                break;
        }
    }

    /**
     * Get color for Slack alerts based on severity
     */
    protected function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'critical' => '#FF0000',
            'high' => '#FF6600',
            'medium' => '#FFAA00',
            'low' => '#FFDD00',
            default => '#36C5F0'
        };
    }

    /**
     * Get recent unresolved alerts
     */
    public function getUnresolvedAlerts(int $limit = 50): array
    {
        return DB::table('security_alerts')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Resolve a security alert
     */
    public function resolveAlert(int $alertId, string $notes = ''): bool
    {
        try {
            DB::table('security_alerts')
                ->where('id', $alertId)
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => Carbon::now(),
                    'resolution_notes' => $notes,
                    'updated_at' => Carbon::now()
                ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to resolve security alert: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(Carbon $startDate): array
    {
        return [
            'total_alerts' => DB::table('security_alerts')
                ->where('created_at', '>=', $startDate)
                ->count(),
                
            'active_alerts' => DB::table('security_alerts')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'active')
                ->count(),
                
            'critical_alerts' => DB::table('security_alerts')
                ->where('created_at', '>=', $startDate)
                ->where('severity', 'critical')
                ->count(),
                
            'alerts_by_type' => DB::table('security_alerts')
                ->select('alert_type', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('alert_type')
                ->orderBy('count', 'desc')
                ->get(),
                
            'alerts_by_severity' => DB::table('security_alerts')
                ->select('severity', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('severity')
                ->get()
        ];
    }
}
