<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="List deposit pending transactions request",
 *      description="List deposit pending transactions with params given",
 *      type="object",
 *      required={}
 * )
 */
class TransactionPendingDepositsRequest
{
    /**
     * @OA\Property(
     *      title="order_by",
     *      description="Ordenation type [desc|asc]",
     *      format="string",
     *      example="asc"
     * )
     *
     * @var string
     */
    public $order_by;

    /**
     * @OA\Property(
     *      title="page",
     *      description="Pagination page",
     *      format="int64",
     *      example=2
     * )
     *
     * @var integer
     */
    public $page;

    /**
     * @OA\Property(
     *      title="per_page",
     *      description="Number of items in a page",
     *      format="int64",
     *      example=15
     * )
     *
     * @var integer
     */
    public $per_page;
}