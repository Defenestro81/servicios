<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
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
