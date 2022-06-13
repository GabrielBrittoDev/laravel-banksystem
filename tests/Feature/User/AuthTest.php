<?php

namespace Tests\Feature\User;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testShouldLogin()
    {
        $password = $this->faker->password(8);
        $user = User::factory()->create([
            'password' => bcrypt($password)
        ]);

        $data = [
            'username' => $user->username,
            'password' => $password
        ];

        $response = $this->post(route('auth.login'), $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'user' => [],
                'access_token'
            ],
            'message'
        ]);
    }

    public function testShouldNotLogin()
    {
        $password = $this->faker->password(8);
        $user = User::factory()->create([
            'password' => bcrypt($password)
        ]);

        $data = [
            'username' => $user->username,
            'password' => $password . '123'
        ];

        $response = $this->post(route('auth.login'), $data);
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'errors' => [],
            'message'
        ]);
    }

    public function testShouldNotLoginWithLoggedUser()
    {
        $password = $this->faker->password(8);
        $user = User::factory()->create([
            'password' => bcrypt($password)
        ]);
        Sanctum::actingAs($user);

        $data = [
            'username' => $user->username,
            'password' => bcrypt($password)
        ];

        $response = $this->post(route('auth.login'), $data);
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function testShouldLogout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post(route('auth.logout'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message'
        ]);
    }

    public function testShouldDenyLogout()
    {
        User::factory()->create();

        $response = $this->post(route('auth.logout'));
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'message'
        ]);
    }
}