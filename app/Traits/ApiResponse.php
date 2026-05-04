<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
            'meta'    => array_merge([
                'timestamp'   => now()->toIso8601String(),
                'status_code' => $statusCode,
            ], $meta),
        ], $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(
        string $message = 'Something went wrong',
        int $statusCode = 400,
        mixed $errors = null,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'meta'    => array_merge([
                'timestamp'   => now()->toIso8601String(),
                'status_code' => $statusCode,
            ], $meta),
        ], $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse(
        $paginator,
        string $message = 'Data fetched successfully'
    ): JsonResponse {
        return $this->successResponse(
            data: $paginator->items(),
            message: $message,
            meta: [
                'pagination' => [
                    'total'         => $paginator->total(),
                    'count'         => $paginator->count(),
                    'per_page'      => $paginator->perPage(),
                    'current_page'  => $paginator->currentPage(),
                    'total_pages'   => $paginator->lastPage(),
                    'has_more_pages'=> $paginator->hasMorePages(),
                ],
            ]
        );
    }
}