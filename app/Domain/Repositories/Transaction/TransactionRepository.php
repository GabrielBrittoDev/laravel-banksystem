<?php

namespace App\Domain\Repositories\Transaction;

use App\Domain\Repositories\BaseRepository;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Fluent;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $transaction)
    {
        parent::__construct($transaction);
    }

    public function list(array $filters): Paginator
    {
        $filters  = new Fluent($filters);
        $status   = $filters->{'status_id'};
        $userId   = $filters->{'user_id'};
        $category = $filters->{'category_id'};
        $orderBy  = $filters->get('order_by', 'DESC');
        $perPage  = $filters->get('per_page', 15);

        $builder = $this->model
            ->select(
                '*',
                'transactions.id',
                'transactions.created_at',
                'transactions.updated_at'
            )
            ->leftjoin('transaction_files as file', 'file.transaction_id', 'transactions.id')
            ->orderBy('transactions.created_at', $orderBy);


        $this->appendFilter($builder, 'status_id', $status);
        $this->appendFilter($builder, 'user_id', $userId);
        $this->appendFilter($builder, 'transaction_category_id', $category);

        return $builder->simplePaginate($perPage);
    }

    private function appendFilter(Builder &$builder, string $column, $value): Builder
    {
        if (empty($value)) {
            return $builder;
        }

        if (is_array($value)) {
            return $builder->whereIn($column, $value);
        }

        return $builder->where($column, $value);
    }
}