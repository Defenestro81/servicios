<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Ingresado',           'orden' => 1],
            ['nombre' => 'En diagnóstico',       'orden' => 2],
            ['nombre' => 'En reparación',        'orden' => 3],
            ['nombre' => 'Esperando repuesto',   'orden' => 4],
            ['nombre' => 'Listo',                'orden' => 5],
            ['nombre' => 'Entregado',            'orden' => 6],
            ['nombre' => 'Sin reparación',       'orden' => 7],
        ];

        foreach ($estados as $estado) {
            Estado::firstOrCreate(['nombre' => $estado['nombre']], $estado);
        }
    }
}
