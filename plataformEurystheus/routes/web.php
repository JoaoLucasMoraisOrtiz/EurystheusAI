<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FreeDashboardController;
use App\Http\Controllers\MarketingController; // Added MarketingController
use Illuminate\Support\Facades\Route;

// Route::get('/', function () { // Commented out old root route
//     return view('welcome');
// });
Route::get('/', [MarketingController::class, 'home'])->name('marketing.home'); // New root route
Route::get('/sales', [MarketingController::class, 'sales'])->name('marketing.sales'); // New sales page route

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->isFree()) {
            return app(FreeDashboardController::class)->show();
        }
        // For paid users, DashboardController@index will be called.
        return app(DashboardController::class)->index(); 
    })->name('dashboard');

    // Route for free users to submit their scope and get prompt suggestions
    Route::post('/dashboard/prompt', [FreeDashboardController::class, 'storePrompt'])
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
    });
});
