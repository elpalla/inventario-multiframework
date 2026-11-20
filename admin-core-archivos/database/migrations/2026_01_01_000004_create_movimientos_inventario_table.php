<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')
                  ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')
                  ->cascadeOnUpdate()->nullOnDelete();
            $table->enum('tipo', ['salida', 'entrada', 'ajuste'])->default('salida');
            $table->unsignedInteger('cantidad');
            $table->integer('stock_resultante');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
