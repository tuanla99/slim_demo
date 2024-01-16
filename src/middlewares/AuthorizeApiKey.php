<?php
namespace App\Middlewares;

use App\Handlers\AppExceptionsHandler;
use App\Handlers\AuthException;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;

class AuthorizeApiKey
{
    private const AUTH_HEADER = 'X-API-KEY';

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $apiKey = @$request->getHeader(self::AUTH_HEADER)[0];
        if ($apiKey !== '123456') {
            throw new HttpUnauthorizedException($request, 'Invalid api key');
        }

        $response = $handler->handle($request);

        return $response;
    }
}
