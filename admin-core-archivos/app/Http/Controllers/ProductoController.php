<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /** Listado + indicadores + últimos movimientos registrados por la API. */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $productos = Producto::with('categoria')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nombre', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('planta', 'like', "%{$q}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'productos' => Producto::count(),
            'unidades' => (int) Producto::sum('stock'),
            'valor' => (float) Producto::sum(DB::raw('stock * precio')),
            'bajo_stock' => Producto::whereColumn('stock', '<=', 'stock_minimo')->count(),
        ];

        $movimientos = MovimientoInventario::with(['producto', 'usuario'])
            ->latest('id')->limit(8)->get();

        return view('productos.index', compact('productos', 'stats', 'movimientos', 'q'));
    }

    public function create()
    {
        return view('productos.create', [
            'producto' => new Producto(['stock' => 0, 'stock_minimo' => 5]),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Producto::create($this->validar($request));

        return redirect()->route('productos.index')
            ->with('ok', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($this->validar($request, $producto->id));

        return redirect()->route('productos.index')
            ->with('ok', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('ok', 'Producto eliminado correctamente.');
    }

    /** Reglas de validación compartidas por store() y update(). */
    private function validar(Request $request, ?int $id = null): array
    {
        $reglas = [
            'sku' => ['required', 'string', 'max:40', Rule::unique('productos', 'sku')->ignore($id)],
            'nombre' => ['required', 'string', 'max:150'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'precio' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'planta' => ['required', 'string', 'max:80'],
            'autonomia_km' => ['nullable', 'integer', 'min:0', 'max:2000'],
        ];

        $mensajes = [
            'required' => 'El campo :attribute es obligatorio.',
            'unique' => 'Ya existe un producto con ese :attribute.',
            'exists' => 'El valor de :attribute no es válido.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'min.numeric' => 'El campo :attribute no puede ser menor que :min.',
            'max.numeric' => 'El campo :attribute no puede ser mayor que :max.',
            'max.string' => 'El campo :attribute no puede superar :max caracteres.',
        ];

        $atributos = [
            'sku' => 'SKU', 'nombre' => 'nombre', 'categoria_id' => 'categoría',
            'descripcion' => 'descripción', 'precio' => 'precio', 'stock' => 'stock',
            'stock_minimo' => 'stock mínimo', 'planta' => 'planta',
            'autonomia_km' => 'autonomía (km)',
        ];

        return $request->validate($reglas, $mensajes, $atributos);
    }
}
