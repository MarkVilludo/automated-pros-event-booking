<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            $types = ['VIP', 'Standard', 'Premium', 'General Admission'];
            foreach (array_slice($types, 0, rand(2, 4)) as $type) {
                Ticket::factory()->create([
                    'event_id' => $event->id,
                    'type' => $type,
                    'price' => match ($type) {
                        'VIP' => rand(150, 500),
                        'Premium' => rand(80, 200),
                        'Standard' => rand(30, 80),
                        default => rand(15, 40),
                    },
                    'quantity' => rand(50, 500),
                ]);
            }
        }
    }
}
