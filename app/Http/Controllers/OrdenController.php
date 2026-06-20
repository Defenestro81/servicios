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
        $user = auth()->user();

        $query = Orden::with(['cliente', 'equipo.tipo', 'estado', 'tecnicoPrincipal'])
            ->orderBy('id', 'desc');

        if ($user->hasRole('usuario')) {
            $query->whereHas('cliente', fn($q) => $q->where('email', $user->email));
        }

        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('cliente', fn($s) => $s->where('apellido', 'like', "%{$q}%")->orWhere('nombre', 'like', "%{$q}%"))
                ->orWhereHas('equipo', fn($s) => $s->where('etiqueta', 'like', "%{$q}%")->orWhere('marca', 'like', "%{$q}%"));
        }

        $ordenes = $query->paginate(25)->withQueryString();
        $estados = Estado::orderBy('orden')->get();

        return view('ordenes.index', compact('ordenes', 'estados'));
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

        return view('ordenes.show', compact('orden', 'tecnicos', 'estados', 'proveedores', 'puedeEditar'));
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
            'fecha_terminado'    => ['nullable', 'date'],
            'fecha_retirado'     => ['nullable', 'date'],
        ]);

        $data['updated_by'] = auth()->id();
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

        $orden->update([
            'estado_id'  => $request->estado_id,
            'updated_by' => auth()->id(),
        ]);

        OrdenEstadoHistorial::create([
            'orden_id'  => $orden->id,
            'estado_id' => $request->estado_id,
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
