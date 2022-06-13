<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionPendingDepositsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_by' => ['sometimes', Rule::in('desc', 'asc')],
            'per_page' => ['sometimes', 'numeric'],
            'page'     => ['sometimes', 'numeric']
        ];
    }
}