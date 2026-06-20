<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoEquipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipoEquipoController extends Controller
{
    public function index(): View
    {
        $tipos = TipoEquipo::orderBy('descripcion')->get();
        return view('admin.tipos-equipos.index', compact('tipos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'descripcion' => ['required', 'string', 'max:100', 'unique:tipos_equipos,descripcion'],
            'notas'       => ['nullable', 'string', 'max:500'],
        ]);

        TipoEquipo::create([
            'descripcion' => $request->descripcion,
            'notas'       => $request->notas,
            'activo'      => true,
        ]);

        return back()->with('success', 'Tipo de equipo creado.');
    }

    public function update(Request $request, TipoEquipo $tipoEquipo): RedirectResponse
    {
        $request->validate([
            'descripcion' => ['required', 'string', 'max:100', "unique:tipos_equipos,descripcion,{$tipoEquipo->id}"],
            'notas'       => ['nullable', 'string', 'max:500'],
        ]);

        $tipoEquipo->update([
            'descripcion' => $request->descripcion,
            'notas'       => $request->notas,
        ]);

        return back()->with('success', 'Tipo actualizado.');
    }

    public function toggleActivo(TipoEquipo $tipoEquipo): RedirectResponse
    {
        $nuevo = !$tipoEquipo->activo;
        $tipoEquipo->update(['activo' => $nuevo]);

        return back()->with('success', $nuevo ? 'Tipo reactivado.' : 'Tipo desactivado.');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $data = $request->validate([
            'descripcion' => ['required', 'string', 'max:100', 'unique:tipos_equipos,descripcion'],
        ]);

        $tipo = TipoEquipo::create(['descripcion' => $data['descripcion'], 'activo' => true]);

        return response()->json(['id' => $tipo->id, 'descripcion' => $tipo->descripcion], 201);
    }
}
