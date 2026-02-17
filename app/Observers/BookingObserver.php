<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Notifications\BookingConfirmedNotification;

class BookingObserver
{
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status') && $booking->status === BookingStatus::Confirmed) {
            $booking->load('user', 'ticket.event');
            $booking->user->notify(new BookingConfirmedNotification($booking));
        }
    }
}
