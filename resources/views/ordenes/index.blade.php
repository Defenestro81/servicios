<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Órdenes de Servicio</h2>
            @role('tecnico|administrador')
            <a href="{{ route('ordenes.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                + Nueva orden
            </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <!-- Filtros -->
            <form method="GET" class="mb-4 flex flex-wrap gap-2 items-center">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Buscar cliente o equipo..."
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-64">
                <select name="estado_id"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm hover:bg-gray-200">Filtrar</button>
                @if(request()->hasAny(['q','estado_id']))
                    <a href="{{ route('ordenes.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
                @endif
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
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
                                <td class="px-4 py-3 text-gray-600">
                                    <span class="font-mono text-xs text-gray-400">{{ $orden->equipo->etiqueta }}</span>
                                    <span class="ml-1">{{ $orden->equipo->descripcion }}</span>
                                </td>
                                <td class="px-4 py-3"><x-estado-badge :estado="$orden->estado" /></td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $orden->tecnicoPrincipal->first()?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $orden->fecha_ingreso->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
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
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">No hay órdenes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $ordenes->links() }}</div>
        </div>
    </div>
</x-app-layout>
