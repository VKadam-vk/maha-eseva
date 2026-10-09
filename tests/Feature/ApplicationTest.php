<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Service $service;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'admin@mahaeseva.com')->first();
        $this->customer = Customer::first();
        $this->service = Service::where('service_code', 'PAN-NEW-01')->first();
        $this->employee = Employee::first();
    }

    public function test_application_creation_with_auto_generated_sr_number_and_financials(): void
    {
        $response = $this->actingAs($this->user)->post('/applications', [
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'application_date' => now()->toDateString(),
            'base_amount' => 200,
            'govt_fee' => 0,
            'service_charge' => 0,
            'additional_charges' => 50,
            'assigned_employee_id' => $this->employee->id,
            'expected_completion_date' => now()->addDays(5)->format('Y-m-d'),
            'remarks' => 'Urgent PAN Card application',
            'custom_fields' => [
                'aadhaar_number' => '123456789012',
                'father_name' => 'Govind Patil',
            ],
        ]);

        $response->assertRedirect();

        $application = Application::where('customer_id', $this->customer->id)
            ->where('service_id', $this->service->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($application);
        $this->assertStringStartsWith('SR-', $application->application_number);
        $this->assertEquals(250.00, $application->total_amount);
        $this->assertEquals(250.00, $application->remaining_amount);
        $this->assertEquals('PENDING', $application->payment_status);
        $this->assertEquals('NEW', $application->work_status);

        // Verify dynamic custom fields saved
        $this->assertDatabaseHas('application_custom_values', [
            'application_id' => $application->id,
            'field_key' => 'aadhaar_number',
            'field_value' => '123456789012',
        ]);
    }

    public function test_application_status_transition_creates_audit_history(): void
    {
        $application = Application::first();
        $oldStatus = $application->work_status;

        $response = $this->actingAs($this->user)->post("/applications/{$application->id}/status", [
            'work_status' => 'APPROVED',
            'remarks' => 'Document approved by authority',
        ]);

        $response->assertRedirect();

        $application->refresh();
        $this->assertEquals('APPROVED', $application->work_status);

        // Verify application_status_histories record created
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => 'APPROVED',
            'changed_by' => $this->user->id,
            'remarks' => 'Document approved by authority',
        ]);
    }

    public function test_application_list_filter_by_status(): void
    {
        $response = $this->actingAs($this->user)->get('/applications?status=IN_PROCESS');
        $response->assertStatus(200);
        $response->assertSee('SR-2026-00001');
    }
}
