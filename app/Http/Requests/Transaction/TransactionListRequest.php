<?php

namespace App\Http\Requests\Transaction;

use App\Domain\Enums\TransactionCategoryEnum;
use App\Domain\Enums\TransactionStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'        => ['sometimes', 'numeric'],
            'order_by'    => ['sometimes', Rule::in('desc', 'asc')],
            'per_page'    => ['sometimes', 'numeric'],
            'status_id'   => [
                'sometimes',
                Rule::in(
                    TransactionStatusEnum::APPROVED,
                    TransactionStatusEnum::REJECTED,
                    TransactionStatusEnum::PENDING
                )
            ],
            'category_id' => [
                'sometimes',
                Rule::in(
                    TransactionCategoryEnum::DEPOSIT,
                    TransactionCategoryEnum::EXPANSE
                )
            ],
        ];
    }
}