<?php

namespace App\Domain\Repositories\User;

use App\Domain\Contracts\Repositories\User\UserRepositoryInterface;
use App\Domain\Repositories\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
}