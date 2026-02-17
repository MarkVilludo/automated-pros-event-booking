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

    private const CACHE_TTL_SECONDS = 300;
    private const FREQUENT_LIST_TTL_SECONDS = 600;
    private const CACHE_PREFIX = 'events';
    private const LIST_VERSION_KEY = 'events_list_version';
    private const FREQUENT_LIST_KEY = 'events.list.frequent';

    public function listFrequentlyAccessed(int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = self::FREQUENT_LIST_KEY . '.' . $perPage;

        return Cache::remember($cacheKey, self::FREQUENT_LIST_TTL_SECONDS, function () use ($perPage) {
            return Event::query()
                ->with('creator:id,name,email', 'tickets')
                ->latest()
                ->paginate($perPage);
        });
    }

    public function list(int $userId, UserRole $role, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $version = Cache::get(self::LIST_VERSION_KEY, 0);
        $page = request()->get('page', 1);
        $cacheKey = self::CACHE_PREFIX . '.list.v' . $version . '.' . $role->value . '.' . $userId . '.' . $perPage . '.' . $page . '.' . md5(json_encode($filters));

        $hasFilters = ! empty(array_filter($filters ?? []));
        if (! $hasFilters && (int) request()->get('page', 1) === 1 && $role !== UserRole::Organizer) {
            return $this->listFrequentlyAccessed($perPage);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($userId, $role, $perPage, $filters) {
            $query = Event::query()->with('creator:id,name,email', 'tickets');
            if ($role === UserRole::Organizer) {
                $query->where('created_by', $userId);
            }
            $query->searchByTitle($filters['search'] ?? null);
            $query->filterByDate([
                'from' => $filters['date_from'] ?? null,
                'to' => $filters['date_to'] ?? null,
            ]);
            if (! empty($filters['location'])) {
                $query->where('location', 'like', '%' . $filters['location'] . '%');
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
        $this->forgetFrequentListCache();
    }

    private function forgetFrequentListCache(): void
    {
        foreach ([15, 20, 30, 50] as $perPage) {
            $this->forgetKey(self::FREQUENT_LIST_KEY . '.' . $perPage);
        }
    }
}
