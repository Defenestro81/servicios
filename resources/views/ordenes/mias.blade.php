<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis órdenes</h2>
            <div class="flex gap-2">
                <a href="{{ route('ordenes.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition">
                    Ver todas
                </a>
                <a href="{{ route('ordenes.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                    + Nueva orden
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <!-- Buscador -->
            <form method="GET" class="mb-6 flex flex-wrap gap-2 items-center">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Buscar por apellido, nombre, empresa o etiqueta de equipo..."
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-80">
                <select name="estado_id"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->nombre }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm hover:bg-gray-200">Buscar</button>
                @if(request()->hasAny(['q','estado_id']))
                    <a href="{{ route('ordenes.mias') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
                @endif
            </form>

            <!-- PENDIENTES -->
            <section class="mb-8">
                <div class="flex items-center gap-2 mb-3">
                    <h3 class="text-base font-semibold text-gray-700">Pendientes</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                        {{ $pendientes->count() }}
                    </span>
                </div>
                @include('ordenes._tabla', ['ordenes' => $pendientes, 'vacio' => 'No tenés órdenes pendientes asignadas.'])
            </section>

            <!-- FINALIZADAS -->
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <h3 class="text-base font-semibold text-gray-700">Finalizadas</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        {{ $finalizados->total() }}
                    </span>
                </div>
                @include('ordenes._tabla', ['ordenes' => $finalizados, 'vacio' => 'No tenés órdenes finalizadas.'])
                <div class="mt-4">{{ $finalizados->links() }}</div>
            </section>

        </div>
    </div>
</x-app-layout>
