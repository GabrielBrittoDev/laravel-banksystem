<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Controller;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    private MockObject $controller;

    protected function setUp(): void
    {
        $this->controller = $this->getMockForAbstractClass(Controller::class);
        parent::setUp();
    }

    public function testShouldFormatAndReturnErrorInProductionEnvironment()
    {
        putenv('APP_DEBUG=false');
        $exception       = new \Exception('Erro ao tentar executar esse teste muito maneiro!');
        $responseMessage = 'Falha no servidor!';
        $response        = $this->controller->buildErrorResponse(
            $exception,
            $responseMessage
        );

        $responseDecoded = json_decode($response->getContent(), true);

        $this->assertEquals($responseMessage, $responseDecoded['message']);
        $this->assertEquals(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertArrayNotHasKey('exceptionMessage', $responseDecoded);
        $this->assertArrayNotHasKey('trace', $responseDecoded);
        $this->assertArrayNotHasKey('file', $responseDecoded);
        $this->assertArrayNotHasKey('line', $responseDecoded);
    }

    public function testShouldFormatAndReturnError()
    {
        $exception       = new \Exception('Erro ao tentar executar esse teste muito maneiro!');
        $responseMessage = 'Falha no servidor!';
        $response        = $this->controller->buildErrorResponse(
            $exception,
            $responseMessage
        );

        $responseDecoded = json_decode($response->getContent(), true);

        $this->assertEquals($responseMessage, $responseDecoded['message']);
        $this->assertEquals($exception->getMessage(), $responseDecoded['exceptionMessage']);
        $this->assertEquals(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, $response->status());
        $this->assertArrayHasKey('trace', $responseDecoded);
        $this->assertArrayHasKey('file', $responseDecoded);
        $this->assertArrayHasKey('line', $responseDecoded);
    }

    public function testShouldFormatAndReturnErrorWithStatusCode()
    {
        $exception       = new \Exception('Erro ao tentar executar esse teste muito maneiro!');
        $responseMessage = 'Falha no servidor!';
        $statusCode      = ResponseAlias::HTTP_UNAUTHORIZED;
        $response = $this->controller->buildErrorResponse(
            $exception,
            $responseMessage,
            $statusCode
        );

        $this->assertEquals($statusCode, $response->status());
    }

    public function testShouldFormatAndReturnErrorsArray()
    {
        $exception       = new \Exception('Erro ao tentar executar esse teste muito maneiro!');
        $responseMessage = 'Falha no servidor!';
        $statusCode      = ResponseAlias::HTTP_UNAUTHORIZED;
        $errors          = [
            'Error 1',
            'Error 2'
        ];
        $response = $this->controller->buildErrorResponse(
            $exception,
            $responseMessage,
            $statusCode,
            $errors
        );

        $responseDecoded = json_decode($response->getContent(), true);

        $this->assertEquals($errors, $responseDecoded['errors']);
    }
}
