<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FailedLoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'email',
        'user_agent',
        'attempted_password',
        'request_data',
        'session_id',
        'country_code',
        'city',
        'is_blocked_ip',
        'triggered_lockout',
        'attack_pattern',
        'attempts_in_window',
        'lockout_until'
    ];

    protected $casts = [
        'request_data' => 'array',
        'is_blocked_ip' => 'boolean',
        'triggered_lockout' => 'boolean',
        'lockout_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'lockout_until',
        'created_at',
        'updated_at'
    ];

    // Attack pattern types
    const PATTERN_BRUTE_FORCE = 'brute_force';
    const PATTERN_CREDENTIAL_STUFFING = 'credential_stuffing';
    const PATTERN_DICTIONARY_ATTACK = 'dictionary_attack';
    const PATTERN_RAINBOW_TABLE = 'rainbow_table';
    const PATTERN_AUTOMATED = 'automated';
    const PATTERN_SUSPICIOUS = 'suspicious';

    /**
     * Scope for filtering by IP address
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope for filtering by email
     */
    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope for recent attempts
     */
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('created_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    /**
     * Scope for blocked IPs
     */
    public function scopeBlockedIps($query)
    {
        return $query->where('is_blocked_ip', true);
    }

    /**
     * Scope for lockout triggers
     */
    public function scopeLockoutTriggers($query)
    {
        return $query->where('triggered_lockout', true);
    }

    /**
     * Scope for active lockouts
     */
    public function scopeActiveLockouts($query)
    {
        return $query->where('lockout_until', '>', Carbon::now());
    }

    /**
     * Scope for attack pattern
     */
    public function scopeByPattern($query, $pattern)
    {
        return $query->where('attack_pattern', $pattern);
    }

    /**
     * Check if IP is currently locked out
     */
    public function isCurrentlyLockedOut(): bool
    {
        return $this->lockout_until && $this->lockout_until->isFuture();
    }

    /**
     * Check if this is a brute force attempt
     */
    public function isBruteForce(): bool
    {
        return $this->attack_pattern === self::PATTERN_BRUTE_FORCE;
    }

    /**
     * Check if this is credential stuffing
     */
    public function isCredentialStuffing(): bool
    {
        return $this->attack_pattern === self::PATTERN_CREDENTIAL_STUFFING;
    }

    /**
     * Get attempts count for IP in time window
     */
    public static function getAttemptsForIp(string $ip, int $minutes = 15): int
    {
        return static::where('ip_address', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Get attempts count for email in time window
     */
    public static function getAttemptsForEmail(string $email, int $minutes = 60): int
    {
        return static::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Check if IP should be blocked
     */
    public static function shouldBlockIp(string $ip, int $threshold = 5, int $minutes = 15): bool
    {
        return static::getAttemptsForIp($ip, $minutes) >= $threshold;
    }

    /**
     * Check if email should be locked out
     */
    public static function shouldLockoutEmail(string $email, int $threshold = 3, int $minutes = 60): bool
    {
        return static::getAttemptsForEmail($email, $minutes) >= $threshold;
    }

    /**
     * Get geographic location display
     */
    public function getLocationDisplayAttribute(): string
    {
        if ($this->city && $this->country_code) {
            return "{$this->city}, {$this->country_code}";
        } elseif ($this->country_code) {
            return $this->country_code;
        }
        return 'Unknown';
    }

    /**
     * Get attack pattern display name
     */
    public function getPatternDisplayNameAttribute(): string
    {
        return match($this->attack_pattern) {
            self::PATTERN_BRUTE_FORCE => 'Brute Force',
            self::PATTERN_CREDENTIAL_STUFFING => 'Credential Stuffing',
            self::PATTERN_DICTIONARY_ATTACK => 'Dictionary Attack',
            self::PATTERN_RAINBOW_TABLE => 'Rainbow Table',
            self::PATTERN_AUTOMATED => 'Automated Attack',
            self::PATTERN_SUSPICIOUS => 'Suspicious Activity',
            default => ucfirst(str_replace('_', ' ', $this->attack_pattern ?? 'unknown'))
        };
    }
}
