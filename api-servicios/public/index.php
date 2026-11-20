<?php
/**
 * Punto de entrada de la API REST (Slim 4).
 * Ejecutar:  php -S localhost:8081 -t public
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ProductoController;
use App\Controllers\ReporteController;
use App\Middleware\CorsMiddleware;
use App\Middleware\JwtMiddleware;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();      // interpreta JSON del cuerpo de la petición
$app->addRoutingMiddleware();

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
$errores = $app->addErrorMiddleware($debug, true, true);
$errores->getDefaultErrorHandler()->forceContentType('application/json');

// CORS se agrega al final para quedar como capa más externa (cubre también errores 404/500)
$app->add(new CorsMiddleware());

// ---------------------------- RUTAS PÚBLICAS ----------------------------
$app->get('/', fn($req, $res) => \App\Http\Json::send($res, [
    'success' => true,
    'message' => 'API VoltRider Motors - Inventario',
    'endpoints' => [
        'POST /api/login', 'GET /api/productos', 'GET /api/productos/{id}',
        'GET /api/categorias', 'GET /api/reportes/resumen',
        'POST /api/productos/descontar (JWT)', 'POST /api/productos (JWT)',
    ],
]));

$app->post('/api/login', [AuthController::class, 'login']);
$app->get('/api/productos', [ProductoController::class, 'listar']);
$app->get('/api/productos/{id:[0-9]+}', [ProductoController::class, 'detalle']);
$app->get('/api/categorias', [ProductoController::class, 'categorias']);
$app->get('/api/reportes/resumen', [ReporteController::class, 'resumen']);

// ------------------- RUTAS PROTEGIDAS (requieren JWT) -------------------
$app->group('/api', function (RouteCollectorProxy $g) {
    $g->post('/productos/descontar', [ProductoController::class, 'descontar']);
    $g->post('/productos', [ProductoController::class, 'crear']);
})->add(new JwtMiddleware());

$app->run();
