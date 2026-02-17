<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_creation_succeeds_as_organizer(): void
    {
        $organizer = User::factory()->organizer()->create();

        Sanctum::actingAs($organizer);

        $payload = [
            'title' => 'Summer Concert 2025',
            'description' => 'Annual summer concert.',
            'date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'location' => 'Central Park',
        ];

        $response = $this->postJson('/api/events', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'date',
                    'location',
                    'created_by',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Summer Concert 2025',
                    'location' => 'Central Park',
                ],
            ]);

        $this->assertDatabaseHas('events', ['title' => 'Summer Concert 2025']);
    }

    public function test_event_creation_forbidden_as_customer(): void
    {
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/events', [
            'title' => 'My Event',
            'date' => now()->addDay()->format('Y-m-d'),
            'location' => 'Somewhere',
        ]);

        $response->assertStatus(403);
    }
}
