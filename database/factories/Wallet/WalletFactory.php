<?php

namespace Database\Factories\Wallet;

use App\Domain\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->customer()->create();

        return [
            'balance' => $this->faker->randomNumber(4),
            'user_id' => $user->id
        ];
    }
}
