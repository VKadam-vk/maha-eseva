<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'mobile',
        'application_number',
        'otp_hash',
        'tracking_token',
        'expires_at',
        'attempts',
        'is_verified',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'attempts' => 'integer',
    ];

    public function isValid(): bool
    {
        return !$this->is_verified && $this->expires_at->isFuture() && $this->attempts < 5;
    }
}
