<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'file'
    ];
}
