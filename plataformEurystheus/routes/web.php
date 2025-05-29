<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreeDashboardController;
use App\Http\Controllers\MarketingController; // Added MarketingController
use App\Http\Controllers\TranslationController; // Added TranslationController
use Illuminate\Support\Facades\Route;

// Route::get('/', function () { // Commented out old root route
//     return view('welcome');
// });
Route::get('/', [MarketingController::class, 'home'])->name('marketing.home'); // New root route
Route::get('/sales', [MarketingController::class, 'sales'])->name('marketing.sales'); // New sales page route

// Language switching route
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'pt_BR'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('language.switch');

// Additional static pages
Route::view('/terms', 'marketing.terms')->name('marketing.terms');
Route::view('/privacy', 'marketing.privacy')->name('marketing.privacy');
// Email Verification Routes
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isFree()) {
            return app(FreeDashboardController::class)->show();
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    // Route for free users to submit their scope and get prompt suggestions
    Route::post('/dashboard/prompt', [FreeDashboardController::class, 'storePrompt'])
         ->middleware('check.prompt.limit')
         ->name('free.dashboard.prompt'); // This can be used by free users or as a general prompt creation endpoint

    // Route to get a specific prompt log (perhaps for display or retry, might be more for free tier)
    Route::get('/dashboard/prompt/{id}', [FreeDashboardController::class, 'getPrompt'])
         ->name('free.dashboard.prompt.get');

    // Route for paid users to execute a generated prompt
    Route::post('/dashboard/execute-prompt', [DashboardController::class, 'executePrompt'])
        ->name('paid.dashboard.execute.prompt')
        ->middleware('role:payed_user,admin'); // Ensure only paid users or admins can access

    // Admin only routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role.update');

        // Translation Management Routes
        Route::get('/translations/{file?}', [TranslationController::class, 'index'])->name('translations.index');
        Route::post('/translations/update', [TranslationController::class, 'update'])->name('translations.update');
        
        // Promotion Management Routes
        Route::resource('promotions', App\Http\Controllers\Admin\PromotionController::class);
        Route::patch('/promotions/{promotion}/toggle', [App\Http\Controllers\Admin\PromotionController::class, 'toggle'])->name('promotions.toggle');
        
        // System Settings Routes
        Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('settings.index');
        Route::match(['POST', 'PATCH'], '/settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('settings.update');
    });
});

// Analytics Routes (can be accessed without authentication)
Route::prefix('api/analytics')->group(function () {
    Route::post('/page-view', [App\Http\Controllers\AnalyticsController::class, 'trackPageView']);
    Route::post('/button-click', [App\Http\Controllers\AnalyticsController::class, 'trackButtonClick']);
});
