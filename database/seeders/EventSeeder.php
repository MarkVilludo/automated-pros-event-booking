<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizers = User::where('role', 'organizer')->get();

        if ($organizers->isEmpty()) {
            Event::factory()->count(5)->create();
            return;
        }

        foreach ($organizers as $organizer) {
            Event::factory()
                ->count(rand(1, 3))
                ->create(['created_by' => $organizer->id]);
        }

        // Ensure at least 5 events exist
        if (Event::count() < 5) {
            Event::factory()
                ->count(5 - Event::count())
                ->create(['created_by' => $organizers->random()->id]);
        }
    }
}
