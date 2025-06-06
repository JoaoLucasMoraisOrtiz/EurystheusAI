<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration options for the automated
    | security monitoring and alert system for Eurystheus AI platform.
    |
    */

    'enabled' => env('SECURITY_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure the thresholds for various security events that will
    | trigger alerts. These values should be tuned based on your
    | normal traffic patterns and security requirements.
    |
    */

    'thresholds' => [
        // Number of attacks per minute before triggering high-priority alert
        'attacks_per_minute' => env('SECURITY_ATTACKS_PER_MINUTE', 10),
        
        // Number of failed login attempts per minute
        'failed_logins_per_minute' => env('SECURITY_FAILED_LOGINS_PER_MINUTE', 20),
        
        // Number of blocked IPs before triggering alert
        'blocked_ips_threshold' => env('SECURITY_BLOCKED_IPS_THRESHOLD', 50),
        
        // SQL injection attempts per minute
        'sql_injection_per_minute' => env('SECURITY_SQL_INJECTION_PER_MINUTE', 5),
        
        // XSS attempts per minute
        'xss_attempts_per_minute' => env('SECURITY_XSS_ATTEMPTS_PER_MINUTE', 5),
        
        // DoS attack indicators per minute
        'dos_attacks_per_minute' => env('SECURITY_DOS_ATTACKS_PER_MINUTE', 30),
        
        // Brute force attempts per minute
        'brute_force_per_minute' => env('SECURITY_BRUTE_FORCE_PER_MINUTE', 15),
        
        // Credential stuffing attempts per minute
        'credential_stuffing_per_minute' => env('SECURITY_CREDENTIAL_STUFFING_PER_MINUTE', 10),
        
        // Session hijacking attempts per minute
        'session_hijacking_per_minute' => env('SECURITY_SESSION_HIJACKING_PER_MINUTE', 3),
        
        // Phishing attempts per minute
        'phishing_attempts_per_minute' => env('SECURITY_PHISHING_ATTEMPTS_PER_MINUTE', 2),
        
        // API security violations per minute
        'api_violations_per_minute' => env('SECURITY_API_VIOLATIONS_PER_MINUTE', 8),
        
        // MITM attack indicators per minute
        'mitm_attacks_per_minute' => env('SECURITY_MITM_ATTACKS_PER_MINUTE', 2),
        
        // Insecure deserialization attempts per minute
        'deserialization_attacks_per_minute' => env('SECURITY_DESERIALIZATION_PER_MINUTE', 3),
        
        // Geographic anomaly threshold (unique countries per hour)
        'geographic_anomaly_threshold' => env('SECURITY_GEOGRAPHIC_ANOMALY', 10),
        
        // Coordinated attack threshold (same attack type from multiple IPs)
        'coordinated_attack_threshold' => env('SECURITY_COORDINATED_ATTACK', 5),
        
        // System health thresholds
        'database_response_time_ms' => env('SECURITY_DB_RESPONSE_TIME', 1000),
        'middleware_failure_rate' => env('SECURITY_MIDDLEWARE_FAILURE_RATE', 0.05), // 5%
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Levels
    |--------------------------------------------------------------------------
    |
    | Define the different alert levels and their characteristics.
    | Each level determines how alerts are processed and delivered.
    |
    */

    'alert_levels' => [
        'low' => [
            'name' => 'Low Priority',
            'color' => '#28a745',
            'icon' => 'info',
            'notify_channels' => ['database', 'log'],
            'escalation_time' => 3600, // 1 hour
        ],
        'medium' => [
            'name' => 'Medium Priority',
            'color' => '#ffc107',
            'icon' => 'warning',
            'notify_channels' => ['database', 'log', 'email'],
            'escalation_time' => 1800, // 30 minutes
        ],
        'high' => [
            'name' => 'High Priority',
            'color' => '#fd7e14',
            'icon' => 'alert-triangle',
            'notify_channels' => ['database', 'log', 'email', 'slack'],
            'escalation_time' => 600, // 10 minutes
        ],
        'critical' => [
            'name' => 'Critical',
            'color' => '#dc3545',
            'icon' => 'alert-circle',
            'notify_channels' => ['database', 'log', 'email', 'slack', 'sms'],
            'escalation_time' => 300, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Channels Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the various notification channels for security alerts.
    | Each channel can be enabled/disabled and configured separately.
    |
    */

    'notifications' => [
        'email' => [
            'enabled' => env('SECURITY_EMAIL_NOTIFICATIONS', true),
            'recipients' => env('SECURITY_EMAIL_RECIPIENTS', 'admin@eurystheusai.com'),
            'template' => 'emails.security-alert',
            'subject_prefix' => '[SECURITY ALERT]',
        ],
        
        'slack' => [
            'enabled' => env('SECURITY_SLACK_NOTIFICATIONS', false),
            'webhook_url' => env('SECURITY_SLACK_WEBHOOK'),
            'channel' => env('SECURITY_SLACK_CHANNEL', '#security-alerts'),
            'username' => 'Eurystheus Security Bot',
            'icon_emoji' => ':shield:',
        ],
        
        'sms' => [
            'enabled' => env('SECURITY_SMS_NOTIFICATIONS', false),
            'service' => env('SECURITY_SMS_SERVICE', 'twilio'), // twilio, nexmo, etc.
            'recipients' => env('SECURITY_SMS_RECIPIENTS'),
        ],
        
        'database' => [
            'enabled' => true,
            'table' => 'security_alerts',
            'retention_days' => env('SECURITY_ALERT_RETENTION_DAYS', 90),
        ],
        
        'log' => [
            'enabled' => true,
            'channel' => 'security',
            'level' => 'warning',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attack Pattern Definitions
    |--------------------------------------------------------------------------
    |
    | Define patterns and signatures for different types of security
    | attacks that the system should monitor and detect.
    |
    */

    'attack_patterns' => [
        'sql_injection' => [
            'signatures' => [
                'union\s+select',
                'drop\s+table',
                'delete\s+from',
                'insert\s+into',
                'update\s+set',
                '\/\*.*\*\/',
                'concat\s*\(',
                'char\s*\(',
                'ascii\s*\(',
                'substring\s*\(',
                'information_schema',
                'mysql\.user',
                'pg_catalog',
                'sys\.tables',
                'xp_cmdshell',
                'sp_executesql',
            ],
            'severity' => 'high',
            'description' => 'SQL Injection Attack Detected',
        ],
        
        'xss' => [
            'signatures' => [
                '<script.*?>',
                'javascript:',
                'onload\s*=',
                'onerror\s*=',
                'onclick\s*=',
                'onmouseover\s*=',
                'onfocus\s*=',
                'onblur\s*=',
                'eval\s*\(',
                'expression\s*\(',
                'vbscript:',
                'data:text\/html',
                'document\.cookie',
                'document\.write',
                'innerHTML',
            ],
            'severity' => 'high',
            'description' => 'Cross-Site Scripting (XSS) Attack Detected',
        ],
        
        'path_traversal' => [
            'signatures' => [
                '\.\./',
                '\.\.\\',
                '/etc/passwd',
                '/etc/shadow',
                'windows/system32',
                'boot\.ini',
                'web\.config',
                '\.htaccess',
                'wp-config\.php',
            ],
            'severity' => 'high',
            'description' => 'Path Traversal Attack Detected',
        ],
        
        'command_injection' => [
            'signatures' => [
                'system\s*\(',
                'exec\s*\(',
                'shell_exec\s*\(',
                'passthru\s*\(',
                'eval\s*\(',
                'base64_decode\s*\(',
                'file_get_contents\s*\(',
                'fopen\s*\(',
                'include\s*\(',
                'require\s*\(',
                '&&',
                '\|\|',
                ';',
                '`',
            ],
            'severity' => 'critical',
            'description' => 'Command Injection Attack Detected',
        ],
        
        'phishing' => [
            'signatures' => [
                'paypal.*login',
                'amazon.*security',
                'microsoft.*verify',
                'google.*suspended',
                'facebook.*locked',
                'urgent.*action.*required',
                'verify.*account.*immediately',
                'suspended.*activity',
                'unusual.*activity.*detected',
                'click.*here.*immediately',
            ],
            'severity' => 'medium',
            'description' => 'Phishing Attempt Detected',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Geographic Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure geographic-based security monitoring and restrictions.
    |
    */

    'geographic' => [
        'enabled' => env('SECURITY_GEOGRAPHIC_MONITORING', true),
        'blocked_countries' => env('SECURITY_BLOCKED_COUNTRIES', ''),
        'allowed_countries' => env('SECURITY_ALLOWED_COUNTRIES', ''),
        'anomaly_detection' => true,
        'vpn_detection' => env('SECURITY_VPN_DETECTION', true),
        'tor_detection' => env('SECURITY_TOR_DETECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for various endpoints and attack prevention.
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        'global_requests_per_minute' => env('SECURITY_GLOBAL_RATE_LIMIT', 1000),
        'per_ip_requests_per_minute' => env('SECURITY_PER_IP_RATE_LIMIT', 60),
        'login_attempts_per_minute' => env('SECURITY_LOGIN_RATE_LIMIT', 5),
        'api_requests_per_minute' => env('SECURITY_API_RATE_LIMIT', 100),
        'password_reset_per_hour' => env('SECURITY_PASSWORD_RESET_LIMIT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Automated Response Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated responses to security threats.
    |
    */

    'automated_responses' => [
        'enabled' => env('SECURITY_AUTOMATED_RESPONSES', true),
        'auto_block_ips' => env('SECURITY_AUTO_BLOCK_IPS', true),
        'auto_block_duration' => env('SECURITY_AUTO_BLOCK_DURATION', 3600), // 1 hour
        'escalate_to_manual' => env('SECURITY_ESCALATE_TO_MANUAL', true),
        'quarantine_suspicious_uploads' => true,
        'disable_compromised_accounts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Intervals
    |--------------------------------------------------------------------------
    |
    | Configure how often different security checks are performed.
    |
    */

    'monitoring_intervals' => [
        'real_time_checks' => 60, // seconds
        'system_health_checks' => 300, // 5 minutes
        'vulnerability_scans' => 3600, // 1 hour
        'dependency_checks' => 86400, // 24 hours
        'log_analysis' => 300, // 5 minutes
        'alert_aggregation' => 60, // 1 minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configure integrations with external security services.
    |
    */

    'integrations' => [
        'threat_intelligence' => [
            'enabled' => env('SECURITY_THREAT_INTEL', false),
            'providers' => [
                'virustotal' => [
                    'api_key' => env('VIRUSTOTAL_API_KEY'),
                    'enabled' => false,
                ],
                'shodan' => [
                    'api_key' => env('SHODAN_API_KEY'),
                    'enabled' => false,
                ],
            ],
        ],
        
        'siem' => [
            'enabled' => env('SECURITY_SIEM_INTEGRATION', false),
            'endpoint' => env('SIEM_ENDPOINT'),
            'api_key' => env('SIEM_API_KEY'),
        ],
        
        'vulnerability_scanners' => [
            'enabled' => env('SECURITY_VULN_SCANNERS', true),
            'snyk' => [
                'enabled' => env('SNYK_ENABLED', false),
                'api_key' => env('SNYK_API_KEY'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance and Reporting
    |--------------------------------------------------------------------------
    |
    | Configure compliance monitoring and security reporting.
    |
    */

    'compliance' => [
        'gdpr' => [
            'enabled' => true,
            'data_retention_days' => 365,
            'anonymize_logs' => true,
        ],
        
        'reporting' => [
            'daily_summary' => true,
            'weekly_report' => true,
            'monthly_analytics' => true,
            'incident_reports' => true,
        ],
        
        'audit_logging' => [
            'enabled' => true,
            'include_user_actions' => true,
            'include_admin_actions' => true,
            'include_api_calls' => true,
            'retention_days' => 1095, // 3 years
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for allowing outbound requests to external APIs.
    | This ensures the security system doesn't block legitimate API calls.
    |
    */

    'external_apis' => [
        'enabled' => env('SECURITY_EXTERNAL_APIS_ENABLED', true),
        
        'allowed_domains' => [
            'generativelanguage.googleapis.com', // Google Gemini API
            'api.openai.com', // OpenAI API (if needed in future)
            'api.anthropic.com', // Anthropic API (if needed in future)
        ],
        
        'timeout' => env('SECURITY_API_TIMEOUT', 30), // 30 seconds
        'max_retries' => env('SECURITY_API_MAX_RETRIES', 3),
        
        'monitoring' => [
            'log_requests' => env('SECURITY_LOG_API_REQUESTS', true),
            'track_response_times' => true,
            'alert_on_failures' => true,
        ],
    ],
];
