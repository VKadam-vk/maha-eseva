<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    public const STATUS_NEW = 'NEW';
    public const STATUS_CONTACTED = 'CONTACTED';
    public const STATUS_FOLLOW_UP = 'FOLLOW_UP';
    public const STATUS_QUALIFIED = 'QUALIFIED';
    public const STATUS_CONVERTED = 'CONVERTED';
    public const STATUS_LOST = 'LOST';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'mobile',
        'email',
        'service_id',
        'message',
        'source',
        'status',
        'assigned_employee_id',
        'remarks',
        'converted_customer_id',
        'converted_application_id',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function convertedApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'converted_application_id');
    }
}
