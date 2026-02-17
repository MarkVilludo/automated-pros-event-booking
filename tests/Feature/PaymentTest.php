<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_succeeds_and_confirms_booking(): void
    {
        $customer = User::factory()->customer()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create([
            'event_id' => $event->id,
            'price' => 25.00,
            'quantity' => 100,
        ]);
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
            'status' => BookingStatus::Pending,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/bookings/{$booking->id}/payment", [
            'simulate_success' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'booking_id',
                    'amount',
                    'status',
                    'success',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'success' => true,
                ],
            ]);

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
    }

    public function test_payment_fails_when_simulate_success_false(): void
    {
        $customer = User::factory()->customer()->create();
        $booking = Booking::factory()->create([
            'user_id' => $customer->id,
            'status' => BookingStatus::Pending,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson("/api/bookings/{$booking->id}/payment", [
            'simulate_success' => false,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'failed',
                    'success' => false,
                ],
            ]);

        $booking->refresh();
        $this->assertSame(BookingStatus::Pending, $booking->status);
    }
}
