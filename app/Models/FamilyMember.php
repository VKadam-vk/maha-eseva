<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'name',
        'relation',
        'gender',
        'birth_date',
        'age',
        'mobile',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'age' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function getRelationLabelAttribute(): string
    {
        return match (strtoupper($this->relation)) {
            'SON' => 'मुलगा (Son)',
            'DAUGHTER' => 'मुलगी (Daughter)',
            'WIFE' => 'पत्नी (Wife)',
            'HUSBAND' => 'पती (Husband)',
            'FATHER' => 'वडील (Father)',
            'MOTHER' => 'आई (Mother)',
            'BROTHER' => 'भाऊ (Brother)',
            'SISTER' => 'बहीण (Sister)',
            default => ucfirst(strtolower($this->relation)),
        };
    }
}
