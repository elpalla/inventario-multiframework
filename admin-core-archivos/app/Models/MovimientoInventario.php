<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Los movimientos los registra la API (Slim) al descontar stock.
 * Laravel los muestra en el panel administrativo (solo lectura).
 */
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    const UPDATED_AT = null;

    protected $guarded = [];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
