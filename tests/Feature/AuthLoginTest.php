<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for testing
        $this->user = User::factory()->create([
            'name' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test successful login with email
     *
     * @return void
     */
    public function test_user_can_login_with_email()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user'
            ])
            ->assertJson([
                'token_type' => 'bearer',
                'user' => [
                    'name' => 'testuser',
                    'email' => 'test@example.com'
                ]
            ]);
    }

    /**
     * Test successful login with name (username)
     *
     * @return void
     */
    public function test_user_can_login_with_name()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user'
            ])
            ->assertJson([
                'token_type' => 'bearer',
                'user' => [
                    'name' => 'testuser',
                    'email' => 'test@example.com'
                ]
            ]);
    }

    /**
     * Test login with invalid credentials
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);
    }

    /**
     * Test login with non-existent user
     *
     * @return void
     */
    public function test_user_cannot_login_with_non_existent_user()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'nonexistent',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);
    }

    /**
     * Test that the login field is required
     *
     * @return void
     */
    public function test_login_field_is_required()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test that the password field is required
     *
     * @return void
     */
    public function test_password_field_is_required()
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }
}
