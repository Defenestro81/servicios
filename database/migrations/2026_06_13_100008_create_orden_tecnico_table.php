<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_tecnico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordenes')->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->boolean('principal')->default(false); // técnico responsable
            $table->timestamps();
            $table->unique(['orden_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_tecnico');
    }
};
