<?php

namespace Tests\Unit\Security;

use Tests\TestCase;
use App\Services\SecurityAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SecurityAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $alertService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertService = new SecurityAlertService();
    }

    /**
     * Test alert creation with different severity levels
     */
    public function test_create_alert_with_different_severities()
    {
        $testCases = [
            ['sql_injection', 'high'],
            ['xss', 'high'],
            ['dos_attack', 'critical'],
            ['brute_force', 'medium'],
            ['phishing', 'medium'],
        ];

        foreach ($testCases as [$type, $expectedSeverity]) {
            $alert = $this->alertService->createAlert($type, [
                'source_ip' => '192.168.1.1',
                'endpoint' => '/test',
            ]);

            $this->assertEquals($type, $alert['type']);
            $this->assertEquals($expectedSeverity, $alert['severity']);
            $this->assertArrayHasKey('id', $alert);
            $this->assertArrayHasKey('detected_at', $alert);
        }
    }

    /**
     * Test anomaly detection thresholds
     */
    public function test_anomaly_detection_thresholds()
    {
        // Mock security events in database
        DB::table('security_events')->insert([
            [
                'type' => 'failed_login',
                'source_ip' => '192.168.1.1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'sql_injection',
                'source_ip' => '192.168.1.2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $anomalies = $this->alertService->detectAnomalies();

        $this->assertIsArray($anomalies);
        // Should detect patterns based on configured thresholds
    }

    /**
     * Test geographic anomaly detection
     */
    public function test_geographic_anomaly_detection()
    {
        // Create events from multiple countries
        $countries = ['US', 'CN', 'RU', 'BR', 'IN', 'FR', 'DE', 'JP', 'GB', 'CA', 'AU'];
        
        foreach ($countries as $country) {
            DB::table('security_events')->insert([
                'type' => 'login_attempt',
                'source_ip' => $this->faker->ipv4,
                'country' => $country,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ]);
        }

        $anomalies = $this->alertService->detectGeographicAnomalies();

        $this->assertIsArray($anomalies);
        // Should detect geographic spread anomaly
        $this->assertArrayHasKey('geographic_anomaly', $anomalies);
    }

    /**
     * Test coordinated attack detection
     */
    public function test_coordinated_attack_detection()
    {
        // Create multiple attacks of same type from different IPs
        $attackType = 'sql_injection';
        $ips = ['192.168.1.1', '192.168.1.2', '192.168.1.3', '192.168.1.4', '192.168.1.5'];

        foreach ($ips as $ip) {
            DB::table('security_events')->insert([
                'type' => $attackType,
                'source_ip' => $ip,
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ]);
        }

        $anomalies = $this->alertService->detectCoordinatedAttacks();

        $this->assertIsArray($anomalies);
        // Should detect coordinated attack pattern
        $this->assertArrayHasKey('coordinated_attack', $anomalies);
    }

    /**
     * Test alert notification channels
     */
    public function test_alert_notification_channels()
    {
        Mail::fake();
        Log::spy();

        $alert = [
            'type' => 'critical_security_event',
            'severity' => 'critical',
            'source_ip' => '192.168.1.1',
            'details' => ['test' => 'data'],
        ];

        $this->alertService->sendNotifications($alert);

        // Verify email was sent for critical alert
        Mail::assertSent(\Illuminate\Mail\Mailable::class);
        
        // Verify logging occurred
        Log::shouldHaveReceived('warning');
    }

    /**
     * Test system health monitoring
     */
    public function test_system_health_monitoring()
    {
        $healthCheck = $this->alertService->checkSystemHealth();

        $this->assertIsArray($healthCheck);
        $this->assertArrayHasKey('database_status', $healthCheck);
        $this->assertArrayHasKey('middleware_status', $healthCheck);
        $this->assertArrayHasKey('response_time', $healthCheck);
    }

    /**
     * Test alert resolution tracking
     */
    public function test_alert_resolution_tracking()
    {
        // Create an alert
        $alert = $this->alertService->createAlert('test_alert', [
            'source_ip' => '192.168.1.1',
        ]);

        // Resolve the alert
        $resolved = $this->alertService->resolveAlert($alert['id'], 'Test resolution');

        $this->assertTrue($resolved);

        // Verify alert is marked as resolved in database
        $alertRecord = DB::table('security_alerts')->where('id', $alert['id'])->first();
        $this->assertEquals('resolved', $alertRecord->status);
        $this->assertNotNull($alertRecord->resolved_at);
    }

    /**
     * Test attack pattern matching
     */
    public function test_attack_pattern_matching()
    {
        $testPatterns = [
            ['payload' => "'; DROP TABLE users; --", 'expected' => 'sql_injection'],
            ['payload' => '<script>alert("xss")</script>', 'expected' => 'xss'],
            ['payload' => '../../../etc/passwd', 'expected' => 'path_traversal'],
            ['payload' => 'system("rm -rf /")', 'expected' => 'command_injection'],
        ];

        foreach ($testPatterns as $test) {
            $detectedType = $this->alertService->detectAttackType($test['payload']);
            $this->assertEquals($test['expected'], $detectedType);
        }
    }

    /**
     * Test alert escalation
     */
    public function test_alert_escalation()
    {
        // Create a medium severity alert
        $alert = $this->alertService->createAlert('brute_force', [
            'source_ip' => '192.168.1.1',
            'severity' => 'medium',
        ]);

        // Simulate time passing without resolution
        DB::table('security_alerts')
            ->where('id', $alert['id'])
            ->update(['created_at' => now()->subHours(2)]);

        // Check for escalation
        $escalated = $this->alertService->checkAlertEscalation($alert['id']);

        $this->assertTrue($escalated);
    }

    /**
     * Test rate limiting detection
     */
    public function test_rate_limiting_detection()
    {
        $ip = '192.168.1.1';
        $threshold = 10;

        // Simulate multiple requests from same IP
        for ($i = 0; $i < $threshold + 5; $i++) {
            DB::table('security_events')->insert([
                'type' => 'request',
                'source_ip' => $ip,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $rateLimitViolation = $this->alertService->detectRateLimitViolation($ip, $threshold);

        $this->assertTrue($rateLimitViolation);
    }

    /**
     * Test alert aggregation
     */
    public function test_alert_aggregation()
    {
        // Create multiple similar alerts
        for ($i = 0; $i < 5; $i++) {
            $this->alertService->createAlert('sql_injection', [
                'source_ip' => '192.168.1.1',
                'endpoint' => '/login',
            ]);
        }

        $aggregated = $this->alertService->aggregateAlerts();

        $this->assertIsArray($aggregated);
        $this->assertArrayHasKey('sql_injection', $aggregated);
        $this->assertGreaterThanOrEqual(5, $aggregated['sql_injection']['count']);
    }

    /**
     * Test threat intelligence integration
     */
    public function test_threat_intelligence_integration()
    {
        $maliciousIp = '192.168.1.100';
        
        // Mock threat intelligence response
        $threatLevel = $this->alertService->checkThreatIntelligence($maliciousIp);

        $this->assertIsString($threatLevel);
        $this->assertContains($threatLevel, ['low', 'medium', 'high', 'critical', 'unknown']);
    }

    /**
     * Test alert statistics
     */
    public function test_alert_statistics()
    {
        // Create various types of alerts
        $alertTypes = ['sql_injection', 'xss', 'dos_attack', 'brute_force'];
        
        foreach ($alertTypes as $type) {
            for ($i = 0; $i < 3; $i++) {
                $this->alertService->createAlert($type, [
                    'source_ip' => '192.168.1.' . ($i + 1),
                ]);
            }
        }

        $statistics = $this->alertService->getAlertStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total_alerts', $statistics);
        $this->assertArrayHasKey('by_type', $statistics);
        $this->assertArrayHasKey('by_severity', $statistics);
        $this->assertEquals(12, $statistics['total_alerts']);
    }

    /**
     * Test false positive handling
     */
    public function test_false_positive_handling()
    {
        $alert = $this->alertService->createAlert('sql_injection', [
            'source_ip' => '192.168.1.1',
        ]);

        // Mark as false positive
        $marked = $this->alertService->markAsFalsePositive($alert['id'], 'Legitimate database query');

        $this->assertTrue($marked);

        // Verify alert is marked as false positive
        $alertRecord = DB::table('security_alerts')->where('id', $alert['id'])->first();
        $this->assertEquals('false_positive', $alertRecord->status);
    }
}
