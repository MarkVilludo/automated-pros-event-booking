<?php

namespace App\DTOs\Responses;

use App\Models\Booking;
use Illuminate\Contracts\Support\Arrayable;

final class BookingResponseDto implements Arrayable
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $ticket_id,
        public int $quantity,
        public string $status,
        public ?object $user = null,
        public ?object $ticket = null,
        public ?object $payment = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Booking $booking): self
    {
        return new self(
            id: $booking->id,
            user_id: $booking->user_id,
            ticket_id: $booking->ticket_id,
            quantity: $booking->quantity,
            status: $booking->status->value,
            user: $booking->relationLoaded('user') ? (object) $booking->user->only('id', 'name', 'email') : null,
            ticket: $booking->relationLoaded('ticket') ? (object) array_merge($booking->ticket->only('id', 'type', 'price', 'event_id'), [
                'event' => $booking->ticket->relationLoaded('event') ? $booking->ticket->event->only('id', 'title', 'date') : null,
            ]) : null,
            payment: $booking->relationLoaded('payment') && $booking->payment ? (object) $booking->payment->only('id', 'amount', 'status') : null,
            created_at: $booking->created_at?->toIso8601String(),
            updated_at: $booking->updated_at?->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'ticket_id' => $this->ticket_id,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'user' => $this->user,
            'ticket' => $this->ticket,
            'payment' => $this->payment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($v) => $v !== null);
    }
}
