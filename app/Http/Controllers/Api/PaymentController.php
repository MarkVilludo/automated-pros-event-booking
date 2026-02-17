<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    use RespondsWithJson;

    public function show(Payment $payment): JsonResponse
    {
        $user = request()->user();
        $booking = $payment->booking;
        $canView = $user->id === $booking->user_id
            || $user->role->value === 'admin'
            || (int) $booking->ticket->event->created_by === (int) $user->id;
        if (! $canView) {
            return ApiResponse::forbidden('Cannot view this payment.');
        }
        $payment->load('booking.ticket.event');
        return $this->success([
            'id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'amount' => (string) $payment->amount,
            'status' => $payment->status->value,
            'created_at' => $payment->created_at?->toIso8601String(),
        ], 'Payment retrieved');
    }
}
