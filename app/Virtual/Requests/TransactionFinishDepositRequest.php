<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="Finish deposit request",
 *      description="Finish deposit with option given",
 *      type="object",
 *      required={"option"}
 * )
 */
class TransactionFinishDepositRequest
{
    /**
     * @OA\Property(
     *      title="option",
     *      description="Option to finalize deposit [2=APPROVED|3=REJECTED]",
     *      format="int64",
     *      example=2
     * )
     *
     * @var integer
     */
    public $option;

}