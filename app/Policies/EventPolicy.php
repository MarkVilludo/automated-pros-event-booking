<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Admin: all. Organizer: own. Customer: view only (index, show).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin || $user->role === UserRole::Organizer;
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }
        if ($user->role === UserRole::Organizer) {
            return (int) $event->created_by === (int) $user->id;
        }
        return false;
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
