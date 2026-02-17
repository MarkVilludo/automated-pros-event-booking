<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin || $user->role === UserRole::Organizer;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::Organizer) {
            return (int) $ticket->event->created_by === (int) $user->id;
        }
        return false;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }
}
