<?php

namespace Tests\Unit;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    use ApiResponseTrait;

    public function test_success_response_returns_expected_structure()
    {
        // Arrange
        $data = ['key' => 'value'];
        $message = 'Operation successful';
        $statusCode = 201;

        // Act
        $response = $this->successResponse($message, $statusCode, $data);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals([
            'message' => $message,
            'data' => $data,
        ], $response->getData(true)); // Get response data as an array
    }

    public function test_error_response_returns_expected_structure()
    {
        // Arrange
        $message = 'An error occurred';
        $statusCode = 400;
        $errorDetails = ['error_key' => 'error_value'];

        // Act
        $response = $this->errorResponse($message, $statusCode, $errorDetails);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($statusCode, $response->getStatusCode());
        $this->assertEquals([
            'message' => $message,
            'error' => $errorDetails,
        ], $response->getData(true)); // Get response data as an array
    }

    public function test_success_response_with_defaults()
    {
        // Act
        $response = $this->successResponse();
        
        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode()); // Default status code
        $this->assertEquals([
            'message' => 'Success', // Default message         // Default data
        ], $response->getData(true));
    }

    public function test_error_response_with_defaults()
    {
        // Act
        $response = $this->errorResponse();

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode()); // Default status code
        $this->assertEquals([
            'message' => 'Error', // Default message
            'error' => null,      // Default error details
        ], $response->getData(true));
    }
}
