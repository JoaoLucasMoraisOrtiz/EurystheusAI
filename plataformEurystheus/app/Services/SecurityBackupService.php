<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use ZipArchive;

class SecurityBackupService
{
    protected $backupDisk;
    protected $encryptionKey;
    
    public function __construct()
    {
        $this->backupDisk = config('filesystems.default');
        $this->encryptionKey = config('app.key');
    }
    
    /**
     * Create a comprehensive security backup
     */
    public function createSecurityBackup($type = 'manual')
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $backupName = "security_backup_{$type}_{$timestamp}";
            $backupPath = "backups/security/{$backupName}";
            
            // Create backup directory
            Storage::makeDirectory($backupPath);
            
            $manifest = [
                'type' => $type,
                'created_at' => Carbon::now()->toISOString(),
                'version' => app()->version(),
                'checksums' => [],
                'files' => [],
            ];
            
            // Backup security configurations
            $this->backupSecurityConfigurations($backupPath, $manifest);
            
            // Backup security database tables
            $this->backupSecurityTables($backupPath, $manifest);
            
            // Backup security logs
            $this->backupSecurityLogs($backupPath, $manifest);
            
            // Backup middleware configurations
            $this->backupMiddlewareConfigs($backupPath, $manifest);
            
            // Backup SSL certificates
            $this->backupSSLCertificates($backupPath, $manifest);
            
            // Create encrypted archive
            $archivePath = $this->createEncryptedArchive($backupPath, $backupName, $manifest);
            
            // Clean up temporary files
            Storage::deleteDirectory($backupPath);
            
            Log::info('Security backup created successfully', [
                'backup_name' => $backupName,
                'archive_path' => $archivePath,
                'type' => $type,
                'size' => Storage::size($archivePath),
            ]);
            
