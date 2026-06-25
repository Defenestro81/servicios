<?php

namespace Database\Seeders;

use App\Models\ArregloTercero;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Equipo;
use App\Models\Estado;
use App\Models\Orden;
use App\Models\OrdenEstadoHistorial;
use App\Models\OrdenTecnico;
use App\Models\Proveedor;
use App\Models\TipoEquipo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Datos de demostración para probar el sistema.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Genera: 3 empresas, 30 clientes (10 con empresa), 40 equipos y 50 órdenes
 * con estados variados, técnicos asignados, historial y algunos arreglos con terceros.
 * Algunos equipos se reutilizan en órdenes de distinto cliente.
 *
 * Es idempotente para las entidades con clave natural (empresa/cliente/equipo) y
 * omite la creación de órdenes si los clientes demo ya tienen órdenes.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Prerrequisitos (roles, estados, tipos). Son idempotentes.
        $this->call([RoleSeeder::class, EstadoSeeder::class, TipoEquipoSeeder::class]);

        $faker = \Faker\Factory::create('es_ES');

        DB::transaction(function () use ($faker) {
            $admin = User::where('email', 'admin@servicios.com')->first()
                ?? User::role('administrador')->firstOrFail();

            // ---- Técnicos de demostración ----
            $tecnicos = collect(range(1, 2))->map(function ($i) {
                $t = User::firstOrCreate(
                    ['email' => "tecnico{$i}@demo.test"],
                    ['name' => "Técnico Demo {$i}", 'password' => Hash::make('password'), 'email_verified_at' => now()]
                );
                Role::firstOrCreate(['name' => 'tecnico', 'guard_name' => 'web']);
                $t->assignRole('tecnico');
                return $t;
            });

            // ---- Usuario del portal (su email coincide con cliente1@demo.test) ----
            $portal = User::firstOrCreate(
                ['email' => 'cliente1@demo.test'],
                ['name' => 'Cliente Demo', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $portal->assignRole('usuario');

            // ---- 3 empresas ----
            $empresas = collect([
                ['nombre' => 'TecnoSur SRL',          'razon_social' => 'TecnoSur Sociedad de Responsabilidad Limitada', 'cuit' => '30-70111111-1'],
                ['nombre' => 'Distribuidora Andina',  'razon_social' => 'Distribuidora Andina SA',                       'cuit' => '30-70222222-2'],
                ['nombre' => 'Estudio Contable Río',  'razon_social' => 'Estudio Contable Río SC',                       'cuit' => '27-70333333-3'],
            ])->map(fn ($e) => Empresa::firstOrCreate(['cuit' => $e['cuit']], $e));

            // ---- 30 clientes (los primeros 10 con empresa) ----
            $clientes = collect(range(1, 30))->map(function ($i) use ($faker, $empresas) {
                $cliente = Cliente::firstOrCreate(
                    ['email' => "cliente{$i}@demo.test"],
                    [
                        'apellido'   => $faker->lastName(),
                        'nombre'     => $faker->firstName(),
                        'empresa_id' => $i <= 10 ? $empresas->random()->id : null,
                    ]
                );
                if ($cliente->telefonos()->count() === 0) {
                    $cliente->telefonos()->create(['numero' => $faker->numerify('29## ######')]);
                    if ($faker->boolean(30)) {
                        $cliente->telefonos()->create(['numero' => $faker->numerify('11 #### ####')]);
                    }
                }
                return $cliente;
            });

            // ---- 40 equipos ----
            $tipos  = TipoEquipo::all();
            $marcas = ['HP', 'Dell', 'Lenovo', 'Asus', 'Acer', 'Samsung', 'LG', 'Epson', 'Canon', 'Kingston', 'APC', 'Logitech', 'Motorola', 'Xiaomi'];
            $equipos = collect(range(1, 40))->map(function ($i) use ($faker, $tipos, $marcas) {
                return Equipo::firstOrCreate(
                    ['etiqueta' => 'EQ-D' . str_pad((string) $i, 4, '0', STR_PAD_LEFT)],
                    [
                        'tipo_equipo_id' => $tipos->random()->id,
                        'marca'          => $faker->randomElement($marcas),
                        'modelo'         => strtoupper($faker->bothify('??-###')),
                        'nro_serie'      => strtoupper($faker->bothify('SN########')),
                    ]
                );
            });

            // ---- 3 proveedores (para arreglos con terceros) ----
            $proveedores = collect([
                ['nombre' => 'Repuestos del Sur',   'contacto' => 'Mostrador', 'telefono' => '2944 410000', 'email' => 'ventas@repsur.test'],
                ['nombre' => 'Service Oficial HP',   'contacto' => 'Soporte',   'telefono' => '0800 111 2222', 'email' => 'service@hp.test'],
                ['nombre' => 'Microelectrónica Río', 'contacto' => 'Taller',    'telefono' => '2944 420000', 'email' => 'info@microrio.test'],
            ])->map(fn ($p) => Proveedor::firstOrCreate(['nombre' => $p['nombre']], $p));

            // Evita duplicar órdenes si el demo ya fue cargado.
            if (Orden::whereIn('cliente_id', $clientes->pluck('id'))->exists()) {
                $this->command->warn('Los datos demo ya tienen órdenes cargadas. Se omite la creación de órdenes.');
                return;
            }

            // ---- 50 órdenes ----
            $trabajos = [
                'No enciende', 'Pantalla rota', 'Lento, posible virus', 'No carga la batería',
                'Se reinicia solo', 'No conecta a internet', 'Ruido en el ventilador', 'No imprime',
                'Teclas que no responden', 'Actualización de sistema', 'Limpieza y mantenimiento',
                'Recuperación de datos',
            ];
            $realizados = [
                'Se reemplazó la fuente.', 'Formateo e instalación del sistema operativo.',
                'Cambio de pantalla.', 'Limpieza interna y cambio de pasta térmica.',
                'Reemplazo de batería.', 'Reparación de pista en placa.', 'Cambio de teclado.',
                'Actualización de drivers y BIOS.',
            ];
            $accesorios = ['Cargador', 'Funda', 'Cable USB', 'Ninguno', 'Cargador y funda', 'Mouse', 'Bolso de transporte'];

            // pool de estados (varios "Entregado" para tener finalizadas)
            $poolEstados = ['Ingresado', 'En diagnóstico', 'En reparación', 'Esperando repuesto',
                'Listo', 'Entregado', 'Entregado', 'Entregado', 'Sin reparación'];
            $estados = Estado::all()->keyBy('nombre');

            $equipoClientes = []; // equipo_id => [cliente_id usados]
            $totalArreglos  = 0;

            for ($n = 0; $n < 50; $n++) {
                // Garantizamos 3 órdenes para el cliente del portal (con equipos distintos),
                // y para el resto elegimos al azar reutilizando equipos con OTRO cliente.
                if ($n < 3) {
                    $cliente = $clientes->first();
                    $equipo  = $equipos[$n];
                } else {
                    $equipo  = $equipos->random();
                    $previos = $equipoClientes[$equipo->id] ?? [];
                    $disp    = $clientes->whereNotIn('id', $previos);
                    $cliente = $disp->isNotEmpty() ? $disp->random() : $clientes->random();
                }
                $equipoClientes[$equipo->id][] = $cliente->id;

                $nombreEstado = $faker->randomElement($poolEstados);
                $estado       = $estados[$nombreEstado];
                $ingreso      = Carbon::now()->subDays(rand(1, 120));

                $terminado = null;
                $retirado  = null;
                $trabajoRealizado = null;

                if (in_array($nombreEstado, ['Listo', 'Entregado', 'Sin reparación'])) {
                    $terminado = (clone $ingreso)->addDays(rand(1, 12));
                    $trabajoRealizado = $nombreEstado === 'Sin reparación'
                        ? 'Sin reparación viable. Se devuelve el equipo al cliente.'
                        : $faker->randomElement($realizados);
                }
                if (in_array($nombreEstado, ['Entregado', 'Sin reparación'])) {
                    $retirado = (clone $terminado)->addDays(rand(0, 8));
                }

                $orden = Orden::create([
                    'cliente_id'         => $cliente->id,
                    'equipo_id'          => $equipo->id,
                    'estado_id'          => $estado->id,
                    'fecha_ingreso'      => $ingreso->toDateString(),
                    'fecha_terminado'    => $terminado?->toDateString(),
                    'fecha_retirado'     => $retirado?->toDateString(),
                    'trabajo_solicitado' => $faker->randomElement($trabajos),
                    'accesorios'         => $faker->randomElement($accesorios),
                    'trabajo_realizado'  => $trabajoRealizado,
                    'detalles'           => $faker->boolean(60) ? $faker->sentence(8) : null,
                    'created_by'         => $admin->id,
                    'updated_by'         => $admin->id,
                ]);

                // Técnico asignado en ~75% de los casos.
                $tecnico = null;
                if ($nombreEstado !== 'Ingresado' || $faker->boolean(50)) {
                    $tecnico = $tecnicos->random();
                    OrdenTecnico::create([
                        'orden_id'  => $orden->id,
                        'user_id'   => $tecnico->id,
                        'principal' => true,
                    ]);
                }

                // Historial coherente.
                $this->historial($orden, $estados['Ingresado'], $admin, 'Orden ingresada.', $ingreso);
                if ($nombreEstado !== 'Ingresado') {
                    $autor = $tecnico ?? $admin;
                    $cuando = $retirado ?? $terminado ?? Carbon::now();
                    $this->historial($orden, $estado, $autor, "Estado actualizado a {$nombreEstado}.", $cuando);
                }

                // Algunos arreglos con terceros (en órdenes que pasaron por reparación).
                if ($totalArreglos < 8 && in_array($nombreEstado, ['En reparación', 'Esperando repuesto', 'Entregado']) && $faker->boolean(40)) {
                    $llevado = (clone $ingreso)->addDays(rand(1, 5));
                    ArregloTercero::create([
                        'orden_id'       => $orden->id,
                        'proveedor_id'   => $proveedores->random()->id,
                        'descripcion'    => $faker->randomElement(['Reballing de GPU', 'Cambio de pin de carga', 'Reparación de fuente', 'Recuperación de disco']),
                        'fecha_llevado'  => $llevado->toDateString(),
                        'fecha_recibido' => $nombreEstado === 'Entregado' ? (clone $llevado)->addDays(rand(2, 10))->toDateString() : null,
                        'importe'        => $faker->randomFloat(2, 5000, 60000),
                    ]);
                    $totalArreglos++;
                }
            }

            $this->command->info('Demo cargado: ' . $empresas->count() . ' empresas, ' . $clientes->count() . ' clientes, ' . $equipos->count() . ' equipos, 50 órdenes.');
        });
    }

    private function historial(Orden $orden, Estado $estado, User $autor, string $nota, Carbon $cuando): void
    {
        $h = new OrdenEstadoHistorial([
            'orden_id'  => $orden->id,
            'estado_id' => $estado->id,
            'user_id'   => $autor->id,
            'nota'      => $nota,
        ]);
        $h->created_at = $cuando;
        $h->updated_at = $cuando;
        $h->save();
    }
}
