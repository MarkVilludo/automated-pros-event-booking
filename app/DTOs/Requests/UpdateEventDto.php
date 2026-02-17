<?php

namespace App\DTOs\Requests;

use Illuminate\Foundation\Http\FormRequest;

final readonly class UpdateEventDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $date = null,
        public ?string $location = null,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $v = $request->validated();
        return new self(
            title: $v['title'] ?? null,
            description: array_key_exists('description', $v) ? $v['description'] : null,
            date: $v['date'] ?? null,
            location: $v['location'] ?? null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'location' => $this->location,
        ], fn ($v) => $v !== null);
    }
}
