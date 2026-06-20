<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_estado_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')->cascadeOnDelete();
            $table->foreignId('estado_id')
                ->constrained('estados')->restrictOnDelete();
            // Quién hizo el cambio de estado.
            $table->foreignId('user_id')
                ->constrained('users')->restrictOnDelete();
            $table->text('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_estado_historial');
    }
};
