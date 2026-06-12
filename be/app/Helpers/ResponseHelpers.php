<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ResponseHelpers
{
    /**
     * Return success JSON response.
     */
    protected function successResponse($data = [], string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return error JSON response.
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
