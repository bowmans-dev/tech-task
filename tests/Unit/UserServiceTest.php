<?php

namespace Tests\Unit;

use App\Models\User as UserModel;
use App\Domains\Core\Services\UserService;
use App\Domains\Core\DTOs\UserData;
use App\Domains\Core\Repositories\UserRepositoryInterface;
use App\Domains\Supporting\ImageUpload\ImageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use DatabaseTransactions;

    private $imageService;
    private $userRepository;
    private $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageService = app(ImageService::class);
        $this->userRepository = app(UserRepositoryInterface::class);

        $this->userService = new UserService(
            $this->imageService,
            $this->userRepository
        );
    }

    public function testCreateUser()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'gender' => 'Female',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => 'password123',
            'profile_picture' => null,
        ];

        $result = $this->userService->createUser($data);


        $this->assertDatabaseHas('users', ['email' => 'jane.doe@example.com']);
        $this->assertEquals($data['first_name'], $result['first_name']);
        $this->assertTrue(Hash::check('password123', $result['password'])); 
    }

    public function testUpdateUser()
    {
        $user = UserModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'gender' => 'Female',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => Hash::make('password123'),
            'profile_picture' => null,
        ]);

        $updateData = [
            'email' => 'john.updated@example.com',
        ];

        $result = $this->userService->updateUser($user, $updateData);

        $this->assertDatabaseHas('users', ['email' => 'john.updated@example.com']);
        $this->assertEquals('john.updated@example.com', $result['email']);
    }

    public function testDeleteUser()
    {
        $user = UserModel::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'gender' => 'Female',
            'phone' => '1234567890',
            'country' => 'United Kingdom',
            'password' => Hash::make('password123'),
            'profile_picture' => null,
        ]);

        $this->userService->deleteUser($user);

        $this->assertDatabaseMissing('users', ['email' => 'jane.doe@example.com']);
    }

    public function testListUsers()
    {
        UserModel::factory()->count(5)->create();

        $result = $this->userService->listUsers();

        $this->assertGreaterThan(4, $result->total());
    }

    public function testFilterUsers()
    {
        UserModel::factory()->create(['first_name' => 'Gyrald']);
        UserModel::factory()->create(['first_name' => 'John']);

        $result = $this->userService->filterUsers('Gyrald');

        $this->assertCount(1, $result);
        $this->assertEquals('Gyrald', $result->first()->first_name);
    }
}