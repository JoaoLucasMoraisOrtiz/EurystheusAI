<?php

namespace App\Http\Middleware;

use App\Models\Analytics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $page
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $page = null)
    {
        // Only track GET requests (not AJAX, POST, etc.)
        if ($request->isMethod('GET') && !$request->ajax()) {
            Analytics::create([
                'event_type' => 'page_visit',
                'page' => $page ?? $request->route()->getName() ?? 'unknown',
                'url' => $request->fullUrl(),
                'referrer' => $request->header('Referer'),
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'session_id' => session()->getId(),
                'metadata' => Auth::check() ? ['user_id' => Auth::id()] : null,
            ]);
        }

        return $next($request);
    }
}
