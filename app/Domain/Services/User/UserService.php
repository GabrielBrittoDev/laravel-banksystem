<?php

namespace App\Domain\Services\User;

use App\Domain\Contracts\Repositories\User\UserRepositoryInterface;
use App\Domain\Enums\RoleEnum;
use App\Domain\Repositories\Wallet\WalletRepository;
use App\Domain\Services\BaseService;
use Psy\Util\Str;

class UserService extends BaseService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepository        $walletRepository
    ) {
    }

    public function create(array $data): array
    {
        $data['password'] = bcrypt($data['password']);
        $data['role_id']  = RoleEnum::CUSTOMER;
        $user             = $this->userRepository->save($data);
        $wallet           = $this->walletRepository->save([
            'user_id' => $user->id
        ]);
        $user->wallet = $wallet;
        $token        = $user->createToken(env('APP_NAME', 'Bank'))->plainTextToken;
        return [
            'access_token' => $token,
            'user'         => $user
        ];
    }

}