<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public function errorResponse($message = 'Error', $statusCode = 500, $errorDetails = null)
    {
        return response()->json([
            'message' => $message,
            'error' => $errorDetails,
        ], $statusCode);
    }
}
