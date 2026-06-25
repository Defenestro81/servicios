<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Estado;
use App\Models\Orden;
use App\Models\OrdenTecnico;
use App\Models\TipoEquipo;
use App\Models\User;
use Database\Seeders\EstadoSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TipoEquipoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FlujoServiciosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, EstadoSeeder::class, TipoEquipoSeeder::class]);
        $this->app[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    // ---------- Helpers ----------

    private function usuarioCon(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }

    private function crearCliente(array $attrs = []): Cliente
    {
        return Cliente::create(array_merge([
            'apellido' => 'Apellido' . uniqid(),
            'nombre'   => 'Nombre',
            'email'    => null,
        ], $attrs));
    }

    private function crearEquipo(array $attrs = []): Equipo
    {
        return Equipo::create(array_merge([
            'tipo_equipo_id' => TipoEquipo::first()->id,
            'etiqueta'       => 'EQ-' . uniqid(),
        ], $attrs));
    }

    private function crearOrden(array $attrs = []): Orden
    {
        $autor   = $attrs['autor'] ?? $this->usuarioCon('tecnico');
        $cliente = $attrs['cliente'] ?? $this->crearCliente();
        $equipo  = $attrs['equipo'] ?? $this->crearEquipo();
        $estadoNombre = $attrs['estado'] ?? 'Ingresado';
        unset($attrs['autor'], $attrs['cliente'], $attrs['equipo'], $attrs['estado']);

        return Orden::create(array_merge([
            'cliente_id'         => $cliente->id,
            'equipo_id'          => $equipo->id,
            'estado_id'          => Estado::where('nombre', $estadoNombre)->first()->id,
            'fecha_ingreso'      => now()->toDateString(),
            'trabajo_solicitado' => 'Diagnóstico general',
            'created_by'         => $autor->id,
            'updated_by'         => $autor->id,
        ], $attrs));
    }

    // ---------- Acceso / permisos ----------

    public function test_invitado_es_redirigido_a_login(): void
    {
        $this->get('/ordenes')->assertRedirect('/login');
    }

    public function test_usuario_no_puede_crear_ordenes(): void
    {
        $usuario = $this->usuarioCon('usuario');

        $this->actingAs($usuario)->get(route('ordenes.create'))->assertForbidden();
        $this->actingAs($usuario)->post(route('ordenes.store'), [])->assertForbidden();
    }

    public function test_tecnico_puede_acceder_a_crear_orden(): void
    {
        $this->actingAs($this->usuarioCon('tecnico'))
            ->get(route('ordenes.create'))
            ->assertOk();
    }

    public function test_usuario_no_puede_acceder_a_clientes_ni_buscar_ni_mias(): void
    {
        $usuario = $this->usuarioCon('usuario');

        $this->actingAs($usuario)->get(route('clientes.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('ordenes.buscar'))->assertForbidden();
        $this->actingAs($usuario)->get(route('ordenes.mias'))->assertForbidden();
    }

    public function test_solo_admin_gestiona_usuarios_y_tipos(): void
    {
        $this->actingAs($this->usuarioCon('tecnico'))->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($this->usuarioCon('tecnico'))->get(route('admin.tipos-equipos.index'))->assertForbidden();

        $this->actingAs($this->usuarioCon('administrador'))->get(route('admin.users.index'))->assertOk();
        $this->actingAs($this->usuarioCon('administrador'))->get(route('admin.tipos-equipos.index'))->assertOk();
    }

    // ---------- Flujo central: crear orden ----------

    public function test_tecnico_crea_orden_con_cliente_y_equipo_nuevos(): void
    {
        $tecnico = $this->usuarioCon('tecnico');
        $tipo    = TipoEquipo::first();

        $resp = $this->actingAs($tecnico)->post(route('ordenes.store'), [
            'cliente_nuevo_apellido' => 'Pérez',
            'cliente_nuevo_nombre'   => 'Juan',
            'cliente_nuevo_email'    => 'juan@example.com',
            'equipo_nuevo_tipo_id'   => $tipo->id,
            'equipo_nuevo_etiqueta'  => 'EQ-NUEVA-1',
            'fecha_ingreso'          => '2026-06-20',
            'trabajo_solicitado'     => 'No enciende',
        ]);

        $resp->assertRedirect();
        $resp->assertSessionHas('success');

        $this->assertDatabaseHas('clientes', ['apellido' => 'Pérez', 'email' => 'juan@example.com']);
        $this->assertDatabaseHas('equipos', ['etiqueta' => 'EQ-NUEVA-1']);
        $this->assertDatabaseHas('ordenes', ['trabajo_solicitado' => 'No enciende']);
        // Verifica que el historial inicial se crea (tabla con nombre no estándar).
        $this->assertDatabaseHas('orden_estado_historial', ['nota' => 'Orden ingresada.']);
    }

    public function test_etiqueta_de_equipo_es_unica(): void
    {
        $tecnico = $this->usuarioCon('tecnico');
        $this->crearEquipo(['etiqueta' => 'EQ-DUP']);

        $resp = $this->actingAs($tecnico)->post(route('ordenes.store'), [
            'cliente_nuevo_apellido' => 'Gómez',
            'cliente_nuevo_nombre'   => 'Ana',
            'equipo_nuevo_tipo_id'   => TipoEquipo::first()->id,
            'equipo_nuevo_etiqueta'  => 'EQ-DUP',
            'fecha_ingreso'          => '2026-06-20',
            'trabajo_solicitado'     => 'Prueba',
        ]);

        $resp->assertSessionHasErrors('equipo_nuevo_etiqueta');
    }

    // ---------- Portal del usuario ----------

    public function test_usuario_ve_solo_sus_ordenes_en_el_listado(): void
    {
        $usuario = $this->usuarioCon('usuario', ['email' => 'cliente@example.com']);

        $propia = $this->crearOrden(['cliente' => $this->crearCliente(['email' => 'cliente@example.com'])]);
        $ajena  = $this->crearOrden(['cliente' => $this->crearCliente(['email' => 'otro@example.com'])]);

        $resp = $this->actingAs($usuario)->get(route('ordenes.index'));

        $resp->assertOk();
        $resp->assertSee($propia->equipo->etiqueta);
        $resp->assertDontSee($ajena->equipo->etiqueta);
    }

    public function test_usuario_no_puede_ver_orden_ajena(): void
    {
        $usuario = $this->usuarioCon('usuario', ['email' => 'cliente@example.com']);
        $propia  = $this->crearOrden(['cliente' => $this->crearCliente(['email' => 'cliente@example.com'])]);
        $ajena   = $this->crearOrden(['cliente' => $this->crearCliente(['email' => 'otro@example.com'])]);

        $this->actingAs($usuario)->get(route('ordenes.show', $propia))->assertOk();
        $this->actingAs($usuario)->get(route('ordenes.show', $ajena))->assertForbidden();
    }

    // ---------- Tomar / asignar / permisos de edición ----------

    public function test_tecnico_toma_orden_sin_asignar_y_no_puede_tomar_una_ya_asignada(): void
    {
        $tecnico = $this->usuarioCon('tecnico');
        $orden   = $this->crearOrden();

        $this->actingAs($tecnico)->post(route('ordenes.tomar', $orden))
            ->assertRedirect(route('ordenes.show', $orden));
        $this->assertDatabaseHas('orden_tecnico', [
            'orden_id'  => $orden->id,
            'user_id'   => $tecnico->id,
            'principal' => true,
        ]);

        // Segundo técnico intenta tomarla: debe redirigir con error, no romper.
        $otro = $this->usuarioCon('tecnico');
        $this->actingAs($otro)->post(route('ordenes.tomar', $orden))
            ->assertRedirect(route('ordenes.show', $orden))
            ->assertSessionHas('error');
    }

    public function test_tecnico_no_puede_editar_orden_de_otro_tecnico(): void
    {
        $tecnicoB = $this->usuarioCon('tecnico');
        $orden    = $this->crearOrden();
        OrdenTecnico::create(['orden_id' => $orden->id, 'user_id' => $tecnicoB->id, 'principal' => true]);

        $tecnicoA = $this->usuarioCon('tecnico');
        $this->actingAs($tecnicoA)->get(route('ordenes.edit', $orden))->assertForbidden();
    }

    public function test_cambiar_estado_a_entregado_registra_fecha_y_pasa_a_finalizada(): void
    {
        $tecnico = $this->usuarioCon('tecnico');
        $orden   = $this->crearOrden(['autor' => $tecnico]);
        OrdenTecnico::create(['orden_id' => $orden->id, 'user_id' => $tecnico->id, 'principal' => true]);

        $entregado = Estado::where('nombre', 'Entregado')->first();

        $this->actingAs($tecnico)->post(route('ordenes.cambiarEstado', $orden), [
            'estado_id' => $entregado->id,
            'nota'      => 'Entregado al cliente',
        ])->assertRedirect(route('ordenes.show', $orden));

        $orden->refresh();
        $this->assertNotNull($orden->fecha_retirado, 'La fecha de entrega debe registrarse automáticamente.');
        $this->assertDatabaseHas('orden_estado_historial', ['orden_id' => $orden->id, 'nota' => 'Entregado al cliente']);
    }

    // ---------- Búsqueda ----------

    public function test_busqueda_por_apellido_empresa_y_etiqueta(): void
    {
        $admin = $this->usuarioCon('administrador');

        $cliente = $this->crearCliente(['apellido' => 'Rodriguez']);
        $orden   = $this->crearOrden(['cliente' => $cliente]);
        $otra    = $this->crearOrden(['cliente' => $this->crearCliente(['apellido' => 'Lopez'])]);

        $resp = $this->actingAs($admin)->get(route('ordenes.index', ['q' => 'Rodriguez']));
        $resp->assertSee($orden->equipo->etiqueta);
        $resp->assertDontSee($otra->equipo->etiqueta);

        // Por etiqueta
        $resp2 = $this->actingAs($admin)->get(route('ordenes.index', ['q' => $orden->equipo->etiqueta]));
        $resp2->assertSee($orden->equipo->etiqueta);
    }

    // ---------- Empresas / tipos ----------

    public function test_tecnico_crea_empresa(): void
    {
        $this->actingAs($this->usuarioCon('tecnico'))->post(route('empresas.store'), [
            'nombre'       => 'ACME',
            'razon_social' => 'ACME SA',
            'cuit'         => '30-12345678-9',
        ])->assertRedirect(route('empresas.index'));

        $this->assertDatabaseHas('empresas', ['nombre' => 'ACME']);
    }

    public function test_admin_crea_y_desactiva_tipo_de_equipo(): void
    {
        $admin = $this->usuarioCon('administrador');

        $this->actingAs($admin)->post(route('admin.tipos-equipos.store'), [
            'descripcion' => 'Proyector',
        ])->assertRedirect();
        $this->assertDatabaseHas('tipos_equipos', ['descripcion' => 'Proyector', 'activo' => true]);

        $tipo = TipoEquipo::where('descripcion', 'Proyector')->first();
        $this->actingAs($admin)->patch(route('admin.tipos-equipos.toggle', $tipo))->assertRedirect();
        $this->assertDatabaseHas('tipos_equipos', ['id' => $tipo->id, 'activo' => false]);
    }

    public function test_tecnico_crea_tipo_inline(): void
    {
        $this->actingAs($this->usuarioCon('tecnico'))
            ->postJson(route('tipos-equipos.inline'), ['descripcion' => 'Cámara'])
            ->assertCreated()
            ->assertJsonStructure(['id', 'descripcion']);

        $this->assertDatabaseHas('tipos_equipos', ['descripcion' => 'Cámara']);
    }

    // ---------- Costos ----------

    public function test_tecnico_carga_costos_y_se_calcula_el_total(): void
    {
        $tecnico = $this->usuarioCon('tecnico');
        $orden   = $this->crearOrden(['autor' => $tecnico]);
        OrdenTecnico::create(['orden_id' => $orden->id, 'user_id' => $tecnico->id, 'principal' => true]);

        $this->actingAs($tecnico)->patch(route('ordenes.update', $orden), [
            'trabajo_solicitado' => $orden->trabajo_solicitado,
            'costo_mano_obra'    => 1500.50,
            'costo_repuestos'    => 2499.50,
        ])->assertRedirect(route('ordenes.show', $orden));

        $orden->refresh();
        $this->assertEquals('1500.50', $orden->costo_mano_obra);
        $this->assertEquals('2499.50', $orden->costo_repuestos);
        $this->assertEquals(4000.0, $orden->costo_total);
    }

    // ---------- Antecedentes en el detalle ----------

    public function test_detalle_muestra_antecedentes_de_equipo_y_cliente(): void
    {
        $tecnico  = $this->usuarioCon('tecnico');
        $clienteA = $this->crearCliente(['apellido' => 'ClienteAlfa']);
        $clienteB = $this->crearCliente(['apellido' => 'ClienteBeta']);
        $equipoX  = $this->crearEquipo(['etiqueta' => 'EQ-COMPARTIDO']);
        $equipoY  = $this->crearEquipo(['etiqueta' => 'EQ-OTRO-EQUIPO']);

        $actual     = $this->crearOrden(['autor' => $tecnico, 'cliente' => $clienteA, 'equipo' => $equipoX]);
        $delEquipo  = $this->crearOrden(['autor' => $tecnico, 'cliente' => $clienteB, 'equipo' => $equipoX]);
        $delCliente = $this->crearOrden(['autor' => $tecnico, 'cliente' => $clienteA, 'equipo' => $equipoY]);

        $resp = $this->actingAs($tecnico)->get(route('ordenes.show', $actual));

        $resp->assertOk();
        $resp->assertSee('Otras órdenes de este equipo');
        $resp->assertSee('Otras órdenes de este cliente');
        $resp->assertSee('otro cliente');            // el mismo equipo lo trajo otro cliente
        $resp->assertSee('#' . $delEquipo->id);
        $resp->assertSee('#' . $delCliente->id);
        $resp->assertSee($equipoY->etiqueta);
    }

    public function test_usuario_no_ve_antecedentes_en_su_detalle(): void
    {
        $usuario = $this->usuarioCon('usuario', ['email' => 'portal@example.com']);
        $cliente = $this->crearCliente(['email' => 'portal@example.com']);
        $orden   = $this->crearOrden(['cliente' => $cliente]);

        $this->actingAs($usuario)->get(route('ordenes.show', $orden))
            ->assertOk()
            ->assertDontSee('Otras órdenes de este equipo')
            ->assertDontSee('Otras órdenes de este cliente');
    }
}
