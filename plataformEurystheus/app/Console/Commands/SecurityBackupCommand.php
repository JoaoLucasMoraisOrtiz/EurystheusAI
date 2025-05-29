<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityBackupService;
use Illuminate\Support\Facades\Log;

class SecurityBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:backup 
                           {--type=auto : Type of backup (auto, manual, emergency)}
                           {--cleanup : Clean up old backups based on retention policy}
                           {--verify : Verify existing backup integrity}
                           {--backup-name= : Name of specific backup to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and manage security backups for the Eurystheus AI platform';

    protected $backupService;

    /**
     * Create a new command instance.
     */
    public function __construct(SecurityBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        
        try {
            if ($this->option('cleanup')) {
                return $this->handleCleanup();
            }
            
            if ($this->option('verify')) {
                return $this->handleVerification();
            }
            
            return $this->createBackup();
            
        } catch (\Exception $e) {
            $this->error('Security backup command failed: ' . $e->getMessage());
            Log::error('SecurityBackupCommand failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'options' => $this->options(),
            ]);
            return 1;
        } finally {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->info("Command execution time: {$executionTime}ms");
        }
    }

    /**
     * Create a security backup
     */
    protected function createBackup()
    {
        $type = $this->option('type');
        
        $this->info("🛡️  Creating security backup...");
        $this->info("Backup Type: " . ucfirst($type));
        
        $progressBar = $this->output->createProgressBar(6);
        $progressBar->setFormat('verbose');
        $progressBar->start();
        
        $progressBar->setMessage('Initializing backup process...');
        $progressBar->advance();
        
        $result = $this->backupService->createSecurityBackup($type);
        
        if ($result['success']) {
            $progressBar->setMessage('Backup created successfully!');
            $progressBar->finish();
            $this->newLine(2);
            
            $this->info('✅ Security backup created successfully!');
            $this->table(['Property', 'Value'], [
                ['Backup Name', $result['backup_name']],
                ['Archive Path', $result['archive_path']],
                ['File Size', $this->formatBytes($result['size'])],
                ['Created At', $result['created_at']->format('Y-m-d H:i:s')],
                ['Type', ucfirst($type)],
            ]);
            
            // Show backup statistics
            $this->showBackupStatistics();
            
            return 0;
        } else {
            $progressBar->setMessage('Backup failed!');
            $progressBar->finish();
            $this->newLine(2);
            
            $this->error('❌ Security backup failed!');
            $this->error('Error: ' . $result['error']);
            return 1;
        }
    }

    /**
     * Handle backup cleanup
     */
    protected function handleCleanup()
    {
        $this->info("🧹 Cleaning up old security backups...");
        
        $retentionDays = config('security.compliance.reporting.retention_days', 30);
        $this->info("Retention policy: {$retentionDays} days");
        
        if ($this->confirm('Are you sure you want to delete old backups?')) {
            $deleted = $this->backupService->cleanupOldBackups($retentionDays);
            
            if ($deleted > 0) {
                $this->info("✅ Deleted {$deleted} old backup(s)");
            } else {
                $this->info("ℹ️  No old backups found to delete");
            }
            
            $this->showBackupStatistics();
            return 0;
        } else {
            $this->info("Cleanup cancelled");
            return 0;
        }
    }

    /**
     * Handle backup verification
     */
    protected function handleVerification()
    {
        $backupName = $this->option('backup-name');
        
        if (!$backupName) {
            $backups = $this->backupService->listBackups();
            
            if (empty($backups)) {
                $this->warn('No backups found to verify');
                return 0;
            }
            
            $this->info("Available backups:");
            foreach ($backups as $index => $backup) {
                $this->line(($index + 1) . ". " . $backup['name'] . " ({$this->formatBytes($backup['size'])}) - " . $backup['created_at']->format('Y-m-d H:i:s'));
            }
            
            $choice = $this->ask('Enter the number of the backup to verify (or backup name)');
            
            if (is_numeric($choice) && isset($backups[$choice - 1])) {
                $backupName = $backups[$choice - 1]['name'];
            } else {
                $backupName = $choice;
            }
        }
        
        $this->info("🔍 Verifying backup: {$backupName}");
        
        $result = $this->backupService->verifyBackupIntegrity($backupName);
        
        if ($result['valid']) {
            $this->info('✅ ' . $result['message']);
            return 0;
        } else {
            $this->error('❌ Backup verification failed: ' . $result['error']);
            return 1;
        }
    }

    /**
     * Show backup statistics
     */
    protected function showBackupStatistics()
    {
        $backups = $this->backupService->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));
        $totalCount = count($backups);
        
        $this->newLine();
        $this->info("📊 Backup Statistics:");
        $this->table(['Metric', 'Value'], [
            ['Total Backups', $totalCount],
            ['Total Size', $this->formatBytes($totalSize)],
            ['Newest Backup', $totalCount > 0 ? $backups[0]['created_at']->format('Y-m-d H:i:s') : 'None'],
            ['Oldest Backup', $totalCount > 0 ? end($backups)['created_at']->format('Y-m-d H:i:s') : 'None'],
        ]);
        
        if ($totalCount > 0) {
            $this->newLine();
            $this->info("Recent backups:");
            $recentBackups = array_slice($backups, 0, 5);
            
            $rows = [];
            foreach ($recentBackups as $backup) {
                $rows[] = [
                    $backup['name'],
                    $this->formatBytes($backup['size']),
                    $backup['created_at']->format('Y-m-d H:i:s'),
                    $backup['created_at']->diffForHumans(),
                ];
            }
            
            $this->table(['Backup Name', 'Size', 'Created', 'Age'], $rows);
        }
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
