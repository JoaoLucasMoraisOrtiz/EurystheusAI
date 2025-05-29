<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\SecurityPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register security-related gates
        Gate::define('viewSecurityDashboard', [SecurityPolicy::class, 'viewSecurityDashboard']);
        Gate::define('manageSecurityAlerts', [SecurityPolicy::class, 'manageSecurityAlerts']);
        Gate::define('manageSecurityBlocks', [SecurityPolicy::class, 'manageSecurityBlocks']);
        Gate::define('exportSecurityData', [SecurityPolicy::class, 'exportSecurityData']);
        Gate::define('viewSecurityLogs', [SecurityPolicy::class, 'viewSecurityLogs']);
        Gate::define('manageSecurityConfiguration', [SecurityPolicy::class, 'manageSecurityConfiguration']);

        // Additional security gates
        Gate::define('blockIpAddress', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('unblockIpAddress', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('viewVulnerabilities', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('resolveVulnerabilities', function (User $user) {
            return $user->isAdmin();
        });
    }
}
