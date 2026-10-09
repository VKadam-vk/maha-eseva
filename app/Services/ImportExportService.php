<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportService
{
    /**
     * Sanitize cell value to prevent CSV / Excel Formula Injection (CSV Injection / CWE-1236).
     */
    public static function sanitizeCsvField(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $firstChar = substr($value, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@', "\t", "\r", '%'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Clean input value on import to strip dangerous leading formula characters.
     */
    public static function cleanImportField(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $trimmed = trim($value);
        if (str_starts_with($trimmed, "'")) {
            $trimmed = substr($trimmed, 1);
        }

        return ltrim($trimmed, "=+-@\t\r%");
    }

    /**
     * Parse and import customers from CSV file.
     * Note: tenant_id and branch_id are strictly determined server-side and NEVER trusted from CSV content.
     */
    public function importCustomers(UploadedFile $file, User $user, int $branchId): array
    {
        $tenantId = $user->tenant_id;
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \InvalidArgumentException('Unable to read the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \InvalidArgumentException('Empty CSV file.');
        }

        $normalizedHeaders = array_map(function ($h) {
            return strtolower(trim(str_replace(' ', '_', $h)));
        }, $header);

        $nameIdx = array_search('name', $normalizedHeaders);
        $mobileIdx = array_search('mobile', $normalizedHeaders);
        $genderIdx = array_search('gender', $normalizedHeaders);
        $emailIdx = array_search('email', $normalizedHeaders);
        $addressIdx = array_search('address', $normalizedHeaders);
        $cityIdx = array_search('city', $normalizedHeaders);
        $pincodeIdx = array_search('pincode', $normalizedHeaders);

        if ($nameIdx === false || $mobileIdx === false) {
            fclose($handle);
            throw new \InvalidArgumentException('CSV must contain at least "name" and "mobile" columns.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        $customerService = app(CustomerService::class);

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $name = self::cleanImportField($row[$nameIdx] ?? '');
                $mobile = preg_replace('/[^0-9]/', '', self::cleanImportField($row[$mobileIdx] ?? ''));

                if (empty($name) || empty($mobile)) {
                    $skipped++;
                    $errors[] = "Row {$rowNum}: Missing valid name or 10-digit mobile.";
                    continue;
                }

                // Check duplicate within tenant
                $existing = $customerService->findByMobile($tenantId, $mobile);
                if ($existing) {
                    $skipped++;
                    $errors[] = "Row {$rowNum}: Customer with mobile {$mobile} already exists.";
                    continue;
                }

                $gender = ($genderIdx !== false && !empty($row[$genderIdx])) ? strtoupper(self::cleanImportField($row[$genderIdx])) : 'MALE';
                if (!in_array($gender, ['MALE', 'FEMALE', 'OTHER'], true)) {
                    $gender = 'MALE';
                }

                $customerService->createCustomer([
                    'branch_id' => $branchId,
                    'name' => $name,
                    'mobile' => $mobile,
                    'gender' => $gender,
                    'email' => ($emailIdx !== false) ? self::cleanImportField($row[$emailIdx] ?? '') : null,
                    'address' => ($addressIdx !== false) ? self::cleanImportField($row[$addressIdx] ?? '') : null,
                    'city' => ($cityIdx !== false) ? self::cleanImportField($row[$cityIdx] ?? '') : null,
                    'pincode' => ($pincodeIdx !== false) ? self::cleanImportField($row[$pincodeIdx] ?? '') : null,
                ], $user);

                $imported++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        fclose($handle);

        AuditService::log('CUSTOMER_IMPORT', null, null, [
            'total_imported' => $imported,
            'total_skipped' => $skipped,
        ]);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Export customers as CSV stream with formula injection sanitization.
     */
    public function exportCustomers(User $user, array $filters = []): StreamedResponse
    {
        $tenantId = $user->tenant_id;
        $query = Customer::where('tenant_id', $tenantId)->with('branch');

        if ($user->isBranchAdmin() || $user->isEmployee()) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        AuditService::log('EXPORT_CUSTOMERS', null, null, ['user_id' => $user->id]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customers_export_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Customer Code', 'Name', 'Mobile', 'Gender', 'Email', 'Address', 'City', 'Pincode', 'Branch', 'Created At']);

            $query->chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        self::sanitizeCsvField($c->customer_code),
                        self::sanitizeCsvField($c->name),
                        self::sanitizeCsvField($c->mobile),
                        self::sanitizeCsvField($c->gender),
                        self::sanitizeCsvField($c->email),
                        self::sanitizeCsvField($c->address),
                        self::sanitizeCsvField($c->city),
                        self::sanitizeCsvField($c->pincode),
                        self::sanitizeCsvField($c->branch?->name),
                        self::sanitizeCsvField($c->created_at?->format('Y-m-d H:i:s')),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export applications as CSV stream with formula injection sanitization.
     */
    public function exportApplications(User $user, array $filters = []): StreamedResponse
    {
        $tenantId = $user->tenant_id;
        $query = Application::where('tenant_id', $tenantId)->with(['customer', 'service', 'assignedEmployee.user', 'branch']);

        if ($user->isBranchAdmin() || $user->isEmployee()) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('work_status', $filters['status']);
        }

        AuditService::log('EXPORT_APPLICATIONS', null, null, ['user_id' => $user->id]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="applications_export_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'SR Number', 'Application Date', 'Customer Name', 'Mobile', 'Service Name',
                'Branch', 'Assigned Employee', 'Work Status', 'Payment Status',
                'Total Amount', 'Received Amount', 'Remaining Amount', 'Due Date'
            ]);

            $query->chunk(200, function ($apps) use ($handle) {
                foreach ($apps as $app) {
                    fputcsv($handle, [
                        self::sanitizeCsvField($app->application_number),
                        self::sanitizeCsvField($app->application_date?->format('Y-m-d')),
                        self::sanitizeCsvField($app->customer?->name),
                        self::sanitizeCsvField($app->customer?->mobile),
                        self::sanitizeCsvField($app->service?->full_name),
                        self::sanitizeCsvField($app->branch?->name),
                        self::sanitizeCsvField($app->assignedEmployee?->user?->name ?? 'Unassigned'),
                        self::sanitizeCsvField($app->work_status),
                        self::sanitizeCsvField($app->payment_status),
                        self::sanitizeCsvField((string) $app->total_amount),
                        self::sanitizeCsvField((string) $app->received_amount),
                        self::sanitizeCsvField((string) $app->remaining_amount),
                        self::sanitizeCsvField($app->due_date?->format('Y-m-d')),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
