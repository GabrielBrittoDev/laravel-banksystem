<?php

namespace App\Domain\Repositories\Wallet;

use App\Domain\Repositories\BaseRepository;
use App\Models\User;
use App\Models\Wallet\Wallet;

class WalletRepository extends BaseRepository
{
    public function __construct(Wallet $wallet)
    {
        parent::__construct($wallet);
    }
}