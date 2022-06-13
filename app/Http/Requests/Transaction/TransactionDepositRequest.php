<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransactionDepositRequest extends FormRequest
{
    public function rules()
    {
        return [
            'file'   => 'required|file',
            'amount' => 'required|numeric|gt:0',
        ];
    }
}