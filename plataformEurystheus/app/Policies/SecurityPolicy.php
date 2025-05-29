<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SecurityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the security dashboard.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSecurityDashboard(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage security alerts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manageSecurityAlerts(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage security blocks.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manageSecurityBlocks(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export security data.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function exportSecurityData(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view security logs.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewSecurityLogs(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage security configurations.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function manageSecurityConfiguration(User $user)
    {
        return $user->isAdmin();
    }
}
