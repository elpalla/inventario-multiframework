<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Http\Json;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ProductoController
{
    private const SELECT = 'SELECT p.id, p.sku, p.nombre, p.categoria_id, c.nombre AS categoria,
                                   p.descripcion, p.precio, p.stock, p.stock_minimo, p.planta, p.autonomia_km
                              FROM productos p
                              JOIN categorias c ON c.id = p.categoria_id';

    /** Convierte tipos para que el JSON tenga números reales y no cadenas. */
    private static function normalizar(array $p): array
    {
        $p['id'] = (int) $p['id'];
        $p['categoria_id'] = (int) $p['categoria_id'];
        $p['precio'] = (float) $p['precio'];
        $p['stock'] = (int) $p['stock'];
        $p['stock_minimo'] = (int) $p['stock_minimo'];
        $p['autonomia_km'] = $p['autonomia_km'] !== null ? (int) $p['autonomia_km'] : null;
        $p['bajo_stock'] = $p['stock'] <= $p['stock_minimo'];
        return $p;
    }

    /** GET /api/productos?q=&categoria_id=&planta=  ->  200 */
    public function listar(Request $request, Response $response): Response
    {
        $f = $request->getQueryParams();
        $where = [];
        $params = [];

        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(p.nombre LIKE ? OR p.sku LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        if (!empty($f['categoria_id']) && ctype_digit((string) $f['categoria_id'])) {
            $where[] = 'p.categoria_id = ?';
            $params[] = (int) $f['categoria_id'];
        }
        if (!empty($f['planta'])) {
            $where[] = 'p.planta = ?';
            $params[] = (string) $f['planta'];
        }

        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY p.nombre';
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        $datos = array_map([self::class, 'normalizar'], $st->fetchAll());

        return Json::send($response, ['success' => true, 'total' => count($datos), 'data' => $datos]);
    }

    /** GET /api/productos/{id}  ->  200 | 404 */
    public function detalle(Request $request, Response $response, array $args): Response
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE p.id = ?');
        $st->execute([(int) $args['id']]);
        $p = $st->fetch();

        if (!$p) {
            return Json::error($response, 'Producto no encontrado.', 404);
        }
        return Json::send($response, ['success' => true, 'data' => self::normalizar($p)]);
    }

    /** GET /api/categorias  ->  200 */
    public function categorias(Request $request, Response $response): Response
    {
        $filas = Database::pdo()->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
        foreach ($filas as &$c) {
            $c['id'] = (int) $c['id'];
        }
        $plantas = Database::pdo()->query('SELECT DISTINCT planta FROM productos ORDER BY planta')->fetchAll(\PDO::FETCH_COLUMN);

        return Json::send($response, ['success' => true, 'data' => $filas, 'plantas' => $plantas]);
    }

    /**
     * POST /api/productos/descontar  {producto_id, cantidad}   [JWT]
     * 200 OK | 401 sin token | 404 no existe | 409 stock insuficiente | 422 datos inválidos
     * Usa transacción + bloqueo de fila (FOR UPDATE) para evitar condiciones de carrera.
     */
    public function descontar(Request $request, Response $response): Response
    {
        $d = (array) ($request->getParsedBody() ?? []);
        $id = filter_var($d['producto_id'] ?? null, FILTER_VALIDATE_INT);
        $cant = filter_var($d['cantidad'] ?? null, FILTER_VALIDATE_INT);

        if ($id === false || $cant === false || $id < 1 || $cant < 1) {
            return Json::error($response, 'Se requiere producto_id y cantidad (enteros positivos).', 422);
        }

        $pdo = Database::pdo();
        $usuario = $request->getAttribute('usuario');

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('SELECT id, nombre, stock FROM productos WHERE id = ? FOR UPDATE');
            $st->execute([$id]);
            $p = $st->fetch();

            if (!$p) {
                $pdo->rollBack();
                return Json::error($response, 'Producto no encontrado.', 404);
            }
            if ((int) $p['stock'] < $cant) {
                $pdo->rollBack();
                return Json::error($response, "Stock insuficiente: solo hay {$p['stock']} unidades de {$p['nombre']}.", 409, [
                    'stock_disponible' => (int) $p['stock'],
                ]);
            }

            $nuevo = (int) $p['stock'] - $cant;
            $pdo->prepare('UPDATE productos SET stock = ?, updated_at = NOW() WHERE id = ?')->execute([$nuevo, $id]);
            $pdo->prepare('INSERT INTO movimientos_inventario (producto_id, usuario_id, tipo, cantidad, stock_resultante)
                           VALUES (?, ?, \'salida\', ?, ?)')
                ->execute([$id, $usuario['sub'] ?? null, $cant, $nuevo]);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return Json::send($response, [
            'success' => true,
            'message' => "Se descontaron {$cant} unidades de {$p['nombre']}.",
            'data' => ['producto_id' => $id, 'descontado' => $cant, 'stock_actual' => $nuevo],
        ]);
    }

    /** POST /api/productos  [JWT]  ->  201 creado | 409 SKU duplicado | 422 inválido */
    public function crear(Request $request, Response $response): Response
    {
        $d = (array) ($request->getParsedBody() ?? []);
        $errores = [];

        $sku = trim((string) ($d['sku'] ?? ''));
        $nombre = trim((string) ($d['nombre'] ?? ''));
        $planta = trim((string) ($d['planta'] ?? ''));
        $cat = filter_var($d['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $precio = filter_var($d['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($d['stock'] ?? 0, FILTER_VALIDATE_INT);
        $min = filter_var($d['stock_minimo'] ?? 5, FILTER_VALIDATE_INT);

        if ($sku === '' || strlen($sku) > 40) $errores['sku'] = 'SKU obligatorio (máx. 40).';
        if ($nombre === '' || strlen($nombre) > 150) $errores['nombre'] = 'Nombre obligatorio (máx. 150).';
        if ($planta === '' || strlen($planta) > 80) $errores['planta'] = 'Planta obligatoria (máx. 80).';
        if ($cat === false || $cat < 1) $errores['categoria_id'] = 'Categoría inválida.';
        if ($precio === false || $precio < 0) $errores['precio'] = 'Precio inválido.';
        if ($stock === false || $stock < 0) $errores['stock'] = 'Stock inválido.';
        if ($min === false || $min < 0) $errores['stock_minimo'] = 'Stock mínimo inválido.';
        if ($errores) {
            return Json::error($response, 'Datos inválidos.', 422, ['errores' => $errores]);
        }

        $autonomia = isset($d['autonomia_km']) && $d['autonomia_km'] !== '' ? (int) $d['autonomia_km'] : null;
        $pdo = Database::pdo();

        try {
            $pdo->prepare('INSERT INTO productos (sku,nombre,categoria_id,descripcion,precio,stock,stock_minimo,planta,autonomia_km,created_at,updated_at)
                           VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())')
                ->execute([$sku, $nombre, $cat, $d['descripcion'] ?? null, $precio, $stock, $min, $planta, $autonomia]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // violación de UNIQUE o FOREIGN KEY
                $esFk = str_contains($e->getMessage(), 'foreign key');
                return Json::error($response, $esFk ? 'La categoría indicada no existe.' : 'Ya existe un producto con ese SKU.', $esFk ? 422 : 409);
            }
            throw $e;
        }

        $id = (int) $pdo->lastInsertId();
        $st = $pdo->prepare(self::SELECT . ' WHERE p.id = ?');
        $st->execute([$id]);

        return Json::send($response, [
            'success' => true,
            'message' => 'Producto creado.',
            'data' => self::normalizar($st->fetch()),
        ], 201)->withHeader('Location', "/api/productos/{$id}");
    }
}
