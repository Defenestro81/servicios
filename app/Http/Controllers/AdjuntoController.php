<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use App\Models\Orden;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    public function store(Request $request, Orden $orden): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        $request->validate([
            'archivos'    => ['required', 'array'],
            'archivos.*'  => ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:10240'],
            'descripcion' => ['nullable', 'string', 'max:150'],
        ]);

        foreach ($request->file('archivos') as $archivo) {
            $ruta = $archivo->store("adjuntos/{$orden->id}", 'public');
            $orden->adjuntos()->create([
                'ruta'            => $ruta,
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type'       => $archivo->getMimeType(),
                'tamano'          => $archivo->getSize(),
                'descripcion'     => $request->descripcion,
                'subido_por'      => auth()->id(),
            ]);
        }

        $orden->update(['updated_by' => auth()->id()]);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Archivos subidos.');
    }

    public function destroy(Orden $orden, Adjunto $adjunto): RedirectResponse
    {
        abort_if(!$orden->puedeEditarTecnico(auth()->user()), 403);

        Storage::disk('public')->delete($adjunto->ruta);
        $adjunto->delete();

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Archivo eliminado.');
    }
}
