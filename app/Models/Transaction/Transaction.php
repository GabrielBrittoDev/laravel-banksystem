<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'amount',
        'description',
        'transaction_category_id'
    ];

    protected $hidden = [
        'file'
    ];
}
