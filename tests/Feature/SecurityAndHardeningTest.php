<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\OtpVerification;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ImportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAndHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected Branch $branchA1;
    protected Branch $branchA2;
    protected Branch $branchB1;
    protected User $ownerTenantA;
    protected User $branchAdminA1;
    protected User $employeeA1;
    protected User $ownerTenantB;
    protected Customer $customerTenantA;
    protected Customer $customerTenantB;
    protected Application $applicationTenantA;
    protected Application $applicationTenantB;
    protected Document $documentTenantA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Tenant A setup
        $this->tenantA = Tenant::first();
        $this->branchA1 = Branch::where('branch_code', 'PUNE-FC-01')->first();
        $this->branchA2 = Branch::where('branch_code', 'PUNE-SN-02')->first();

        $this->ownerTenantA = User::where('email', 'admin@mahaeseva.com')->first();
        $this->branchAdminA1 = User::where('email', 'branchadmin@mahaeseva.com')->first();
        $this->employeeA1 = User::where('email', 'employee@mahaeseva.com')->first();

        $this->customerTenantA = Customer::where('tenant_id', $this->tenantA->id)->first();
        $this->applicationTenantA = Application::where('tenant_id', $this->tenantA->id)->first();
        $this->documentTenantA = Document::where('tenant_id', $this->tenantA->id)->first() ?? Document::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'customer_id' => $this->customerTenantA->id,
            'application_id' => $this->applicationTenantA->id,
            'document_type_name' => 'Aadhaar Card',
            'original_filename' => 'aadhaar.pdf',
            'storage_path' => 'documents/test_aadhaar.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'dummy_content'),
            'status' => 'RECEIVED',
            'uploaded_by' => $this->ownerTenantA->id,
        ]);

        // Tenant B setup
        $this->tenantB = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Kendra Nagpur Vidarbha',
            'slug' => 'kendra-nagpur-vidarbha',
            'contact_name' => 'Vijay Kale',
            'contact_email' => 'vijay@nagpuresava.com',
            'contact_mobile' => '9890000000',
            'status' => 'ACTIVE',
        ]);

        $this->branchB1 = Branch::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Nagpur Main Branch',
            'branch_code' => 'NGP-MAIN-01',
            'status' => 'ACTIVE',
            'is_main_branch' => true,
        ]);

        $this->ownerTenantB = User::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Vijay Kale',
            'email' => 'vijay@nagpuresava.com',
            'mobile' => '9890000000',
            'password' => Hash::make('Password@123'),
            'status' => 'ACTIVE',
        ]);
        $ownerRole = Role::where('slug', 'BUSINESS_OWNER')->first();
        $this->ownerTenantB->roles()->attach($ownerRole->id);

        $this->customerTenantB = Customer::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'customer_code' => 'CUST-2026-88888',
            'name' => 'Nagpur Resident Citizen',
            'mobile' => '9890989098',
            'gender' => 'MALE',
            'created_by' => $this->ownerTenantB->id,
            'updated_by' => $this->ownerTenantB->id,
        ]);

        $this->applicationTenantB = Application::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'customer_id' => $this->customerTenantB->id,
            'service_id' => $this->applicationTenantA->service_id,
            'application_number' => 'SR-2026-88888',
            'application_date' => now()->toDateString(),
            'work_status' => 'NEW',
            'payment_status' => 'PENDING',
            'total_amount' => 300.00,
            'received_amount' => 0.00,
            'remaining_amount' => 300.00,
            'created_by' => $this->ownerTenantB->id,
            'updated_by' => $this->ownerTenantB->id,
        ]);
    }

    /**
     * TEST: Cross-Tenant Access to Customer Profile is Blocked.
     */
    public function test_cross_tenant_customer_access_is_blocked(): void
    {
        $response = $this->actingAs($this->ownerTenantB)->get('/customers/' . $this->customerTenantA->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant access.");
    }

    /**
     * TEST: Cross-Tenant Access to Application is Blocked.
     */
    public function test_cross_tenant_application_access_is_blocked(): void
    {
        $response = $this->actingAs($this->ownerTenantB)->get('/applications/' . $this->applicationTenantA->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant application access.");
    }

    /**
     * TEST: Cross-Tenant Access to Private Document Download is Blocked.
     */
    public function test_cross_tenant_document_download_is_blocked(): void
    {
        $response = $this->actingAs($this->ownerTenantB)->get("/documents/{$this->documentTenantA->id}/download");
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-tenant document download.");
    }

    /**
     * TEST: Cross-Branch Access between Branch 1 and Branch 2 is Blocked.
     */
    public function test_cross_branch_access_is_blocked_for_branch_admin(): void
    {
        // Create Customer in Branch A1 (FC Road)
        $customerA1 = Customer::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'customer_code' => 'CUST-2026-77777',
            'name' => 'Branch 1 Only Customer',
            'mobile' => '9888877777',
            'gender' => 'MALE',
            'created_by' => $this->ownerTenantA->id,
            'updated_by' => $this->ownerTenantA->id,
        ]);

        // Branch Admin A2 (Shivaji Nagar) tries to view Branch A1 customer -> Blocked
        $response = $this->actingAs($this->branchAdminA1)->get('/customers/' . $customerA1->id);
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 for cross-branch access.");
    }

    /**
     * TEST: Branch Admin cannot create another branch.
     */
    public function test_branch_admin_cannot_create_branches(): void
    {
        $response = $this->actingAs($this->branchAdminA1)->post('/branches', [
            'name' => 'Unauthorized Extra Branch',
            'branch_code' => 'PUN-UNAUTH-01',
        ]);

        $response->assertStatus(403);
    }

    /**
     * TEST: Branch Admin cannot create employees in another branch.
     */
    public function test_branch_admin_cannot_create_employee_for_another_branch(): void
    {
        // Branch Admin A2 (Shivaji Nagar) tries to create employee for Branch A1 (FC Road) -> Blocked
        $response = $this->actingAs($this->branchAdminA1)->post('/employees', [
            'name' => 'Injected Operator',
            'email' => 'injected.op@example.com',
            'mobile' => '9900990099',
            'password' => 'Password@123',
            'branch_id' => $this->branchA1->id, // Attempting to create in Branch A1
            'designation' => 'Executive',
        ]);

        $response->assertStatus(403);
    }

    /**
     * TEST: CSV / Formula Injection Defense in ImportExportService.
     */
    public function test_csv_formula_injection_is_defended(): void
    {
        $dangerousInput1 = '=cmd|\' /C calc\'!A0';
        $dangerousInput2 = '+SUM(1,2)';
        $dangerousInput3 = '-10+20';
        $dangerousInput4 = '@HYPERLINK("http://evil.com")';

        $this->assertEquals("'=cmd|' /C calc'!A0", ImportExportService::sanitizeCsvField($dangerousInput1));
        $this->assertEquals("'+SUM(1,2)", ImportExportService::sanitizeCsvField($dangerousInput2));
        $this->assertEquals("'-10+20", ImportExportService::sanitizeCsvField($dangerousInput3));
        $this->assertEquals("'@HYPERLINK(\"http://evil.com\")", ImportExportService::sanitizeCsvField($dangerousInput4));

        $safeInput = 'Rajesh Patil';
        $this->assertEquals('Rajesh Patil', ImportExportService::sanitizeCsvField($safeInput));
    }

    /**
     * TEST: Public Citizen Portal IDOR is Blocked (Tampered token is rejected).
     */
    public function test_tampered_tracking_token_is_rejected(): void
    {
        $fakeToken = Str::random(64);
        $response = $this->get('/portal/status/' . $fakeToken);

        // Redirects back to tracking page with session error
        $response->assertRedirect('/track');
    }

    /**
     * TEST: Unauthenticated Guest cannot access internal ERP dashboard.
     */
    public function test_guest_cannot_access_internal_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * TEST: Security Headers are present on responses.
     */
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
