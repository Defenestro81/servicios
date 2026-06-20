@props(['empresas', 'name' => 'empresa_id', 'selected' => ''])

<div x-data="{
    empresas: @js($empresas->map(fn($e) => ['id' => $e->id, 'nombre' => $e->nombre])->values()),
    selected: '{{ $selected }}',
    modal: false,
    form: { nombre: '', razon_social: '', cuit: '' },
    errores: {},
    guardando: false,
    async crear() {
        this.errores = {};
        this.guardando = true;
        try {
            const r = await fetch('{{ route('empresas.inline') }}', {
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
                this.empresas.push(data);
                this.selected = String(data.id);
                this.modal = false;
                this.form = { nombre: '', razon_social: '', cuit: '' };
            } else {
                this.errores = data.errors ?? {};
            }
        } finally {
            this.guardando = false;
        }
    }
}">
    <div class="flex gap-2 items-center">
        <select name="{{ $name }}" x-model="selected"
            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            <option value="">— Sin empresa —</option>
            <template x-for="e in empresas" :key="e.id">
                <option :value="String(e.id)" x-text="e.nombre"></option>
            </template>
        </select>
        <button type="button" @click="modal = true"
            class="shrink-0 px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 whitespace-nowrap">
            + Nueva
        </button>
    </div>

    {{-- Modal teleportado al body para evitar problemas de z-index --}}
    <template x-teleport="body">
        <div x-show="modal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center"
            @keydown.escape.window="modal = false">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50" @click="modal = false"></div>

            {{-- Panel --}}
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6"
                @click.stop>
                <h3 class="text-base font-semibold text-gray-800 mb-5">Nueva empresa</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                        <input type="text" x-model="form.nombre" @keydown.enter.prevent="crear()"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <p x-show="errores.nombre" x-text="errores.nombre?.[0]" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón social *</label>
                        <input type="text" x-model="form.razon_social" @keydown.enter.prevent="crear()"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <p x-show="errores.razon_social" x-text="errores.razon_social?.[0]" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">CUIT *</label>
                        <input type="text" x-model="form.cuit" placeholder="20-12345678-9" @keydown.enter.prevent="crear()"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono">
                        <p x-show="errores.cuit" x-text="errores.cuit?.[0]" class="mt-1 text-xs text-red-600"></p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="crear()" :disabled="guardando"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 transition">
                        <span x-text="guardando ? 'Guardando...' : 'Crear empresa'"></span>
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
