<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;

/** Utilidad para responder siempre en JSON con el código HTTP correcto. */
final class Json
{
    public static function send(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)
        );

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

    public static function error(ResponseInterface $response, string $mensaje, int $status, array $extra = []): ResponseInterface
    {
        return self::send($response, ['success' => false, 'message' => $mensaje] + $extra, $status);
    }
}
