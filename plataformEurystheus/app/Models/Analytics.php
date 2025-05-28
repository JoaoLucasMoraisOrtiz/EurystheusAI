<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'page',
        'element',
        'url',
        'referrer',
        'user_agent',
        'ip_address',
        'session_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get page views count.
     */
    public static function getPageViews($page = null, $days = 30)
    {
        $query = static::where('event_type', 'page_view')
            ->where('created_at', '>=', now()->subDays($days));
        
        if ($page) {
            $query->where('page', $page);
        }
        
        return $query->count();
    }

    /**
     * Get button clicks count.
     */
    public static function getButtonClicks($element = null, $days = 30)
    {
        $query = static::where('event_type', 'button_click')
            ->where('created_at', '>=', now()->subDays($days));
        
        if ($element) {
            $query->where('element', $element);
        }
        
        return $query->count();
    }

    /**
     * Track a page view.
     */
    public static function trackPageView($page, $request)
    {
        return static::create([
            'event_type' => 'page_view',
            'page' => $page,
            'url' => $request->fullUrl(),
            'referrer' => $request->header('referer'),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Track a button click.
     */
    public static function trackButtonClick($element, $page, $request, $metadata = [])
    {
        return static::create([
            'event_type' => 'button_click',
            'page' => $page,
            'element' => $element,
            'url' => $request->header('referer') ?: $request->fullUrl(),
            'referrer' => $request->header('referer'),
            'user_agent' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
            'session_id' => session()->getId(),
            'metadata' => $metadata,
        ]);
    }
}
