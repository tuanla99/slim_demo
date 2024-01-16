<?php
namespace App\Handlers;

use Slim\Interfaces\ErrorRendererInterface;
use Slim\Psr7\Response;
use Throwable;

class CustomErrorRenderer implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails)
    {
        // $response = new Response();
        // $payload = json_encode([
        //     'error' => $exception->getMessage(),
        //     'status' => $exception->getCode()
        // ]);
        // $response->getBody()->write($payload);

        // return $response->withHeader('Content-Type', 'application/json')
        //   ->withStatus($exception->getCode());

        return $exception->getMessage();
    }
}
