<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $tickets = Ticket::all();

        if ($customers->isEmpty() || $tickets->isEmpty()) {
            return;
        }

        $customersToBook = $customers->shuffle()->take(min(8, $customers->count()));

        foreach ($customersToBook as $customer) {
            $ticket = $tickets->random();
            $quantity = min(rand(1, 4), $ticket->quantity);
            Booking::factory()->create([
                'user_id' => $customer->id,
                'ticket_id' => $ticket->id,
                'quantity' => $quantity,
                'status' => fake()->randomElement(BookingStatus::cases()),
            ]);
        }

        // Create a few more random bookings
        Booking::factory()->count(5)->create();
    }
}
