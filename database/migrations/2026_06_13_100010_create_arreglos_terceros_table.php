<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arreglos_terceros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')->cascadeOnDelete();
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')->restrictOnDelete();
            $table->text('descripcion');
            $table->date('fecha_llevado')->nullable();
            $table->date('fecha_recibido')->nullable();
            $table->decimal('importe', 12, 2)->nullable(); // costo que cobra el proveedor
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arreglos_terceros');
    }
};
