<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Orden #{{ $orden->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <!-- Info de referencia (solo lectura) -->
            <div class="bg-gray-50 border border-gray-200 sm:rounded-lg p-4 mb-5 text-sm text-gray-600 flex flex-wrap gap-4">
                <span><strong>Cliente:</strong> {{ $orden->cliente->nombre_completo }}</span>
                <span><strong>Equipo:</strong> {{ $orden->equipo->etiqueta }} {{ $orden->equipo->descripcion }}</span>
                <span><strong>Ingreso:</strong> {{ $orden->fecha_ingreso->format('d/m/Y') }}</span>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('ordenes.update', $orden) }}">
                    @csrf
                    @method('PATCH')

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-5">
                        <div>
                            <x-input-label for="trabajo_solicitado" value="Trabajo solicitado *" />
                            <textarea id="trabajo_solicitado" name="trabajo_solicitado" rows="3" required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('trabajo_solicitado', $orden->trabajo_solicitado) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="trabajo_realizado" value="Trabajo realizado" />
                            <textarea id="trabajo_realizado" name="trabajo_realizado" rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('trabajo_realizado', $orden->trabajo_realizado) }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="accesorios" value="Accesorios" />
                            <x-text-input id="accesorios" name="accesorios" type="text" class="mt-1 block w-full"
                                value="{{ old('accesorios', $orden->accesorios) }}" />
                        </div>
                        <div>
                            <x-input-label for="detalles" value="Detalles / Notas internas" />
                            <textarea id="detalles" name="detalles" rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('detalles', $orden->detalles) }}</textarea>
                        </div>

                        <!-- Costos -->
                        <div class="border-t border-gray-200 pt-5"
                            x-data="{
                                mano: parseFloat('{{ old('costo_mano_obra', $orden->costo_mano_obra) }}') || 0,
                                repuestos: parseFloat('{{ old('costo_repuestos', $orden->costo_repuestos) }}') || 0,
                                get total() { return (this.mano + this.repuestos); },
                                fmt(n) { return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
                            }">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Costos</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
                                <div>
                                    <x-input-label for="costo_mano_obra" value="Mano de obra" />
                                    <div class="mt-1 relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                                        <input id="costo_mano_obra" name="costo_mano_obra" type="number" step="0.01" min="0"
                                            x-model.number="mano"
                                            class="block w-full pl-7 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="costo_repuestos" value="Repuestos" />
                                    <div class="mt-1 relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                                        <input id="costo_repuestos" name="costo_repuestos" type="number" step="0.01" min="0"
                                            x-model.number="repuestos"
                                            class="block w-full pl-7 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-md px-4 py-2 text-right">
                                    <div class="text-xs text-gray-500 uppercase">Total</div>
                                    <div class="text-lg font-semibold text-gray-800">$<span x-text="fmt(total)"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="fecha_terminado" value="Fecha terminado" />
                                <x-text-input id="fecha_terminado" name="fecha_terminado" type="date" class="mt-1 block w-full"
                                    value="{{ old('fecha_terminado', $orden->fecha_terminado?->format('Y-m-d')) }}" />
                            </div>
                            <div>
                                <x-input-label for="fecha_retirado" value="Fecha retirado" />
                                <x-text-input id="fecha_retirado" name="fecha_retirado" type="date" class="mt-1 block w-full"
                                    value="{{ old('fecha_retirado', $orden->fecha_retirado?->format('Y-m-d')) }}" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <x-primary-button>Guardar cambios</x-primary-button>
                        <a href="{{ route('ordenes.show', $orden) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
