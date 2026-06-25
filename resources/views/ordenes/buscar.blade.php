<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Búsqueda avanzada</h2>
            <a href="{{ route('ordenes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a órdenes</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Formulario de filtros -->
            <form method="GET" action="{{ route('ordenes.buscar') }}" class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <x-input-label for="apellido" value="Apellido del cliente" />
                        <x-text-input id="apellido" name="apellido" type="text" class="mt-1 block w-full"
                            value="{{ request('apellido') }}" />
                    </div>
                    <div>
                        <x-input-label for="nombre" value="Nombre del cliente" />
                        <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                            value="{{ request('nombre') }}" />
                    </div>
                    <div>
                        <x-input-label for="empresa" value="Empresa" />
                        <x-text-input id="empresa" name="empresa" type="text" class="mt-1 block w-full"
                            value="{{ request('empresa') }}" />
                    </div>
                    <div>
                        <x-input-label for="etiqueta" value="Etiqueta del equipo" />
                        <x-text-input id="etiqueta" name="etiqueta" type="text" class="mt-1 block w-full font-mono"
                            value="{{ request('etiqueta') }}" />
                    </div>
                    <div>
                        <x-input-label for="nro_serie" value="Número de serie" />
                        <x-text-input id="nro_serie" name="nro_serie" type="text" class="mt-1 block w-full"
                            value="{{ request('nro_serie') }}" />
                    </div>
                    <div>
                        <x-input-label for="estado_id" value="Estado" />
                        <select id="estado_id" name="estado_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach ($estados as $estado)
                                <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                                    {{ $estado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="tecnico_id" value="Técnico" />
                        <select id="tecnico_id" name="tecnico_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach ($tecnicos as $tecnico)
                                <option value="{{ $tecnico->id }}" {{ request('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                    {{ $tecnico->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="fecha_desde" value="Ingreso desde" />
                        <x-text-input id="fecha_desde" name="fecha_desde" type="date" class="mt-1 block w-full"
                            value="{{ request('fecha_desde') }}" />
                    </div>
                    <div>
                        <x-input-label for="fecha_hasta" value="Ingreso hasta" />
                        <x-text-input id="fecha_hasta" name="fecha_hasta" type="date" class="mt-1 block w-full"
                            value="{{ request('fecha_hasta') }}" />
                    </div>
                    <div>
                        <x-input-label for="entrega" value="Estado de entrega" />
                        <select id="entrega" name="entrega"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <option value="todos" {{ request('entrega', 'todos') == 'todos' ? 'selected' : '' }}>Todas</option>
                            <option value="pendientes" {{ request('entrega') == 'pendientes' ? 'selected' : '' }}>Sólo pendientes</option>
                            <option value="finalizados" {{ request('entrega') == 'finalizados' ? 'selected' : '' }}>Sólo finalizadas</option>
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <x-primary-button>Buscar</x-primary-button>
                    <a href="{{ route('ordenes.buscar') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Limpiar filtros</a>
                </div>
            </form>

            @if ($busco)
                <!-- PENDIENTES -->
                @if ($pendientes->isNotEmpty() || request('entrega') === 'pendientes')
                    <section class="mb-8">
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="text-base font-semibold text-gray-700">Pendientes</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                {{ $pendientes->count() }}
                            </span>
                        </div>
                        @include('ordenes._tabla', ['ordenes' => $pendientes, 'vacio' => 'Sin resultados pendientes.'])
                    </section>
                @endif

                <!-- FINALIZADAS -->
                @if ($finalizados->isNotEmpty() || request('entrega') === 'finalizados')
                    <section>
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="text-base font-semibold text-gray-700">Finalizadas</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                {{ $finalizados->count() }}
                            </span>
                        </div>
                        @include('ordenes._tabla', ['ordenes' => $finalizados, 'vacio' => 'Sin resultados finalizados.'])
                    </section>
                @endif

                @if (request('entrega', 'todos') === 'todos' && $pendientes->isEmpty() && $finalizados->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-400">
                        No se encontraron órdenes con esos criterios.
                    </div>
                @endif
            @else
                <div class="bg-white shadow-sm sm:rounded-lg p-10 text-center text-gray-400">
                    Completá uno o más filtros y presioná <span class="font-medium text-gray-600">Buscar</span>.
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
