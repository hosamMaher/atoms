<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait BaseResponse {
    public function success($data = null, $message = 'success', $code = 200): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public function error($message = 'error', $code = 400, $errors = null): JsonResponse {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}

