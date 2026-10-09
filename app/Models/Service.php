<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'service_code',
        'main_service_name',
        'sub_service_name',
        'service_variant',
        'description',
        'price',
        'govt_fee',
        'service_charge',
        'additional_charge_rules_json',
        'required_documents_json',
        'service_portal_link',
        'work_portal_link',
        'video_instruction_link',
        'portal_username_encrypted',
        'portal_password_encrypted',
        'expected_processing_days',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'govt_fee' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'additional_charge_rules_json' => 'array',
        'required_documents_json' => 'array',
        'portal_username_encrypted' => 'encrypted',
        'portal_password_encrypted' => 'encrypted',
        'expected_processing_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ServiceCustomField::class)->orderBy('sort_order', 'asc');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_services');
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->main_service_name . ' - ' . $this->sub_service_name;
        if ($this->service_variant) {
            $name .= ' (' . $this->service_variant . ')';
        }
        return $name;
    }

    public function getTotalDefaultChargeAttribute(): float
    {
        return (float) ($this->price > 0 ? $this->price : ($this->govt_fee + $this->service_charge));
    }
}
