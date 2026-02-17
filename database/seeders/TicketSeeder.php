<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    private const TYPES = [
        'VIP' => [150, 500],
        'Premium' => [80, 200],
        'Standard' => [30, 80],
        'General Admission' => [15, 40],
        'Early Bird' => [10, 30],
    ];

    public function run(): void
    {
        $events = Event::orderBy('id')->get();
        if ($events->isEmpty()) {
            return;
        }

        $typeNames = array_keys(self::TYPES);
        $created = 0;
        $target = 15;

        foreach ($events as $event) {
            for ($i = 0; $i < 3 && $created < $target; $i++) {
                $type = $typeNames[($created + $event->id) % count($typeNames)];
                [$min, $max] = self::TYPES[$type];
                Ticket::firstOrCreate(
                    ['event_id' => $event->id, 'type' => $type],
                    [
                        'price' => rand($min, $max),
                        'quantity' => rand(50, 500),
                    ]
                );
                $created++;
            }
        }
    }
}
