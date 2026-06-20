<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo Cliente</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('clientes.store') }}" x-data="{ telefonos: [''] }">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="apellido" value="Apellido *" />
                            <x-text-input id="apellido" name="apellido" type="text" class="mt-1 block w-full"
                                value="{{ old('apellido') }}" required autofocus />
                            <x-input-error :messages="$errors->get('apellido')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nombre" value="Nombre *" />
                            <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full"
                                value="{{ old('nombre') }}" required />
                            <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                value="{{ old('email') }}" />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Empresa" />
                            <div class="mt-1">
                                <x-empresa-select :empresas="$empresas" name="empresa_id" :selected="old('empresa_id', '')" />
                            </div>
                        </div>
                    </div>

                    <!-- Teléfonos -->
                    <div class="mt-6">
                        <x-input-label value="Teléfonos" />
                        <div class="space-y-2 mt-1">
                            <template x-for="(tel, i) in telefonos" :key="i">
                                <div class="flex gap-2">
                                    <input type="text" :name="'telefonos[]'" x-model="telefonos[i]"
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

                    <div class="mt-6 flex gap-3">
                        <x-primary-button>Guardar cliente</x-primary-button>
                        <a href="{{ route('clientes.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
