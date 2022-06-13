<?php

namespace App\Virtual\Requests;

/**
 * @OA\Schema(
 *      title="Login request",
 *      description="Login with body data",
 *      type="object",
 *      required={"username", "password"}
 * )
 */
class LoginRequest
{
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