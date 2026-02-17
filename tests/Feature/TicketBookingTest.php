<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_booking_succeeds_for_customer(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create(['created_by' => User::factory()->organizer()->create()->id]);
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10,
            'price' => 50.00,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'ticket_id',
                    'quantity',
                    'status',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'quantity' => 2,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);
    }

    public function test_ticket_booking_fails_when_quantity_exceeds_available(): void
    {
        $customer = User::factory()->customer()->create();
        $ticket = Ticket::factory()->create(['quantity' => 2]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/tickets/{$ticket->id}/bookings", [
            'quantity' => 5,
        ]);

        $response->assertStatus(422);
    }
}
