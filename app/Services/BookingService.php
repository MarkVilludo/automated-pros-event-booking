<?php

namespace App\Services;

use App\DTOs\Responses\BookingResponseDto;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BookingService
{
    private const CACHE_TTL_SECONDS = 300;
    private const CACHE_PREFIX = 'bookings';
    private const LIST_VERSION_KEY = 'bookings_list_version';

    public function list(int $userId, UserRole $role, int $perPage = 15): LengthAwarePaginator
    {
        $version = Cache::get(self::LIST_VERSION_KEY, 0);
        $page = request()->get('page', 1);
        $cacheKey = self::CACHE_PREFIX . ".list.v{$version}.{$role->value}.{$userId}.{$perPage}.{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($userId, $role, $perPage) {
            $query = Booking::with('user:id,name,email', 'ticket.event:id,title,date', 'payment');
            if ($role === UserRole::Admin) {
                // all
            } elseif ($role === UserRole::Organizer) {
                $query->whereHas('ticket.event', fn ($q) => $q->where('created_by', $userId));
            } else {
                $query->where('user_id', $userId);
            }
            return $query->latest()->paginate($perPage);
        });
    }

    public function find(int $id): ?Booking
    {
        $cacheKey = self::CACHE_PREFIX . ".{$id}";
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($id) {
            return Booking::with('user:id,name,email', 'ticket.event:id,title,date,location', 'payment')->find($id);
        });
    }

    public function store(int $userId, array $data): Booking
    {
        $booking = Booking::create([
            'user_id' => $userId,
            'ticket_id' => $data['ticket_id'],
            'quantity' => $data['quantity'],
            'status' => $data['status'] ?? \App\Enums\BookingStatus::Pending,
        ]);
        $ticket = $booking->ticket;
        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $ticket->price * $booking->quantity,
            'status' => \App\Enums\PaymentStatus::Success,
        ]);
        $booking->load('user:id,name,email', 'ticket.event:id,title,date', 'payment');
        $this->bumpListVersion();
        return $booking;
    }

    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);
        $booking->load('user:id,name,email', 'ticket.event:id,title,date', 'payment');
        Cache::forget(self::CACHE_PREFIX . ".{$booking->id}");
        $this->bumpListVersion();
        return $booking;
    }

    public function delete(Booking $booking): void
    {
        $booking->delete();
        Cache::forget(self::CACHE_PREFIX . ".{$booking->id}");
        $this->bumpListVersion();
    }

    public function toResponseDto(Booking $booking): BookingResponseDto
    {
        return BookingResponseDto::fromModel($booking);
    }

    private function bumpListVersion(): void
    {
        Cache::put(self::LIST_VERSION_KEY, Cache::get(self::LIST_VERSION_KEY, 0) + 1);
    }
}
