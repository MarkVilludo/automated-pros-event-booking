<?php

namespace App\Services;

use App\DTOs\Responses\TicketResponseDto;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Traits\InvalidatesCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TicketService
{
    use InvalidatesCache;

    private const CACHE_TTL_SECONDS = 300;
    private const CACHE_PREFIX = 'tickets';
    private const LIST_VERSION_KEY = 'tickets_list_version';

    public function list(int $userId, UserRole $role, int $perPage = 15): LengthAwarePaginator
    {
        $version = Cache::get(self::LIST_VERSION_KEY, 0);
        $page = request()->get('page', 1);
        $cacheKey = self::CACHE_PREFIX . ".list.v{$version}.{$role->value}.{$userId}.{$perPage}.{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($userId, $role, $perPage) {
            $query = Ticket::with('event:id,title,date,location,created_by');
            if ($role === UserRole::Organizer) {
                $query->whereHas('event', fn ($q) => $q->where('created_by', $userId));
            }
            return $query->latest()->paginate($perPage);
        });
    }

    public function find(int $id): ?Ticket
    {
        $cacheKey = self::CACHE_PREFIX . ".{$id}";
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn () => Ticket::with('event')->find($id));
    }

    public function store(array $data): Ticket
    {
        $ticket = Ticket::create($data);
        $ticket->load('event:id,title,date,location');
        $this->bumpListVersion();
        return $ticket;
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        $ticket->update($data);
        $ticket->load('event:id,title,date,location');
        $this->forgetKey(self::CACHE_PREFIX . ".{$ticket->id}");
        $this->bumpListVersion();
        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
        $this->forgetKey(self::CACHE_PREFIX . ".{$ticket->id}");
        $this->bumpListVersion();
    }

    public function toResponseDto(Ticket $ticket): TicketResponseDto
    {
        return TicketResponseDto::fromModel($ticket);
    }

    private function bumpListVersion(): void
    {
        Cache::put(self::LIST_VERSION_KEY, Cache::get(self::LIST_VERSION_KEY, 0) + 1);
    }
}
