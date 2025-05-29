<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\PromptLog;
use App\Models\Promotion;
use App\Models\Analytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Telemetrias básicas de usuários
        $totalUsers = User::count();
        $freeUsers = User::where('role', UserRole::FREE_USER)->count();
        $payedUsers = User::where('role', UserRole::PAYED_USER)->count();
        
        // 2. Analytics de visitas às páginas
        $homeVisits = Analytics::getPageViews('home', 30); // últimos 30 dias
        $salesVisits = Analytics::getPageViews('sales', 30); // últimos 30 dias
        
        // 2.1 Dados para gráficos - últimos 7 dias
        $last7Days = [];
        $homeVisitsLast7Days = [];
        $salesVisitsLast7Days = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days[] = $date->format('M d');
            
            $homeVisitsDay = Analytics::where('page', 'home')
                ->whereDate('created_at', $date)
                ->count();
            $salesVisitsDay = Analytics::where('page', 'sales')
                ->whereDate('created_at', $date)
                ->count();
                
            $homeVisitsLast7Days[] = $homeVisitsDay;
            $salesVisitsLast7Days[] = $salesVisitsDay;
        }
        
        // 3. Média de consumo de prompts por usuários gratuitos
        $freeUserPromptAverage = 0;
        $freeUsersWithPrompts = User::where('role', UserRole::FREE_USER)
            ->whereHas('promptLogs')
            ->withCount('promptLogs')
            ->get();
        
        if ($freeUsersWithPrompts->count() > 0) {
            $freeUserPromptAverage = $freeUsersWithPrompts->avg('prompt_logs_count');
        }
            
        // 4. Média de consumo de prompts por usuários pagos (se houver dados)
        $payedUserPromptAverage = 0;
        $payedUsersWithPrompts = User::where('role', UserRole::PAYED_USER)
            ->whereHas('promptLogs')
            ->withCount('promptLogs')
            ->get();
            
        if ($payedUsersWithPrompts->count() > 0) {
            $payedUserPromptAverage = $payedUsersWithPrompts->avg('prompt_logs_count');
        }

        // 5. Estatísticas adicionais
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $totalPromptsToday = PromptLog::whereDate('created_at', today())->count();

        $promptLogContents = PromptLog::orderBy('created_at', 'desc')->take(50)->pluck('content');
        $users = User::paginate(10); // Keep existing user pagination for role management
        
        // Get promotions data
        $totalPromotions = Promotion::count();
        $activePromotions = Promotion::where('is_active', true)->count();
        $currentPromotion = Promotion::getActivePromotion();

        return view('admin.index', compact(
            'totalUsers', 'freeUsers', 'payedUsers',
            'homeVisits', 'salesVisits', 
            'last7Days', 'homeVisitsLast7Days', 'salesVisitsLast7Days',
            'freeUserPromptAverage', 'payedUserPromptAverage',
            'newUsersToday', 'newUsersThisWeek', 'totalPromptsToday',
            'promptLogContents', 'users', 
            'totalPromotions', 'activePromotions', 'currentPromotion'
        ));
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

    public function refreshAnalytics()
    {
        $totalUsers = User::count();
        $freeUsers = User::where('role', UserRole::FREE_USER)->count();
        $payedUsers = User::where('role', UserRole::PAYED_USER)->count();
        
        $homeVisits = Analytics::getPageViews('home', 30);
        $salesVisits = Analytics::getPageViews('sales', 30);
        
        $freeUserPromptAverage = 0;
        $freeUsersWithPrompts = User::where('role', UserRole::FREE_USER)
            ->whereHas('promptLogs')
            ->withCount('promptLogs')
            ->get();
        
        if ($freeUsersWithPrompts->count() > 0) {
            $freeUserPromptAverage = $freeUsersWithPrompts->avg('prompt_logs_count');
        }

        return response()->json([
            'totalUsers' => $totalUsers,
            'freeUsers' => $freeUsers,
            'payedUsers' => $payedUsers,
            'homeVisits' => $homeVisits,
            'salesVisits' => $salesVisits,
            'freeUserPromptAverage' => $freeUserPromptAverage,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    public function exportData($type)
    {
        $filename = "eurystheus_analytics_{$type}_" . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($type) {
            $file = fopen('php://output', 'w');
            
            switch ($type) {
                case 'users':
                    fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Created At', 'Prompt Count']);
                    User::with('promptLogs')->chunk(100, function($users) use ($file) {
                        foreach ($users as $user) {
                            fputcsv($file, [
                                $user->id,
                                $user->name,
                                $user->email,
                                $user->role->label(),
                                $user->created_at->format('Y-m-d H:i:s'),
                                $user->promptLogs->count()
                            ]);
                        }
                    });
                    break;
                    
                case 'analytics':
                    fputcsv($file, ['ID', 'Page', 'Action', 'Created At', 'Date']);
                    Analytics::chunk(100, function($analytics) use ($file) {
                        foreach ($analytics as $analytic) {
                            fputcsv($file, [
                                $analytic->id,
                                $analytic->page,
                                $analytic->action,
                                $analytic->created_at->format('Y-m-d H:i:s'),
                                $analytic->created_at->format('Y-m-d')
                            ]);
                        }
                    });
                    break;
                    
                case 'prompts':
                    fputcsv($file, ['ID', 'User ID', 'Content Preview', 'Created At']);
                    PromptLog::chunk(100, function($prompts) use ($file) {
                        foreach ($prompts as $prompt) {
                            fputcsv($file, [
                                $prompt->id,
                                $prompt->user_id ?? $prompt->anonymous_user,
                                Str::limit($prompt->content, 100),
                                $prompt->created_at->format('Y-m-d H:i:s')
                            ]);
                        }
                    });
                    break;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
