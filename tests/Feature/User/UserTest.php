<?php

namespace Tests\Feature\User;

use App\Domain\Enums\RoleEnum;
use App\Models\User;
use App\Models\Wallet\Wallet;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testShouldCreateUser()
    {
        $data = [
            'username' => $this->faker->username,
            'password' => $this->faker->password(8),
            'name'     => $this->faker->firstName,
            'email'    => $this->faker->email
        ];

        $response = $this->post(route('user.create'), $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role_id'
                ],
                'access_token'
            ],
            'message'
        ]);

        $responseData = $response->decodeResponseJson();
        $user         = User::find($responseData['data']['user']['id']);
        $this->assertNotNull($user);
        $this->assertEquals($user->name, $responseData['data']['user']['name']);
        $this->assertEquals($user->username, $responseData['data']['user']['username']);
        $this->assertEquals($user->email, $responseData['data']['user']['email']);
        $this->assertEquals($user->role_id, RoleEnum::CUSTOMER);
    }

    public function testShouldNotCreateUserWithLoggedUser()
    {
        $user = User::factory()->create();
        $userCount = User::count();

        Sanctum::actingAs($user);
        $data = [
            'username' => $this->faker->username,
            'password' => $this->faker->password,
            'name'     => $this->faker->name,
            'email'    => $this->faker->email
        ];

        $response = $this->post(route('user.create'), $data);
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);

        $this->assertEquals($userCount, User::count());
    }

    public function testShouldValidateDataCreateUser()
    {
        $userCount = User::count();

        $data = [
            'username' => 'a',
            'password' => '1234567',
            'name'     => '@wa9duhuiaw!@#ouigads',
            'email'    => 'gabriel@'
        ];

        $response = $this->post(route('user.create'), $data);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [
                'username' => [],
                'password' => [],
                'name'     => [],
                'email'    => [],
            ],
            'message'
        ]);

        $this->assertEquals($userCount, User::count());
    }

    public function testShouldValidateUniqueFieldsCreateUser()
    {
        $user = User::factory()->create();
        $userCount = User::count();
        $data = [
            'username' => $user->username,
            'password' => $this->faker->password,
            'name'     => $this->faker->name,
            'email'    => $user->email
        ];

        $response = $this->post(route('user.create'), $data);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [
                'username' => [],
                'email'    => [],
            ],
            'message'
        ]);

        $this->assertEquals($userCount, User::count());
    }

}