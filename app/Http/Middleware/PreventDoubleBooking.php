<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleBooking
{
    public function handle(Request $request, Closure $next): Response
    {
        $ticketId = $request->route('ticket')?->id ?? $request->route('ticket');
        if (! $ticketId) {
            return $next($request);
        }
        $ticketId = is_object($ticketId) ? (int) $ticketId->id : (int) $ticketId;
        $userId = $request->user()?->id;
        if (! $userId) {
            return $next($request);
        }
        $existing = Booking::where('user_id', $userId)
            ->where('ticket_id', $ticketId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        if ($existing) {
            return ApiResponse::error('You already have an active booking for this ticket.', 422);
        }
        return $next($request);
    }
}
