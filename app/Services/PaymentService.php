<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use App\Enums\BookingStatus;

class PaymentService
{
    public function processMockPayment(Booking $booking, bool $forceSuccess = true): array
    {
        $amount = $booking->ticket->price * $booking->quantity;
        $success = $forceSuccess;

        $payment = $booking->payment ?? new Payment(['booking_id' => $booking->id]);
        $payment->amount = $amount;
        $payment->status = $success ? PaymentStatus::Success : PaymentStatus::Failed;
        $payment->save();

        if ($success) {
            $booking->update(['status' => BookingStatus::Confirmed]);
            $booking->load('user', 'ticket.event');
            $booking->user->notify(new BookingConfirmedNotification($booking));
        }

        return ['payment' => $payment, 'success' => $success];
    }

    public function simulateSuccess(Booking $booking): Payment
    {
        $result = $this->processMockPayment($booking, true);
        return $result['payment'];
    }

    public function simulateFailure(Booking $booking): Payment
    {
        $result = $this->processMockPayment($booking, false);
        return $result['payment'];
    }
}
