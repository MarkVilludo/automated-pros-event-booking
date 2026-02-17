<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizerIds = User::where('role', 'organizer')->pluck('id')->all();
        if (count($organizerIds) < 1) {
            Event::factory()->count(5)->create();
            return;
        }

        $events = [
            ['title' => 'Summer Concert 2025', 'description' => 'Annual summer concert.', 'date' => now()->addDays(30), 'location' => 'Central Park'],
            ['title' => 'Tech Conference', 'description' => 'Developer conference.', 'date' => now()->addDays(45), 'location' => 'Convention Center'],
            ['title' => 'Food Festival', 'description' => 'Local food and drinks.', 'date' => now()->addDays(60), 'location' => 'Downtown Square'],
            ['title' => 'Comedy Night', 'description' => 'Stand-up comedy show.', 'date' => now()->addDays(14), 'location' => 'City Theater'],
            ['title' => 'Charity Run', 'description' => '5K charity run.', 'date' => now()->addDays(90), 'location' => 'Riverside Trail'],
        ];

        foreach ($events as $index => $attrs) {
            Event::firstOrCreate(
                ['title' => $attrs['title']],
                array_merge($attrs, [
                    'created_by' => $organizerIds[$index % count($organizerIds)],
                ])
            );
        }

        if (Event::count() < 5) {
            Event::factory()
                ->count(5 - Event::count())
                ->create(['created_by' => $organizerIds[0]]);
        }
    }
}
