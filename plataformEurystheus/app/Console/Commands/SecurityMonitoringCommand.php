<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityAlertService;
use Illuminate\Support\Facades\Log;

class SecurityMonitoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:monitor {--force : Force monitoring even if disabled}';

    /**
     * The console command description.
     */
    protected $description = 'Run security monitoring checks and trigger alerts for anomalies';

    /**
     * The security alert service instance
     */
    protected SecurityAlertService $securityAlertService;

    /**
     * Create a new command instance
     */
    public function __construct(SecurityAlertService $securityAlertService)
    {
        parent::__construct();
        $this->securityAlertService = $securityAlertService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('🛡️  Starting security monitoring check...');
            
            // Check if security monitoring is enabled (can be configured in settings)
            $monitoringEnabled = config('security.monitoring.enabled', true);
            
            if (!$monitoringEnabled && !$this->option('force')) {
                $this->warn('Security monitoring is disabled. Use --force to run anyway.');
                return self::SUCCESS;
            }

            $startTime = microtime(true);
            
            // Run security anomaly checks
            $this->securityAlertService->checkSecurityAnomalies();
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            $this->info("✅ Security monitoring check completed in {$executionTime}ms");
            
            // Log the monitoring activity
            Log::info('Security monitoring check completed', [
                'execution_time_ms' => $executionTime,
                'forced' => $this->option('force')
            ]);
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Security monitoring check failed: ' . $e->getMessage());
            Log::error('Security monitoring check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
