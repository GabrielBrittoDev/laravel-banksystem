<?php

namespace Database\Factories;

use App\Domain\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'              => $this->faker->name(),
            'username'          => $this->faker->userName,
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => bcrypt($this->faker->password),
            'role_id'           => $this->faker->randomElement([RoleEnum::CUSTOMER, RoleEnum::ADMINISTRATOR]),
            'remember_token'    => Str::random(10),
        ];
    }

    public function administrator(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => RoleEnum::ADMINISTRATOR,
            ];
        });
    }

    public function customer(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'role_id' => RoleEnum::CUSTOMER,
            ];
        });
    }
}
