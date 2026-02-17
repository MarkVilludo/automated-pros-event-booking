<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Requests\CreateEventDto;
use App\DTOs\Requests\UpdateEventDto;
use App\DTOs\Responses\EventResponseDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\StoreTicketForEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use App\Services\EventService;
use App\Services\TicketService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private EventService $eventService,
        private TicketService $ticketService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50);
        $filters = [
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'location' => $request->get('location'),
        ];
        $paginator = $this->eventService->list(
            $request->user()->id,
            $request->user()->role,
            $perPage,
            $filters
        );
        return $this->success($paginator, 'Events retrieved');
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $dto = CreateEventDto::fromRequest($request);
        $event = $this->eventService->store($dto, $request->user()->id);
        $responseDto = $this->eventService->toResponseDto($event);
        return ApiResponse::success($responseDto->toArray(), 'Event created', 201);
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);
        $cached = $this->eventService->find($event->id) ?? $event->load('creator:id,name,email', 'tickets');
        return $this->success(
            EventResponseDto::fromModel($cached)->toArray(),
            'Event retrieved'
        );
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);
        $dto = UpdateEventDto::fromRequest($request);
        $event = $this->eventService->update($event, $dto);
        return $this->success(
            EventResponseDto::fromModel($event)->toArray(),
            'Event updated'
        );
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        $this->authorize('delete', $event);
        $this->eventService->delete($event);
        return $this->success(null, 'Event deleted');
    }

    public function storeTicket(StoreTicketForEventRequest $request, Event $event): JsonResponse
    {
        $data = array_merge($request->validated(), ['event_id' => $event->id]);
        $ticket = $this->ticketService->store($data);
        return ApiResponse::success($this->ticketService->toResponseDto($ticket)->toArray(), 'Ticket created', 201);
    }
}
