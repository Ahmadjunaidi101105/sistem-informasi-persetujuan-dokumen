<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRolesAndPermissions();
    }

    // --- Register ---

    public function test_user_can_register_as_pemohon_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Pemohon',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'PT Test Company',
            'company_address' => 'Jl. Test No. 1, Jakarta',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test Pemohon',
            'company_name' => 'PT Test Company',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('pemohon'));
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $this->createPemohon(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'PT Another',
            'company_address' => 'Jl. Another',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_without_company_name(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_address' => 'Jl. Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_name']);
    }

    public function test_registration_fails_with_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'company_name' => 'PT Test',
            'company_address' => 'Jl. Test',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // --- Login ---

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->createPemohon([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['user', 'token', 'permissions'],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->createPemohon([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    // --- Profile ---

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createPemohon();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/user');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['user'],
            ]);
    }

    // --- Logout ---

    public function test_user_can_logout(): void
    {
        $user = $this->createPemohon();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    // --- Unauthenticated ---

    public function test_unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson('/api/v1/auth/user');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(401);
    }
}
