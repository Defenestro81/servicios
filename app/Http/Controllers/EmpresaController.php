<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Empresa::withCount('clientes')->orderBy('nombre');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('razon_social', 'like', "%{$q}%")
                    ->orWhere('cuit', 'like', "%{$q}%");
            });
        }

        $empresas = $query->paginate(20)->withQueryString();

        return view('empresas.index', compact('empresas'));
    }

    public function create(): View
    {
        return view('empresas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'razon_social' => ['required', 'string', 'max:150'],
            'cuit'         => ['required', 'string', 'max:13', 'unique:empresas,cuit'],
        ]);

        $empresa = Empresa::create($data);

        return redirect()->route('empresas.index')
            ->with('success', "Empresa «{$empresa->nombre}» creada.");
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'razon_social' => ['required', 'string', 'max:150'],
            'cuit'         => ['required', 'string', 'max:13', "unique:empresas,cuit,{$empresa->id}"],
        ]);

        $empresa->update($data);

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa actualizada.');
    }

    public function storeInline(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'razon_social' => ['required', 'string', 'max:150'],
            'cuit'         => ['required', 'string', 'max:13', 'unique:empresas,cuit'],
        ]);

        $empresa = Empresa::create($data);

        return response()->json(['id' => $empresa->id, 'nombre' => $empresa->nombre], 201);
    }
}
