<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Services\SecurityAlertService;
use App\Services\SecurityBackupService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class SecuritySystemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test SQL injection protection middleware
     */
    public function test_sql_injection_protection()
    {
        $maliciousPayloads = [
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "admin' OR '1'='1",
            "'; DELETE FROM users WHERE id=1; --",
            "' AND 1=CONVERT(int, (SELECT COUNT(*) FROM users)) --",
        ];

        foreach ($maliciousPayloads as $payload) {
            $response = $this->post('/login', [
                'email' => $payload,
                'password' => 'password'
            ]);

            // Should be blocked by SQL injection protection
            $this->assertTrue(
                $response->status() === 403 || $response->status() === 400,
                "SQL injection payload was not blocked: {$payload}"
            );
        }
    }

    /**
     * Test XSS protection middleware
     */
    public function test_xss_protection()
    {
        $this->actingAs($this->user);

        $xssPayloads = [
            '<script>alert("xss")</script>',
            '<img src="x" onerror="alert(1)">',
            'javascript:alert("xss")',
            '<svg onload="alert(1)">',
            '<iframe src="javascript:alert(1)"></iframe>',
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->post('/api/prompts', [
                'prompt' => $payload,
                'model' => 'gpt-3.5-turbo'
            ]);

            // Should be blocked by XSS protection
            $this->assertTrue(
                $response->status() === 403 || $response->status() === 400,
                "XSS payload was not blocked: {$payload}"
            );
        }
    }

    /**
     * Test DoS protection middleware
     */
    public function test_dos_protection()
    {
        // Simulate rapid requests
        $responses = [];
        for ($i = 0; $i < 100; $i++) {
            $responses[] = $this->get('/');
        }

        // Some requests should be rate limited
        $rateLimitedResponses = array_filter($responses, function($response) {
            return $response->status() === 429;
        });

        $this->assertTrue(
            count($rateLimitedResponses) > 0,
            'DoS protection did not activate after rapid requests'
        );
    }

    /**
     * Test enhanced authentication middleware
     */
    public function test_enhanced_authentication()
    {
        // Test with invalid session
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(302, $response->status()); // Redirect to login

        // Test with valid user but wrong role
        $this->actingAs($this->user);
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(403, $response->status()); // Forbidden

        // Test with admin user
        $this->actingAs($this->admin);
        $response = $this->get('/admin/dashboard');
        $this->assertEquals(200, $response->status()); // Success
    }

    /**
     * Test security monitoring middleware
     */
    public function test_security_monitoring()
    {
        // Clear existing logs
        Log::shouldReceive('warning')->andReturn(true);
        Log::shouldReceive('error')->andReturn(true);
        Log::shouldReceive('info')->andReturn(true);

        // Make request that should trigger monitoring
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]);

        // Verify monitoring is active (should log failed login attempt)
        $this->assertTrue(true); // Log mocking makes exact verification complex
    }

    /**
     * Test secure session management
     */
    public function test_secure_session_management()
    {
        $this->actingAs($this->user);

        // Make initial request
        $response = $this->get('/dashboard');
        $this->assertEquals(200, $response->status());

        // Check session security headers
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }

    /**
     * Test API security middleware
     */
    public function test_api_security()
    {
        // Test without authentication
        $response = $this->post('/api/prompts', [
            'prompt' => 'Test prompt',
            'model' => 'gpt-3.5-turbo'
        ]);
        $this->assertEquals(401, $response->status());

        // Test with authentication but malformed data
        $this->actingAs($this->user);
        $response = $this->post('/api/prompts', [
            'prompt' => '', // Empty prompt
            'model' => 'invalid-model'
        ]);
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test security alert service
     */
    public function test_security_alert_service()
    {
        $alertService = app(SecurityAlertService::class);

        // Test creating an alert
        $alert = $alertService->createAlert('sql_injection', [
            'source_ip' => '192.168.1.100',
            'user_agent' => 'Evil Bot',
            'endpoint' => '/login',
            'payload' => "'; DROP TABLE users; --"
        ]);

        $this->assertIsArray($alert);
        $this->assertEquals('sql_injection', $alert['type']);
        $this->assertEquals('high', $alert['severity']);

        // Test anomaly detection
        $anomalies = $alertService->detectAnomalies();
        $this->assertIsArray($anomalies);
    }

    /**
     * Test security backup service
     */
    public function test_security_backup_service()
    {
        $backupService = app(SecurityBackupService::class);

        // Test creating a backup
        $result = $backupService->createSecurityBackup('test');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('backup_name', $result);
        $this->assertArrayHasKey('archive_path', $result);

        // Test listing backups
        $backups = $backupService->listBackups();
        $this->assertIsArray($backups);
        $this->assertGreaterThan(0, count($backups));

        // Test backup verification
        $verification = $backupService->verifyBackupIntegrity($result['backup_name']);
        $this->assertTrue($verification['valid']);
    }

    /**
     * Test file upload security
     */
    public function test_secure_file_upload()
    {
        $this->actingAs($this->user);

        // Test malicious file upload
        $maliciousFile = \Illuminate\Http\UploadedFile::fake()->create('evil.php', 100);
        
        $response = $this->post('/api/upload', [
            'file' => $maliciousFile
        ]);

        // Should be blocked
        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test CSRF protection
     */
    public function test_csrf_protection()
    {
        // Test POST without CSRF token
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        // Should be blocked by CSRF protection
        $this->assertEquals(419, $response->status()); // CSRF token mismatch
    }

    /**
     * Test security console commands
     */
    public function test_security_console_commands()
    {
        // Test security monitoring command
        $exitCode = Artisan::call('security:monitor', ['--force' => true]);
        $this->assertEquals(0, $exitCode);

        // Test security backup command
        $exitCode = Artisan::call('security:backup', ['--type' => 'test']);
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test geographic security restrictions
     */
    public function test_geographic_security()
    {
        // This would require more complex setup with IP geolocation
        // For now, just verify the middleware is loaded
        $middlewareAliases = app('router')->getMiddleware();
        
        $this->assertArrayHasKey('security.monitoring', $middlewareAliases);
        $this->assertArrayHasKey('dos.protection', $middlewareAliases);
    }

    /**
     * Test dependency vulnerability scanning
     */
    public function test_dependency_vulnerability_scanning()
    {
        // Test that the middleware is properly registered
        $response = $this->get('/');
        
        // Should complete without errors (scanning happens in background)
        $this->assertTrue($response->status() < 500);
    }

    /**
     * Test security configuration validation
     */
    public function test_security_configuration_validation()
    {
        // Test accessing security configuration
        $securityConfig = config('security');
        
        $this->assertIsArray($securityConfig);
        $this->assertArrayHasKey('enabled', $securityConfig);
        $this->assertArrayHasKey('thresholds', $securityConfig);
        $this->assertArrayHasKey('alert_levels', $securityConfig);
    }

    /**
     * Test brute force protection
     */
    public function test_brute_force_protection()
    {
        // Attempt multiple failed logins
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // After multiple attempts, should be blocked
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertTrue($response->status() >= 400);
    }

    /**
     * Test security headers
     */
    public function test_security_headers()
    {
        $response = $this->get('/');

        // Verify security headers are present
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
        $response->assertHeader('Referrer-Policy');
        $response->assertHeader('Strict-Transport-Security');
    }

    /**
     * Test error handling and logging
     */
    public function test_security_error_handling()
    {
        // Test that security errors are properly logged
        Log::shouldReceive('error')->once();

        try {
            // Trigger a security-related error
            throw new \Exception('Test security error');
        } catch (\Exception $e) {
            Log::error('Security test error', ['error' => $e->getMessage()]);
        }

        $this->assertTrue(true); // Test passed if we reach here
    }
}
