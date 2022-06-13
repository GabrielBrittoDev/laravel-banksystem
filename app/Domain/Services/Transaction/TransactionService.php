<?php

namespace App\Domain\Services\Transaction;

use App\Domain\Enums\TransactionCategoryEnum;
use App\Domain\Enums\TransactionStatusEnum;
use App\Domain\Repositories\Transaction\TransactionFileRepository;
use App\Domain\Repositories\Transaction\TransactionRepository;
use App\Domain\Repositories\Wallet\WalletRepository;
use App\Domain\Services\BaseService;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\NotEnoughBalanceException;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TransactionService extends BaseService
{
    public function __construct(
        private TransactionRepository     $transactionRepository,
        private WalletRepository          $walletRepository,
        private TransactionFileRepository $transactionFileRepository
    ) {
    }

    public function deposit(
        UploadedFile $checkFile,
        float        $amount,
        User         $user
    ): Transaction {
        try {
            DB::beginTransaction();
            $transaction = $this->transactionRepository->save([
                'user_id'                 => $user->id,
                'amount'                  => $amount,
                'transaction_category_id' => TransactionCategoryEnum::DEPOSIT,
                'status_id'               => TransactionStatusEnum::PENDING
            ]);

            $filePath = Storage::disk(config('filesystems.default'))->put('checks', $checkFile);

            $this->transactionFileRepository->save([
                'transaction_id' => $transaction->id,
                'file'           => $filePath
            ]);

            DB::commit();
            return $transaction;
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function purchase(float $amount, string $description, User $user): Transaction
    {
        try {
            DB::beginTransaction();
            $wallet = $this->walletRepository->findOneBy(['user_id' => $user->id]);

            if ($amount > $wallet->balance) {
                throw new NotEnoughBalanceException(__('exceptions.transaction.not_enough_balance'));
            }

            $transaction = $this->transactionRepository->save([
                'user_id'                 => $user->id,
                'amount'                  => $amount * -1,
                'description'             => $description,
                'transaction_category_id' => TransactionCategoryEnum::EXPANSE,
                'status_id'               => TransactionStatusEnum::APPROVED
            ]);

            $this->walletRepository->update([
                'balance' => $wallet->balance - $amount
            ], $wallet->id);

            DB::commit();
            return $transaction;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function list(array $filters, User $user): Paginator
    {
        $filters['user_id'] = $user->id;
        return $this->transactionRepository->list($filters);
    }

    public function finishDeposit(int $transactionId, int $option): void
    {
        try {
            DB::beginTransaction();
            if (!in_array($option, [TransactionStatusEnum::REJECTED, TransactionStatusEnum::APPROVED])) {
                throw new InvalidTransactionException(__('exceptions.transaction.invalid_option'));
            }

            $transaction = $this->transactionRepository->find($transactionId);
            if ($transaction->status_id !== TransactionStatusEnum::PENDING) {
                throw new InvalidTransactionException(__('exceptions.transaction.invalid_not_pending'));
            }

            $wallet = $this->walletRepository->findOneBy([
                'user_id' => $transaction->user_id
            ]);
            $this->transactionRepository->update([
                'status_id' => $option
            ], $transactionId);

            if ($option === TransactionStatusEnum::APPROVED) {
                $this->walletRepository->update([
                    'balance' => $wallet->balance + $transaction->amount
                ], $wallet->id);
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function pendingDeposits(array $filters): Paginator
    {
        $filters['status_id'] = TransactionStatusEnum::PENDING;

        $transactions = $this->transactionRepository->list($filters);
        foreach ($transactions as &$transaction) {
            $transaction->file_url = Storage::disk(config('filesystems.default'))
                ->temporaryUrl(basename($transaction->file), Carbon::now()->addHour());
            unset($transaction->file);
        }

        return $transactions;
    }

}