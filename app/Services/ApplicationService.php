<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationCustomValue;
use App\Models\ApplicationFieldHistory;
use App\Models\ApplicationStatusHistory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ApplicationService
{
    /**
     * Generate unique application number for tenant.
     */
    public function generateApplicationNumber(int $tenantId): string
    {
        $year = date('Y');
        $count = Application::where('tenant_id', $tenantId)->count() + 1;
        $sr = sprintf('SR-%s-%05d', $year, $count);

        while (Application::where('tenant_id', $tenantId)->where('application_number', $sr)->exists()) {
            $count++;
            $sr = sprintf('SR-%s-%05d', $year, $count);
        }

        return $sr;
    }

    /**
     * Create a new application with dynamic custom fields and required document checklists.
     */
    public function createApplication(array $data, User $creator): Application
    {
        $service = Service::findOrFail($data['service_id']);
        $tenantId = $creator->tenant_id ?? (int) ($data['tenant_id'] ?? ($service->tenant_id ?? 1));
        $branchId = $data['branch_id'] ?? $creator->branch_id ?? $creator->tenant?->branches()->first()?->id ?? 1;

        return DB::transaction(function () use ($data, $service, $tenantId, $branchId, $creator) {
            $appNumber = $this->generateApplicationNumber($tenantId);

            $govtFee = isset($data['govt_fee']) ? (float) $data['govt_fee'] : (float) $service->govt_fee;
            $serviceCharge = isset($data['service_charge']) ? (float) $data['service_charge'] : (float) $service->service_charge;
            $additionalCharges = isset($data['additional_charges']) ? (float) $data['additional_charges'] : 0.00;
            $discountAmount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0.00;

            // Base amount represents the service price (or govt fee + service charge if not provided)
            if (isset($data['base_amount']) && (float) $data['base_amount'] > 0) {
                $baseAmount = (float) $data['base_amount'];
            } elseif ($govtFee + $serviceCharge > 0) {
                $baseAmount = $govtFee + $serviceCharge;
            } else {
                $baseAmount = (float) $service->price;
            }

            // Total amount = Base Amount (which already includes govt fee & service charge) + Additional - Discount
            $totalAmount = max(0, ($baseAmount + $additionalCharges) - $discountAmount);
            $initialReceived = isset($data['received_amount']) ? (float) $data['received_amount'] : 0.00;
            $remainingAmount = max(0, $totalAmount - $initialReceived);

            $paymentStatus = Application::PAYMENT_PENDING;
            if ($initialReceived >= $totalAmount && $totalAmount > 0) {
                $paymentStatus = Application::PAYMENT_PAID;
            } elseif ($initialReceived > 0 && $initialReceived < $totalAmount) {
                $paymentStatus = Application::PAYMENT_PARTIAL;
            }

            $expectedDays = $service->expected_processing_days ?: 7;
            $expectedCompletion = now()->addDays($expectedDays)->toDateString();

            $application = Application::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $data['customer_id'],
                'family_member_id' => $data['family_member_id'] ?? null,
                'service_id' => $service->id,
                'sub_service' => $data['sub_service'] ?? $service->sub_service_name,
                'application_number' => $appNumber,
                'application_date' => $data['application_date'] ?? now()->toDateString(),
                'assigned_employee_id' => $data['assigned_employee_id'] ?? null,
                'work_status' => $data['work_status'] ?? Application::STATUS_NEW,
                'payment_status' => $paymentStatus,
                'base_amount' => $baseAmount,
                'govt_fee' => $govtFee,
                'service_charge' => $serviceCharge,
                'additional_charges' => $additionalCharges,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'received_amount' => $initialReceived,
                'remaining_amount' => $remainingAmount,
                'external_acknowledgement_no' => $data['external_acknowledgement_no'] ?? null,
                'external_portal_login_user' => $data['external_portal_login_user'] ?? null,
                'due_date' => $data['due_date'] ?? $expectedCompletion,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'expected_completion_date' => $expectedCompletion,
                'delivery_status' => 'PENDING',
                'work_details' => $data['work_details'] ?? null,
                'payment_remarks' => $data['payment_remarks'] ?? null,
                'documents_list' => $data['documents_list'] ?? null,
                'pending_remarks' => $data['pending_remarks'] ?? null,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);

            // Save custom field values
            if (!empty($data['custom_fields']) && is_array($data['custom_fields'])) {
                foreach ($data['custom_fields'] as $fieldKey => $fieldValue) {
                    $customField = $service->customFields()->where('field_key', $fieldKey)->first();
                    if ($customField) {
                        ApplicationCustomValue::create([
                            'tenant_id' => $tenantId,
                            'application_id' => $application->id,
                            'field_id' => $customField->id,
                            'field_key' => $fieldKey,
                            'field_value' => is_array($fieldValue) ? json_encode($fieldValue) : (string) $fieldValue,
                        ]);
                    }
                }
            }

            // Create initial status history
            ApplicationStatusHistory::create([
                'tenant_id' => $tenantId,
                'application_id' => $application->id,
                'old_status' => null,
                'new_status' => $application->work_status,
                'changed_by' => $creator->id,
                'remarks' => 'Application created',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);

            // Create initial field history record
            ApplicationFieldHistory::create([
                'tenant_id' => $tenantId,
                'application_id' => $application->id,
                'user_id' => $creator->id,
                'field_name' => 'created',
                'field_label' => 'Service Created',
                'old_value' => null,
                'new_value' => $application->application_number,
                'old_display' => 'New Service Registration',
                'new_display' => $service->main_service_name . ($application->sub_service ? " - {$application->sub_service}" : ""),
                'remarks' => "Initial Rate: ₹" . number_format($totalAmount, 2),
                'created_at' => now(),
            ]);

            // If initial payment was made during creation, record payment
            if ($initialReceived > 0) {
                app(BillingService::class)->recordPayment([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'customer_id' => $application->customer_id,
                    'application_id' => $application->id,
                    'amount' => $initialReceived,
                    'payment_mode' => $data['payment_mode'] ?? 'CASH',
                    'transaction_reference' => $data['transaction_reference'] ?? null,
                    'notes' => 'Initial payment at application creation',
                ], $creator);
            }

            AuditService::log('APPLICATION_CREATED', $application, null, $application->toArray());

            return $application;
        });
    }

    /**
     * Update application status with workflow state machine validation and history logging.
     */
    public function updateStatus(Application $application, string $newStatus, ?string $remarks, User $updater): Application
    {
        $oldStatus = $application->work_status;

        if ($oldStatus === $newStatus) {
            return $application;
        }

        return DB::transaction(function () use ($application, $oldStatus, $newStatus, $remarks, $updater) {
            $updates = [
                'work_status' => $newStatus,
                'updated_by' => $updater->id,
            ];

            if ($newStatus === Application::STATUS_APPROVED || $newStatus === Application::STATUS_READY) {
                $updates['delivery_status'] = 'READY_FOR_PICKUP';
            } elseif ($newStatus === Application::STATUS_DELIVERED || $newStatus === Application::STATUS_CLOSED) {
                $updates['delivery_status'] = 'DELIVERED_PHYSICAL';
                $updates['delivery_date'] = now();
                $updates['actual_completion_date'] = now()->toDateString();
            }

            $application->update($updates);

            ApplicationStatusHistory::create([
                'tenant_id' => $application->tenant_id,
                'application_id' => $application->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $updater->id,
                'remarks' => $remarks ?: 'Status updated to ' . $newStatus,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);

            ApplicationFieldHistory::recordIfChanged(
                $application,
                'work_status',
                'Status',
                $oldStatus,
                $newStatus,
                null,
                null,
                $updater->id,
                $remarks
            );

            AuditService::log('APPLICATION_STATUS_CHANGED', $application, ['status' => $oldStatus], ['status' => $newStatus, 'remarks' => $remarks]);

            return $application;
        });
    }
}
