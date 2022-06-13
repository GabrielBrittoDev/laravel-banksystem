<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Enums\TransactionStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionFinishDepositRequest extends FormRequest
{
    public function rules()
    {
        return [
            'option' => [
                'required',
                Rule::in(TransactionStatusEnum::APPROVED, TransactionStatusEnum::REJECTED)
            ]
        ];
    }
}