<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')->cascadeOnDelete();
            $table->string('ruta');                       // path en el storage
            $table->string('nombre_original');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano')->nullable(); // bytes
            $table->string('descripcion')->nullable();     // foto de ingreso, comprobante, etc.
            $table->foreignId('subido_por')
                ->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjuntos');
    }
};
