<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Maha E-Seva');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mahaeseva.com',
            'password' => 'Password@123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@mahaeseva.com',
            'password' => 'WrongPassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_is_locked_out_after_5_failed_attempts(): void
    {
        $user = User::where('email', 'employee@mahaeseva.com')->first();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'employee@mahaeseva.com',
                'password' => 'WrongPassword',
            ]);
        }

        $user->refresh();
        $this->assertEquals('LOCKED', $user->status);
        $this->assertNotNull($user->locked_until);

        // Next login attempt is rejected due to account lockout
        $response = $this->post('/login', [
            'email' => 'employee@mahaeseva.com',
            'password' => 'Password@123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::where('email', 'admin@mahaeseva.com')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
