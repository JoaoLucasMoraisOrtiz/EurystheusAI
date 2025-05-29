<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityDashboardController extends Controller
{
    /**
     * Display the security dashboard
     */
    public function index(Request $request)
    {
        // Ensure user has admin privileges
        $this->authorize('viewSecurityDashboard');

        $timeframe = $request->get('timeframe', '24h');
        $startDate = $this->getStartDate($timeframe);

        $data = [
            'overview' => $this->getSecurityOverview($startDate),
            'threats' => $this->getThreatAnalysis($startDate),
            'attacks' => $this->getAttackStatistics($startDate),
            'sessions' => $this->getSessionSecurity($startDate),
            'vulnerabilities' => $this->getVulnerabilities(),
            'alerts' => $this->getRecentAlerts($startDate),
            'blockedIps' => $this->getBlockedIps(),
            'systemHealth' => $this->getSystemHealthMetrics(),
        ];

        return view('admin.security.dashboard', compact('data', 'timeframe'));
    }

    /**
     * Get security overview metrics
     */
    private function getSecurityOverview(Carbon $startDate): array
    {
        $cacheKey = 'security_overview_' . $startDate->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate) {
            return [
                'total_attacks_blocked' => DB::table('security_audit_log')
                    ->where('created_at', '>=', $startDate)
                    ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack', 'csrf_attack'])
                    ->count(),
                
                'unique_attackers' => DB::table('security_audit_log')
                    ->where('created_at', '>=', $startDate)
                    ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack'])
                    ->distinct('ip_address')
                    ->count(),
                
                'failed_logins' => DB::table('security_audit_log')
                    ->where('created_at', '>=', $startDate)
                    ->where('event_type', 'failed_login')
                    ->count(),
                
                'suspicious_activities' => DB::table('security_audit_log')
                    ->where('created_at', '>=', $startDate)
                    ->whereIn('event_type', ['suspicious_request', 'ip_change', 'session_hijacking'])
                    ->count(),
                
                'active_security_alerts' => DB::table('security_alerts')
                    ->where('resolved', false)
                    ->count(),
                
                'blocked_ips_count' => DB::table('blocked_ips')
                    ->where('expires_at', '>', now())
                    ->count(),
            ];
        });
    }

    /**
     * Get threat analysis data
     */
    private function getThreatAnalysis(Carbon $startDate): array
    {
        $cacheKey = 'threat_analysis_' . $startDate->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate) {
            // Threat types distribution
            $threatTypes = DB::table('security_audit_log')
                ->select('event_type', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack', 'csrf_attack', 'file_upload_attack'])
                ->groupBy('event_type')
                ->orderBy('count', 'desc')
                ->get();

            // Top attacking IPs
            $topAttackers = DB::table('security_audit_log')
                ->select('ip_address', DB::raw('COUNT(*) as attack_count'))
                ->where('created_at', '>=', $startDate)
                ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack'])
                ->groupBy('ip_address')
                ->orderBy('attack_count', 'desc')
                ->limit(10)
                ->get();

            // Attack timeline (hourly)
            $timeline = DB::table('security_audit_log')
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour'),
                    DB::raw('COUNT(*) as attacks')
                )
                ->where('created_at', '>=', $startDate)
                ->whereIn('event_type', ['sql_injection', 'xss_attack', 'dos_attack', 'csrf_attack'])
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            return [
                'threat_types' => $threatTypes,
                'top_attackers' => $topAttackers,
                'timeline' => $timeline,
            ];
        });
    }

    /**
     * Get attack statistics
     */
    private function getAttackStatistics(Carbon $startDate): array
    {
        return [
            'sql_injection' => $this->getAttackStats('sql_injection', $startDate),
            'xss_attacks' => $this->getAttackStats('xss_attack', $startDate),
            'dos_attacks' => $this->getAttackStats('dos_attack', $startDate),
            'csrf_attacks' => $this->getAttackStats('csrf_attack', $startDate),
            'file_attacks' => $this->getAttackStats('file_upload_attack', $startDate),
        ];
    }

    /**
     * Get specific attack type statistics
     */
    private function getAttackStats(string $attackType, Carbon $startDate): array
    {
        $cacheKey = "attack_stats_{$attackType}_" . $startDate->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($attackType, $startDate) {
            $stats = DB::table('security_audit_log')
                ->where('event_type', $attackType)
                ->where('created_at', '>=', $startDate)
                ->get();

            $uniqueIps = $stats->unique('ip_address')->count();
            $totalAttempts = $stats->count();
            
            // Most targeted endpoints
            $targetedUrls = $stats->groupBy('url')
                ->map(function ($group) {
                    return $group->count();
                })
                ->sortDesc()
                ->take(5);

            return [
                'total_attempts' => $totalAttempts,
                'unique_attackers' => $uniqueIps,
                'targeted_urls' => $targetedUrls,
                'recent_attacks' => $stats->take(5),
            ];
        });
    }

    /**
     * Get session security metrics
     */
    private function getSessionSecurity(Carbon $startDate): array
    {
        $cacheKey = 'session_security_' . $startDate->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate) {
            return [
                'session_hijacking_attempts' => DB::table('security_audit_log')
                    ->where('event_type', 'session_hijacking')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                
                'ip_changes' => DB::table('security_audit_log')
                    ->where('event_type', 'ip_change')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                
                'concurrent_session_violations' => DB::table('security_audit_log')
                    ->where('event_type', 'concurrent_session_limit')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                
                'active_user_sessions' => $this->getActiveUserSessions(),
            ];
        });
    }

    /**
     * Get active user sessions
     */
    private function getActiveUserSessions(): array
    {
        // This would depend on your session storage implementation
        // For demonstration, we'll use a simplified version
        return [
            'total_active' => 0, // Would count active sessions
            'unique_users' => 0, // Would count unique authenticated users
            'average_duration' => 0, // Average session duration
        ];
    }

    /**
     * Get vulnerabilities from dependency scanner
     */
    private function getVulnerabilities(): array
    {
        $cacheKey = 'vulnerabilities_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            $alerts = DB::table('security_alerts')
                ->where('type', 'dependency_vulnerability')
                ->where('resolved', false)
                ->orderBy('severity', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            $severityCounts = $alerts->groupBy('severity')->map(function ($group) {
                return $group->count();
            });

            return [
                'total_vulnerabilities' => $alerts->count(),
                'severity_breakdown' => $severityCounts,
                'recent_vulnerabilities' => $alerts->take(10),
            ];
        });
    }

    /**
     * Get recent security alerts
     */
    private function getRecentAlerts(Carbon $startDate): array
    {
        return DB::table('security_alerts')
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Get blocked IPs information
     */
    private function getBlockedIps(): array
    {
        $blockedIps = DB::table('blocked_ips')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $recentBlocks = DB::table('blocked_ips')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'active_blocks' => $blockedIps,
            'recent_blocks_24h' => $recentBlocks,
            'total_blocked' => $blockedIps->count(),
        ];
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealthMetrics(): array
    {
        $cacheKey = 'system_health_' . date('Y-m-d-H');
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'security_middleware_status' => $this->checkMiddlewareStatus(),
                'database_connectivity' => $this->checkDatabaseHealth(),
                'cache_status' => $this->checkCacheHealth(),
                'log_file_sizes' => $this->getLogFileSizes(),
                'security_config_status' => $this->checkSecurityConfiguration(),
            ];
        });
    }

    /**
     * Check middleware status
     */
    private function checkMiddlewareStatus(): array
    {
        // This would check if security middleware is properly loaded
        $middlewareList = [
            'SecurityMonitoring',
            'DosProtection',
            'SqlInjectionProtection',
            'XssProtection',
            'SecureCommunicationProtocols',
            'SecurityConfigurationValidation',
        ];

        $status = [];
        foreach ($middlewareList as $middleware) {
            $status[$middleware] = class_exists("App\\Http\\Middleware\\{$middleware}");
        }

        return $status;
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Database connectivity check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): bool
    {
        try {
            Cache::put('health_check', true, 60);
            return Cache::get('health_check', false);
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get log file sizes
     */
    private function getLogFileSizes(): array
    {
        $logPath = storage_path('logs');
        $sizes = [];

        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            foreach ($files as $file) {
                $sizes[basename($file)] = $this->formatBytes(filesize($file));
            }
        }

        return $sizes;
    }

    /**
     * Check security configuration
     */
    private function checkSecurityConfiguration(): array
    {
        return [
            'https_enforced' => config('app.force_https', false),
            'debug_disabled' => !config('app.debug'),
            'session_secure' => config('session.secure', false),
            'session_http_only' => config('session.http_only', true),
            'csrf_enabled' => true, // Always enabled in Laravel
        ];
    }

    /**
     * Resolve security alert
     */
    public function resolveAlert(Request $request, $alertId)
    {
        $this->authorize('manageSecurityAlerts');

        $alert = DB::table('security_alerts')->where('id', $alertId)->first();
        
        if (!$alert) {
            return response()->json(['error' => 'Alert not found'], 404);
        }

        DB::table('security_alerts')
            ->where('id', $alertId)
            ->update([
                'resolved' => true,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_notes' => $request->input('notes'),
                'updated_at' => now(),
            ]);

        Log::info('Security alert resolved', [
            'alert_id' => $alertId,
            'resolved_by' => auth()->id(),
            'notes' => $request->input('notes'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Unblock IP address
     */
    public function unblockIp(Request $request, $ipId)
    {
        $this->authorize('manageSecurityBlocks');

        $blockedIp = DB::table('blocked_ips')->where('id', $ipId)->first();
        
        if (!$blockedIp) {
            return response()->json(['error' => 'Blocked IP not found'], 404);
        }

        DB::table('blocked_ips')->where('id', $ipId)->delete();

        Log::info('IP address unblocked manually', [
            'ip_address' => $blockedIp->ip_address,
            'unblocked_by' => auth()->id(),
            'reason' => $request->input('reason'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Export security data
     */
    public function exportData(Request $request)
    {
        $this->authorize('exportSecurityData');

        $type = $request->get('type', 'audit_log');
        $startDate = $request->get('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $filename = "security_{$type}_{$startDate}_to_{$endDate}.csv";

        switch ($type) {
            case 'audit_log':
                return $this->exportAuditLog($startDate, $endDate, $filename);
            case 'security_alerts':
                return $this->exportSecurityAlerts($startDate, $endDate, $filename);
            case 'blocked_ips':
                return $this->exportBlockedIps($filename);
            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }
    }

    /**
     * Export audit log to CSV
     */
    private function exportAuditLog(string $startDate, string $endDate, string $filename)
    {
        $data = DB::table('security_audit_log')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateCsvResponse($data, $filename);
    }

    /**
     * Export security alerts to CSV
     */
    private function exportSecurityAlerts(string $startDate, string $endDate, string $filename)
    {
        $data = DB::table('security_alerts')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateCsvResponse($data, $filename);
    }

    /**
     * Export blocked IPs to CSV
     */
    private function exportBlockedIps(string $filename)
    {
        $data = DB::table('blocked_ips')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateCsvResponse($data, $filename);
    }

    /**
     * Generate CSV response
     */
    private function generateCsvResponse($data, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                // Write headers
                fputcsv($file, array_keys((array) $data->first()));
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($file, (array) $row);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get start date based on timeframe
     */
    private function getStartDate(string $timeframe): Carbon
    {
        switch ($timeframe) {
            case '1h':
                return now()->subHour();
            case '24h':
                return now()->subDay();
            case '7d':
                return now()->subWeek();
            case '30d':
                return now()->subMonth();
            default:
                return now()->subDay();
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
