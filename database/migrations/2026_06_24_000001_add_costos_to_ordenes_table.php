<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->decimal('costo_mano_obra', 12, 2)->default(0)->after('detalles');
            $table->decimal('costo_repuestos', 12, 2)->default(0)->after('costo_mano_obra');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn(['costo_mano_obra', 'costo_repuestos']);
        });
    }
};
