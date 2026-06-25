{{--
    Tabla de órdenes reutilizable.
    Espera:
      $ordenes  -> Collection o Paginator de órdenes
      $vacio    -> texto a mostrar cuando no hay resultados (opcional)
--}}
<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entregado</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($ordenes as $orden)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('ordenes.show', $orden) }}" class="hover:text-indigo-600">#{{ $orden->id }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $orden->cliente->nombre_completo }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $orden->cliente->empresa?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        <span class="font-mono text-xs text-gray-400">{{ $orden->equipo->etiqueta }}</span>
                        <span class="ml-1">{{ $orden->equipo->descripcion }}</span>
                    </td>
                    <td class="px-4 py-3"><x-estado-badge :estado="$orden->estado" /></td>
                    <td class="px-4 py-3 text-gray-500">{{ $orden->tecnicoPrincipal->first()?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $orden->fecha_ingreso->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $orden->fecha_retirado?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @role('tecnico|administrador')
                            @if(!$orden->estaAsignada())
                                <form method="POST" action="{{ route('ordenes.tomar', $orden) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded hover:bg-amber-200 font-medium">
                                        Tomar
                                    </button>
                                </form>
                            @endif
                        @endrole
                        <a href="{{ route('ordenes.show', $orden) }}"
                            class="ml-2 text-indigo-600 hover:text-indigo-800 text-sm">Ver</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center text-gray-400">{{ $vacio ?? 'No hay órdenes.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
