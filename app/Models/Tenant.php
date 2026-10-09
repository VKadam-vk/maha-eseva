<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'business_type',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'address',
        'city',
        'district',
        'state',
        'pincode',
        'gst_number',
        'status',
        'subscription_plan',
        'subscription_expires_at',
        'max_branches',
        'max_users',
        'max_storage_mb',
        'settings_json',
    ];

    protected $casts = [
        'settings_json' => 'array',
        'subscription_expires_at' => 'date',
        'max_branches' => 'integer',
        'max_users' => 'integer',
        'max_storage_mb' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($tenant) {
            if (empty($tenant->uuid)) {
                $tenant->uuid = (string) Str::uuid();
            }
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name) . '-' . Str::random(5);
            }
        });
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
