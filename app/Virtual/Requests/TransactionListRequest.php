<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="List transactions request",
 *      description="List transactions with params given",
 *      type="object",
 *      required={}
 * )
 */
class TransactionListRequest
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
     *      title="status_id",
     *      description="Status of the transaction",
     *      format="int64",
     *      example=2
     * )
     *
     * @var integer
     */
    public $status_id;

    /**
     * @OA\Property(
     *      title="category_id",
     *      description="Category of the transaction",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $category_id;

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