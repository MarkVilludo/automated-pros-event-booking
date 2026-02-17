<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_and_returns_token(): void
    {
        User::factory()->customer()->create(['email' => 'customer@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => ['email' => 'customer@example.com'],
                    'token_type' => 'Bearer',
                ],
            ]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->customer()->create(['email' => 'customer@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }
}
