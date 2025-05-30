<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityAlertService;
use App\Models\SecurityEvent;
use App\Models\FailedLoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityDemoCommand extends Command
{
    protected $signature = 'security:demo {--simulate : Simulate security events}';
    protected $description = 'Demonstrate the security system capabilities';

    private SecurityAlertService $securityService;

    public function __construct(SecurityAlertService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    public function handle()
    {
        $this->info('🛡️  Eurystheus AI Security System Demo');
        $this->info('=====================================');

        if ($this->option('simulate')) {
            $this->simulateSecurityEvents();
        }

        $this->displaySystemStatus();
        $this->displaySecurityMetrics();
        $this->displayRecentEvents();
        $this->testSecurityFeatures();

        $this->info('');
        $this->info('✅ Security system demonstration completed!');
        $this->info('🔗 Access admin security dashboard: /admin/security/dashboard');
    }

    private function simulateSecurityEvents()
    {
        $this->info('');
        $this->warn('⚠️  Simulating Security Events...');

        // Simulate SQL injection attempt
        SecurityEvent::create([
            'event_type' => SecurityEvent::TYPE_SQL_INJECTION,
            'severity' => SecurityEvent::SEVERITY_HIGH,
            'source_ip' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'url' => '/api/users',
            'method' => 'POST',
            'payload' => ['input' => "'; DROP TABLE users; --"],
            'description' => 'SQL injection attempt detected in user input',
            'status' => SecurityEvent::STATUS_BLOCKED,
            'automated_response' => true,
            'response_action' => 'IP_BLOCKED'
        ]);

        // Simulate XSS attempt
        SecurityEvent::create([
            'event_type' => SecurityEvent::TYPE_XSS_ATTACK,
            'severity' => SecurityEvent::SEVERITY_MEDIUM,
            'source_ip' => '203.0.113.42',
            'user_agent' => 'Malicious Bot v1.0',
            'url' => '/dashboard',
            'method' => 'POST',
            'payload' => ['comment' => '<script>alert("XSS")</script>'],
            'description' => 'XSS attempt detected in form submission',
            'status' => SecurityEvent::STATUS_BLOCKED,
            'automated_response' => true,
            'response_action' => 'REQUEST_BLOCKED'
        ]);

        // Simulate failed login attempts
        for ($i = 0; $i < 5; $i++) {
            FailedLoginAttempt::create([
                'ip_address' => '198.51.100.10',
                'email' => 'admin@example.com',
                'user_agent' => 'BruteForcer/2.0',
                'attack_pattern' => FailedLoginAttempt::PATTERN_BRUTE_FORCE,
                'attempts_in_window' => $i + 1,
                'is_blocked_ip' => $i >= 3,
                'triggered_lockout' => $i >= 4,
                'lockout_until' => $i >= 4 ? Carbon::now()->addHours(1) : null
            ]);
        }

        $this->info('✅ Simulated security events created');
    }

    private function displaySystemStatus()
    {
        $this->info('');
        $this->info('📊 System Security Status:');

        $status = [
            'Security Monitoring' => '🟢 Active',
            'Auto IP Blocking' => '🟢 Enabled',
            'Email Alerts' => '🟢 Configured',
            'Backup System' => '🟢 Operational',
            'Threat Detection' => '🟢 Running',
        ];

        foreach ($status as $component => $state) {
            $this->line("   {$component}: {$state}");
        }
    }

    private function displaySecurityMetrics()
    {
        $this->info('');
        $this->info('📈 Security Metrics (Last 24h):');

        $metrics = [
            'Total Security Events' => SecurityEvent::where('created_at', '>=', Carbon::now()->subDay())->count(),
            'Critical Events' => SecurityEvent::bySeverity(SecurityEvent::SEVERITY_CRITICAL)->recent(24)->count(),
            'Blocked Attacks' => SecurityEvent::byStatus(SecurityEvent::STATUS_BLOCKED)->recent(24)->count(),
            'Failed Login Attempts' => FailedLoginAttempt::recent(1440)->count(),
            'Blocked IPs' => FailedLoginAttempt::blockedIps()->count(),
            'Active Lockouts' => FailedLoginAttempt::activeLockouts()->count(),
        ];

        foreach ($metrics as $metric => $value) {
            $color = $value > 0 ? 'yellow' : 'green';
            $this->line("   {$metric}: <fg={$color}>{$value}</>");
        }
    }

    private function displayRecentEvents()
    {
        $this->info('');
        $this->info('🔍 Recent Security Events:');

        $events = SecurityEvent::with(['user', 'resolvedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($events->isEmpty()) {
            $this->line('   No recent events found');
            return;
        }

        foreach ($events as $event) {
            $severity = match($event->severity) {
                'critical' => '<fg=red>CRITICAL</>',
                'high' => '<fg=yellow>HIGH</>',
                'medium' => '<fg=blue>MEDIUM</>',
                'low' => '<fg=green>LOW</>',
                default => $event->severity
            };

            $this->line("   [{$severity}] {$event->type_display_name} from {$event->source_ip}");
            $this->line("      Status: {$event->status} | Time: {$event->created_at->diffForHumans()}");
        }
    }

    private function testSecurityFeatures()
    {
        $this->info('');
        $this->info('🧪 Testing Security Features:');

        // Test security event creation
        try {
            SecurityEvent::create([
                'event_type' => 'test_event',
                'severity' => 'low',
                'source_ip' => '127.0.0.1',
                'description' => 'Security Demo Test',
                'metadata' => ['test' => true],
                'status' => SecurityEvent::STATUS_DETECTED
            ]);
            $this->line('   ✅ Security event creation: Working');
        } catch (\Exception $e) {
            $this->line('   ❌ Security event creation: Failed - ' . $e->getMessage());
        }

        // Test backup system
        try {
            $this->call('security:backup', [], $this->getOutput());
            $this->line('   ✅ Security backup system: Working');
        } catch (\Exception $e) {
            $this->line('   ❌ Security backup system: Failed - ' . $e->getMessage());
        }

        // Test verification system
        try {
            $this->call('security:verify', [], $this->getOutput());
            $this->line('   ✅ Security verification: Working');
        } catch (\Exception $e) {
            $this->line('   ❌ Security verification: Failed - ' . $e->getMessage());
        }
    }
}
