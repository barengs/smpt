<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration
     *
     * @return void
     */
    public function test_user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
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
                    'name' => 'Test User',
                    'email' => 'test@example.com'
                ]
            ]);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    /**
     * Test registration with invalid data
     *
     * @return void
     */
    public function test_user_cannot_register_with_invalid_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * Test registration with existing email
     *
     * @return void
     */
    public function test_user_cannot_register_with_existing_email()
    {
        // Create a user first
        User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error'
            ]);
    }
}
