<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Bank system api documentation",
     *      description="Bank Swagger OpenApi description",
     * )
     *
     * @OA\Server(
     *      url="http://localhost:5000",
     *      description="Bank API Server"
     * )
     * @OAS\SecurityScheme(
     *      securityScheme="sanctum",
     *      type="http",
     *      scheme="bearer"
     * )
     * @OA\Get(
     *     path="/",
     *     @OA\Response(response="200", description="Display Framework(Laravel) Version")
     * )
     */

    public function buildSuccessResponse(
        string $message,
        $data = [],
        int    $httpCode = HttpResponse::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'data'    => $data
        ], $httpCode);
    }

    public function buildErrorResponse(
        \Exception $exception,
        string     $message = 'Erro inesperado aconteceu!',
        int        $httpCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        array      $errors = []
    ): JsonResponse {
        $error = [
            'message' => $message,
            'errors'  => $errors,
        ];

        if (boolval(env('APP_DEBUG'))) {
            $error['exceptionMessage'] = $exception->getMessage();
            $error['file']             = $exception->getFile();
            $error['line']             = $exception->getLine();
        }

        return response()->json($error, $httpCode);
    }
}
