<?php

namespace App\DTOs\Requests;

use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateEventDto
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $date,
        public string $location,
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $v = $request->validated();
        return new self(
            title: $v['title'],
            description: $v['description'] ?? null,
            date: $v['date'],
            location: $v['location'],
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'location' => $this->location,
        ];
    }
}
