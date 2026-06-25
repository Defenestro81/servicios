<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Estado;
use App\Models\Orden;
use App\Models\OrdenEstadoHistorial;
use App\Models\OrdenTecnico;
use App\Models\TipoEquipo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdenController extends Controller
{
    public function index(Request $request): View
    {
        $base = $this->queryBase($request);

        $pendientes = (clone $base)
            ->whereNull('fecha_retirado')
            ->orderByDesc('fecha_ingreso')
            ->get();

        $finalizados = (clone $base)
            ->whereNotNull('fecha_retirado')
            ->orderByDesc('fecha_retirado')
            ->paginate(15, ['*'], 'finalizados')
            ->withQueryString();

        $estados = Estado::orderBy('orden')->get();

        return view('ordenes.index', compact('pendientes', 'finalizados', 'estados'));
    }

    public function mias(Request $request): View
    {
        $base = $this->queryBase($request)
            ->whereHas('tecnicos', fn($t) => $t->where('users.id', auth()->id()));

        $pendientes = (clone $base)
            ->whereNull('fecha_retirado')
            ->orderByDesc('fecha_ingreso')
            ->get();

        $finalizados = (clone $base)
            ->whereNotNull('fecha_retirado')
            ->orderByDesc('fecha_retirado')
            ->paginate(15, ['*'], 'finalizados')
            ->withQueryString();

        $estados = Estado::orderBy('orden')->get();

        return view('ordenes.mias', compact('pendientes', 'finalizados', 'estados'));
    }

    public function buscar(Request $request): View
    {
        $base = $this->queryBase($request);

        $entrega = $request->get('entrega', 'todos');

        $pendientes  = collect();
        $finalizados = collect();

        if (in_array($entrega, ['todos', 'pendientes'])) {
            $pendientes = (clone $base)
                ->whereNull('fecha_retirado')
                ->orderByDesc('fecha_ingreso')
                ->get();
        }

        if (in_array($entrega, ['todos', 'finalizados'])) {
            $finalizados = (clone $base)
                ->whereNotNull('fecha_retirado')
                ->orderByDesc('fecha_retirado')
                ->get();
        }

        $estados  = Estado::orderBy('orden')->get();
        $tecnicos = User::role(['tecnico', 'administrador'])->orderBy('name')->get();
        $busco    = $request->hasAny(['q', 'apellido', 'nombre', 'empresa', 'etiqueta', 'nro_serie', 'estado_id', 'tecnico_id', 'fecha_desde', 'fecha_hasta', 'entrega']);

        return view('ordenes.buscar', compact('pendientes', 'finalizados', 'estados', 'tecnicos', 'busco'));
    }

    /**
     * Arma la consulta base de órdenes aplicando el alcance por rol y todos
     * los filtros de búsqueda (rápida y avanzada) presentes en el request.
     */
    private function queryBase(Request $request)
    {
        $user = auth()->user();

        $query = Orden::with(['cliente.empresa', 'equipo.tipo', 'estado', 'tecnicoPrincipal']);

        // El rol "usuario" sólo ve las órdenes de clientes con su mismo email.
        if ($user->hasRole('usuario')) {
            $query->whereHas('cliente', fn($q) => $q->where('email', $user->email));
        }

        // Búsqueda rápida: apellido, nombre, empresa del cliente o etiqueta del equipo.
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->whereHas('cliente', function ($c) use ($q) {
                    $c->where('apellido', 'like', "%{$q}%")
                        ->orWhere('nombre', 'like', "%{$q}%")
                        ->orWhereHas('empresa', fn($e) => $e->where('nombre', 'like', "%{$q}%"));
                })->orWhereHas('equipo', fn($e) => $e->where('etiqueta', 'like', "%{$q}%"));
            });
        }

        // Filtros avanzados (cada uno opcional).
        if ($request->filled('apellido')) {
            $query->whereHas('cliente', fn($c) => $c->where('apellido', 'like', "%{$request->apellido}%"));
        }
        if ($request->filled('nombre')) {
            $query->whereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$request->nombre}%"));
        }
        if ($request->filled('empresa')) {
            $query->whereHas('cliente.empresa', fn($e) => $e->where('nombre', 'like', "%{$request->empresa}%"));
        }
        if ($request->filled('etiqueta')) {
            $query->whereHas('equipo', fn($e) => $e->where('etiqueta', 'like', "%{$request->etiqueta}%"));
        }
        if ($request->filled('nro_serie')) {
            $query->whereHas('equipo', fn($e) => $e->where('nro_serie', 'like', "%{$request->nro_serie}%"));
        }
        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }
        if ($request->filled('tecnico_id')) {
            $query->whereHas('tecnicos', fn($t) => $t->where('users.id', $request->tecnico_id));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_ingreso', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_ingreso', '<=', $request->fecha_hasta);
        }

        return $query;
    }

    public function create(): View
    {
        $clientes = Cliente::orderBy('apellido')->get();
        $equipos  = Equipo::with('tipo')->orderBy('id', 'desc')->get();
        $tipos    = TipoEquipo::activos()->orderBy('descripcion')->get();
        $empresas = \App\Models\Empresa::orderBy('nombre')->get();

        return view('ordenes.create', compact('clientes', 'equipos', 'tipos', 'empresas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id'              => ['required_without:cliente_nuevo_apellido', 'nullable', 'exists:clientes,id'],
            'cliente_nuevo_apellido'  => ['required_without:cliente_id', 'nullable', 'string', 'max:100'],
            'cliente_nuevo_nombre'    => ['required_with:cliente_nuevo_apellido', 'nullable', 'string', 'max:100'],
            'cliente_nuevo_email'     => ['nullable', 'email', 'max:150'],
            'cliente_nuevo_empresa_id'=> ['nullable', 'exists:empresas,id'],
            'cliente_nuevo_telefonos' => ['nullable', 'array'],
            'cliente_nuevo_telefonos.*'=> ['string', 'max:30'],
            'equipo_id'               => ['required_without:equipo_nuevo_tipo_id', 'nullable', 'exists:equipos,id'],
            'equipo_nuevo_tipo_id'    => ['required_without:equipo_id', 'nullable', 'exists:tipos_equipos,id'],
            'equipo_nuevo_etiqueta'   => ['required_with:equipo_nuevo_tipo_id', 'nullable', 'string', 'max:50', 'unique:equipos,etiqueta'],
            'equipo_nuevo_marca'      => ['nullable', 'string', 'max:80'],
            'equipo_nuevo_modelo'     => ['nullable', 'string', 'max:80'],
            'equipo_nuevo_nro_serie'  => ['nullable', 'string', 'max:100'],
            'fecha_ingreso'           => ['required', 'date'],
            'trabajo_solicitado'      => ['required', 'string'],
            'accesorios'              => ['nullable', 'string'],
            'detalles'                => ['nullable', 'string'],
            'fotos'                   => ['nullable', 'array'],
            'fotos.*'                 => ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:10240'],
        ]);

        // Resolver cliente
        if ($request->filled('cliente_nuevo_apellido')) {
            $cliente = Cliente::create([
                'apellido'   => $request->cliente_nuevo_apellido,
                'nombre'     => $request->cliente_nuevo_nombre,
                'email'      => $request->cliente_nuevo_email,
                'empresa_id' => $request->cliente_nuevo_empresa_id,
            ]);
            foreach (array_filter((array) $request->cliente_nuevo_telefonos) as $numero) {
                $cliente->telefonos()->create(['numero' => $numero]);
            }
        } else {
            $cliente = Cliente::findOrFail($request->cliente_id);
        }

        // Resolver equipo
        if ($request->filled('equipo_nuevo_tipo_id')) {
            $equipo = Equipo::create([
                'tipo_equipo_id' => $request->equipo_nuevo_tipo_id,
                'etiqueta'       => $request->equipo_nuevo_etiqueta,
                'marca'          => $request->equipo_nuevo_marca,
                'modelo'         => $request->equipo_nuevo_modelo,
                'nro_serie'      => $request->equipo_nuevo_nro_serie,
            ]);
        } else {
            $equipo = Equipo::findOrFail($request->equipo_id);
        }

        $estadoInicial = Estado::where('nombre', 'Ingresado')->firstOrFail();

        $orden = Orden::create([
            'cliente_id'        => $cliente->id,
            'equipo_id'         => $equipo->id,
            'estado_id'         => $estadoInicial->id,
            'fecha_ingreso'     => $request->fecha_ingreso,
            'trabajo_solicitado'=> $request->trabajo_solicitado,
            'accesorios'        => $request->accesorios,
            'detalles'          => $request->detalles,
            'created_by'        => auth()->id(),
            'updated_by'        => auth()->id(),
        ]);

        OrdenEstadoHistorial::create([
            'orden_id'  => $orden->id,
            'estado_id' => $estadoInicial->id,
            'user_id'   => auth()->id(),
            'nota'      => 'Orden ingresada.',
        ]);

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $ruta = $foto->store("adjuntos/{$orden->id}", 'public');
                $orden->adjuntos()->create([
                    'ruta'            => $ruta,
                    'nombre_original' => $foto->getClientOriginalName(),
                    'mime_type'       => $foto->getMimeType(),
                    'tamano'          => $foto->getSize(),
                    'descripcion'     => 'Foto de ingreso',
                    'subido_por'      => auth()->id(),
                ]);
            }
        }

        return redirect()->route('ordenes.show', $orden)
            ->with('success', "Orden #{$orden->id} creada.");
    }

    public function show(Orden $orden): View
    {
        $user = auth()->user();

        // El rol "usuario" sólo puede ver órdenes de clientes con su mismo email.
        abort_if(
            $user->hasRole('usuario') && $orden->cliente->email !== $user->email,
            403,
            'No tenés acceso a esta orden.'
        );

        $orden->load([
            'cliente', 'equipo.tipo', 'estado',
            'tecnicos', 'tecnicoPrincipal',
            'historialEstados.estado', 'historialEstados.user',
            'adjuntos', 'arreglosTerceros.proveedor',
            'creadoPor',
        ]);

        $tecnicos    = User::role(['tecnico', 'administrador'])->orderBy('name')->get();
        $estados     = Estado::orderBy('orden')->get();
        $proveedores = \App\Models\Proveedor::orderBy('nombre')->get();
        $puedeEditar = $orden->puedeEditarTecnico(auth()->user());

        // Antecedentes (solo para técnico/admin): otras órdenes del mismo equipo
        // —incluyendo las que trajeron otros clientes— y otras del mismo cliente.
        $ordenesEquipo  = collect();
        $ordenesCliente = collect();

        if ($user->hasRole(['tecnico', 'administrador'])) {
            $ordenesEquipo = Orden::with(['cliente', 'estado'])
                ->where('equipo_id', $orden->equipo_id)
                ->whereKeyNot($orden->id)
                ->orderByDesc('fecha_ingreso')
                ->get();

            $ordenesCliente = Orden::with(['equipo.tipo', 'estado'])
                ->where('cliente_id', $orden->cliente_id)
                ->whereKeyNot($orden->id)
                ->orderByDesc('fecha_ingreso')
                ->get();
        }

        return view('ordenes.show', compact(
            'orden', 'tecnicos', 'estados', 'proveedores', 'puedeEditar',
            'ordenesEquipo', 'ordenesCliente'
        ));
    }

    public function edit(Orden $orden): View
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $orden->load('equipo.tipo', 'cliente');

        return view('ordenes.edit', compact('orden'));
    }

    public function update(Request $request, Orden $orden): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $data = $request->validate([
            'trabajo_solicitado' => ['required', 'string'],
            'trabajo_realizado'  => ['nullable', 'string'],
            'accesorios'         => ['nullable', 'string'],
            'detalles'           => ['nullable', 'string'],
            'costo_mano_obra'    => ['nullable', 'numeric', 'min:0'],
            'costo_repuestos'    => ['nullable', 'numeric', 'min:0'],
            'fecha_terminado'    => ['nullable', 'date'],
            'fecha_retirado'     => ['nullable', 'date'],
        ]);

        $data['costo_mano_obra'] = $data['costo_mano_obra'] ?? 0;
        $data['costo_repuestos'] = $data['costo_repuestos'] ?? 0;
        $data['updated_by']      = auth()->id();
        $orden->update($data);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Orden actualizada.');
    }

    public function cambiarEstado(Request $request, Orden $orden): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $request->validate([
            'estado_id' => ['required', 'exists:estados,id'],
            'nota'      => ['nullable', 'string', 'max:500'],
        ]);

        $estado = Estado::findOrFail($request->estado_id);

        $cambios = [
            'estado_id'  => $estado->id,
            'updated_by' => auth()->id(),
        ];

        // Registro automático de fechas según el estado al que se pasa.
        if ($estado->nombre === 'Listo' && !$orden->fecha_terminado) {
            $cambios['fecha_terminado'] = now();
        }
        if ($estado->nombre === 'Entregado' && !$orden->fecha_retirado) {
            $cambios['fecha_retirado'] = now();
        }

        $orden->update($cambios);

        OrdenEstadoHistorial::create([
            'orden_id'  => $orden->id,
            'estado_id' => $estado->id,
            'user_id'   => auth()->id(),
            'nota'      => $request->nota,
        ]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Estado actualizado.');
    }

    public function tomarOrden(Orden $orden): RedirectResponse
    {
        abort_if(!auth()->user()->hasRole(['tecnico', 'administrador']), 403);

        if ($orden->estaAsignada()) {
            return redirect()->route('ordenes.show', $orden)
                ->with('error', 'La orden ya tiene técnico asignado.');
        }

        OrdenTecnico::create([
            'orden_id'  => $orden->id,
            'user_id'   => auth()->id(),
            'principal' => true,
        ]);

        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Tomaste la orden.');
    }

    public function asignarTecnico(Request $request, Orden $orden): RedirectResponse
    {
        abort_if(!auth()->user()->hasRole('administrador'), 403);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        OrdenTecnico::updateOrCreate(
            ['orden_id' => $orden->id],
            ['user_id' => $request->user_id, 'principal' => true]
        );

        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Técnico asignado.');
    }
}
