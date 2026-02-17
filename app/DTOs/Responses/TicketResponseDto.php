<?php

namespace App\DTOs\Responses;

use App\Models\Ticket;
use Illuminate\Contracts\Support\Arrayable;

final class TicketResponseDto implements Arrayable
{
    public function __construct(
        public int $id,
        public string $type,
        public string $price,
        public int $quantity,
        public int $event_id,
        public ?object $event = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Ticket $ticket): self
    {
        return new self(
            id: $ticket->id,
            type: $ticket->type,
            price: (string) $ticket->price,
            quantity: $ticket->quantity,
            event_id: $ticket->event_id,
            event: $ticket->relationLoaded('event') ? (object) $ticket->event->only('id', 'title', 'date', 'location') : null,
            created_at: $ticket->created_at?->toIso8601String(),
            updated_at: $ticket->updated_at?->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'event_id' => $this->event_id,
            'event' => $this->event,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($v) => $v !== null);
    }
}
