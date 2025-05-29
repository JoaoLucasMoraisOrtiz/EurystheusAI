<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_percentage',
        'original_price',
        'discounted_price',
        'currency',
        'is_active',
        'show_urgency',
        'show_floating_banner',
        'valid_from',
        'valid_until',
        'max_uses',
        'current_uses',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_urgency' => 'boolean',
        'show_floating_banner' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'discount_percentage' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    /**
     * Check if the promotion is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Get the formatted price with currency
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return $this->currency === 'BRL' 
            ? 'R$ ' . number_format($this->original_price, 2, ',', '.') 
            : '$' . number_format($this->original_price, 2);
    }

    /**
     * Get the formatted discounted price with currency
     */
    public function getFormattedDiscountedPriceAttribute(): string
    {
        return $this->currency === 'BRL' 
            ? 'R$ ' . number_format($this->discounted_price, 2, ',', '.') 
            : '$' . number_format($this->discounted_price, 2);
    }

    /**
     * Get the active promotion
     */
    public static function getActivePromotion(): ?self
    {
        return self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', Carbon::now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', Carbon::now());
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereRaw('current_uses < max_uses');
            })
            ->first();
    }

    /**
     * Increment the usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }
}
