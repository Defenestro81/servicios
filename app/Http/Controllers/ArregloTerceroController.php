<?php

namespace App\Http\Controllers;

use App\Models\ArregloTercero;
use App\Models\Orden;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArregloTerceroController extends Controller
{
    public function store(Request $request, Orden $orden): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $data = $request->validate([
            'proveedor_id'   => ['required', 'exists:proveedores,id'],
            'descripcion'    => ['required', 'string'],
            'fecha_llevado'  => ['nullable', 'date'],
            'fecha_recibido' => ['nullable', 'date', 'after_or_equal:fecha_llevado'],
            'importe'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $orden->arreglosTerceros()->create($data);
        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Arreglo con tercero registrado.');
    }

    public function update(Request $request, Orden $orden, ArregloTercero $arreglo): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $data = $request->validate([
            'proveedor_id'   => ['required', 'exists:proveedores,id'],
            'descripcion'    => ['required', 'string'],
            'fecha_llevado'  => ['nullable', 'date'],
            'fecha_recibido' => ['nullable', 'date', 'after_or_equal:fecha_llevado'],
            'importe'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $arreglo->update($data);
        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Arreglo actualizado.');
    }

    public function destroy(Orden $orden, ArregloTercero $arreglo): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $arreglo->delete();
        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Arreglo eliminado.');
    }
}
