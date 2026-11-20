<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Http\Json;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuthController
{
    /** POST /api/login  {email, password}  ->  200 {token} | 401 | 422 */
    public function login(Request $request, Response $response): Response
    {
        $d = (array) ($request->getParsedBody() ?? []);
        $email = trim((string) ($d['email'] ?? ''));
        $password = (string) ($d['password'] ?? '');

        if ($email === '' || $password === '') {
            return Json::error($response, 'Email y contraseña son obligatorios.', 422);
        }

        $st = Database::pdo()->prepare('SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();

        // Los hashes bcrypt ($2y$) generados por Laravel son verificables con password_verify
        if (!$u || !password_verify($password, $u['password'])) {
            return Json::error($response, 'Credenciales incorrectas.', 401);
        }

        $ttl = (int) ($_ENV['JWT_TTL_MINUTES'] ?? 120) * 60;
        $ahora = time();
        $token = JWT::encode([
            'iss' => 'api-servicios',
            'iat' => $ahora,
            'exp' => $ahora + $ttl,
            'sub' => (int) $u['id'],
            'nombre' => $u['nombre'],
            'rol' => $u['rol'],
        ], $_ENV['JWT_SECRET'], 'HS256');

        return Json::send($response, [
            'success' => true,
            'message' => 'Autenticación correcta.',
            'data' => [
                'token' => $token,
                'expira_en_segundos' => $ttl,
                'usuario' => ['id' => (int) $u['id'], 'nombre' => $u['nombre'], 'rol' => $u['rol']],
            ],
        ]);
    }
}
