<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'failed_login_attempts',
        'locked_until',
        'last_login_at',
        'last_login_ip',
        'avatar_path',
        'two_factor_secret',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'failed_login_attempts' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    // Role helper checks
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('PLATFORM_SUPER_ADMIN');
    }

    public function isBusinessOwner(): bool
    {
        return $this->hasRole('BUSINESS_OWNER');
    }

    public function isBranchAdmin(): bool
    {
        return $this->hasRole('BRANCH_ADMIN');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('EMPLOYEE');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('CUSTOMER');
    }

    // Permission check
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Business owner has all tenant-level permissions
        if ($this->isBusinessOwner()) {
            return true;
        }

        // Check direct user permissions if user has custom assigned permissions
        $userPerms = $this->relationLoaded('permissions') ? $this->permissions : $this->permissions()->get();
        if ($userPerms->isNotEmpty()) {
            return $userPerms->contains('slug', $permissionSlug);
        }

        // Fallback to role permissions
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get query for services accessible to this user.
     */
    public function allowedServices()
    {
        if ($this->isSuperAdmin() || $this->isBusinessOwner() || $this->isBranchAdmin()) {
            return Service::where('is_active', true);
        }

        if ($this->employee) {
            $assignedCount = $this->employee->services()->count();
            if ($assignedCount > 0) {
                return $this->employee->services()->where('is_active', true);
            }
        }

        return Service::where('is_active', true);
    }

    /**
     * Check if user is permitted to access a specific service.
     */
    public function canAccessService(int|Service $service): bool
    {
        if ($this->isSuperAdmin() || $this->isBusinessOwner() || $this->isBranchAdmin()) {
            return true;
        }

        $serviceId = $service instanceof Service ? $service->id : (int) $service;

        if ($this->employee) {
            $assignedCount = $this->employee->services()->count();
            if ($assignedCount > 0) {
                return $this->employee->services()->where('services.id', $serviceId)->exists();
            }
        }

        return true;
    }

    // Account lockout & Security
    public function isLocked(): bool
    {
        if ($this->status === 'LOCKED') {
            return true;
        }

        if ($this->locked_until && $this->locked_until->isFuture()) {
            return true;
        }

        return false;
    }

    public function recordFailedLogin(): void
    {
        $this->increment('failed_login_attempts');
        if ($this->failed_login_attempts >= 5) {
            $this->update([
                'status' => 'LOCKED',
                'locked_until' => now()->addMinutes(15),
            ]);
        }
    }

    public function resetFailedLogin(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }
}
