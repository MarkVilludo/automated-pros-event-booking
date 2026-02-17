<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Responses\BookingResponseDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\BookingService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * List bookings. Admin: all. Organizer: their events. Customer: own only.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50);
        $paginator = $this->bookingService->list(
            $request->user()->id,
            $request->user()->role,
            $perPage
        );
        return $this->success($paginator, 'Bookings retrieved');
    }

    /**
     * Create booking. Response: BookingResponseDto with HTTP 201.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $ticket = Ticket::findOrFail($data['ticket_id']);
        if ($ticket->quantity < ($data['quantity'] ?? 1)) {
            return ApiResponse::error('Insufficient ticket quantity available.', 422);
        }
        $booking = $this->bookingService->store($request->user()->id, $data);
        $responseDto = $this->bookingService->toResponseDto($booking);
        return ApiResponse::success($responseDto->toArray(), 'Booking created', 201);
    }

    /**
     * Show booking (Admin, Organizer for their event, or owning Customer).
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);
        $cached = $this->bookingService->find($booking->id) ?? $booking->load('user:id,name,email', 'ticket.event:id,title,date,location', 'payment');
        return $this->success(
            BookingResponseDto::fromModel($cached)->toArray(),
            'Booking retrieved'
        );
    }

    /**
     * Update booking (e.g. status).
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);
        $booking = $this->bookingService->update($booking, $request->validated());
        return $this->success(
            BookingResponseDto::fromModel($booking)->toArray(),
            'Booking updated'
        );
    }

    /**
     * Delete/cancel booking.
     */
    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('delete', $booking);
        $this->bookingService->delete($booking);
        return $this->success(null, 'Booking deleted');
    }
}
