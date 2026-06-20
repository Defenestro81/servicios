@props(['tipos', 'name' => 'tipo_equipo_id', 'selected' => '', 'required' => false])

<div x-data="{
    tipos: @js($tipos->map(fn($t) => ['id' => $t->id, 'descripcion' => $t->descripcion])->values()),
    selected: '{{ $selected }}',
    modal: false,
    form: { descripcion: '' },
    errores: {},
    guardando: false,
    async crear() {
        this.errores = {};
        this.guardando = true;
        try {
            const r = await fetch('{{ route('tipos-equipos.inline') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            });
            const data = await r.json();
            if (r.ok) {
                this.tipos.push(data);
                this.tipos.sort((a, b) => a.descripcion.localeCompare(b.descripcion, 'es'));
                this.selected = String(data.id);
                this.modal = false;
                this.form = { descripcion: '' };
            } else {
                this.errores = data.errors ?? {};
            }
        } finally {
            this.guardando = false;
        }
    }
}">
    <div class="flex gap-2 items-center">
        <select name="{{ $name }}" x-model="selected" {{ $required ? 'required' : '' }}
            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            <option value="">— Seleccionar tipo —</option>
            <template x-for="t in tipos" :key="t.id">
                <option :value="String(t.id)" x-text="t.descripcion"></option>
            </template>
        </select>
        <button type="button" @click="modal = true"
            class="shrink-0 px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 whitespace-nowrap">
            + Nuevo
        </button>
    </div>

    <template x-teleport="body">
        <div x-show="modal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center"
            @keydown.escape.window="modal = false">

            <div class="absolute inset-0 bg-black/50" @click="modal = false"></div>

            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm mx-4 p-6" @click.stop>
                <h3 class="text-base font-semibold text-gray-800 mb-5">Nuevo tipo de equipo</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción *</label>
                    <input type="text" x-model="form.descripcion"
                        @keydown.enter.prevent="crear()"
                        x-ref="inputDescripcion"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                        placeholder="Ej: Tablet">
                    <p x-show="errores.descripcion" x-text="errores.descripcion?.[0]"
                        class="mt-1 text-xs text-red-600"></p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="crear()" :disabled="guardando"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 transition">
                        <span x-text="guardando ? 'Guardando...' : 'Crear tipo'"></span>
                    </button>
                    <button type="button" @click="modal = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
