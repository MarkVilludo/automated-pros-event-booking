<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Admin: all. Organizer: bookings for their events. Customer: own only.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::Organizer) {
            return (int) $booking->ticket->event->created_by === (int) $user->id;
        }
        return (int) $booking->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true; // Customer can book; admin/organizer can create on behalf
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::Organizer) {
            return (int) $booking->ticket->event->created_by === (int) $user->id;
        }
        return (int) $booking->user_id === (int) $user->id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $this->update($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::Organizer) {
            return false;
        }
        return (int) $booking->user_id === (int) $user->id;
    }
}
