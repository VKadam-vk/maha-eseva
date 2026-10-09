<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, BelongsToBranch;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'branch_id',
        'user_id',
        'employee_code',
        'designation',
        'joining_date',
        'salary',
        'id_proof_type',
        'id_proof_number',
        'address',
        'emergency_contact',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($employee) {
            if (empty($employee->uuid)) {
                $employee->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'assigned_employee_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeTask::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assigned_employee_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'employee_services');
    }
}