            return [
                'success' => true,
                'backup_name' => $backupName,
                'archive_path' => $archivePath,
                'size' => Storage::size($archivePath),
                'created_at' => Carbon::now(),
            ];
            
        } catch (\Exception $e) {
            Log::error('Security backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Backup security configuration files
     */
    protected function backupSecurityConfigurations($backupPath, &$manifest)
    {
        $configFiles = [
            'config/security.php',
            'config/auth.php',
            'config/session.php',
            'config/mail.php',
            'config/logging.php',
            'bootstrap/app.php',
            'routes/web.php',
            'routes/console.php',
            '.env.security.example',
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists(base_path($file))) {
                $content = file_get_contents(base_path($file));
                $encrypted = $this->encryptData($content);
                $fileName = str_replace('/', '_', $file) . '.enc';
                
                Storage::put("{$backupPath}/configs/{$fileName}", $encrypted);
                
                $manifest['files'][] = $file;
                $manifest['checksums'][$file] = hash('sha256', $content);
            }
        }
    }
    
    /**
     * Backup security-related database tables
     */
    protected function backupSecurityTables($backupPath, &$manifest)
    {
        $securityTables = [
            'security_alerts',
            'security_events',
            'blocked_ips',
            'failed_login_attempts',
            'password_resets',
            'users',
            'sessions',
        ];
        
        foreach ($securityTables as $table) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $data = DB::table($table)->get()->toArray();
                    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                    $encrypted = $this->encryptData($jsonData);
                    
                    Storage::put("{$backupPath}/database/{$table}.json.enc", $encrypted);
                    
                    $manifest['files'][] = "database/{$table}";
                    $manifest['checksums'][$table] = hash('sha256', $jsonData);
                }
            } catch (\Exception $e) {
                Log::warning("Could not backup table {$table}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Backup security logs
     */
    protected function backupSecurityLogs($backupPath, &$manifest)
    {
        $logFiles = [
            'security.log',
            'laravel.log',
            'security-monitoring.log',
        ];
        
        foreach ($logFiles as $logFile) {
            $logPath = storage_path("logs/{$logFile}");
            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $encrypted = $this->encryptData($content);
                
                Storage::put("{$backupPath}/logs/{$logFile}.enc", $encrypted);
                
                $manifest['files'][] = "logs/{$logFile}";
                $manifest['checksums'][$logFile] = hash('sha256', $content);
            }
        }
    }
    
    /**
     * Backup middleware configuration files
     */
    protected function backupMiddlewareConfigs($backupPath, &$manifest)
    {
        $middlewarePath = app_path('Http/Middleware');
        $files = glob("{$middlewarePath}/*Security*.php");
        $files = array_merge($files, glob("{$middlewarePath}/*Protection*.php"));
        $files = array_merge($files, glob("{$middlewarePath}/*Monitoring*.php"));
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $encrypted = $this->encryptData($content);
            $fileName = basename($file) . '.enc';
            
            Storage::put("{$backupPath}/middleware/{$fileName}", $encrypted);
            
            $manifest['files'][] = "middleware/" . basename($file);
            $manifest['checksums'][basename($file)] = hash('sha256', $content);
        }
    }
    
    /**
     * Backup SSL certificates and keys
     */
    protected function backupSSLCertificates($backupPath, &$manifest)
    {
        $certPaths = [
            '/etc/ssl/certs/',
            '/etc/letsencrypt/live/',
            storage_path('ssl/'),
        ];
        
        foreach ($certPaths as $certPath) {
            if (is_dir($certPath)) {
                $extensions = ['crt', 'pem', 'key', 'csr'];
                $files = [];
                
                foreach ($extensions as $ext) {
                    $matches = glob("{$certPath}*.{$ext}");
                    if ($matches) {
                        $files = array_merge($files, $matches);
                    }
                }
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $content = file_get_contents($file);
                        $encrypted = $this->encryptData($content);
                        $fileName = basename($file) . '.enc';
                        
                        Storage::put("{$backupPath}/ssl/{$fileName}", $encrypted);
                        
                        $manifest['files'][] = "ssl/" . basename($file);
                        $manifest['checksums'][basename($file)] = hash('sha256', $content);
                    }
                }
            }
        }
    }
    
    /**
     * Create encrypted archive of backup
     */
    protected function createEncryptedArchive($backupPath, $backupName, $manifest)
    {
        // Save manifest
        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT);
        $encryptedManifest = $this->encryptData($manifestJson);
        Storage::put("{$backupPath}/manifest.json.enc", $encryptedManifest);
        
        // Create ZIP archive
        $zipPath = "backups/security/{$backupName}.zip";
        $zip = new ZipArchive();
        
        if ($zip->open(Storage::path($zipPath), ZipArchive::CREATE) === TRUE) {
            $files = Storage::allFiles($backupPath);
            
            foreach ($files as $file) {
                $zip->addFile(Storage::path($file), str_replace($backupPath . '/', '', $file));
            }
            
            $zip->close();
        }
        
        return $zipPath;
    }
    
    /**
     * Restore security backup
     */
    public function restoreSecurityBackup($backupName, $options = [])
    {
        try {
            $zipPath = "backups/security/{$backupName}.zip";
            
            if (!Storage::exists($zipPath)) {
                throw new \Exception("Backup file not found: {$backupName}");
            }
            
            $extractPath = "backups/restore/{$backupName}_" . time();
            Storage::makeDirectory($extractPath);
            
            // Extract ZIP
            $zip = new ZipArchive();
            if ($zip->open(Storage::path($zipPath)) === TRUE) {
                $zip->extractTo(Storage::path($extractPath));
                $zip->close();
            } else {
                throw new \Exception("Could not extract backup archive");
            }
            
            // Read and verify manifest
            $manifestPath = "{$extractPath}/manifest.json.enc";
            if (!Storage::exists($manifestPath)) {
                throw new \Exception("Backup manifest not found");
            }
            
            $encryptedManifest = Storage::get($manifestPath);
            $manifestJson = $this->decryptData($encryptedManifest);
            $manifest = json_decode($manifestJson, true);
            
            $results = [
                'restored_configs' => 0,
                'restored_tables' => 0,
                'restored_logs' => 0,
                'restored_middleware' => 0,
                'restored_ssl' => 0,
                'errors' => [],
            ];
            
            // Restore configurations
            if ($options['restore_configs'] ?? true) {
                $this->restoreConfigurations($extractPath, $manifest, $results);
            }
            
            // Restore database tables
            if ($options['restore_database'] ?? true) {
                $this->restoreSecurityDatabase($extractPath, $manifest, $results);
            }
            
            // Restore logs
            if ($options['restore_logs'] ?? false) {
                $this->restoreSecurityLogs($extractPath, $manifest, $results);
            }
            
            // Restore middleware
            if ($options['restore_middleware'] ?? true) {
                $this->restoreMiddleware($extractPath, $manifest, $results);
            }
            
            // Restore SSL certificates
            if ($options['restore_ssl'] ?? false) {
                $this->restoreSSLCertificates($extractPath, $manifest, $results);
            }
            
            // Clean up
            Storage::deleteDirectory($extractPath);
            
            Log::info('Security backup restored successfully', [
                'backup_name' => $backupName,
                'results' => $results,
            ]);
            
            return [
                'success' => true,
                'results' => $results,
                'manifest' => $manifest,
            ];
            
        } catch (\Exception $e) {
            Log::error('Security backup restoration failed', [
                'backup_name' => $backupName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Restore configuration files
     */
    protected function restoreConfigurations($extractPath, $manifest, &$results)
    {
        $configFiles = Storage::files("{$extractPath}/configs");
        
        foreach ($configFiles as $file) {
            try {
                $encrypted = Storage::get($file);
                $content = $this->decryptData($encrypted);
                $originalFile = str_replace(['_', '.enc'], ['/', ''], basename($file));
                
                // Backup current file before restoring
                if (file_exists(base_path($originalFile))) {
                    copy(base_path($originalFile), base_path($originalFile . '.backup'));
                }
                
                file_put_contents(base_path($originalFile), $content);
                $results['restored_configs']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Config restore failed for {$file}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Restore security database tables
     */
    protected function restoreSecurityDatabase($extractPath, $manifest, &$results)
    {
        $tableFiles = Storage::files("{$extractPath}/database");
        
        foreach ($tableFiles as $file) {
            try {
                $encrypted = Storage::get($file);
                $jsonData = $this->decryptData($encrypted);
                $data = json_decode($jsonData, true);
                $tableName = str_replace(['.json.enc'], '', basename($file));
                
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    // Backup current data
                    DB::table($tableName . '_backup_' . time())->insertUsing(
                        ['*'],
                        DB::table($tableName)->select('*')
                    );
                    
                    // Clear and restore
                    DB::table($tableName)->truncate();
                    
                    if (!empty($data)) {
                        DB::table($tableName)->insert($data);
                    }
                    
                    $results['restored_tables']++;
                }
                
            } catch (\Exception $e) {
                $results['errors'][] = "Database restore failed for {$file}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Restore security logs
     */
    protected function restoreSecurityLogs($extractPath, $manifest, &$results)
    {
        $logFiles = Storage::files("{$extractPath}/logs");
        
        foreach ($logFiles as $file) {
            try {
                $encrypted = Storage::get($file);
                $content = $this->decryptData($encrypted);
                $logName = str_replace('.enc', '', basename($file));
                $logPath = storage_path("logs/{$logName}");
                
                // Backup current log
                if (file_exists($logPath)) {
                    copy($logPath, $logPath . '.backup');
                }
                
                file_put_contents($logPath, $content);
                $results['restored_logs']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Log restore failed for {$file}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Restore middleware files
     */
    protected function restoreMiddleware($extractPath, $manifest, &$results)
    {
        $middlewareFiles = Storage::files("{$extractPath}/middleware");
        
        foreach ($middlewareFiles as $file) {
            try {
                $encrypted = Storage::get($file);
                $content = $this->decryptData($encrypted);
                $middlewareName = str_replace('.enc', '', basename($file));
                $middlewarePath = app_path("Http/Middleware/{$middlewareName}");
                
                // Backup current middleware
                if (file_exists($middlewarePath)) {
                    copy($middlewarePath, $middlewarePath . '.backup');
                }
                
                file_put_contents($middlewarePath, $content);
                $results['restored_middleware']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "Middleware restore failed for {$file}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Restore SSL certificates
     */
    protected function restoreSSLCertificates($extractPath, $manifest, &$results)
    {
        $sslFiles = Storage::files("{$extractPath}/ssl");
        
        foreach ($sslFiles as $file) {
            try {
                $encrypted = Storage::get($file);
                $content = $this->decryptData($encrypted);
                $certName = str_replace('.enc', '', basename($file));
                $certPath = storage_path("ssl/{$certName}");
                
                // Ensure SSL directory exists
                if (!is_dir(storage_path('ssl'))) {
                    mkdir(storage_path('ssl'), 0755, true);
                }
                
                // Backup current certificate
                if (file_exists($certPath)) {
                    copy($certPath, $certPath . '.backup');
                }
                
                file_put_contents($certPath, $content);
                chmod($certPath, 0600); // Secure permissions for SSL files
                $results['restored_ssl']++;
                
            } catch (\Exception $e) {
                $results['errors'][] = "SSL restore failed for {$file}: " . $e->getMessage();
            }
        }
    }
    
    /**
     * List available security backups
     */
    public function listBackups()
    {
        $backups = [];
        $files = Storage::files('backups/security');
        
        foreach ($files as $file) {
            if (str_ends_with($file, '.zip')) {
                $backups[] = [
                    'name' => basename($file, '.zip'),
                    'size' => Storage::size($file),
                    'created_at' => Carbon::createFromTimestamp(Storage::lastModified($file)),
                    'path' => $file,
                ];
            }
        }
        
        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });
        
        return $backups;
    }
    
    /**
     * Delete old backups based on retention policy
     */
    public function cleanupOldBackups($retentionDays = 30)
    {
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        $backups = $this->listBackups();
        $deleted = 0;
        
        foreach ($backups as $backup) {
            if ($backup['created_at']->lt($cutoffDate)) {
                Storage::delete($backup['path']);
                $deleted++;
            }
        }
        
        Log::info("Cleaned up {$deleted} old security backups older than {$retentionDays} days");
        
        return $deleted;
    }
    
    /**
     * Encrypt sensitive data
     */
    protected function encryptData($data)
    {
        return Crypt::encrypt($data);
    }
    
    /**
     * Decrypt sensitive data
     */
    protected function decryptData($encryptedData)
    {
        return Crypt::decrypt($encryptedData);
    }
    
    /**
     * Verify backup integrity
     */
    public function verifyBackupIntegrity($backupName)
    {
        try {
            $zipPath = "backups/security/{$backupName}.zip";
            
            if (!Storage::exists($zipPath)) {
                return ['valid' => false, 'error' => 'Backup file not found'];
            }
            
            // Test ZIP integrity
            $zip = new ZipArchive();
            $result = $zip->open(Storage::path($zipPath), ZipArchive::CHECKCONS);
            
            if ($result !== TRUE) {
                return ['valid' => false, 'error' => 'Archive is corrupted'];
            }
            
            $zip->close();
            
            return ['valid' => true, 'message' => 'Backup integrity verified'];
            
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
}
