<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\TipoEquipo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Equipo::with('tipo')->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('etiqueta', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%")
                    ->orWhere('nro_serie', 'like', "%{$q}%");
            });
        }

        $equipos = $query->paginate(20)->withQueryString();

        return view('equipos.index', compact('equipos'));
    }

    public function create(): View
    {
        $tipos = TipoEquipo::activos()->orderBy('descripcion')->get();
        return view('equipos.create', compact('tipos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo_equipo_id' => ['required', 'exists:tipos_equipos,id'],
            'etiqueta'       => ['required', 'string', 'max:50', 'unique:equipos,etiqueta'],
            'marca'          => ['nullable', 'string', 'max:80'],
            'modelo'         => ['nullable', 'string', 'max:80'],
            'nro_serie'      => ['nullable', 'string', 'max:100'],
        ]);

        $equipo = Equipo::create($data);

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Equipo registrado.');
    }

    public function show(Equipo $equipo): View
    {
        $equipo->load(['tipo', 'ordenes.cliente', 'ordenes.estado']);
        return view('equipos.show', compact('equipo'));
    }

    public function edit(Equipo $equipo): View
    {
        $tipos = TipoEquipo::activos()->orderBy('descripcion')->get();
        return view('equipos.edit', compact('equipo', 'tipos'));
    }

    public function update(Request $request, Equipo $equipo): RedirectResponse
    {
        $data = $request->validate([
            'tipo_equipo_id' => ['required', 'exists:tipos_equipos,id'],
            'etiqueta'       => ['nullable', 'string', 'max:50', "unique:equipos,etiqueta,{$equipo->id}"],
            'marca'          => ['nullable', 'string', 'max:80'],
            'modelo'         => ['nullable', 'string', 'max:80'],
            'nro_serie'      => ['nullable', 'string', 'max:100'],
        ]);

        $equipo->update($data);

        return redirect()->route('equipos.show', $equipo)
            ->with('success', 'Equipo actualizado.');
    }
}
