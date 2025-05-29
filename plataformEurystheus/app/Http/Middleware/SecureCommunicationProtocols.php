<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SecureCommunicationProtocols
{
    /**
     * Minimum TLS version required
     */
    private const MIN_TLS_VERSION = '1.2';
    
    /**
     * Secure cipher suites
     */
    private array $secureCipherSuites = [
        'TLS_AES_256_GCM_SHA384',
        'TLS_CHACHA20_POLY1305_SHA256',
        'TLS_AES_128_GCM_SHA256',
        'ECDHE-RSA-AES256-GCM-SHA384',
        'ECDHE-RSA-AES128-GCM-SHA256',
        'ECDHE-RSA-AES256-SHA384',
        'ECDHE-RSA-AES128-SHA256',
    ];

    /**
     * Insecure protocols to block
     */
    private array $insecureProtocols = [
        'SSLv2',
        'SSLv3',
        'TLSv1.0',
        'TLSv1.1',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Force HTTPS in production
        $this->enforceHttps($request);

        // Validate TLS configuration
        $this->validateTlsConfiguration($request);

        // Check for weak encryption
        $this->checkEncryptionStrength($request);

        // Validate certificate information
        $this->validateCertificate($request);

        // Add security headers for secure communication
        $response = $next($request);
        
        return $this->addSecureCommunicationHeaders($response);
    }

    /**
     * Enforce HTTPS in production and staging environments
     */
    private function enforceHttps(Request $request): void
    {
        if (app()->environment(['production', 'staging'])) {
            if (!$request->secure() && !$this->isWhitelistedRoute($request)) {
                Log::warning('Insecure HTTP request blocked', [
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->header('referer'),
                ]);

                // Force redirect to HTTPS
                abort(301, 'Moved Permanently', [
                    'Location' => $request->fullUrlWithQuery([]),
                ]);
            }
        }
    }

    /**
     * Validate TLS configuration
     */
    private function validateTlsConfiguration(Request $request): void
    {
        // Check TLS version from headers (if available)
        $tlsVersion = $request->header('X-Forwarded-Proto-Version') ?? 
                     $request->header('X-SSL-Protocol') ??
                     $request->server('SSL_PROTOCOL');

        if ($tlsVersion) {
            if ($this->isInsecureProtocol($tlsVersion)) {
                Log::error('Insecure TLS protocol detected', [
                    'protocol' => $tlsVersion,
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Block insecure protocols
                abort(426, 'Upgrade Required - Insecure protocol detected');
            }

            if (!$this->isMinimumTlsVersion($tlsVersion)) {
                Log::warning('TLS version below minimum requirement', [
                    'protocol' => $tlsVersion,
                    'minimum_required' => self::MIN_TLS_VERSION,
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                ]);
            }
        }

        // Check cipher suite (if available)
        $cipherSuite = $request->header('X-SSL-Cipher') ?? 
                      $request->server('SSL_CIPHER');

        if ($cipherSuite && !$this->isSecureCipherSuite($cipherSuite)) {
            Log::warning('Weak cipher suite detected', [
                'cipher' => $cipherSuite,
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);
        }
    }

    /**
     * Check encryption strength
     */
    private function checkEncryptionStrength(Request $request): void
    {
        // Check for weak encryption indicators
        $weakEncryptionHeaders = [
            'X-SSL-Key-Size' => 1024, // Minimum 2048 bits
            'X-SSL-Cipher-Usekeysize' => 128, // Minimum 256 bits for symmetric
        ];

        foreach ($weakEncryptionHeaders as $header => $minValue) {
            $value = $request->header($header);
            if ($value && is_numeric($value) && (int)$value < $minValue) {
                Log::warning('Weak encryption detected', [
                    'header' => $header,
                    'value' => $value,
                    'minimum_required' => $minValue,
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                ]);
            }
        }

        // Check for deprecated algorithms
        $algorithm = $request->header('X-SSL-Cipher-Algorithm') ?? 
                    $request->server('SSL_CIPHER_ALGKEYSIZE');

        if ($algorithm) {
            $weakAlgorithms = ['RC4', 'DES', '3DES', 'MD5'];
            foreach ($weakAlgorithms as $weakAlg) {
                if (stripos($algorithm, $weakAlg) !== false) {
                    Log::error('Deprecated encryption algorithm detected', [
                        'algorithm' => $algorithm,
                        'weak_component' => $weakAlg,
                        'ip' => $request->ip(),
                        'url' => $request->url(),
                    ]);
                }
            }
        }
    }

    /**
     * Validate SSL certificate information
     */
    private function validateCertificate(Request $request): void
    {
        // Check certificate expiration (if available in headers)
        $certExpiry = $request->header('X-SSL-Cert-Expires') ?? 
                     $request->server('SSL_CLIENT_V_END');

        if ($certExpiry) {
            $expiryTime = strtotime($certExpiry);
            $daysUntilExpiry = ($expiryTime - time()) / 86400;

            if ($daysUntilExpiry < 30) {
                Log::warning('SSL certificate expiring soon', [
                    'expires_at' => $certExpiry,
                    'days_remaining' => round($daysUntilExpiry),
                    'url' => $request->url(),
                ]);
            }

            if ($expiryTime < time()) {
                Log::error('Expired SSL certificate detected', [
                    'expired_at' => $certExpiry,
                    'url' => $request->url(),
                    'ip' => $request->ip(),
                ]);
            }
        }

        // Check certificate issuer
        $certIssuer = $request->header('X-SSL-Cert-Issuer') ?? 
                     $request->server('SSL_CLIENT_I_DN');

        if ($certIssuer) {
            // Log certificate issuer for monitoring
            $cacheKey = 'cert_issuer_' . md5($certIssuer);
            if (!Cache::has($cacheKey)) {
                Log::info('SSL certificate issuer', [
                    'issuer' => $certIssuer,
                    'url' => $request->url(),
                ]);
                Cache::put($cacheKey, true, 3600); // Log once per hour
            }
        }
    }

    /**
     * Add secure communication headers to response
     */
    private function addSecureCommunicationHeaders($response)
    {
        $headers = [
            // Force HTTPS for future requests
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            
            // Prevent mixed content
            'Content-Security-Policy' => "upgrade-insecure-requests; block-all-mixed-content",
            
            // Referrer policy for secure referrers
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Expect certificate transparency
            'Expect-CT' => 'max-age=86400, enforce',
            
            // Public key pinning (implement based on your certificates)
            // 'Public-Key-Pins' => 'pin-sha256="base64+primary="; pin-sha256="base64+backup="; max-age=5184000; includeSubDomains',
        ];

        foreach ($headers as $name => $value) {
            $response->header($name, $value);
        }

        return $response;
    }

    /**
     * Check if protocol is insecure
     */
    private function isInsecureProtocol(string $protocol): bool
    {
        return in_array($protocol, $this->insecureProtocols);
    }

    /**
     * Check if TLS version meets minimum requirement
     */
    private function isMinimumTlsVersion(string $protocol): bool
    {
        // Extract version number from protocol string
        if (preg_match('/TLSv?(\d+\.\d+)/', $protocol, $matches)) {
            return version_compare($matches[1], self::MIN_TLS_VERSION, '>=');
        }

        return false;
    }

    /**
     * Check if cipher suite is secure
     */
    private function isSecureCipherSuite(string $cipher): bool
    {
        // Check against known secure cipher suites
        foreach ($this->secureCipherSuites as $secureCipher) {
            if (stripos($cipher, $secureCipher) !== false) {
                return true;
            }
        }

        // Check for insecure indicators
        $insecureIndicators = ['RC4', 'DES', 'MD5', 'NULL', 'EXPORT', 'anon'];
        foreach ($insecureIndicators as $indicator) {
            if (stripos($cipher, $indicator) !== false) {
                return false;
            }
        }

        // If not in secure list but no insecure indicators, consider moderately secure
        return true;
    }

    /**
     * Check if route is whitelisted for HTTP (e.g., health checks)
     */
    private function isWhitelistedRoute(Request $request): bool
    {
        $whitelistedRoutes = [
            '/up', // Health check endpoint
            '/health',
            '/.well-known/*',
        ];

        $path = $request->path();
        
        foreach ($whitelistedRoutes as $route) {
            if ($route === $path || (str_ends_with($route, '*') && str_starts_with($path, rtrim($route, '*')))) {
                return true;
            }
        }

        return false;
    }
}
