<?php

namespace Database\Seeders;

use App\Models\TipoEquipo;
use Illuminate\Database\Seeder;

class TipoEquipoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Notebook',
            'Computadora',
            'All-in-One',
            'Tablet',
            'Impresora',
            'Monitor',
            'Teléfono celular',
            'Router / Switch',
            'Consola de videojuegos',
            'Disco externo',
            'Pendrive',
            'UPS / Fuente de alimentación',
        ];

        foreach ($tipos as $tipo) {
            TipoEquipo::firstOrCreate(['descripcion' => $tipo]);
        }
    }
}
