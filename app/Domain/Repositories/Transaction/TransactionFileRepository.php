<?php

namespace App\Domain\Repositories\Transaction;

use App\Domain\Repositories\BaseRepository;
use App\Models\Transaction\TransactionFile;

class TransactionFileRepository extends BaseRepository
{
    public function __construct(TransactionFile $transactionFile)
    {
        $this->model = $transactionFile;
    }


}