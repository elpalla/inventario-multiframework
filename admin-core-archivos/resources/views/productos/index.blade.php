@extends('layouts.app')
@section('title', 'Inventario')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card kpi shadow-sm"><div class="card-body">
        <div class="text-muted small">Referencias</div><div class="fs-3 fw-semibold">{{ number_format($stats['productos']) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi shadow-sm"><div class="card-body">
        <div class="text-muted small">Unidades en stock</div><div class="fs-3 fw-semibold">{{ number_format($stats['unidades']) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi shadow-sm"><div class="card-body">
        <div class="text-muted small">Valor del inventario (USD)</div><div class="fs-3 fw-semibold">${{ number_format($stats['valor'], 0) }}</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card kpi shadow-sm"><div class="card-body">
        <div class="text-muted small">Con stock bajo</div><div class="fs-3 fw-semibold text-danger">{{ $stats['bajo_stock'] }}</div>
    </div></div></div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h2 class="h5 mb-0">Productos</h2>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar SKU, nombre o planta">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="{{ route('productos.create') }}" class="btn btn-sm btn-vr">+ Nuevo</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>SKU</th><th>Producto</th><th>Categoría</th><th>Planta</th>
                    <th class="text-end">Precio (USD)</th><th class="text-end">Stock</th><th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($productos as $p)
                <tr>
                    <td><code>{{ $p->sku }}</code></td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->categoria->nombre }}</td>
                    <td>{{ $p->planta }}</td>
                    <td class="text-end">{{ number_format($p->precio, 2) }}</td>
                    <td class="text-end">
                        <span class="badge {{ $p->bajo_stock ? 'text-bg-danger' : 'text-bg-success' }}">{{ $p->stock }}</span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form method="POST" action="{{ route('productos.destroy', $p) }}" class="d-inline"
                              onsubmit="return confirm('¿Eliminar {{ addslashes($p->nombre) }}?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay productos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $productos->links('pagination::bootstrap-5') }}</div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><h2 class="h6 mb-0">Últimos movimientos (registrados por la API Slim)</h2></div>
    <ul class="list-group list-group-flush">
        @forelse ($movimientos as $m)
            <li class="list-group-item small d-flex justify-content-between">
                <span>
                    <strong>{{ ucfirst($m->tipo) }}</strong> de {{ $m->cantidad }} u. · {{ $m->producto->nombre ?? 'Producto eliminado' }}
                    <span class="text-muted">por {{ $m->usuario->nombre ?? 'sistema' }}</span>
                </span>
                <span class="text-muted">stock: {{ $m->stock_resultante }} · {{ \Illuminate\Support\Carbon::parse($m->created_at)->format('d/m/Y H:i') }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted small">Aún no hay movimientos. Descuenta unidades desde el frontend (puerto 8082) y aparecerán aquí.</li>
        @endforelse
    </ul>
</div>
@endsection
