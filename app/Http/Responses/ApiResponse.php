<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

class ApiResponse
{
    /**
     * Success response (200 OK).
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = self::normalizeData($data);
        }

        return response()->json($payload, $status);
    }

    /**
     * Created response (201).
     */
    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * No content (204).
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Error response (4xx/5xx).
     */
    public static function error(
        string $message = 'An error occurred',
        int $status = 400,
        ?array $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    /**
     * Validation error (422).
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Unauthorized (401).
     */
    public static function unauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Forbidden (403).
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Not found (404).
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Normalize data for consistent JSON (paginator, resource, etc.).
     */
    protected static function normalizeData(mixed $data): mixed
    {
        if ($data instanceof ResourceCollection) {
            return $data->response()->getData(true)['data'] ?? $data->response()->getData(true);
        }

        if ($data instanceof JsonResource) {
            return $data->response()->getData(true)['data'] ?? $data->response()->getData(true);
        }

        if ($data instanceof AbstractPaginator || $data instanceof AbstractCursorPaginator) {
            $items = $data->items();
            $mapped = array_map(fn ($item) => $item instanceof Arrayable ? $item->toArray() : $item, $items);
            return [
                'data' => $mapped,
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
            ];
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof Collection) {
            return $data->toArray();
        }

        return $data;
    }
}
