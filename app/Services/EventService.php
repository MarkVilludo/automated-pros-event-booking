<?php

namespace App\Services;

use App\DTOs\Requests\CreateEventDto;
use App\DTOs\Requests\UpdateEventDto;
use App\DTOs\Responses\EventResponseDto;
use App\Enums\UserRole;
use App\Models\Event;
use App\Traits\InvalidatesCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class EventService
{
    use InvalidatesCache;

    private const CACHE_TTL_SECONDS = 300; // 5 minutes
    private const CACHE_PREFIX = 'events';
    private const LIST_VERSION_KEY = 'events_list_version';

    public function list(int $userId, UserRole $role, int $perPage = 15): LengthAwarePaginator
    {
        $version = Cache::get(self::LIST_VERSION_KEY, 0);
        $page = request()->get('page', 1);
        $cacheKey = self::CACHE_PREFIX . ".list.v{$version}.{$role->value}.{$userId}.{$perPage}.{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($userId, $role, $perPage) {
            $query = Event::with('creator:id,name,email', 'tickets');
            if ($role === UserRole::Organizer) {
                $query->where('created_by', $userId);
            }
            return $query->latest()->paginate($perPage);
        });
    }

    public function find(int $id): ?Event
    {
        $cacheKey = self::CACHE_PREFIX . ".{$id}";
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, fn () => Event::with('creator:id,name,email', 'tickets')->find($id));
    }

    public function store(CreateEventDto $dto, int $userId): Event
    {
        $event = Event::create([...$dto->toArray(), 'created_by' => $userId]);
        $event->load('creator:id,name,email', 'tickets');
        $this->bumpListVersion();
        return $event;
    }

    public function update(Event $event, UpdateEventDto $dto): Event
    {
        $event->update($dto->toArray());
        $event->load('creator:id,name,email', 'tickets');
        $this->forgetKey(self::CACHE_PREFIX . ".{$event->id}");
        $this->bumpListVersion();
        return $event;
    }

    public function delete(Event $event): void
    {
        $event->delete();
        $this->forgetKey(self::CACHE_PREFIX . ".{$event->id}");
        $this->bumpListVersion();
    }

    public function toResponseDto(Event $event): EventResponseDto
    {
        return EventResponseDto::fromModel($event);
    }

    private function bumpListVersion(): void
    {
        Cache::put(self::LIST_VERSION_KEY, Cache::get(self::LIST_VERSION_KEY, 0) + 1);
    }
}
