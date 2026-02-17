<?php

namespace App\DTOs\Responses;

use App\Models\Event;
use Illuminate\Contracts\Support\Arrayable;

final class EventResponseDto implements Arrayable
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $date,
        public string $location,
        public int $created_by,
        public ?object $creator = null,
        public ?array $tickets = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {}

    public static function fromModel(Event $event): self
    {
        return new self(
            id: $event->id,
            title: $event->title,
            description: $event->description,
            date: $event->date->toIso8601String(),
            location: $event->location,
            created_by: $event->created_by,
            creator: $event->relationLoaded('creator') ? (object) $event->creator->only('id', 'name', 'email') : null,
            tickets: $event->relationLoaded('tickets') ? $event->tickets->toArray() : null,
            created_at: $event->created_at?->toIso8601String(),
            updated_at: $event->updated_at?->toIso8601String(),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'location' => $this->location,
            'created_by' => $this->created_by,
            'creator' => $this->creator,
            'tickets' => $this->tickets,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($v) => $v !== null);
    }
}
