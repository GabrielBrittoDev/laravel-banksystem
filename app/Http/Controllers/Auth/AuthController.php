<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Services\Auth\AuthService;
use App\Exceptions\InvalidLoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="Login",
     *      tags={"Auth"},
     *      summary="Login a User",
     *      description="Authenticate and return access token to user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent  (ref="#/components/schemas/LoginRequest")
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
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->authenticate(
                $request->get('username'),
                $request->get('password')
            );
            return $this->buildSuccessResponse(
                __('messages.auth.login'),
                $token
            );
        } catch (InvalidLoginException $exception) {
            return $this->buildErrorResponse(
                $exception,
                $exception->getMessage(),
                HttpResponse::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      operationId="Logout",
     *      tags={"Auth"},
     *      summary="Logout a User",
     *      description="Revoke User token",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
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
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return $this->buildSuccessResponse('sucesso');
        } catch (\Exception $exception) {
            return $this->buildErrorResponse($exception);
        }
    }
}
