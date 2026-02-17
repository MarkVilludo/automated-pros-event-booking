<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Responses\TicketResponseDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * List tickets. Admin: all. Organizer: their events. Customer: all (read).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 50);
        $paginator = $this->ticketService->list(
            $request->user()->id,
            $request->user()->role,
            $perPage
        );
        return $this->success($paginator, 'Tickets retrieved');
    }

    /**
     * Store ticket (Admin, Organizer for their event). Response: TicketResponseDto with HTTP 201.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->store($request->validated());
        $responseDto = $this->ticketService->toResponseDto($ticket);
        return ApiResponse::success($responseDto->toArray(), 'Ticket created', 201);
    }

    /**
     * Show ticket (all roles).
     */
    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);
        $cached = $this->ticketService->find($ticket->id) ?? $ticket->load('event:id,title,date,location');
        return $this->success(
            TicketResponseDto::fromModel($cached)->toArray(),
            'Ticket retrieved'
        );
    }

    /**
     * Update ticket (Admin or Organizer owner of event).
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('update', $ticket);
        $ticket = $this->ticketService->update($ticket, $request->validated());
        return $this->success(
            TicketResponseDto::fromModel($ticket)->toArray(),
            'Ticket updated'
        );
    }

    /**
     * Delete ticket (Admin or Organizer owner of event).
     */
    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);
        $this->ticketService->delete($ticket);
        return $this->success(null, 'Ticket deleted');
    }
}
