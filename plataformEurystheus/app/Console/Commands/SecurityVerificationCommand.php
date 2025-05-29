<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SecurityVerificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:verify 
                           {--fix : Attempt to fix issues automatically}
                           {--detailed : Show detailed information}
                           {--export= : Export results to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify all security implementations and configurations';

    protected $issues = [];
    protected $passed = [];
    protected $warnings = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🛡️  Eurystheus AI Security Verification');
        $this->info('==========================================');
        $this->newLine();

        $checks = [
            'verifyMiddleware',
            'verifyDatabaseTables',
            'verifyConfigurationFiles',
            'verifySecurityServices',
            'verifyBackupSystem',
            'verifyConsoleCommands',
            'verifyRouteProtection',
            'verifyEmailTemplates',
            'verifyTestSuite',
            'verifyDocumentation',
            'verifyEnvironmentSettings',
            'verifyFilePermissions',
            'verifyDependencies',
            'verifyLogRotation',
            'verifySSLConfiguration',
        ];

        $progressBar = $this->output->createProgressBar(count($checks));
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($checks as $check) {
            $progressBar->setMessage("Running {$check}...");
            $this->$check();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->displayResults();

        if ($this->option('export')) {
            $this->exportResults();
        }

        return count($this->issues) === 0 ? 0 : 1;
    }

    /**
     * Verify security middleware
     */
    protected function verifyMiddleware()
    {
        $middlewareClasses = [
            'SqlInjectionProtection',
            'XssProtection',
            'EnhancedAuthentication',
            'DosProtection',
            'SecureFileUpload',
            'ApiSecurityMiddleware',
            'SecureSessionManagement',
            'EnhancedCsrfProtection',
            'SecurityMonitoring',
            'SecureCommunicationProtocols',
            'SecurityConfigurationValidation',
            'DependencyVulnerabilityScanner',
        ];

        foreach ($middlewareClasses as $middleware) {
            $path = app_path("Http/Middleware/{$middleware}.php");
            if (File::exists($path)) {
                $this->passed[] = "Middleware {$middleware} exists";
            } else {
                $this->issues[] = "Missing middleware: {$middleware}";
            }
        }

        // Check middleware registration
        $bootstrapFile = base_path('bootstrap/app.php');
        if (File::exists($bootstrapFile)) {
            $content = File::get($bootstrapFile);
            if (str_contains($content, 'SecurityMonitoring')) {
                $this->passed[] = "Security middleware registered in bootstrap";
            } else {
                $this->issues[] = "Security middleware not properly registered";
            }
        }
    }

    /**
     * Verify database tables
     */
    protected function verifyDatabaseTables()
    {
        $requiredTables = [
            'security_alerts',
            'security_events',
            'blocked_ips',
            'failed_login_attempts',
        ];

        foreach ($requiredTables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $this->passed[] = "Database table {$table} exists";
                } else {
                    $this->issues[] = "Missing database table: {$table}";
                }
            } catch (\Exception $e) {
                $this->issues[] = "Database connection error: " . $e->getMessage();
            }
        }
    }

    /**
     * Verify configuration files
     */
    protected function verifyConfigurationFiles()
    {
        $configFiles = [
            'config/security.php',
            '.env.security.example',
        ];

        foreach ($configFiles as $file) {
            if (File::exists(base_path($file))) {
                $this->passed[] = "Configuration file {$file} exists";
            } else {
                $this->issues[] = "Missing configuration file: {$file}";
            }
        }

        // Verify security config structure
        try {
            $securityConfig = config('security');
            if (is_array($securityConfig) && isset($securityConfig['enabled'])) {
                $this->passed[] = "Security configuration is properly structured";
            } else {
                $this->issues[] = "Security configuration is malformed";
            }
        } catch (\Exception $e) {
            $this->issues[] = "Cannot load security configuration: " . $e->getMessage();
        }
    }

    /**
     * Verify security services
     */
    protected function verifySecurityServices()
    {
        $services = [
            'SecurityAlertService',
            'SecurityBackupService',
        ];

        foreach ($services as $service) {
            $path = app_path("Services/{$service}.php");
            if (File::exists($path)) {
                $this->passed[] = "Security service {$service} exists";
                
                // Try to instantiate service
                try {
                    $serviceClass = "App\\Services\\{$service}";
                    if (class_exists($serviceClass)) {
                        $this->passed[] = "Security service {$service} is loadable";
                    } else {
                        $this->issues[] = "Security service {$service} class not found";
                    }
                } catch (\Exception $e) {
                    $this->issues[] = "Cannot instantiate {$service}: " . $e->getMessage();
                }
            } else {
                $this->issues[] = "Missing security service: {$service}";
            }
        }
    }

    /**
     * Verify backup system
     */
    protected function verifyBackupSystem()
    {
        // Check backup directory
        if (Storage::exists('backups/security')) {
            $this->passed[] = "Security backup directory exists";
        } else {
            $this->warnings[] = "Security backup directory not found (will be created on first backup)";
        }

        // Check backup command
        try {
            $exitCode = Artisan::call('list');
            $output = Artisan::output();
            if (str_contains($output, 'security:backup')) {
                $this->passed[] = "Security backup command is registered";
            } else {
                $this->issues[] = "Security backup command not found";
            }
        } catch (\Exception $e) {
            $this->issues[] = "Cannot verify backup command: " . $e->getMessage();
        }
    }

    /**
     * Verify console commands
     */
    protected function verifyConsoleCommands()
    {
        $commands = [
            'SecurityMonitoringCommand.php',
            'SecurityBackupCommand.php',
            'SecurityVerificationCommand.php',
        ];

        foreach ($commands as $command) {
            $path = app_path("Console/Commands/{$command}");
            if (File::exists($path)) {
                $this->passed[] = "Console command {$command} exists";
            } else {
                $this->issues[] = "Missing console command: {$command}";
            }
        }

        // Check console routes
        $consolePath = base_path('routes/console.php');
        if (File::exists($consolePath)) {
            $content = File::get($consolePath);
            if (str_contains($content, 'security:monitor')) {
                $this->passed[] = "Security monitoring scheduled";
            } else {
                $this->issues[] = "Security monitoring not scheduled";
            }
        }
    }

    /**
     * Verify route protection
     */
    protected function verifyRouteProtection()
    {
        $webRoutes = base_path('routes/web.php');
        if (File::exists($webRoutes)) {
            $content = File::get($webRoutes);
            if (str_contains($content, 'admin') && str_contains($content, 'role:admin')) {
                $this->passed[] = "Admin routes are protected";
            } else {
                $this->warnings[] = "Admin route protection may be incomplete";
            }
        }
    }

    /**
     * Verify email templates
     */
    protected function verifyEmailTemplates()
    {
        $emailTemplate = resource_path('views/emails/security-alert.blade.php');
        if (File::exists($emailTemplate)) {
            $this->passed[] = "Security alert email template exists";
        } else {
            $this->issues[] = "Missing security alert email template";
        }
    }

    /**
     * Verify test suite
     */
    protected function verifyTestSuite()
    {
        $testFiles = [
            'tests/Feature/Security/SecuritySystemTest.php',
            'tests/Unit/Security/SecurityAlertServiceTest.php',
        ];

        foreach ($testFiles as $testFile) {
            if (File::exists(base_path($testFile))) {
                $this->passed[] = "Test file {$testFile} exists";
            } else {
                $this->issues[] = "Missing test file: {$testFile}";
            }
        }
    }

    /**
     * Verify documentation
     */
    protected function verifyDocumentation()
    {
        $docs = [
            'SECURITY.md',
            'README.md',
        ];

        foreach ($docs as $doc) {
            if (File::exists(base_path($doc))) {
                $this->passed[] = "Documentation file {$doc} exists";
            } else {
                $this->warnings[] = "Missing documentation: {$doc}";
            }
        }
    }

    /**
     * Verify environment settings
     */
    protected function verifyEnvironmentSettings()
    {
        $envFile = base_path('.env');
        if (!File::exists($envFile)) {
            $this->warnings[] = ".env file not found";
            return;
        }

        $envContent = File::get($envFile);
        $requiredVars = [
            'APP_KEY',
            'DB_CONNECTION',
            'MAIL_MAILER',
        ];

        foreach ($requiredVars as $var) {
            if (str_contains($envContent, $var . '=')) {
                $this->passed[] = "Environment variable {$var} is set";
            } else {
                $this->warnings[] = "Environment variable {$var} may not be set";
            }
        }

        // Check for security-specific variables
        $securityVars = [
            'SECURITY_MONITORING_ENABLED',
            'SECURITY_EMAIL_NOTIFICATIONS',
        ];

        foreach ($securityVars as $var) {
            if (str_contains($envContent, $var)) {
                $this->passed[] = "Security environment variable {$var} is configured";
            } else {
                $this->warnings[] = "Security variable {$var} not found (will use defaults)";
            }
        }
    }

    /**
     * Verify file permissions
     */
    protected function verifyFilePermissions()
    {
        $criticalFiles = [
            '.env' => 600,
            'storage/logs' => 755,
            'bootstrap/cache' => 755,
        ];

        foreach ($criticalFiles as $file => $expectedPerm) {
            $fullPath = base_path($file);
            if (File::exists($fullPath)) {
                $actualPerm = substr(sprintf('%o', fileperms($fullPath)), -3);
                if ($actualPerm == $expectedPerm) {
                    $this->passed[] = "File permissions correct for {$file}";
                } else {
                    $this->warnings[] = "File permissions for {$file}: expected {$expectedPerm}, got {$actualPerm}";
                }
            }
        }
    }

    /**
     * Verify dependencies
     */
    protected function verifyDependencies()
    {
        $composerFile = base_path('composer.json');
        if (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            
            $securityPackages = [
                'laravel/framework',
            ];

            foreach ($securityPackages as $package) {
                if (isset($composer['require'][$package])) {
                    $this->passed[] = "Required package {$package} is listed";
                } else {
                    $this->warnings[] = "Package {$package} not found in composer.json";
                }
            }
        }
    }

    /**
     * Verify log rotation
     */
    protected function verifyLogRotation()
    {
        $loggingConfig = config('logging');
        if (isset($loggingConfig['channels']['security'])) {
            $this->passed[] = "Security logging channel is configured";
        } else {
            $this->warnings[] = "Security logging channel not configured";
        }
    }

    /**
     * Verify SSL configuration
     */
    protected function verifySSLConfiguration()
    {
        $sessionConfig = config('session');
        if ($sessionConfig['secure'] ?? false) {
            $this->passed[] = "Secure cookies are enabled";
        } else {
            $this->warnings[] = "Secure cookies are not enabled (should be enabled in production)";
        }

        if ($sessionConfig['http_only'] ?? false) {
            $this->passed[] = "HTTP-only cookies are enabled";
        } else {
            $this->issues[] = "HTTP-only cookies are not enabled";
        }
    }

    /**
     * Display verification results
     */
    protected function displayResults()
    {
        $this->info('🔍 Security Verification Results');
        $this->info('===============================');
        $this->newLine();

        // Passed checks
        if (!empty($this->passed)) {
            $this->info('✅ Passed Checks (' . count($this->passed) . ')');
            foreach ($this->passed as $check) {
                if ($this->option('detailed')) {
                    $this->line("  ✓ {$check}");
                }
            }
            $this->newLine();
        }

        // Warnings
        if (!empty($this->warnings)) {
            $this->warn('⚠️  Warnings (' . count($this->warnings) . ')');
            foreach ($this->warnings as $warning) {
                $this->line("  ⚠ {$warning}");
            }
            $this->newLine();
        }

        // Issues
        if (!empty($this->issues)) {
            $this->error('❌ Issues Found (' . count($this->issues) . ')');
            foreach ($this->issues as $issue) {
                $this->line("  ✗ {$issue}");
            }
            $this->newLine();
        }

        // Summary
        $total = count($this->passed) + count($this->warnings) + count($this->issues);
        $this->info("📊 Summary: {$total} checks performed");
        $this->info("  ✅ Passed: " . count($this->passed));
        $this->info("  ⚠️  Warnings: " . count($this->warnings));
        $this->info("  ❌ Issues: " . count($this->issues));

        if (count($this->issues) === 0) {
            $this->info('🎉 All critical security checks passed!');
        } else {
            $this->error('🚨 Security issues found that need attention.');
        }
    }

    /**
     * Export results to file
     */
    protected function exportResults()
    {
        $filename = $this->option('export');
        $timestamp = now()->toISOString();
        
        $report = [
            'timestamp' => $timestamp,
            'summary' => [
                'total_checks' => count($this->passed) + count($this->warnings) + count($this->issues),
                'passed' => count($this->passed),
                'warnings' => count($this->warnings),
                'issues' => count($this->issues),
            ],
            'passed' => $this->passed,
            'warnings' => $this->warnings,
            'issues' => $this->issues,
        ];

        if (str_ends_with($filename, '.json')) {
            File::put($filename, json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $content = "Security Verification Report - {$timestamp}\n";
            $content .= str_repeat('=', 50) . "\n\n";
            
            $content .= "SUMMARY:\n";
            $content .= "Total Checks: " . $report['summary']['total_checks'] . "\n";
            $content .= "Passed: " . $report['summary']['passed'] . "\n";
            $content .= "Warnings: " . $report['summary']['warnings'] . "\n";
            $content .= "Issues: " . $report['summary']['issues'] . "\n\n";
            
            if (!empty($this->passed)) {
                $content .= "PASSED CHECKS:\n";
                foreach ($this->passed as $check) {
                    $content .= "✓ {$check}\n";
                }
                $content .= "\n";
            }
            
            if (!empty($this->warnings)) {
                $content .= "WARNINGS:\n";
                foreach ($this->warnings as $warning) {
                    $content .= "⚠ {$warning}\n";
                }
                $content .= "\n";
            }
            
            if (!empty($this->issues)) {
                $content .= "ISSUES:\n";
                foreach ($this->issues as $issue) {
                    $content .= "✗ {$issue}\n";
                }
                $content .= "\n";
            }
            
            File::put($filename, $content);
        }

        $this->info("📄 Report exported to: {$filename}");
    }
}
