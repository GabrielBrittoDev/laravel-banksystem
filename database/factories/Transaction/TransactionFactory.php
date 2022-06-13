<?php

namespace Database\Factories\Transaction;

use App\Domain\Enums\RoleEnum;
use App\Domain\Enums\TransactionCategoryEnum;
use App\Domain\Enums\TransactionStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
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
            'amount' => $this->faker->randomNumber(3),
            'status_id'               => $this->faker->randomElement([
                TransactionStatusEnum::APPROVED,
                TransactionStatusEnum::PENDING,
                TransactionStatusEnum::PENDING
            ]),
            'user_id'                 => $user->id,
            'transaction_category_id' => $this->faker->randomElement([
                TransactionCategoryEnum::EXPANSE,
                TransactionCategoryEnum::DEPOSIT
            ]),
            'description'             => $this->faker->text
        ];
    }

    public function expanse(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_category_id' => TransactionCategoryEnum::EXPANSE
            ];
        });
    }

    public function deposit(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_category_id' => TransactionCategoryEnum::DEPOSIT
            ];
        });
    }

    public function pending(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_id' => TransactionStatusEnum::PENDING
            ];
        });
    }

    public function approved(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_id' => TransactionStatusEnum::APPROVED
            ];
        });
    }

    public function rejected(): TransactionFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status_id' => TransactionStatusEnum::REJECTED
            ];
        });
    }
}
