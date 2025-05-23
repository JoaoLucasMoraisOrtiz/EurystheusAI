<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function home()
    {
        return view('marketing.home');
    }

    public function sales()
    {
        // For now, let's just return a simple view. We'll build this out later.
        return view('marketing.sales');
    }
}
