<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class SecureFileUpload
{
    /**
     * Security configurations for file uploads
     */
    private array $securityConfig = [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', // Images
            'pdf', 'doc', 'docx', 'txt', 'rtf', // Documents
            'csv', 'xlsx', 'xls', // Spreadsheets
        ],
        'allowed_mime_types' => [
            'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain', 'text/rtf',
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'quarantine_path' => '/tmp/quarantine/',
        'scan_timeout' => 30, // seconds
    ];

    /**
     * Dangerous file signatures (magic bytes)
     */
    private array $dangerousSignatures = [
        // Executable files
        'MZ' => 'Windows executable',
        '7fELF' => 'Linux executable',
        'cafebabe' => 'Java bytecode',
        
        // Script files
        '<?php' => 'PHP script',
        '#!/bin/sh' => 'Shell script',
        '#!/bin/bash' => 'Bash script',
        '<script' => 'JavaScript',
        
        // Archive files (can contain malware)
        'PK' => 'ZIP archive',
        'Rar!' => 'RAR archive',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Check if request contains file uploads
        if ($request->hasFile('*')) {
            $this->validateFileUploads($request);
        }
        
        return $next($request);
    }

    private function validateFileUploads(Request $request): void
    {
        foreach ($request->allFiles() as $files) {
            $files = is_array($files) ? $files : [$files];
            
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $this->validateSingleFile($file, $request);
                }
            }
        }
    }

    private function validateSingleFile(UploadedFile $file, Request $request): void
    {
        // Check if file upload was successful
        if (!$file->isValid()) {
            $this->logSecurityEvent($request, 'INVALID_FILE_UPLOAD', [
                'filename' => $file->getClientOriginalName(),
                'error' => $file->getErrorMessage(),
            ]);
            abort(400, 'Invalid file upload');
        }
        
        // Validate file size
        if ($file->getSize() > $this->securityConfig['max_file_size']) {
            $this->logSecurityEvent($request, 'FILE_TOO_LARGE', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);
            abort(413, 'File too large');
        }
        
        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->securityConfig['allowed_extensions'])) {
            $this->logSecurityEvent($request, 'INVALID_FILE_EXTENSION', [
                'filename' => $file->getClientOriginalName(),
                'extension' => $extension,
            ]);
            abort(415, 'File type not allowed');
        }
        
        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->securityConfig['allowed_mime_types'])) {
            $this->logSecurityEvent($request, 'INVALID_MIME_TYPE', [
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
            ]);
            abort(415, 'MIME type not allowed');
        }
        
        // Check for file signature spoofing
        if (!$this->validateFileSignature($file)) {
            $this->logSecurityEvent($request, 'FILE_SIGNATURE_MISMATCH', [
                'filename' => $file->getClientOriginalName(),
                'declared_type' => $mimeType,
            ]);
            abort(415, 'File signature does not match declared type');
        }
        
        // Scan for malicious content
        if ($this->containsMaliciousContent($file, $request)) {
            $this->quarantineFile($file, $request);
            abort(415, 'Malicious content detected');
        }
        
        // Validate filename for path traversal
        if ($this->hasPathTraversal($file->getClientOriginalName())) {
            $this->logSecurityEvent($request, 'PATH_TRAVERSAL_ATTEMPT', [
                'filename' => $file->getClientOriginalName(),
            ]);
            abort(400, 'Invalid filename');
        }
        
        // Check for suspicious filenames
        if ($this->hasSuspiciousFilename($file->getClientOriginalName())) {
            $this->logSecurityEvent($request, 'SUSPICIOUS_FILENAME', [
                'filename' => $file->getClientOriginalName(),
            ]);
            abort(400, 'Suspicious filename detected');
        }
    }

    private function validateFileSignature(UploadedFile $file): bool
    {
        $path = $file->getRealPath();
        if (!$path || !file_exists($path)) {
            return false;
        }
        
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return false;
        }
        
        $bytes = fread($handle, 10);
        fclose($handle);
        
        $mimeType = $file->getMimeType();
        
        // Validate common file signatures
        if (strpos($mimeType, 'image/jpeg') === 0) {
            return substr($bytes, 0, 3) === "\xFF\xD8\xFF";
        }
        
        if (strpos($mimeType, 'image/png') === 0) {
            return substr($bytes, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        }
        
        if (strpos($mimeType, 'image/gif') === 0) {
            return substr($bytes, 0, 6) === 'GIF87a' || substr($bytes, 0, 6) === 'GIF89a';
        }
        
        if (strpos($mimeType, 'application/pdf') === 0) {
            return substr($bytes, 0, 4) === '%PDF';
        }
        
        // For other file types, assume valid if MIME type is allowed
        return true;
    }

    private function containsMaliciousContent(UploadedFile $file, Request $request): bool
    {
        $path = $file->getRealPath();
        if (!$path || !file_exists($path)) {
            return false;
        }
        
        // Read file content for analysis
        $content = file_get_contents($path, false, null, 0, 1024 * 1024); // Read first 1MB
        
        // Check for dangerous signatures
        foreach ($this->dangerousSignatures as $signature => $description) {
            if (strpos($content, $signature) !== false) {
                $this->logSecurityEvent($request, 'MALICIOUS_SIGNATURE_DETECTED', [
                    'filename' => $file->getClientOriginalName(),
                    'signature' => $description,
                ]);
                return true;
            }
        }
        
        // Check for embedded scripts in images
        if ($this->isImageFile($file)) {
            if ($this->hasEmbeddedScript($content)) {
                $this->logSecurityEvent($request, 'EMBEDDED_SCRIPT_IN_IMAGE', [
                    'filename' => $file->getClientOriginalName(),
                ]);
                return true;
            }
        }
        
        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
            '/<script[^>]*>/i',
            '/<iframe[^>]*>/i',
            '/javascript:/i',
            '/vbscript:/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->logSecurityEvent($request, 'SUSPICIOUS_PATTERN_DETECTED', [
                    'filename' => $file->getClientOriginalName(),
                    'pattern' => $pattern,
                ]);
                return true;
            }
        }
        
        return false;
    }

    private function isImageFile(UploadedFile $file): bool
    {
        return strpos($file->getMimeType(), 'image/') === 0;
    }

    private function hasEmbeddedScript(string $content): bool
    {
        // Look for script tags or JavaScript in image files
        $scriptPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
        ];
        
        foreach ($scriptPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }

    private function hasPathTraversal(string $filename): bool
    {
        // Check for directory traversal attempts
        $dangerous = ['../', '..\\', '..\/', '../\\'];
        
        foreach ($dangerous as $pattern) {
            if (strpos($filename, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function hasSuspiciousFilename(string $filename): bool
    {
        // Check for suspicious filename patterns
        $suspiciousPatterns = [
            '/\.(php|phtml|php3|php4|php5|pht|phar)$/i',
            '/\.(asp|aspx|jsp|jspx)$/i',
            '/\.(exe|bat|cmd|com|scr|pif)$/i',
            '/\.(sh|bash|csh|ksh|zsh)$/i',
            '/\.(pl|py|rb|jar)$/i',
            '/\.htaccess$/i',
            '/web\.config$/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }
        
        // Check for null bytes
        if (strpos($filename, "\0") !== false) {
            return true;
        }
        
        // Check for excessively long filenames
        if (strlen($filename) > 255) {
            return true;
        }
        
        return false;
    }

    private function quarantineFile(UploadedFile $file, Request $request): void
    {
        $quarantinePath = $this->securityConfig['quarantine_path'];
        
        if (!is_dir($quarantinePath)) {
            mkdir($quarantinePath, 0700, true);
        }
        
        $quarantineFilename = sprintf(
            '%s_%s_%s',
            date('Y-m-d_H-i-s'),
            uniqid(),
            basename($file->getClientOriginalName())
        );
        
        $quarantineFullPath = $quarantinePath . $quarantineFilename;
        
        try {
            $file->move($quarantinePath, $quarantineFilename);
            
            $this->logSecurityEvent($request, 'FILE_QUARANTINED', [
                'original_filename' => $file->getClientOriginalName(),
                'quarantine_path' => $quarantineFullPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to quarantine malicious file', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
        }
    }

    private function logSecurityEvent(Request $request, string $event, array $extra = []): void
    {
        \Log::warning("File upload security event: {$event}", array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ], $extra));
    }
}
