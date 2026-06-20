<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Orden de Servicio</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('ordenes.store') }}" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- CLIENTE -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4"
                    x-data="{
                        modo: '{{ old('cliente_nuevo_apellido') ? 'nuevo' : 'existente' }}',
                        buscar: ''
                    }">
                    <h3 class="text-base font-semibold text-gray-700 mb-4">Cliente</h3>

                    <div class="flex gap-3 mb-4">
                        <button type="button" @click="modo = 'existente'"
                            :class="modo === 'existente' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition">
                            Seleccionar existente
                        </button>
                        <button type="button" @click="modo = 'nuevo'"
                            :class="modo === 'nuevo' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition">
                            Crear nuevo
                        </button>
                    </div>

                    <div x-show="modo === 'existente'" x-cloak>
                        <input type="text" x-model="buscar" placeholder="Filtrar por nombre o email..."
                            class="mb-2 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <select name="cliente_id"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            size="6">
                            <option value="">— seleccionar —</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id }}"
                                    {{ old('cliente_id', request('cliente_id')) == $c->id ? 'selected' : '' }}
                                    x-show="buscar === '' || '{{ strtolower($c->nombre_completo . ' ' . ($c->email ?? '')) }}'.includes(buscar.toLowerCase())">
                                    {{ $c->nombre_completo }}{{ $c->email ? ' — ' . $c->email : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="modo === 'nuevo'" x-cloak
                        x-data="{ telefonos: {{ json_encode(old('cliente_nuevo_telefonos', [''])) }} }"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label value="Apellido *" />
                            <x-text-input name="cliente_nuevo_apellido" type="text" class="mt-1 block w-full"
                                value="{{ old('cliente_nuevo_apellido') }}" />
                        </div>
                        <div>
                            <x-input-label value="Nombre *" />
                            <x-text-input name="cliente_nuevo_nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('cliente_nuevo_nombre') }}" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Email" />
                            <x-text-input name="cliente_nuevo_email" type="email" class="mt-1 block w-full"
                                value="{{ old('cliente_nuevo_email') }}" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Empresa" />
                            <div class="mt-1">
                                <x-empresa-select :empresas="$empresas" name="cliente_nuevo_empresa_id" :selected="old('cliente_nuevo_empresa_id', '')" />
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Teléfonos" />
                            <div class="space-y-2 mt-1">
                                <template x-for="(tel, i) in telefonos" :key="i">
                                    <div class="flex gap-2">
                                        <input type="text" name="cliente_nuevo_telefonos[]" x-model="telefonos[i]"
                                            placeholder="Ej: 2944 123456"
                                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <button type="button" @click="telefonos.splice(i, 1)"
                                            x-show="telefonos.length > 1"
                                            class="text-red-500 hover:text-red-700 px-2">✕</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="telefonos.push('')"
                                class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">+ Agregar teléfono</button>
                        </div>
                    </div>
                </div>

                <!-- EQUIPO -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4"
                    x-data="{
                        modo: '{{ old('equipo_nuevo_tipo_id') ? 'nuevo' : 'existente' }}',
                        buscar: ''
                    }">
                    <h3 class="text-base font-semibold text-gray-700 mb-4">Equipo</h3>

                    <div class="flex gap-3 mb-4">
                        <button type="button" @click="modo = 'existente'"
                            :class="modo === 'existente' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition">
                            Seleccionar existente
                        </button>
                        <button type="button" @click="modo = 'nuevo'"
                            :class="modo === 'nuevo' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="px-3 py-1.5 text-sm rounded-md font-medium transition">
                            Registrar nuevo
                        </button>
                    </div>

                    <div x-show="modo === 'existente'" x-cloak>
                        <input type="text" x-model="buscar" placeholder="Filtrar por etiqueta, marca, modelo o serie..."
                            class="mb-2 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <select name="equipo_id"
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-mono"
                            size="6">
                            <option value="">— seleccionar —</option>
                            @foreach ($equipos as $e)
                                <option value="{{ $e->id }}"
                                    {{ old('equipo_id') == $e->id ? 'selected' : '' }}
                                    x-show="buscar === '' || '{{ strtolower($e->etiqueta . ' ' . $e->descripcion . ' ' . ($e->nro_serie ?? '')) }}'.includes(buscar.toLowerCase())">
                                    {{ $e->etiqueta }} — {{ $e->tipo->descripcion }} {{ $e->descripcion }}{{ $e->nro_serie ? ' (S/N: '.$e->nro_serie.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="modo === 'nuevo'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label value="Tipo de equipo *" />
                            <div class="mt-1">
                                <x-tipo-equipo-select :tipos="$tipos" name="equipo_nuevo_tipo_id"
                                    :selected="old('equipo_nuevo_tipo_id', '')" />
                            </div>
                            <x-input-error :messages="$errors->get('equipo_nuevo_tipo_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Marca" />
                            <x-text-input name="equipo_nuevo_marca" type="text" class="mt-1 block w-full"
                                value="{{ old('equipo_nuevo_marca') }}" />
                        </div>
                        <div>
                            <x-input-label value="Modelo" />
                            <x-text-input name="equipo_nuevo_modelo" type="text" class="mt-1 block w-full"
                                value="{{ old('equipo_nuevo_modelo') }}" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Número de serie" />
                            <x-text-input name="equipo_nuevo_nro_serie" type="text" class="mt-1 block w-full"
                                value="{{ old('equipo_nuevo_nro_serie') }}" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Etiqueta interna *" />
                            <x-text-input name="equipo_nuevo_etiqueta" type="text" class="mt-1 block w-full font-mono"
                                value="{{ old('equipo_nuevo_etiqueta') }}" placeholder="Ej: EQ-00001" required />
                            <x-input-error :messages="$errors->get('equipo_nuevo_etiqueta')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- DATOS DE LA ORDEN -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-4">
                    <h3 class="text-base font-semibold text-gray-700 mb-4">Datos de la orden</h3>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <x-input-label for="fecha_ingreso" value="Fecha de ingreso *" />
                            <x-text-input id="fecha_ingreso" name="fecha_ingreso" type="date" class="mt-1 block w-48"
                                value="{{ old('fecha_ingreso', today()->format('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-input-label for="trabajo_solicitado" value="Trabajo solicitado *" />
                            <textarea id="trabajo_solicitado" name="trabajo_solicitado" rows="3" required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('trabajo_solicitado') }}</textarea>
                            <x-input-error :messages="$errors->get('trabajo_solicitado')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="accesorios" value="Accesorios que trae el cliente" />
                            <x-text-input id="accesorios" name="accesorios" type="text" class="mt-1 block w-full"
                                value="{{ old('accesorios') }}" placeholder="Ej: cable, cargador, mouse..." />
                        </div>
                        <div>
                            <x-input-label for="detalles" value="Detalles adicionales / Estado del equipo al ingreso" />
                            <textarea id="detalles" name="detalles" rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('detalles') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- FOTOS DE INGRESO -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-base font-semibold text-gray-700 mb-2">Fotos de ingreso <span class="text-gray-400 font-normal text-sm">(opcional)</span></h3>
                    <input type="file" name="fotos[]" multiple accept="image/*,application/pdf"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-400">JPG, PNG, PDF — máx. 10 MB por archivo.</p>
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Crear orden</x-primary-button>
                    <a href="{{ route('ordenes.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
