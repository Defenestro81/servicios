<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')
                ->constrained('equipos')->restrictOnDelete();
            $table->foreignId('cliente_id')
                ->constrained('clientes')->restrictOnDelete();
            $table->foreignId('estado_id')
                ->constrained('estados')->restrictOnDelete();
            $table->date('fecha_ingreso');
            $table->date('fecha_terminado')->nullable();
            $table->date('fecha_retirado')->nullable();
            $table->text('trabajo_solicitado');
            $table->text('trabajo_realizado')->nullable();
            $table->text('detalles')->nullable();
            // Auditoría: quién creó / modificó la orden.
            $table->foreignId('created_by')
                ->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')
                ->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
