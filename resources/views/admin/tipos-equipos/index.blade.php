<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tipos de Equipo</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Nuevo tipo --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Agregar tipo</h3>
                <form method="POST" action="{{ route('admin.tipos-equipos.store') }}"
                    class="flex flex-col sm:flex-row gap-3 items-start">
                    @csrf
                    <div class="flex-1">
                        <x-text-input name="descripcion" type="text" class="w-full"
                            value="{{ old('descripcion') }}"
                            placeholder="Descripción del tipo (ej: Notebook)" required />
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>
                    <div class="flex-1">
                        <x-text-input name="notas" type="text" class="w-full"
                            value="{{ old('notas') }}"
                            placeholder="Notas (opcional)" />
                    </div>
                    <x-primary-button type="submit">Agregar</x-primary-button>
                </form>
            </div>

            {{-- Listado --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Descripción</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Notas</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tipos as $tipo)
                            <tr x-data="{
                                    editando: false,
                                    descripcion: @js($tipo->descripcion),
                                    notas: @js($tipo->notas ?? ''),
                                    guardando: false,
                                    error: '',
                                    async guardar() {
                                        if (!this.descripcion.trim()) return;
                                        this.guardando = true;
                                        this.error = '';
                                        try {
                                            const r = await fetch('{{ route('admin.tipos-equipos.update', $tipo) }}', {
                                                method: 'PATCH',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json',
                                                },
                                                body: JSON.stringify({ descripcion: this.descripcion, notas: this.notas }),
                                            });
                                            if (r.ok) {
                                                this.editando = false;
                                                window.location.reload();
                                            } else {
                                                const d = await r.json();
                                                this.error = Object.values(d.errors ?? {})[0]?.[0] ?? 'Error al guardar.';
                                            }
                                        } finally {
                                            this.guardando = false;
                                        }
                                    }
                                }"
                                :class="{{ $tipo->activo ? 'false' : 'true' }} ? 'bg-gray-50 opacity-60' : ''">

                                {{-- Descripción --}}
                                <td class="px-4 py-3">
                                    <span x-show="!editando" class="font-medium text-gray-800">{{ $tipo->descripcion }}</span>
                                    <div x-show="editando" x-cloak class="space-y-1">
                                        <input type="text" x-model="descripcion" @keydown.escape="editando = false"
                                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <p x-show="error" x-text="error" class="text-xs text-red-600"></p>
                                    </div>
                                </td>

                                {{-- Notas --}}
                                <td class="px-4 py-3 text-gray-500">
                                    <span x-show="!editando">{{ $tipo->notas ?? '—' }}</span>
                                    <input x-show="editando" x-cloak type="text" x-model="notas"
                                        placeholder="Notas..."
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                </td>

                                {{-- Estado --}}
                                <td class="px-4 py-3 text-center">
                                    @if ($tipo->activo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Activo</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-500">Inactivo</span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Botones modo normal --}}
                                        <template x-if="!editando">
                                            <div class="flex gap-2">
                                                <button type="button" @click="editando = true"
                                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                                    Editar
                                                </button>

                                                <form method="POST"
                                                    action="{{ route('admin.tipos-equipos.toggle', $tipo) }}"
                                                    onsubmit="return confirm('¿Confirmar cambio de estado?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="text-sm font-medium {{ $tipo->activo ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }}">
                                                        {{ $tipo->activo ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </template>

                                        {{-- Botones modo edición --}}
                                        <template x-if="editando">
                                            <div class="flex gap-2">
                                                <button type="button" @click="guardar()" :disabled="guardando"
                                                    class="text-sm font-medium text-green-700 hover:text-green-900 disabled:opacity-50">
                                                    <span x-text="guardando ? 'Guardando...' : 'Guardar'"></span>
                                                </button>
                                                <button type="button" @click="editando = false"
                                                    class="text-sm text-gray-500 hover:text-gray-700">
                                                    Cancelar
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                    No hay tipos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
