<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Equipo {{ $equipo->etiqueta }}
            </h2>
            <a href="{{ route('equipos.edit', $equipo) }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                Editar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Datos del equipo</h3>
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Tipo</dt>
                        <dd class="text-gray-900 mt-1">{{ $equipo->tipo->descripcion }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Marca</dt>
                        <dd class="text-gray-900 mt-1">{{ $equipo->marca ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Modelo</dt>
                        <dd class="text-gray-900 mt-1">{{ $equipo->modelo ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">N° Serie</dt>
                        <dd class="text-gray-900 mt-1 font-mono text-xs">{{ $equipo->nro_serie ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Historial de órdenes</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($equipo->ordenes as $orden)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <a href="{{ route('ordenes.show', $orden) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">#{{ $orden->id }}</a>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $orden->cliente->nombre_completo }}</td>
                                <td class="px-6 py-3"><x-estado-badge :estado="$orden->estado" /></td>
                                <td class="px-6 py-3 text-gray-500">{{ $orden->fecha_ingreso->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Sin órdenes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
