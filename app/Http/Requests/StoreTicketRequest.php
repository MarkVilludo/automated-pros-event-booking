<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()->can('create', \App\Models\Ticket::class)) {
            return false;
        }
        $eventId = $this->input('event_id');
        if (! $eventId) {
            return true; // validation will require event_id
        }
        $event = \App\Models\Event::find($eventId);
        if (! $event) {
            return true; // validation will fail exists:events,id
        }
        return $this->user()->role->value === 'admin' || (int) $event->created_by === (int) $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'type' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
