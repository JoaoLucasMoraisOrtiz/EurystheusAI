<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\PromptLog;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        // Placeholder for home visits - this requires a mechanism to track visits
        $homeVisits = 0; // Replace with actual tracking logic later

        $promptLogContents = PromptLog::orderBy('created_at', 'desc')->take(50)->pluck('content');
        
        $users = User::paginate(10); // Keep existing user pagination for role management
        
        // Get promotions data
        $totalPromotions = Promotion::count();
        $activePromotions = Promotion::where('is_active', true)->count();
        $currentPromotion = Promotion::getActivePromotion();

        return view('admin.index', compact('totalUsers', 'homeVisits', 'promptLogContents', 'users', 'totalPromotions', 'activePromotions', 'currentPromotion'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:' . implode(',', UserRole::values()),
        ]);

        // Impedir que o último admin perca a role
        if ($user->isAdmin() && $request->role !== UserRole::ADMIN->value) {
            $adminCount = User::where('role', UserRole::ADMIN)->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot remove the last admin user.');
            }
        }

        $user->assignRole(UserRole::from($request->role));

        return back()->with('success', 'User role updated successfully.');
    }
}
