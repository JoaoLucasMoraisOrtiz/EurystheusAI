<?php

namespace App\Http\Controllers;

use App\Models\Analytics;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function home(Request $request)
    {
        // Track page view
        Analytics::trackPageView('home', $request);
        
        return view('marketing.home');
    }

    public function sales(Request $request)
    {
        // Track page view
        Analytics::trackPageView('sales', $request);
        
        return view('marketing.sales');
    }
}
