<?php

namespace App\Domain\Services\Auth;

use App\Domain\Contracts\Repositories\User\UserRepositoryInterface;
use App\Domain\Services\BaseService;
use App\Exceptions\InvalidLoginException;
use Illuminate\Support\Facades\Hash;

class AuthService extends BaseService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function authenticate(string $username, string $password): array
    {
        $user = $this->userRepository->findOneBy([
            'username' => $username
        ]);

        if (!$user) {
            throw new InvalidLoginException(__('exceptions.auth.invalid_username'));
        }

        if (!Hash::check($password, $user->password)) {
            throw new InvalidLoginException(__('exceptions.auth.invalid_password'));
        }


        return [
            'access_token' => $user->createToken(env('APP_NAME', 'Bank'))->plainTextToken,
            'user'         => $user
        ];
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
    }

}