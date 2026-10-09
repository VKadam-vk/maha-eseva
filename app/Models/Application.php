<?php

namespace App\Models;

use App\Models\Traits\BelongsToBranch;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, BelongsToBranch;

    public const STATUS_NEW = 'NEW';
    public const STATUS_DOCUMENT_PENDING = 'DOCUMENT_PENDING';
    public const STATUS_DOCUMENT_VERIFIED = 'DOCUMENT_VERIFIED';
    public const STATUS_IN_PROCESS = 'IN_PROCESS';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_UNDER_PROCESS = 'UNDER_PROCESS';
    public const STATUS_RETURNED_CORRECTION = 'RETURNED_CORRECTION';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_READY = 'READY';
    public const STATUS_DELIVERED = 'DELIVERED';
    public const STATUS_CLOSED = 'CLOSED';
    public const STATUS_ON_HOLD = 'ON_HOLD';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const PAYMENT_PENDING = 'PENDING';
    public const PAYMENT_PARTIAL = 'PARTIAL';
    public const PAYMENT_PAID = 'PAID';
    public const PAYMENT_REFUNDED = 'REFUNDED';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'branch_id',
        'customer_id',
        'service_id',
        'application_number',
        'application_date',
        'assigned_employee_id',
        'work_status',
        'payment_status',
        'base_amount',
        'govt_fee',
        'service_charge',
        'additional_charges',
        'discount_amount',
        'total_amount',
        'received_amount',
        'remaining_amount',
        'external_acknowledgement_no',
        'external_portal_login_user',
        'submit_date',
        'due_date',
        'expected_completion_date',
        'actual_completion_date',
        'delivery_status',
        'delivery_date',
        'work_details',
        'pending_remarks',
        'rejection_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'application_date' => 'date',
        'submit_date' => 'date',
        'due_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'delivery_date' => 'datetime',
        'base_amount' => 'decimal:2',
        'govt_fee' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'additional_charges' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($app) {
            if (empty($app->uuid)) {
                $app->uuid = (string) Str::uuid();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customValues(): HasMany
    {
        return $this->hasMany(ApplicationCustomValue::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeTask::class);
    }

    public function recalculateFinancials(): void
    {
        $total = ($this->base_amount + $this->govt_fee + $this->service_charge + $this->additional_charges) - $this->discount_amount;
        $total = max(0, $total);
        $received = (float) $this->payments()->where('payment_status', 'SUCCESS')->sum('amount');
        $remaining = max(0, $total - $received);

        $paymentStatus = self::PAYMENT_PENDING;
        if ($received >= $total && $total > 0) {
            $paymentStatus = self::PAYMENT_PAID;
        } elseif ($received > 0 && $received < $total) {
            $paymentStatus = self::PAYMENT_PARTIAL;
        }

        $this->total_amount = $total;
        $this->received_amount = $received;
        $this->remaining_amount = $remaining;
        $this->payment_status = $paymentStatus;
        $this->saveQuietly();
    }
}
