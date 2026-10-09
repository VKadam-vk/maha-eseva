<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@mahaeseva.com')->first();
    }

    public function test_quick_customer_creation_with_minimum_information(): void
    {
        $response = $this->actingAs($this->user)->post('/customers', [
            'name' => 'Mahesh Suresh Shinde',
            'mobile' => '9881122334',
            'gender' => 'MALE',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('customers', [
            'name' => 'Mahesh Suresh Shinde',
            'mobile' => '9881122334',
            'tenant_id' => $this->user->tenant_id,
            'branch_id' => $this->user->branch_id,
        ]);

        $customer = Customer::where('mobile', '9881122334')->first();
        $this->assertNotNull($customer->customer_code);
        $this->assertStringStartsWith('CUST-', $customer->customer_code);
    }

    public function test_duplicate_mobile_search_finds_existing_customer(): void
    {
        $existingCustomer = Customer::where('mobile', '9822012345')->first();

        $response = $this->actingAs($this->user)->getJson('/customers/check-duplicate?mobile=9822012345');

        $response->assertStatus(200);
        $response->assertJson([
            'exists' => true,
            'customer' => [
                'id' => $existingCustomer->id,
                'name' => $existingCustomer->name,
                'mobile' => '9822012345',
            ],
        ]);
    }

    public function test_submitting_existing_mobile_reuses_existing_customer_without_creating_duplicate(): void
    {
        $initialCount = Customer::where('tenant_id', $this->user->tenant_id)->count();
        $existingCustomer = Customer::where('mobile', '9822012345')->first();

        $response = $this->actingAs($this->user)->post('/customers', [
            'name' => 'Duplicate Attempt Citizen',
            'mobile' => '9822012345', // already seeded
            'gender' => 'MALE',
        ]);

        $response->assertRedirect('/customers/' . $existingCustomer->id);
        
        // Ensure count has not increased
        $this->assertEquals($initialCount, Customer::where('tenant_id', $this->user->tenant_id)->count());
    }

    public function test_customer_profile_renders_with_associated_records(): void
    {
        $customer = Customer::where('mobile', '9822012345')->first();

        $response = $this->actingAs($this->user)->get('/customers/' . $customer->id);

        $response->assertStatus(200);
        $response->assertSee($customer->name);
        $response->assertSee($customer->customer_code);
        $response->assertSee('Applications');
        $response->assertSee('Documents');
        $response->assertSee('Payments');
    }

    public function test_customer_can_be_updated_safely(): void
    {
        $customer = Customer::where('mobile', '9822012345')->first();

        $response = $this->actingAs($this->user)->put('/customers/' . $customer->id, [
            'name' => 'Rajesh Anant Patil (Updated)',
            'mobile' => '9822012345',
            'email' => 'rajesh.patil.updated@example.com',
            'gender' => 'MALE',
            'city' => 'Pune',
            'pincode' => '411030',
        ]);

        $response->assertRedirect('/customers/' . $customer->id);
        
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Rajesh Anant Patil (Updated)',
            'email' => 'rajesh.patil.updated@example.com',
            'city' => 'Pune',
            'pincode' => '411030',
        ]);
    }
}
