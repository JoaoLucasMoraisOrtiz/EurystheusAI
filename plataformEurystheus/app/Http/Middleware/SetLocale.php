<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is provided in the URL
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (in_array($locale, ['en', 'pt_BR'])) {
                Session::put('locale', $locale);
            }
        }

        // Set locale from session or default
        $locale = Session::get('locale', config('app.locale'));
        
        // Ensure the locale is valid
        if (!in_array($locale, ['en', 'pt_BR'])) {
            $locale = 'pt_BR'; // Default to Portuguese
        }
        
        App::setLocale($locale);

        return $next($request);
    }
}
