<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customers.view');
    }

    /**
     * Determine whether the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $customer->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('customers.view');
        }

        // Branch-restricted users can only view their branch's customers
        if ($user->branch_id && $user->branch_id !== $customer->branch_id) {
            return false;
        }

        return $user->hasPermission('customers.view');
    }

    /**
     * Determine whether the user can create customers.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('customers.create');
    }

    /**
     * Determine whether the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $customer->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('customers.edit');
        }

        if ($user->branch_id && $user->branch_id !== $customer->branch_id) {
            return false;
        }

        return $user->hasPermission('customers.edit');
    }

    /**
     * Determine whether the user can delete the customer.
     */
    public function delete(User $user, Customer $customer): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $customer->tenant_id) {
            return false;
        }

        if (!$user->isBusinessOwner()) {
            return false; // Only Business Owner or Super Admin can delete customers
        }

        return $user->hasPermission('customers.delete');
    }
}
