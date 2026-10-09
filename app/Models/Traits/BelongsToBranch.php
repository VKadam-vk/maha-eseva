<?php

namespace App\Models\Traits;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToBranch
{
    /**
     * Boot the BelongsToBranch trait.
     */
    protected static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (Auth::check() && empty($model->branch_id)) {
                $user = Auth::user();
                if ($user->branch_id) {
                    $model->branch_id = $user->branch_id;
                }
            }
        });
    }

    /**
     * Branch relationship.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
