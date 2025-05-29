<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SecurityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'severity',
        'source_ip',
        'user_agent',
        'url',
        'method',
        'payload',
        'headers',
        'user_id',
        'session_id',
        'description',
        'metadata',
        'status',
        'automated_response',
        'response_action',
        'resolved_at',
        'resolved_by',
        'resolution_notes'
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'metadata' => 'array',
        'automated_response' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'resolved_at',
        'created_at',
        'updated_at'
    ];

    // Severity levels
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    // Status types
    const STATUS_DETECTED = 'detected';
    const STATUS_BLOCKED = 'blocked';
    const STATUS_RESOLVED = 'resolved';

    // Event types
    const TYPE_SQL_INJECTION = 'sql_injection';
    const TYPE_XSS_ATTACK = 'xss_attack';
    const TYPE_CSRF_ATTACK = 'csrf_attack';
    const TYPE_BRUTE_FORCE = 'brute_force';
    const TYPE_DOS_ATTACK = 'dos_attack';
    const TYPE_UNAUTHORIZED_ACCESS = 'unauthorized_access';
    const TYPE_SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    const TYPE_MALWARE_DETECTED = 'malware_detected';
    const TYPE_PHISHING_ATTEMPT = 'phishing_attempt';

    /**
     * Get the user associated with the security event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the event
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for filtering by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by event type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for recent events
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope for unresolved events
     */
    public function scopeUnresolved($query)
    {
        return $query->where('status', '!=', self::STATUS_RESOLVED);
    }

    /**
     * Check if event is critical
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Check if event is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Mark event as resolved
     */
    public function markAsResolved(User $user, string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => Carbon::now(),
            'resolved_by' => $user->id,
            'resolution_notes' => $notes
        ]);
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_LOW => '#28a745',
            self::SEVERITY_MEDIUM => '#ffc107',
            self::SEVERITY_HIGH => '#fd7e14',
            self::SEVERITY_CRITICAL => '#dc3545',
            default => '#6c757d'
        };
    }

    /**
     * Get event type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->event_type) {
            self::TYPE_SQL_INJECTION => 'SQL Injection',
            self::TYPE_XSS_ATTACK => 'XSS Attack',
            self::TYPE_CSRF_ATTACK => 'CSRF Attack',
            self::TYPE_BRUTE_FORCE => 'Brute Force',
            self::TYPE_DOS_ATTACK => 'DoS Attack',
            self::TYPE_UNAUTHORIZED_ACCESS => 'Unauthorized Access',
            self::TYPE_SUSPICIOUS_ACTIVITY => 'Suspicious Activity',
            self::TYPE_MALWARE_DETECTED => 'Malware Detected',
            self::TYPE_PHISHING_ATTEMPT => 'Phishing Attempt',
            default => ucfirst(str_replace('_', ' ', $this->event_type))
        ];
    }
}
