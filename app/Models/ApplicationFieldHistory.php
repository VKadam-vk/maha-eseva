<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFieldHistory extends Model
{
    use HasFactory, BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'application_id',
        'user_id',
        'field_name',
        'field_label',
        'old_value',
        'new_value',
        'old_display',
        'new_display',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function formatDisplay(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        if (in_array($field, ['rate', 'total_amount'])) {
            return '₹' . number_format((float)$value, 2);
        }
        if (in_array($field, ['follow_up_date', 'followup_date'])) {
            try {
                return date('d M Y', strtotime($value));
            } catch (\Exception $e) {
                return (string)$value;
            }
        }
        if (in_array($field, ['assigned_employee_id', 'operator'])) {
            if (!$value) return 'Unassigned';
            $emp = Employee::with('user')->find($value);
            return $emp?->user?->name ?? ('Staff #' . $value);
        }
        return (string)$value;
    }

    public static function recordIfChanged(
        Application $application,
        string $fieldName,
        string $fieldLabel,
        $oldValue,
        $newValue,
        ?string $oldDisplay = null,
        ?string $newDisplay = null,
        ?int $userId = null,
        ?string $remarks = null
    ): ?self {
        $normOld = is_null($oldValue) ? '' : trim((string)$oldValue);
        $normNew = is_null($newValue) ? '' : trim((string)$newValue);

        // Float comparison for rate
        if (in_array($fieldName, ['rate', 'total_amount']) && is_numeric($normOld) && is_numeric($normNew)) {
            if (abs((float)$normOld - (float)$normNew) < 0.001) {
                return null;
            }
        } elseif ($normOld === $normNew) {
            return null;
        }

        $oldDisp = $oldDisplay ?: self::formatDisplay($fieldName, $oldValue);
        $newDisp = $newDisplay ?: self::formatDisplay($fieldName, $newValue);

        return self::create([
            'tenant_id' => $application->tenant_id,
            'application_id' => $application->id,
            'user_id' => $userId ?? auth()->id(),
            'field_name' => $fieldName,
            'field_label' => $fieldLabel,
            'old_value' => $oldValue !== null ? (string)$oldValue : null,
            'new_value' => $newValue !== null ? (string)$newValue : null,
            'old_display' => $oldDisp,
            'new_display' => $newDisp,
            'remarks' => $remarks,
            'created_at' => now(),
        ]);
    }
}
