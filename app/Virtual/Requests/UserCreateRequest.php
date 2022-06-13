<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="User create request",
 *      description="User create with body data",
 *      type="object",
 *      required={"name", "email", "password", "username"}
 * )
 */
class UserCreateRequest
{
    /**
     * @OA\Property(
     *      title="name",
     *      description="User name",
     *      format="string",
     *      example="Max"
     * )
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *      title="username",
     *      description="Username of the User",
     *      format="string",
     *      example="Max02"
     * )
     * @var string
     */
    public $username;

    /**
     * @OA\Property(
     *      title="email",
     *      description="User email",
     *      format="string",
     *      example="max@max.com"
     * )
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *      title="password",
     *      description="User password",
     *      format="string",
     *      example="password123"
     * )
     *
     * @var string
     */
    public $password;
}