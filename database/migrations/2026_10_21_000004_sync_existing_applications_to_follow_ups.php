<?php

use App\Models\Application;
use App\Models\FollowUp;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill all applications with follow_up_date into follow_ups table
        $apps = Application::withoutGlobalScopes()
            ->whereNotNull('follow_up_date')
            ->with(['service', 'customer'])
            ->get();

        foreach ($apps as $app) {
            $serviceName = $app->service?->main_service_name ?? 'Service';
            $subServiceName = $app->sub_service ?? '';
            $reason = $subServiceName ? "{$serviceName} - {$subServiceName}" : "Service Follow-up ({$serviceName})";

            FollowUp::updateOrCreate(
                [
                    'application_id' => $app->id,
                ],
                [
                    'tenant_id' => $app->tenant_id,
                    'branch_id' => $app->branch_id,
                    'customer_id' => $app->customer_id,
                    'assigned_employee_id' => $app->assigned_employee_id,
                    'follow_up_date' => $app->follow_up_date,
                    'reason' => $reason,
                    'remarks' => $app->work_details ?: 'Fast Entry Desk Schedule',
                    'status' => in_array($app->work_status, ['READY', 'DELIVERED', 'COMPLETED']) ? FollowUp::STATUS_COMPLETED : FollowUp::STATUS_PENDING,
                    'created_by' => $app->created_by,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down: do not delete user-created follow-ups
    }
};
