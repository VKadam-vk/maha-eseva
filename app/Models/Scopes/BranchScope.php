<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    /**
     * Apply branch-level scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin and Business Owner have access to all branches of their tenant
            if ($user->isSuperAdmin() || $user->isBusinessOwner()) {
                return;
            }

            // Branch Admin and Employee are strictly scoped to their assigned branch
            if ($user->branch_id) {
                $builder->where($model->getTable() . '.branch_id', $user->branch_id);
            }
        }
    }
}
