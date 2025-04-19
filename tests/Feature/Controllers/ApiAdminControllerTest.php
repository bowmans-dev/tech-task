<?php

namespace Tests\Feature\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;



class ApiAdminControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test setup - authenticate as admin using JWT.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user and generate a JWT token
        $this->admin = Admin::factory()->create();
        $this->adminToken = JWTAuth::fromUser($this->admin);
    }

    public function test_store_creates_user()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'tim.doe@example.com',
            'phone' => '123456789',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Use the admin instance and token created in the setUp method
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->postJson('/api/users', $data);

        // Assertions
        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User created successfully!',
                    'data' => [
                        'first_name' => 'John',
                        'email' => 'tim.doe@example.com',
                    ],
                ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'email' => 'tim.doe@example.com',
        ]);
    }

    public function test_store_fails_without_authentication()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'tim.doe@example.com',
            'phone' => '123456789',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Attempt to create user without authentication
        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(401)
                ->assertJsonPath('message', 'Unauthenticated.');
    }

    /**
     * Test unauthenticated or unauthorized access to API admin endpoints returns 403 Forbidden.
     */
    public function test_unauthenticated_or_unauthorized_access_returns_unauthorized_for_api_admin()
    {
        // Do not use the admin token or pass an Authorization header
        $response = $this->getJson('/api/users'); // Attempt unauthenticated access

        // Assert that the response returns a 401 Unauthorized status code
        $response->assertStatus(401)
                ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_admin_can_access_users_endpoint()
    {
        // Use the admin token in the Authorization header
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/users');

        // Assert successful access and response structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data', // Ensure the data (users) structure is included
                ]);

        // Assert the response message is correct
        $this->assertEquals('Users retrieved successfully.', $response->json('message'));

        // Assert the data returned is paginated (depending on implementation)
        $this->assertTrue(array_key_exists('data', $response->json()));
    }

    /**
     * Test the store method fails with invalid data.
     */
    public function test_store_user_fails_with_invalid_data()
    {
        $data = [
            'first_name' => '',                // Missing required first name
            'last_name'  => 'Doe',
            'gender'     => 'male',
            'country'    => 'United Kingdom',
            'email'      => 'invalid-email',  // Invalid email format
            'phone'      => 'not-a-phone-number', // Invalid phone format
            'password'   => 'short',          // Invalid password (too short)
            'password_confirmation' => 'short', // Password confirmation does not meet length requirement
        ];

        // Use the admin token from setUp() for authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->postJson('/api/users', $data);

        // Assert the response returns a 422 Unprocessable Entity status code
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name', 'email', 'phone', 'password']); // Assert validation errors
    }

    /**
     * Test the update method.
     */
    public function test_update_user()
    {
        // Create a user for the update operation
        $user = User::factory()->create();

        // Prepare updated data
        $data = [
            'first_name' => 'Updated Name',
            'last_name' => $user->last_name,
            'gender' => $user->gender,
            'email' => $user->email,
            'phone' => '9876543210',
            'country' => $user->country,
        ];

        // Authenticate the admin making the request
        $admin = Admin::factory()->create();
        $adminToken = JWTAuth::fromUser($admin);

        // Perform PATCH request with the admin's token
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
                        ->patchJson('/api/users/' . $user->id, $data);

        // Assert response structure and status
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'User updated successfully!',
                    'data' => [
                        'first_name' => 'Updated Name',
                    ],
                ]);

        // Verify the updated user in the database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated Name',
            'phone' => '9876543210',
        ]);
    }

    /**
     * Test the update method fails with invalid data.
     */
    public function test_update_user_fails_with_invalid_data()
    {
        // Create a user for the update operation
        $user = User::factory()->create();

        // Prepare invalid data
        $data = [
            'first_name' => '',                // Missing required first name
            'last_name'  => 'Doe',
            'gender'     => 'male',
            'country'    => 'United Kingdom',
            'email'      => 'invalid-email',  // Invalid email format
            'phone'      => 'not-a-phone-number', // Invalid phone format (does not meet the regex rules)
        ];

        // Use the admin token from setUp() for authentication
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->patchJson('/api/users/' . $user->id, $data);

        // Assert the response returns a 422 Unprocessable Entity status code
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['first_name', 'email', 'phone']); // Assert validation errors
    }

    /**
     * Test the destroy method deletes a user.
     */
    public function destroy(User $user)
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();
            Log::info('Authenticated admin:', ['admin' => $admin]);

            if (!$admin) {
                return $this->errorResponse('Unauthorized access.', 401);
            }

            Log::info('Admin performing user deletion:', ['admin_id' => $admin->id, 'user_id' => $user->id]);

            $this->userService->deleteUser($user, 'admin');

            return $this->successResponse('User deleted successfully!', 200);
        } catch (JWTException $e) {
            Log::error('JWT Token error:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Invalid or expired token.', 401);
        } catch (\Exception $e) {
            Log::error('Error deleting user:', ['message' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete user.', 500, $e->getMessage());
        }
    }

//     /**
//      * Test the show method.
//      */
//     public function test_show_user_details()
//     {
//         $user = User::factory()->create();

//         $response = $this->getJson('/api/users/' . $user->id); // API route

//         $response->assertStatus(200)
//             ->assertJson([ 
//                 'message' => 'User details retrieved successfully.',
//                 'data' => [
//                     'id' => $user->id,
//                     'first_name' => $user->first_name,
//                     'email' => $user->email,
//                 ],
//             ]);
//     }

    /**
     * Test the index method.
     */
    public function test_can_retrieve_users_with_correct_pagination_limit()
    {
        User::factory()->count(25)->create(); // Ensure more than 10 users exist

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                        ->getJson('/api/users/' );

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Users retrieved successfully.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'current_page',
                    'data' => [ // Check data structure
                        '*' => ['id', 'first_name', 'last_name', 'email'],
                    ],
                ],
            ]);

        // Explicitly check pagination limit
        $response->assertJsonCount(10, 'data.data'); // Ensure only 10 users are returned per page
    }

/**
 * Test the filter method with JWT authentication.
 */
public function test_filter_returns_paginated_filtered_results()
{
    // Create enough users to exceed one page of results
    User::factory()->count(11)->create(['first_name' => 'Jerri']); // 11 matching users for pagination testing
    $nonMatchingUser = User::factory()->create(['first_name' => 'Jane']); // Non-matching user

    // Use the admin token from setUp() for authentication
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
                  ->getJson('/api/users/filter?search=Jerri'); // Pass search explicitly in query string

    // Assertions for filtered results
    $response->assertStatus(200)
             ->assertJsonPath('message', 'Users filtered successfully.') // Ensure success message
             ->assertJsonFragment(['first_name' => 'Jerri']) // Ensure matching user is included
             ->assertJsonMissing(['first_name' => 'Jane']) // Ensure non-matching user is excluded
             ->assertJsonStructure([
                 'message',
                 'data' => [
                     'current_page',
                     'data' => [ // Check data structure
                         '*' => ['id', 'first_name', 'last_name', 'email'],
                     ],
                 ],
             ]);

    // Explicitly check pagination limit for filtered results
    $response->assertJsonCount(10, 'data.data'); // Ensure only 10 users are returned in the first page
}
}
