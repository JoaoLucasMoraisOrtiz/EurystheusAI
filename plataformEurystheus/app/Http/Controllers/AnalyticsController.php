<?php

namespace App\Http\Controllers;

use App\Models\Analytics;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Track page visits and events
     */
    public function track(Request $request)
    {
        $data = $request->validate([
            'event_type' => 'required|string|max:50',
            'page' => 'nullable|string|max:100',
            'element' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'referrer' => 'nullable|string|max:255',
            'user_agent' => 'nullable|string|max:500',
            'ip_address' => 'nullable|ip',
            'metadata' => 'nullable|array',
        ]);

        $analytics = Analytics::create([
            'event_type' => $data['event_type'],
            'page' => $data['page'] ?? null,
            'element' => $data['element'] ?? null,
            'url' => $data['url'] ?? $request->fullUrl(),
            'referrer' => $data['referrer'] ?? $request->header('Referer'),
            'user_agent' => $data['user_agent'] ?? $request->header('User-Agent'),
            'ip_address' => $data['ip_address'] ?? $request->ip(),
            'session_id' => session()->getId(),
            'metadata' => $data['metadata'] ?? null,
        ]);

        return response()->json(['success' => true, 'id' => $analytics->id]);
    }

    /**
     * Track a page view.
     */
    public function trackPageView(Request $request)
    {
        try {
            $request->validate([
                'page' => 'required|string|max:255',
                'timestamp' => 'nullable|string'
            ]);

            Analytics::trackPageView($request->input('page'), $request);

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to track page view'], 500);
        }
    }

    /**
     * Track a button click.
     */
    public function trackButtonClick(Request $request)
    {
        try {
            $request->validate([
                'element' => 'required|string|max:255',
                'page' => 'required|string|max:255',
                'timestamp' => 'nullable|string'
            ]);

            $metadata = [
                'timestamp' => $request->input('timestamp', now()->toISOString())
            ];

            Analytics::trackButtonClick(
                $request->input('element'),
                $request->input('page'),
                $request,
                $metadata
            );

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to track button click'], 500);
        }
    }

    /**
     * Get analytics data for admin dashboard
     */
    public function getDashboardData()
    {
        $homeVisits = Analytics::where('event_type', 'page_visit')
            ->where('page', 'home')
            ->count();

        $salesClicks = Analytics::where('event_type', 'button_click')
            ->where('page', 'home')
            ->whereIn('element', ['hero-register', 'hero-demo', 'sales-cta'])
            ->count();

        $todayVisits = Analytics::where('event_type', 'page_visit')
            ->where('page', 'home')
            ->whereDate('created_at', today())
            ->count();

        $todayClicks = Analytics::where('event_type', 'button_click')
            ->where('page', 'home')
            ->whereIn('element', ['hero-register', 'hero-demo', 'sales-cta'])
            ->whereDate('created_at', today())
            ->count();

        $weeklyVisits = Analytics::where('event_type', 'page_visit')
            ->where('page', 'home')
            ->where('created_at', '>=', now()->subWeek())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as visits')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'home_visits' => $homeVisits,
            'sales_clicks' => $salesClicks,
            'today_visits' => $todayVisits,
            'today_clicks' => $todayClicks,
            'weekly_visits' => $weeklyVisits,
        ];
    }
}
