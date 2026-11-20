<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Http\Json;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Throwable;

/**
 * Reto avanzado: solo las peticiones con "Authorization: Bearer <token>"
 * válido pueden modificar el inventario. Si no, responde 401.
 */
final class JwtMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return Json::error(new Response(), 'Token no proporcionado. Inicia sesión para continuar.', 401);
        }

        try {
            $datos = JWT::decode(trim($m[1]), new Key($_ENV['JWT_SECRET'], 'HS256'));
        } catch (Throwable $e) {
            return Json::error(new Response(), 'Token inválido o expirado.', 401);
        }

        return $handler->handle($request->withAttribute('usuario', (array) $datos));
    }
}
