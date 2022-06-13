<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="Create deposit request",
 *      description="Create deposit with body data",
 *      type="object",
 *      required={"file", "amount"}
 * )
 */
class TransactionDepositRequest
{
    /**
     * @OA\Property(
     *      title="file",
     *      description="Check image file",
     *      type="string",
     *      format="binary"
     * )
     *
     */
    public $file;

    /**
     * @OA\Property(
     *      title="amount",
     *      description="Check value",
     *      format="int64",
     *      example=100
     * )
     *
     * @var integer
     */
    public $amount;
}