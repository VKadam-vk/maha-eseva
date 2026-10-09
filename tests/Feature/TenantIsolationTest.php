<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userTenantA;
    protected User $userTenantB;
    protected Customer $customerTenantA;
    protected Customer $customerTenantB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Retrieve seeded Tenant A
        $this->tenantA = Tenant::first();
        $this->userTenantA = User::where('email', 'admin@mahaeseva.com')->first();
        $this->customerTenantA = Customer::where('tenant_id', $this->tenantA->id)->first();

        // Create second separate Tenant B
        $this->tenantB = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Second Kendra Nagpur',
            'slug' => 'kendra-nagpur',
            'contact_name' => 'Nitin Gadre',
            'contact_email' => 'admin@nagpuresava.com',
            'contact_mobile' => '9890000000',
            'status' => 'ACTIVE',
        ]);

        $branchB = Branch::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Nagpur Main Branch',
            'branch_code' => 'NGP-MAIN-01',
            'status' => 'ACTIVE',
            'is_main_branch' => true,
        ]);

        $this->userTenantB = User::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $branchB->id,
            'name' => 'Nitin Gadre',
            'email' => 'admin@nagpuresava.com',
            'mobile' => '9890000000',
            'password' => Hash::make('Password@123'),
            'status' => 'ACTIVE',
        ]);
        $ownerRole = Role::where('slug', 'BUSINESS_OWNER')->first();
        $this->userTenantB->roles()->attach($ownerRole->id);

        $this->customerTenantB = Customer::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $branchB->id,
            'customer_code' => 'CUST-2026-99999',
            'name' => 'Nagpur Secret Citizen',
            'mobile' => '9999999999',
            'gender' => 'MALE',
            'created_by' => $this->userTenantB->id,
            'updated_by' => $this->userTenantB->id,
        ]);
    }

    public function test_tenant_a_cannot_view_tenant_b_customer_profile(): void
    {
        // Tenant A user attempts to view Tenant B's customer directly -> Blocked by TenantScope (404) or Policy (403)
        $response = $this->actingAs($this->userTenantA)->get('/customers/' . $this->customerTenantB->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant access but received {$response->status()}");
    }

    public function test_tenant_b_cannot_view_tenant_a_customer_profile(): void
    {
        // Tenant B user attempts to view Tenant A's customer directly -> Blocked by TenantScope (404) or Policy (403)
        $response = $this->actingAs($this->userTenantB)->get('/customers/' . $this->customerTenantA->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant access but received {$response->status()}");
    }

    public function test_customer_list_is_automatically_scoped_to_current_tenant(): void
    {
        // Tenant A visits customer directory -> sees only Tenant A customers
        $responseA = $this->actingAs($this->userTenantA)->get('/customers');
        $responseA->assertStatus(200);
        $responseA->assertSee($this->customerTenantA->name);
        $responseA->assertDontSee($this->customerTenantB->name);

        // Tenant B visits customer directory -> sees only Tenant B customers
        $responseB = $this->actingAs($this->userTenantB)->get('/customers');
        $responseB->assertStatus(200);
        $responseB->assertSee($this->customerTenantB->name);
        $responseB->assertDontSee($this->customerTenantA->name);
    }

    public function test_cross_tenant_application_access_is_forbidden(): void
    {
        $appTenantA = Application::where('tenant_id', $this->tenantA->id)->first();

        $response = $this->actingAs($this->userTenantB)->get('/applications/' . $appTenantA->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant access but received {$response->status()}");
    }
}
