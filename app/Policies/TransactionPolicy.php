<?php

namespace App\Policies;

use App\Domain\Enums\RoleEnum;
use App\Domain\Enums\TransactionStatusEnum;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function finishDeposit(User $user): bool
    {
        return $user->role_id === RoleEnum::ADMINISTRATOR;
    }

    public function create(User $user)
    {
        return $user->role_id === RoleEnum::CUSTOMER;
    }

    public function update(User $user, Transaction $transaction)
    {
        return $user->role_id === RoleEnum::ADMINISTRATOR &&
               $transaction->status_id === TransactionStatusEnum::PENDING;

    }

    public function view(User $user)
    {
        return $user->role_id === RoleEnum::CUSTOMER;
    }
}
