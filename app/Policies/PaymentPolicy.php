<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->tenant_id !== $payment->tenant_id) {
            return false;
        }

        if ($user->isBusinessOwner()) {
            return $user->hasPermission('payments.view');
        }

        if ($user->branch_id && $user->branch_id !== $payment->branch_id) {
            return false;
        }

        return $user->hasPermission('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('payments.create');
    }

    public function refund(User $user, Payment $payment): bool
    {
        if ($user->tenant_id !== $payment->tenant_id) {
            return false;
        }

        return $user->hasPermission('payments.refund');
    }
}
