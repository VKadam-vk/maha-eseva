<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Search for duplicate or existing customer by mobile or alternate mobile number within tenant (and optionally branch).
     */
    public function findByMobile(int $tenantId, string $mobile, ?int $branchId = null): ?Customer
    {
        $cleanedMobile = preg_replace('/[^0-9]/', '', $mobile);
        $query = Customer::where('tenant_id', $tenantId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->where(function ($q) use ($cleanedMobile, $mobile) {
                $q->where('mobile', $mobile)
                  ->orWhere('mobile', $cleanedMobile)
                  ->orWhere('alternate_mobile', $mobile)
                  ->orWhere('alternate_mobile', $cleanedMobile);

                if (strlen($cleanedMobile) >= 10) {
                    $last10 = substr($cleanedMobile, -10);
                    $q->orWhere('mobile', 'like', "%{$last10}%")
                      ->orWhere('alternate_mobile', 'like', "%{$last10}%");
                }
            })
            ->first();
    }

    /**
     * Generate unique customer code for tenant.
     */
    public function generateCustomerCode(int $tenantId): string
    {
        $year = date('Y');
        $prefix = "CUST-{$year}-";

        // Query the highest existing customer code with this prefix, including soft deleted records
        $latest = Customer::withTrashed()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('customer_code', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(customer_code) DESC, customer_code DESC')
            ->value('customer_code');

        $nextNumber = 1;
        if ($latest && preg_match('/CUST-\d{4}-(\d+)/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = Customer::withTrashed()->withoutGlobalScopes()->where('tenant_id', $tenantId)->count() + 1;
        }

        $code = sprintf('CUST-%s-%05d', $year, $nextNumber);

        while (Customer::withTrashed()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('customer_code', $code)->exists()) {
            $nextNumber++;
            $code = sprintf('CUST-%s-%05d', $year, $nextNumber);
        }

        return $code;
    }

    /**
     * Create or retrieve existing customer.
     */
    public function createCustomer(array $data, User $creator): Customer
    {
        $tenantId = (int) ($creator->tenant_id ?? \App\Models\Tenant::first()?->id ?? 1);
        $branchId = $data['branch_id'] ?? $creator->branch_id ?? \App\Models\Branch::where('tenant_id', $tenantId)->first()?->id ?? 1;

        // Check for duplicate by mobile within branch
        $existing = $this->findByMobile($tenantId, $data['mobile'], $branchId);
        if ($existing) {
            return $existing;
        }

        $maxAttempts = 5;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                return DB::transaction(function () use ($data, $tenantId, $branchId, $creator) {
                    $customerCode = $this->generateCustomerCode($tenantId);

                    $customer = Customer::create([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'customer_code' => $customerCode,
                        'name' => trim($data['name']),
                        'mobile' => trim($data['mobile']),
                        'gender' => $data['gender'] ?? 'MALE',
                        'email' => $data['email'] ?? null,
                        'address' => $data['address'] ?? null,
                        'city' => $data['city'] ?? null,
                        'district' => $data['district'] ?? null,
                        'state' => $data['state'] ?? 'Maharashtra',
                        'pincode' => $data['pincode'] ?? null,
                        'birth_date' => $data['birth_date'] ?? null,
                        'alternate_mobile' => $data['alternate_mobile'] ?? null,
                        'occupation' => $data['occupation'] ?? null,
                        'star_rating' => isset($data['star_rating']) ? (int) $data['star_rating'] : 5,
                        'category_id' => $data['category_id'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => $creator->id,
                        'updated_by' => $creator->id,
                    ]);

                    AuditService::log('CUSTOMER_CREATED', $customer, null, $customer->toArray());

                    return $customer;
                });
            } catch (\Illuminate\Database\QueryException $e) {
                if ($attempt >= $maxAttempts || (!str_contains($e->getMessage(), 'UNIQUE constraint') && !str_contains($e->getMessage(), 'Duplicate entry') && !str_contains($e->getMessage(), 'customer_code'))) {
                    throw $e;
                }
                usleep(50000); // 50ms pause before retry
            }
        }

        throw new \RuntimeException('Failed to generate a unique customer code after multiple attempts.');
    }

    /**
     * Update customer details.
     */
    public function updateCustomer(Customer $customer, array $data, User $updater): Customer
    {
        $old = $customer->toArray();

        $customer->update([
            'name' => trim($data['name']),
            'mobile' => trim($data['mobile']),
            'gender' => $data['gender'] ?? $customer->gender,
            'email' => $data['email'] ?? $customer->email,
            'address' => $data['address'] ?? $customer->address,
            'city' => $data['city'] ?? $customer->city,
            'district' => $data['district'] ?? $customer->district,
            'state' => $data['state'] ?? $customer->state,
            'pincode' => $data['pincode'] ?? $customer->pincode,
            'birth_date' => $data['birth_date'] ?? $customer->birth_date,
            'alternate_mobile' => $data['alternate_mobile'] ?? $customer->alternate_mobile,
            'occupation' => array_key_exists('occupation', $data) ? $data['occupation'] : $customer->occupation,
            'star_rating' => isset($data['star_rating']) ? (int) $data['star_rating'] : $customer->star_rating,
            'category_id' => $data['category_id'] ?? $customer->category_id,
            'notes' => $data['notes'] ?? $customer->notes,
            'updated_by' => $updater->id,
        ]);

        AuditService::log('CUSTOMER_UPDATED', $customer, $old, $customer->fresh()->toArray());

        return $customer;
    }
}
