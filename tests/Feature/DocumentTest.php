<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected User $userTenantA;
    protected User $userTenantB;
    protected Customer $customerTenantA;
    protected Application $applicationTenantA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->userTenantA = User::where('email', 'admin@mahaeseva.com')->first();
        $this->customerTenantA = Customer::where('tenant_id', $this->userTenantA->tenant_id)->first();
        $this->applicationTenantA = Application::where('tenant_id', $this->userTenantA->tenant_id)->first();

        // Create Tenant B user
        $tenantB = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Nagpur Kendra',
            'slug' => 'nagpur-kendra',
            'contact_name' => 'Nitin',
            'contact_email' => 'nitin@nagpur.com',
            'contact_mobile' => '9890000000',
            'status' => 'ACTIVE',
        ]);
        $this->userTenantB = User::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Nitin',
            'email' => 'nitin@nagpur.com',
            'mobile' => '9890000000',
            'password' => Hash::make('Password@123'),
            'status' => 'ACTIVE',
        ]);
        $ownerRole = Role::where('slug', 'BUSINESS_OWNER')->first();
        $this->userTenantB->roles()->attach($ownerRole->id);
    }

    public function test_document_upload_stores_in_private_vault_with_hash_and_metadata(): void
    {
        $file = UploadedFile::fake()->create('aadhaar_card.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->userTenantA)->post('/documents/upload', [
            'customer_id' => $this->customerTenantA->id,
            'application_id' => $this->applicationTenantA->id,
            'document_type_name' => 'Aadhaar Card Copy',
            'file' => $file,
        ]);

        $response->assertRedirect();

        $document = Document::where('document_type_name', 'Aadhaar Card Copy')->first();
        $this->assertNotNull($document);
        $this->assertEquals('application/pdf', $document->mime_type);
        $this->assertNotNull($document->file_hash);
        $this->assertEquals(64, strlen($document->file_hash)); // SHA-256
        $this->assertEquals('RECEIVED', $document->status);
    }

    public function test_authorized_user_can_download_document(): void
    {
        $file = UploadedFile::fake()->create('pan_form.pdf', 200, 'application/pdf');

        $this->actingAs($this->userTenantA)->post('/documents/upload', [
            'customer_id' => $this->customerTenantA->id,
            'application_id' => $this->applicationTenantA->id,
            'document_type_name' => 'PAN Application Form',
            'file' => $file,
        ]);

        $document = Document::where('document_type_name', 'PAN Application Form')->first();
        $this->assertNotNull($document);

        $response = $this->actingAs($this->userTenantA)->get("/documents/{$document->id}/download");
        $response->assertStatus(200);
    }

    public function test_unauthorized_cross_tenant_document_download_is_blocked(): void
    {
        $file = UploadedFile::fake()->create('confidential_income.pdf', 150, 'application/pdf');

        $this->actingAs($this->userTenantA)->post('/documents/upload', [
            'customer_id' => $this->customerTenantA->id,
            'application_id' => $this->applicationTenantA->id,
            'document_type_name' => 'Income Certificate Proof',
            'file' => $file,
        ]);

        $document = Document::where('document_type_name', 'Income Certificate Proof')->first();
        $this->assertNotNull($document);

        // Tenant B user attempts to download Tenant A's private document -> Blocked by Global Scope (404) or Policy (403)
        $response = $this->actingAs($this->userTenantB)->get("/documents/{$document->id}/download");
        $this->assertTrue(in_array($response->status(), [403, 404]), "Expected 403 or 404 but got {$response->status()}");
    }

    public function test_document_status_can_be_verified(): void
    {
        $file = UploadedFile::fake()->create('ration_card.pdf', 100, 'application/pdf');

        $this->actingAs($this->userTenantA)->post('/documents/upload', [
            'customer_id' => $this->customerTenantA->id,
            'application_id' => $this->applicationTenantA->id,
            'document_type_name' => 'Ration Card Proof',
            'file' => $file,
        ]);

        $document = Document::where('document_type_name', 'Ration Card Proof')->first();
        $this->assertNotNull($document);

        $response = $this->actingAs($this->userTenantA)->post("/documents/{$document->id}/status", [
            'status' => 'VERIFIED',
            'remarks' => 'Original verified by operator',
        ]);

        $response->assertRedirect();

        $document->refresh();
        $this->assertEquals('VERIFIED', $document->status);
        $this->assertEquals($this->userTenantA->id, $document->verified_by);
        $this->assertNotNull($document->verified_at);
    }
}
