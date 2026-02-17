<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->booking->ticket->event;
        $ticket = $this->booking->ticket;
        $total = $ticket->price * $this->booking->quantity;

        return (new MailMessage)
            ->subject('Booking confirmed – ' . $event->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your booking has been confirmed.')
            ->line('Event: ' . $event->title)
            ->line('Date: ' . $event->date->format('F j, Y \a\t g:i A'))
            ->line('Location: ' . $event->location)
            ->line('Ticket: ' . $ticket->type . ' x ' . $this->booking->quantity)
            ->line('Total: $' . number_format((float) $total, 2))
            ->line('Thank you for your booking!');
    }
}
