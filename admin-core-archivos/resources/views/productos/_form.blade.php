@if ($errors->any())
    <div class="alert alert-danger"><strong>Revisa los datos:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">SKU *</label>
        <input name="sku" value="{{ old('sku', $producto->sku) }}" class="form-control @error('sku') is-invalid @enderror" required maxlength="40">
    </div>
    <div class="col-md-8">
        <label class="form-label">Nombre *</label>
        <input name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required maxlength="150">
    </div>
    <div class="col-md-6">
        <label class="form-label">Categoría *</label>
        <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
            <option value="">Seleccionar...</option>
            @foreach ($categorias as $c)
                <option value="{{ $c->id }}" @selected(old('categoria_id', $producto->categoria_id) == $c->id)>{{ $c->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Planta de fabricación *</label>
        <input name="planta" value="{{ old('planta', $producto->planta) }}" class="form-control @error('planta') is-invalid @enderror" required maxlength="80" placeholder="Ej: Medellín, Colombia">
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror" maxlength="1000">{{ old('descripcion', $producto->descripcion) }}</textarea>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label">Precio (USD) *</label>
        <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio', $producto->precio) }}" class="form-control @error('precio') is-invalid @enderror" required>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label">Stock *</label>
        <input type="number" min="0" name="stock" value="{{ old('stock', $producto->stock) }}" class="form-control @error('stock') is-invalid @enderror" required>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label">Stock mínimo *</label>
        <input type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}" class="form-control @error('stock_minimo') is-invalid @enderror" required>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label">Autonomía (km)</label>
        <input type="number" min="0" max="2000" name="autonomia_km" value="{{ old('autonomia_km', $producto->autonomia_km) }}" class="form-control @error('autonomia_km') is-invalid @enderror">
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-vr">Guardar</button>
    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
