<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Search for duplicate customer by mobile number within tenant.
     */
    public function findByMobile(int $tenantId, string $mobile): ?Customer
    {
        $cleanedMobile = preg_replace('/[^0-9]/', '', $mobile);
        return Customer::where('tenant_id', $tenantId)
            ->where(function ($query) use ($cleanedMobile, $mobile) {
                $query->where('mobile', $mobile)
                      ->orWhere('mobile', $cleanedMobile);
            })
            ->first();
    }

    /**
     * Generate unique customer code for tenant.
     */
    public function generateCustomerCode(int $tenantId): string
    {
        $year = date('Y');
        $count = Customer::where('tenant_id', $tenantId)->count() + 1;
        $code = sprintf('CUST-%s-%05d', $year, $count);

        while (Customer::where('tenant_id', $tenantId)->where('customer_code', $code)->exists()) {
            $count++;
            $code = sprintf('CUST-%s-%05d', $year, $count);
        }

        return $code;
    }

    /**
     * Create or retrieve existing customer.
     */
    public function createCustomer(array $data, User $creator): Customer
    {
        $tenantId = $creator->tenant_id;
        $branchId = $data['branch_id'] ?? $creator->branch_id ?? $creator->tenant->branches()->first()?->id;

        // Check for duplicate by mobile
        $existing = $this->findByMobile($tenantId, $data['mobile']);
        if ($existing) {
            return $existing;
        }

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
                'category_id' => $data['category_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);

            AuditService::log('CUSTOMER_CREATED', $customer, null, $customer->toArray());

            return $customer;
        });
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
            'category_id' => $data['category_id'] ?? $customer->category_id,
            'notes' => $data['notes'] ?? $customer->notes,
            'updated_by' => $updater->id,
        ]);

        AuditService::log('CUSTOMER_UPDATED', $customer, $old, $customer->fresh()->toArray());

        return $customer;
    }
}
