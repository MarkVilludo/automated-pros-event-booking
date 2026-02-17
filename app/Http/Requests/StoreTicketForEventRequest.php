<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketForEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        if (! $event instanceof Event) {
            return false;
        }
        return $this->user()->can('update', $event);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $event = $this->route('event');
        $eventId = $event instanceof Event ? $event->id : 0;

        return [
            'type' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tickets')->where('event_id', $eventId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.unique' => 'A ticket with this type already exists for this event.',
        ];
    }
}
