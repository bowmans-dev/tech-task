<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

trait ApiResponseTrait
{
    public function successResponse($message = 'Success', $statusCode = 200, $data = null, $token = null)
    {
        // Automatically fetch token if not explicitly provided
        if (!$token && auth()->check()) {
            $token = JWTAuth::getToken(); // Use JWTAuth to retrieve the token
        }
    
        $response = [
            'message' => $message,
        ];
    
        // Include the token only if it's provided
        if ($token) {
            $response['token'] = $token;
        }
    
        // Include data only if provided
        if ($data) {
            $response['data'] = $data;
        }
    
        return response()->json($response, $statusCode);
    }

    
    public function errorResponse($message = 'Error', $statusCode = 500, $errorDetails = null)
    {
        return response()->json([
            'message' => $message,
            'error' => $errorDetails,
        ], $statusCode);
    }
}
