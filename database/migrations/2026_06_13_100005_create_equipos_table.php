<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_equipo_id')
                ->constrained('tipos_equipos')->restrictOnDelete();
            $table->string('etiqueta', 50)->unique(); // asset tag del taller
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->string('nro_serie', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
