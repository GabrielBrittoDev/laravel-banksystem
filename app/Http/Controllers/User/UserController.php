<?php

namespace App\Http\Controllers\User;

use App\Domain\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
    }

    /**
     * @OA\Post(
     *      path="/api/user",
     *      operationId="CreateUser",
     *      tags={"Users"},
     *      summary="Create a new customer User",
     *      description="Create and return a new Customer User with his access token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent  (ref="#/components/schemas/UserCreateRequest")
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
    public function create(UserCreateRequest $request): JsonResponse
    {
        $data = $this->userService->create($request->all());
        return $this->buildSuccessResponse(
            __('messages.user.create'),
            $data,
            HttpResponse::HTTP_CREATED
        );
    }

}
