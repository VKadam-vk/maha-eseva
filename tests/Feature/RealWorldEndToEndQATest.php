<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\OtpVerification;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RealWorldEndToEndQATest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $branchAdmin;
    protected User $employee;
    protected Tenant $tenant;
    protected Branch $mainBranch;
    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->tenant = Tenant::first();
        $this->owner = User::where('email', 'admin@mahaeseva.com')->first();
        $this->branchAdmin = User::where('email', 'branchadmin@mahaeseva.com')->first();
        $this->employee = User::where('email', 'employee@mahaeseva.com')->first();
        $this->mainBranch = Branch::where('branch_code', 'PUNE-FC-01')->first();
        $this->branch2 = Branch::where('branch_code', 'PUNE-SN-02')->first();
    }

    /**
     * 1. QA TEST: Business Owner Complete Lifecycle.
     * (Branch -> Employee -> Customer -> Service -> App -> Dynamic Fields -> Doc -> Payment -> Invoice -> Status).
     */
    public function test_business_owner_complete_end_to_end_workflow(): void
    {
        // 1. Create a Branch
        $branchResponse = $this->actingAs($this->owner)->post('/branches', [
            'name' => 'Kothrud Suvidha Kendra',
            'branch_code' => 'PUNE-KTH-04',
            'contact_person' => 'Sunil Shinde',
            'contact_mobile' => '9822004455',
            'city' => 'Pune',
            'district' => 'Pune',
        ]);
        $branchResponse->assertRedirect('/branches');
        $newBranch = Branch::where('branch_code', 'PUNE-KTH-04')->first();
        $this->assertNotNull($newBranch);

        // 2. Create an Employee in the new branch
        $empResponse = $this->actingAs($this->owner)->post('/employees', [
            'name' => 'Amit Sunil Shinde',
            'email' => 'amit.kothrud@mahaeseva.com',
            'mobile' => '9822004456',
            'password' => 'Password@123',
            'branch_id' => $newBranch->id,
            'designation' => 'Counter Executive',
            'salary' => 22000,
        ]);
        $empResponse->assertRedirect('/employees');
        $newEmpUser = User::where('email', 'amit.kothrud@mahaeseva.com')->first();
        $this->assertNotNull($newEmpUser);
        $this->assertEquals($newBranch->id, $newEmpUser->branch_id);

        // 3. Quick Add Customer
        $custResponse = $this->actingAs($this->owner)->post('/customers', [
            'name' => 'Vikas Prabhakar Patil',
            'mobile' => '9822991122',
            'gender' => 'MALE',
            'branch_id' => $newBranch->id,
        ]);
        $custResponse->assertRedirect();
        $customer = Customer::where('mobile', '9822991122')->first();
        $this->assertNotNull($customer);
        $this->assertStringStartsWith('CUST-', $customer->customer_code);

        // 4. Update Customer Profile with full information
        $updateCustResponse = $this->actingAs($this->owner)->put('/customers/' . $customer->id, [
            'name' => 'Vikas Prabhakar Patil',
            'mobile' => '9822991122',
            'gender' => 'MALE',
            'email' => 'vikas.patil@example.com',
            'address' => 'Plot 45, Kothrud',
            'city' => 'Pune',
            'pincode' => '411038',
            'birth_date' => '1990-05-15',
        ]);
        $updateCustResponse->assertRedirect('/customers/' . $customer->id);
        $customer->refresh();
        $this->assertEquals('vikas.patil@example.com', $customer->email);

        // 5. Create Application for PAN Card with Dynamic Fields
        $service = Service::where('service_code', 'PAN-NEW-01')->first();
        $appResponse = $this->actingAs($this->owner)->post('/applications', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'branch_id' => $newBranch->id,
            'assigned_employee_id' => $newEmpUser->employee->id,
            'application_date' => now()->toDateString(),
            'base_amount' => 200,
            'govt_fee' => 107,
            'service_charge' => 93,
            'additional_charges' => 50,
            'custom_fields' => [
                'aadhaar_number' => '556677889900',
                'father_name' => 'Prabhakar Patil',
                'dob_aadhaar' => '1990-05-15',
            ],
        ]);
        $appResponse->assertRedirect();
        $application = Application::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($application);
        $this->assertStringStartsWith('SR-', $application->application_number);
        $this->assertEquals(450.00, $application->total_amount); // 200 + 107 + 93 + 50
        $this->assertEquals(450.00, $application->remaining_amount);
        $this->assertEquals('PENDING', $application->payment_status);

        // Verify Dynamic Field was saved in application_custom_values
        $this->assertDatabaseHas('application_custom_values', [
            'application_id' => $application->id,
            'field_key' => 'aadhaar_number',
            'field_value' => '556677889900',
        ]);

        // 6. Upload Document to Private Vault
        Storage::fake('local');
        $file = UploadedFile::fake()->create('pan_form_vikas.pdf', 350, 'application/pdf');
        $docResponse = $this->actingAs($this->owner)->post('/documents/upload', [
            'customer_id' => $customer->id,
            'application_id' => $application->id,
            'document_type_name' => 'Signed Form 49A',
            'file' => $file,
        ]);
        $docResponse->assertRedirect();
        $document = Document::where('document_type_name', 'Signed Form 49A')->first();
        $this->assertNotNull($document);
        $this->assertEquals('RECEIVED', $document->status);

        // 7. Verify Document
        $verifyDocResponse = $this->actingAs($this->owner)->post("/documents/{$document->id}/status", [
            'status' => 'VERIFIED',
            'remarks' => 'Signatures verified',
        ]);
        $verifyDocResponse->assertRedirect();
        $document->refresh();
        $this->assertEquals('VERIFIED', $document->status);

        // 8. Record Partial Payment
        $payResponse1 = $this->actingAs($this->owner)->post('/billing/payments/record', [
            'customer_id' => $customer->id,
            'application_id' => $application->id,
            'amount' => 200.00,
            'payment_mode' => 'UPI',
            'payment_date' => now()->toDateString(),
            'transaction_reference' => 'UPI5544332211',
            'notes' => 'Advance token payment',
        ]);
        $payResponse1->assertRedirect();
        $application->refresh();
        $this->assertEquals(200.00, $application->received_amount);
        $this->assertEquals(250.00, $application->remaining_amount);
        $this->assertEquals('PARTIAL', $application->payment_status);

        // 9. Record Final Payment
        $payResponse2 = $this->actingAs($this->owner)->post('/billing/payments/record', [
            'customer_id' => $customer->id,
            'application_id' => $application->id,
            'amount' => 250.00,
            'payment_mode' => 'CASH',
            'payment_date' => now()->toDateString(),
            'notes' => 'Balance settlement',
        ]);
        $payResponse2->assertRedirect();
        $application->refresh();
        $this->assertEquals(450.00, $application->received_amount);
        $this->assertEquals(0.00, $application->remaining_amount);
        $this->assertEquals('PAID', $application->payment_status);

        // 10. Follow-up Creation & Completion
        $followUpResponse = $this->actingAs($this->owner)->post('/followups', [
            'customer_id' => $customer->id,
            'application_id' => $application->id,
            'assigned_employee_id' => $newEmpUser->employee->id,
            'follow_up_date' => now()->addDays(2)->toDateString(),
            'follow_up_time' => '11:00',
            'reason' => 'Status Intimation',
            'remarks' => 'Call customer once acknowledgment is received',
        ]);
        $followUpResponse->assertRedirect();
        $followUp = FollowUp::where('customer_id', $customer->id)->first();
        $this->assertNotNull($followUp);
        $this->assertEquals('PENDING', $followUp->status);

        // Mark follow-up completed
        $completeFollowUp = $this->actingAs($this->owner)->post("/followups/{$followUp->id}/status", [
            'status' => 'COMPLETED',
            'remarks' => 'Customer intimated successfully',
        ]);
        $completeFollowUp->assertRedirect();
        $followUp->refresh();
        $this->assertEquals('COMPLETED', $followUp->status);

        // 11. Application Lifecycle Status Transitions
        // NEW -> IN_PROCESS
        $this->actingAs($this->owner)->post("/applications/{$application->id}/status", [
            'work_status' => 'IN_PROCESS',
            'remarks' => 'Uploaded to NSDL portal',
        ]);
        $application->refresh();
        $this->assertEquals('IN_PROCESS', $application->work_status);

        // IN_PROCESS -> APPROVED
        $this->actingAs($this->owner)->post("/applications/{$application->id}/status", [
            'work_status' => 'APPROVED',
            'remarks' => 'PAN Generated by Income Tax Dept',
        ]);
        $application->refresh();
        $this->assertEquals('APPROVED', $application->work_status);

        // APPROVED -> READY
        $this->actingAs($this->owner)->post("/applications/{$application->id}/status", [
            'work_status' => 'READY',
            'remarks' => 'Physical card arrived at branch',
        ]);
        $application->refresh();
        $this->assertEquals('READY', $application->work_status);

        // READY -> DELIVERED
        $this->actingAs($this->owner)->post("/applications/{$application->id}/status", [
            'work_status' => 'DELIVERED',
            'remarks' => 'Handed over to customer',
        ]);
        $application->refresh();
        $this->assertEquals('DELIVERED', $application->work_status);

        // Verify status history count
        $this->assertGreaterThanOrEqual(4, $application->statusHistories()->count());

        // 12. Dashboard Verification
        $dashResponse = $this->actingAs($this->owner)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Overview');
    }

    /**
     * 2. QA TEST: Duplicate Mobile Customer Detection & Prevention.
     */
    public function test_duplicate_mobile_detection_and_prevention_flow(): void
    {
        $mobile = '9877001122';

        // 1. Create first customer
        $this->actingAs($this->owner)->post('/customers', [
            'name' => 'Original Citizen A',
            'mobile' => $mobile,
            'gender' => 'MALE',
        ]);
        $original = Customer::where('mobile', $mobile)->first();
        $this->assertNotNull($original);

        $initialCount = Customer::where('tenant_id', $this->owner->tenant_id)->count();

        // 2. Search mobile via AJAX duplicate endpoint
        $checkResponse = $this->actingAs($this->owner)->getJson("/customers/check-duplicate?mobile={$mobile}");
        $checkResponse->assertStatus(200);
        $checkResponse->assertJson([
            'exists' => true,
            'customer' => [
                'id' => $original->id,
                'name' => 'Original Citizen A',
                'mobile' => $mobile,
            ],
        ]);

        // 3. Attempt duplicate customer creation with same mobile
        $dupResponse = $this->actingAs($this->owner)->post('/customers', [
            'name' => 'Duplicate Attempt B',
            'mobile' => $mobile,
            'gender' => 'MALE',
        ]);

        // Redirects to existing customer profile instead of creating new row
        $dupResponse->assertRedirect('/customers/' . $original->id);
        $this->assertEquals($initialCount, Customer::where('tenant_id', $this->owner->tenant_id)->count());
    }

    /**
     * 3. QA TEST: Public Citizen Tracking Portal with OTP.
     */
    public function test_public_citizen_tracking_portal_flow(): void
    {
        $application = Application::first();
        $customer = $application->customer;

        // 1. Request Tracking OTP
        $otpReqResponse = $this->postJson('/api/portal/request-otp', [
            'application_number' => $application->application_number,
            'mobile' => $customer->mobile,
        ]);
        $otpReqResponse->assertStatus(200);
        $otpReqResponse->assertJson(['success' => true]);

        $verification = OtpVerification::where('application_number', $application->application_number)->latest()->first();
        $this->assertNotNull($verification);

        // Setup fixed OTP hash
        $knownOtp = '987654';
        $verification->update(['otp_hash' => hash('sha256', $knownOtp)]);

        // 2. Verify OTP
        $verifyResponse = $this->postJson('/api/portal/verify-otp', [
            'application_number' => $application->application_number,
            'mobile' => $customer->mobile,
            'otp' => $knownOtp,
        ]);
        $verifyResponse->assertStatus(200);
        $token = $verifyResponse->json('tracking_token');
        $this->assertNotEmpty($token);

        // 3. View Citizen Status Page
        $statusPageResponse = $this->get('/portal/status/' . $token);
        $statusPageResponse->assertStatus(200);
        $statusPageResponse->assertSee($application->application_number);
        $statusPageResponse->assertSee($customer->name);
        $statusPageResponse->assertSee($application->service->name);

        // Verify NO internal ERP internals leaked
        $statusPageResponse->assertDontSee('Manage Branches');
        $statusPageResponse->assertDontSee('Audit Logs');
        $statusPageResponse->assertDontSee('Billing Settings');
    }

    /**
     * 4. QA TEST: Website Enquiry (Lead) Capture & 1-Click Conversion.
     */
    public function test_website_enquiry_capture_and_conversion_flow(): void
    {
        $service = Service::first();

        // 1. Capture public lead
        $leadResponse = $this->postJson('/api/leads/capture', [
            'name' => 'Ketan Manohar Deshmukh',
            'mobile' => '9822776655',
            'email' => 'ketan.deshmukh@example.com',
            'service_id' => $service->id,
            'source' => 'WEBSITE',
            'message' => 'Need 11-month registered rent agreement for Kothrud flat',
        ]);
        $leadResponse->assertStatus(200);
        $lead = Lead::where('mobile', '9822776655')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('NEW', $lead->status);

        // 2. Convert Lead to Customer & Application
        $convertResponse = $this->actingAs($this->owner)->post("/leads/{$lead->id}/convert");
        $convertResponse->assertRedirect();

        $lead->refresh();
        $this->assertEquals('CONVERTED', $lead->status);
        $this->assertNotNull($lead->converted_customer_id);
        $this->assertNotNull($lead->converted_application_id);

        $convertedCustomer = Customer::find($lead->converted_customer_id);
        $this->assertEquals('Ketan Manohar Deshmukh', $convertedCustomer->name);
    }

    /**
     * 5. QA TEST: Billing Invoicing, Receipts & Refund.
     */
    public function test_billing_invoicing_receipt_and_refund_flow(): void
    {
        $application = Application::first();
        $customer = $application->customer;

        // 1. Record payment
        $this->actingAs($this->owner)->post('/billing/payments/record', [
            'customer_id' => $customer->id,
            'application_id' => $application->id,
            'amount' => 150.00,
            'payment_mode' => 'CASH',
            'payment_date' => now()->toDateString(),
            'notes' => 'Service fee',
        ]);

        $payment = Payment::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($payment);

        // 2. Printable Receipt
        $receiptView = $this->actingAs($this->owner)->get("/billing/payments/{$payment->id}/receipt");
        $receiptView->assertStatus(200);
        $receiptView->assertSee($payment->receipt_number);

        // 3. Process Refund
        $refundResponse = $this->actingAs($this->owner)->post("/billing/payments/{$payment->id}/refund", [
            'refund_amount' => 150.00,
            'reason' => 'Application returned by authority',
        ]);
        $refundResponse->assertRedirect();
        $payment->refresh();
        $this->assertEquals('REFUNDED', $payment->payment_status);
    }
}
