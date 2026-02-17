<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'status' => fake()->randomElement(PaymentStatus::cases()),
        ];
    }

    /**
     * Set amount from the related booking (ticket price * quantity).
     */
    public function withAmountFromBooking(): static
    {
        return $this->afterCreating(function (Payment $payment) {
            $payment->update([
                'amount' => $payment->booking->ticket->price * $payment->booking->quantity,
            ]);
        });
    }
}
