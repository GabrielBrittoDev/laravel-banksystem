<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="Create purchase transaction",
 *      description="Create purchase transaction request",
 *      type="object",
 *      required={"description", "amount"}
 * )
 */
class TransactionPurchaseRequest
{
    /**
     * @OA\Property(
     *      title="description",
     *      description="Description of the purchase",
     *      format="string",
     *      example="Laptop Dell"
     * )
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *      title="amount",
     *      description="Value of the purchase",
     *      format="float64",
     *      example=100.00
     * )
     *
     * @var float
     */
    public $amount;
}