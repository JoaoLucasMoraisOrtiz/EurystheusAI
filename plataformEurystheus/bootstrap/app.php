<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Security middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'check.prompt.limit' => \App\Http\Middleware\CheckFreeUserPromptLimit::class,
            'sql.injection.protection' => \App\Http\Middleware\SqlInjectionProtection::class,
            'enhanced.auth' => \App\Http\Middleware\EnhancedAuthentication::class,
            'xss.protection' => \App\Http\Middleware\XssProtection::class,
            'dos.protection' => \App\Http\Middleware\DosProtection::class,
            'api.security' => \App\Http\Middleware\ApiSecurityMiddleware::class,
            'secure.file.upload' => \App\Http\Middleware\SecureFileUpload::class,
            'security.monitoring' => \App\Http\Middleware\SecurityMonitoring::class,
            'enhanced.csrf' => \App\Http\Middleware\EnhancedCsrfProtection::class,
            'security.config' => \App\Http\Middleware\SecurityConfigurationValidation::class,
            'dependency.scanner' => \App\Http\Middleware\DependencyVulnerabilityScanner::class,
            'secure.communication' => \App\Http\Middleware\SecureCommunicationProtocols::class,
            'secure.session' => \App\Http\Middleware\SecureSessionManagement::class,
        ]);
        
        // Global security middleware for all requests
        $middleware->use([
            \App\Http\Middleware\SecurityMonitoring::class,
            \App\Http\Middleware\DosProtection::class,
            \App\Http\Middleware\SqlInjectionProtection::class,
            \App\Http\Middleware\XssProtection::class,
            \App\Http\Middleware\SecureCommunicationProtocols::class,
            \App\Http\Middleware\SecurityConfigurationValidation::class,
        ]);
        
        // Web-specific middleware
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnhancedCsrfProtection::class,
            \App\Http\Middleware\SecureSessionManagement::class,
        ]);
        
        // API-specific middleware
        $middleware->api(append: [
            \App\Http\Middleware\ApiSecurityMiddleware::class,
        ]);
        
        // Run dependency scan periodically (only in admin routes or scheduled)
        $middleware->group('admin', [
            \App\Http\Middleware\DependencyVulnerabilityScanner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
