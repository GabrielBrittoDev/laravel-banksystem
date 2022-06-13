<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Transaction",
 *     description="Transaction model",
 *     @OA\Xml(
 *         name="Transaction"
 *     )
 * )
 */
class Transaction
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="Amount",
     *      description="Amount of the transaction",
     *      example="100.00",
     *      format="float64",
     * )
     *
     * @var float
     */
    public $amount;

    /**
     * @OA\Property(
     *      title="Description",
     *      description="Description of the transaction",
     *      example="New backpack"
     * )
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @OA\Property(
     *      title="User ID",
     *      description="User id of the transaction",
     *      format="int64",
     *      example=1
     * )
     *
     * @var integer
     */
    public $user_id;
}