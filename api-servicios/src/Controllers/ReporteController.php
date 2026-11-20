<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Http\Json;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ReporteController
{
    /** GET /api/reportes/resumen  ->  indicadores en tiempo real para el frontend */
    public function resumen(Request $request, Response $response): Response
    {
        $pdo = Database::pdo();

        $t = $pdo->query('SELECT COUNT(*) AS referencias,
                                 COALESCE(SUM(stock),0) AS unidades,
                                 COALESCE(SUM(stock * precio),0) AS valor_inventario,
                                 COALESCE(SUM(stock <= stock_minimo),0) AS bajo_stock
                            FROM productos')->fetch();

        $porCat = $pdo->query('SELECT c.nombre AS categoria, COUNT(p.id) AS referencias, COALESCE(SUM(p.stock),0) AS unidades
                                 FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id
                                GROUP BY c.id, c.nombre ORDER BY c.nombre')->fetchAll();

        foreach ($porCat as &$c) {
            $c['referencias'] = (int) $c['referencias'];
            $c['unidades'] = (int) $c['unidades'];
        }

        return Json::send($response, [
            'success' => true,
            'data' => [
                'referencias' => (int) $t['referencias'],
                'unidades' => (int) $t['unidades'],
                'valor_inventario' => (float) $t['valor_inventario'],
                'bajo_stock' => (int) $t['bajo_stock'],
                'por_categoria' => $porCat,
                'generado_en' => date('c'),
            ],
        ]);
    }
}
