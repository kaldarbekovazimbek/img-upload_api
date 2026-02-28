<?php

namespace App\Http\Responses;

use App\Enums\ApiCode;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'code'    => null,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error(ApiCode $code, string $message, int $status = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}
