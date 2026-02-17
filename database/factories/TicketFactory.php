<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['VIP', 'Standard', 'Premium', 'General Admission', 'Early Bird'];

        return [
            'type' => fake()->randomElement($types),
            'price' => fake()->randomFloat(2, 10, 500),
            'quantity' => fake()->numberBetween(50, 1000),
            'event_id' => Event::factory(),
        ];
    }
}
