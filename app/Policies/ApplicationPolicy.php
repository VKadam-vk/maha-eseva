<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('applications.view');
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $application->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('applications.view');
        }

        if ($user->branch_id && $user->branch_id !== $application->branch_id) {
            return false;
        }

        return $user->hasPermission('applications.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('applications.create');
    }

    public function update(User $user, Application $application): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $application->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('applications.edit');
        }

        if ($user->branch_id && $user->branch_id !== $application->branch_id) {
            return false;
        }

        return $user->hasPermission('applications.edit');
    }

    public function delete(User $user, Application $application): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $application->tenant_id) {
            return false;
        }

        if (!$user->isBusinessOwner()) {
            return false;
        }

        return $user->hasPermission('applications.delete');
    }

    public function assign(User $user, Application $application): bool
    {
        return $user->hasPermission('applications.assign');
    }
}
