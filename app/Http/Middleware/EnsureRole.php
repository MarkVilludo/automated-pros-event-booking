<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return ApiResponse::unauthorized();
        }

        $roleEnums = array_map(
            fn (string $r) => match ($r) {
                'admin' => UserRole::Admin,
                'organizer' => UserRole::Organizer,
                'customer' => UserRole::Customer,
                default => null,
            },
            $roles
        );

        $roleEnums = array_filter($roleEnums);

        if ($roleEnums === [] || ! in_array($request->user()->role, $roleEnums, true)) {
            return ApiResponse::forbidden('Insufficient permissions.');
        }

        return $next($request);
    }
}
