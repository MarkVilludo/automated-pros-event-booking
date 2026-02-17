<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');
        return $booking && (int) $booking->user_id === (int) $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'simulate_success' => ['sometimes', 'boolean'], // true = success, false = failure
        ];
    }
}
