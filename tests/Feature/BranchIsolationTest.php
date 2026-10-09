<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch1;
    protected Branch $branch2;
    protected User $businessOwner;
    protected User $branchAdmin1;
    protected User $branchAdmin2;
    protected User $employeeBranch1;
    protected Customer $customerBranch1;
    protected Customer $customerBranch2;
    protected ?Application $applicationBranch1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::first();
        $this->branch1 = Branch::where('branch_code', 'PUNE-FC-01')->first();
        $this->branch2 = Branch::where('branch_code', 'PUNE-SN-02')->first();

        $this->businessOwner = User::where('email', 'admin@mahaeseva.com')->first();
        $this->branchAdmin1 = User::where('email', 'branchadmin@mahaeseva.com')->first();
        $this->employeeBranch1 = User::where('email', 'employee@mahaeseva.com')->first();

        // Create a Branch Admin for Branch 2
        $this->branchAdmin2 = User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Shivaji Nagar Branch Admin',
            'email' => 'sn.admin@mahaeseva.com',
            'mobile' => '9822000002',
            'password' => bcrypt('Password@123'),
            'status' => 'ACTIVE',
        ]);
        $branchAdminRole = Role::where('slug', 'BRANCH_ADMIN')->first();
        $this->branchAdmin2->roles()->attach($branchAdminRole->id);

        $this->customerBranch1 = Customer::where('branch_id', $this->branch1->id)->first();
        
        $this->customerBranch2 = Customer::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'customer_code' => 'CUST-2026-00099',
            'name' => 'Shivaji Nagar Citizen',
            'mobile' => '9822998877',
            'gender' => 'MALE',
            'created_by' => $this->businessOwner->id,
            'updated_by' => $this->businessOwner->id,
        ]);

        $this->applicationBranch1 = Application::where('branch_id', $this->branch1->id)->first();
    }

    public function test_business_owner_can_access_all_branches_in_tenant(): void
    {
        $response1 = $this->actingAs($this->businessOwner)->get('/customers/' . $this->customerBranch1->id);
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->businessOwner)->get('/customers/' . $this->customerBranch2->id);
        $response2->assertStatus(200);
    }

    public function test_branch_admin_cannot_access_another_branch_customer(): void
    {
        // Branch Admin 2 tries to view Branch 1's customer -> Blocked by BranchScope (404) or Policy (403)
        $response = $this->actingAs($this->branchAdmin2)->get('/customers/' . $this->customerBranch1->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-branch access but got {$response->status()}");
    }

    public function test_branch_employee_cannot_access_another_branch_customer(): void
    {
        // Employee from Branch 1 tries to access Branch 2's customer -> Blocked by BranchScope (404) or Policy (403)
        $response = $this->actingAs($this->employeeBranch1)->get('/customers/' . $this->customerBranch2->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-branch access but got {$response->status()}");
    }

    public function test_branch_admin_cannot_create_branches(): void
    {
        // Branch Admin attempting to create a branch should be rejected with 403 Forbidden
        $response = $this->actingAs($this->branchAdmin1)->post('/branches', [
            'name' => 'Illegal Sub-Branch',
            'branch_code' => 'PUN-ILL-99',
            'mobile' => '9876543210',
        ]);

        $response->assertStatus(403);
    }
}
