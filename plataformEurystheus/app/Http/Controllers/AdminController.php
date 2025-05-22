<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        
        return view('admin.index', compact('users'));
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
