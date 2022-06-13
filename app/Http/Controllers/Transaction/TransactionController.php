<?php

namespace App\Http\Controllers\Transaction;

use App\Domain\Services\Transaction\TransactionService;
use App\Exceptions\InvalidTransactionException;
use App\Exceptions\NotEnoughBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\TransactionDepositRequest;
use App\Http\Requests\Transaction\TransactionFinishDepositRequest;
use App\Http\Requests\Transaction\TransactionListRequest;
use App\Http\Requests\Transaction\TransactionPendingDepositsRequest;
use App\Http\Requests\Transaction\TransactionPurchaseRequest;
use App\Models\Transaction\Transaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {
    }

    /**
     * @OA\Post(
     *      path="/api/transaction/deposit",
     *      operationId="CreateDepositTransaction",
     *      tags={"Transactions"},
     *      summary="Create a new transaction deposit",
     *      description="Create a new transaction of type deposit",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema  (ref="#/components/schemas/TransactionDepositRequest")
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid Data",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     *     )
     */
    public function deposit(TransactionDepositRequest $request): JsonResponse
    {
        $this->authorize('create', Transaction::class);

        $file        = $request->file('file');
        $amount      = $request->get('amount');
        $transaction = $this->transactionService->deposit(
            $file,
            $amount,
            $request->user()
        );

        return $this->buildSuccessResponse(
            __('messages.transaction.deposit'),
            $transaction,
            HttpResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/api/transaction/",
     *      operationId="ListTransactions",
     *      tags={"Transactions"},
     *      summary="List user transactions",
     *      description="Return all user transactions",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent  (ref="#/components/schemas/TransactionListRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid Data",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     *     )
     */
    public function get(TransactionListRequest $request): JsonResponse|Paginator
    {
        $this->authorize('view', Transaction::class);

        return $this->transactionService->list($request->validated(), $request->user());
    }

    /**
     * @OA\Post(
     *      path="/api/transaction/purchase",
     *      operationId="CreatePurchaseTransaction",
     *      tags={"Transactions"},
     *      summary="Create a new transaction purchase",
     *      description="Create a new transaction of type purchase",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/TransactionPurchaseRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid Data",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     *     )
     */
    public function purchase(TransactionPurchaseRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Transaction::class);

            $amount      = $request->get('amount');
            $description = $request->get('description');

            $transaction = $this->transactionService->purchase(
                $amount,
                $description,
                $request->user()
            );

            return $this->buildSuccessResponse(
                __('messages.transaction.purchase'),
                $transaction,
                HttpResponse::HTTP_CREATED
            );
        } catch (NotEnoughBalanceException $exception) {
            return $this->buildErrorResponse(
                $exception,
                $exception->getMessage(),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @OA\Post(
     *      path="/api/transaction/admin/finish-deposit/{transactionId}",
     *      operationId="CreateFinishDepositTransaction",
     *      tags={"Transactions"},
     *      summary="Finish deposit transaction",
     *      description="Update transaction to APPROVED or REJECTED",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="transactionId",
     *          description="Transaction id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/TransactionFinishDepositRequest")
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid Data",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     *     )
     */
    public function finishDeposit(int $transactionId, TransactionFinishDepositRequest $request): JsonResponse
    {
        try {
            $this->authorize('finishDeposit', Transaction::class);

            $option = $request->get('option');

            $this->transactionService->finishDeposit($transactionId, $option);
            return $this->buildSuccessResponse(__('messages.transaction.finished_deposit'));
        } catch (InvalidTransactionException $exception) {
            return $this->buildErrorResponse($exception, $exception->getMessage(), HttpResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/transaction/admin/pending-deposits",
     *      operationId="ListPendingDepositTransactions",
     *      tags={"Transactions"},
     *      summary="List pending deposit transactions",
     *      description="Return all deposit transactions pending",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent  (ref="#/components/schemas/TransactionPendingDepositsRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *     @OA\Response(
     *          response=422,
     *          description="Invalid Data",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      )
     *     )
     */
    public function pendingDeposits(TransactionPendingDepositsRequest $request): Paginator|JsonResponse
    {
        $this->authorize('finishDeposit', Transaction::class);

        return $this->transactionService->pendingDeposits($request->validated());
    }
}