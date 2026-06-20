<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cliente::with('empresa')->withCount('ordenes')->orderBy('apellido');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('apellido', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $clientes = $query->paginate(20)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create(): View
    {
        $empresas = Empresa::orderBy('nombre')->get();
        return view('clientes.create', compact('empresas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'apellido'   => ['required', 'string', 'max:100'],
            'nombre'     => ['required', 'string', 'max:100'],
            'email'      => ['nullable', 'email', 'max:150'],
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'telefonos'  => ['nullable', 'array'],
            'telefonos.*'=> ['string', 'max:30'],
        ]);

        $cliente = Cliente::create($data);

        if (!empty($data['telefonos'])) {
            foreach (array_filter($data['telefonos']) as $numero) {
                $cliente->telefonos()->create(['numero' => $numero]);
            }
        }

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load(['empresa', 'telefonos', 'ordenes.estado', 'ordenes.equipo', 'user']);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        $cliente->load('telefonos');
        $empresas = Empresa::orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'empresas'));
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validate([
            'apellido'   => ['required', 'string', 'max:100'],
            'nombre'     => ['required', 'string', 'max:100'],
            'email'      => ['nullable', 'email', 'max:150'],
            'empresa_id' => ['nullable', 'exists:empresas,id'],
            'telefonos'  => ['nullable', 'array'],
            'telefonos.*'=> ['string', 'max:30'],
        ]);

        $cliente->update($data);

        $cliente->telefonos()->delete();
        if (!empty($data['telefonos'])) {
            foreach (array_filter($data['telefonos']) as $numero) {
                $cliente->telefonos()->create(['numero' => $numero]);
            }
        }

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        $cliente->delete();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado.');
    }
}
