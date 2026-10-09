<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Customer;
use App\Models\OtpVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Application $application;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->application = Application::first();
        $this->customer = $this->application->customer;
    }

    public function test_tracking_portal_page_is_accessible_to_public(): void
    {
        $response = $this->get('/track');
        $response->assertStatus(200);
        $response->assertSee('Track Service Application');
    }

    public function test_request_otp_with_valid_credentials_creates_hashed_otp(): void
    {
        $response = $this->postJson('/api/portal/request-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('otp_verifications', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
            'is_verified' => false,
        ]);
    }

    public function test_request_otp_with_non_matching_mobile_fails_safely(): void
    {
        $response = $this->postJson('/api/portal/request-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => '9999900000', // incorrect mobile
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    public function test_verify_otp_with_correct_code_generates_token_and_displays_status(): void
    {
        // 1. Request OTP
        $this->postJson('/api/portal/request-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
        ]);

        $verification = OtpVerification::where('application_number', $this->application->application_number)->latest()->first();
        $this->assertNotNull($verification);
        
        // Setup fixed known OTP hash for verification
        $knownOtp = '765432';
        $verification->update(['otp_hash' => hash('sha256', $knownOtp)]);

        // 2. Verify with correct OTP
        $verifyResponse = $this->postJson('/api/portal/verify-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
            'otp' => $knownOtp,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson(['success' => true]);

        $trackingToken = $verifyResponse->json('tracking_token');
        $this->assertNotEmpty($trackingToken);

        // 3. View status page using tracking token
        $statusResponse = $this->get('/portal/status/' . $trackingToken);
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee($this->application->application_number);
        $statusResponse->assertSee($this->customer->name);
        $statusResponse->assertSee($this->application->service->name);

        // Ensure ERP internals are NOT leaked to citizen
        $statusResponse->assertDontSee('Manage Branches');
        $statusResponse->assertDontSee('Audit Logs');
    }

    public function test_invalid_otp_is_rejected_and_increments_attempts(): void
    {
        $this->postJson('/api/portal/request-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
        ]);

        $verification = OtpVerification::where('application_number', $this->application->application_number)->latest()->first();
        $this->assertNotNull($verification);
        $verification->update(['otp_hash' => hash('sha256', '123456')]);

        $response = $this->postJson('/api/portal/verify-otp', [
            'application_number' => $this->application->application_number,
            'mobile' => $this->customer->mobile,
            'otp' => '999999', // wrong OTP
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => false]);

        $verification->refresh();
        $this->assertEquals(1, $verification->attempts);
    }
}
