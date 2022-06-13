<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransactionPurchaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount'      => 'required|numeric|gt:0',
            'description' => 'required|string|min:2|max:255'
        ];
    }
}