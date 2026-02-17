<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {
            Payment::factory()->create([
                'booking_id' => $booking->id,
                'amount' => $booking->ticket->price * $booking->quantity,
                'status' => fake()->randomElement(PaymentStatus::cases()),
            ]);
        }
    }
}
