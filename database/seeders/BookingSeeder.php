<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->pluck('id')->all();
        $tickets = Ticket::with('event')->get();
        if (empty($customers) || $tickets->isEmpty()) {
            return;
        }

        $target = 20;
        $created = 0;
        $attempts = 0;
        $maxAttempts = $target * 20;

        while ($created < $target && $attempts < $maxAttempts) {
            $attempts++;
            $customerId = $customers[array_rand($customers)];
            $ticket = $tickets->random();
            $quantity = min(rand(1, 3), (int) $ticket->quantity);
            if ($quantity < 1) {
                continue;
            }
            $exists = Booking::where('user_id', $customerId)
                ->where('ticket_id', $ticket->id)
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
                ->exists();
            if ($exists) {
                continue;
            }
            Booking::create([
                'user_id' => $customerId,
                'ticket_id' => $ticket->id,
                'quantity' => $quantity,
                'status' => fake()->randomElement(BookingStatus::cases()),
            ]);
            $created++;
        }
    }
}
