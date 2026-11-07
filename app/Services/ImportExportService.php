<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Customer;
use App\Models\CustomerCategory;
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
        $categoryIdx = array_search('category', $normalizedHeaders);
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

                $categoryId = null;
                if ($categoryIdx !== false && !empty($row[$categoryIdx])) {
                    $catRaw = self::cleanImportField($row[$categoryIdx]);
                    if (!empty($catRaw) && strtolower($catRaw) !== 'general') {
                        $matchedCategory = CustomerCategory::where('tenant_id', $tenantId)
                            ->where(function ($q) use ($catRaw) {
                                $q->whereRaw('LOWER(name) = ?', [strtolower($catRaw)])
                                  ->orWhereRaw('LOWER(code) = ?', [strtolower($catRaw)]);
                            })->first();
                        $categoryId = $matchedCategory?->id;
                    }
                }

                $customerService->createCustomer([
                    'branch_id' => $branchId,
                    'name' => $name,
                    'mobile' => $mobile,
                    'category_id' => $categoryId,
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
        $query = Customer::where('tenant_id', $tenantId)->with(['branch', 'category']);

        if ($user->isBranchAdmin() || $user->isEmployee()) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%")
                  ->orWhere('customer_code', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
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
            fputcsv($handle, ['Customer Code', 'Name', 'Mobile', 'Category', 'Gender', 'Email', 'Address', 'City', 'Pincode', 'Branch', 'Created At']);

            $query->chunk(200, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    fputcsv($handle, [
                        self::sanitizeCsvField($c->customer_code),
                        self::sanitizeCsvField($c->name),
                        self::sanitizeCsvField($c->mobile),
                        self::sanitizeCsvField($c->category?->name ?? 'General'),
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
        $query = Application::where('tenant_id', $tenantId)
            ->with(['customer', 'service', 'assignedEmployee.user', 'branch']);

        // Search filter
        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('application_number', 'like', "%{$s}%")
                    ->orWhere('external_acknowledgement_no', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($c) use ($s) {
                        $c->where('name', 'like', "%{$s}%")
                            ->orWhere('mobile', 'like', "%{$s}%")
                            ->orWhere('alternate_mobile', 'like', "%{$s}%")
                            ->orWhere('customer_code', 'like', "%{$s}%");
                    });
            });
        }

        // Branch filter
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif (!empty($filters['branch_id']) && ($user->isSuperAdmin() || $user->isBusinessOwner())) {
            $query->where('branch_id', $filters['branch_id']);
        }

        // Service filter
        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        // Status filter (accept both 'work_status' and 'status')
        $status = $filters['work_status'] ?? $filters['status'] ?? null;
        if (!empty($status)) {
            $query->where('work_status', $status);
        }

        // Payment status filter
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Employee filter
        if (!empty($filters['employee_id'])) {
            $query->where('assigned_employee_id', $filters['employee_id']);
        }

        // Date range filters
        if (!empty($filters['from_date'])) {
            $query->whereDate('application_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('application_date', '<=', $filters['to_date']);
        }

        // Restrict employee visibility to only their allowed services or assigned applications
        if ($user->hasServiceRestrictions()) {
            $allowedServiceIds = $user->getAllowedServiceIds();
            $empId = $user->employee?->id;
            $query->where(function ($q) use ($allowedServiceIds, $empId) {
                $q->whereIn('service_id', $allowedServiceIds);
                if ($empId) {
                    $q->orWhere('assigned_employee_id', $empId);
                }
            });
        }

        AuditService::log('EXPORT_APPLICATIONS', null, null, ['user_id' => $user->id]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="all_entries_export_' . date('Y-m-d_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for seamless Marathi / Unicode rendering in Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'SR Number',
                'Application Date',
                'Customer Code',
                'Customer Name',
                'Mobile',
                'Main Service',
                'Sub Service',
                'Expected Days',
                'Rate (INR)',
                'Received (INR)',
                'Remaining (INR)',
                'Payment Mode',
                'Work Details',
                'Pending Remark',
                'Follow-Up Date',
                'Status',
                'Operator',
                'Branch'
            ]);

            $query->latest('id')->chunk(200, function ($apps) use ($handle) {
                foreach ($apps as $app) {
                    $mainService = $app->service?->main_service_name ?? 'N/A';
                    $subService = $app->sub_service ?: ($app->service?->sub_service_name ?? 'N/A');
                    $expDays = $app->service?->expected_processing_days ?? '-';
                    $paymentMode = $app->payment_mode ?: ($app->payments->first()?->payment_mode ?? 'Cash');
                    $operator = $app->assignedEmployee?->user?->name ?? 'Unassigned';

                    fputcsv($handle, [
                        self::sanitizeCsvField($app->application_number),
                        self::sanitizeCsvField($app->application_date ? $app->application_date->format('Y-m-d') : ($app->created_at ? $app->created_at->format('Y-m-d') : '')),
                        self::sanitizeCsvField($app->customer?->customer_code ?? ''),
                        self::sanitizeCsvField($app->customer?->name ?? ''),
                        self::sanitizeCsvField($app->customer?->mobile ?? ''),
                        self::sanitizeCsvField($mainService),
                        self::sanitizeCsvField($subService),
                        self::sanitizeCsvField((string)$expDays),
                        self::sanitizeCsvField(number_format((float)$app->total_amount, 2, '.', '')),
                        self::sanitizeCsvField(number_format((float)$app->received_amount, 2, '.', '')),
                        self::sanitizeCsvField(number_format((float)$app->remaining_amount, 2, '.', '')),
                        self::sanitizeCsvField((string)$paymentMode),
                        self::sanitizeCsvField($app->work_details ?? ''),
                        self::sanitizeCsvField($app->pending_remarks ?? ''),
                        self::sanitizeCsvField($app->follow_up_date ? (is_string($app->follow_up_date) ? $app->follow_up_date : $app->follow_up_date->format('Y-m-d')) : ''),
                        self::sanitizeCsvField($app->work_status ?? ''),
                        self::sanitizeCsvField($operator),
                        self::sanitizeCsvField($app->branch?->name ?? '')
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
