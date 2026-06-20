<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Equipo — {{ $equipo->etiqueta }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('equipos.update', $equipo) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="tipo_equipo_id" value="Tipo de equipo *" />
                            <div class="mt-1">
                                <x-tipo-equipo-select :tipos="$tipos" name="tipo_equipo_id"
                                    :selected="old('tipo_equipo_id', $equipo->tipo_equipo_id)" :required="true" />
                            </div>
                            <x-input-error :messages="$errors->get('tipo_equipo_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="marca" value="Marca" />
                            <x-text-input id="marca" name="marca" type="text" class="mt-1 block w-full"
                                value="{{ old('marca', $equipo->marca) }}" />
                        </div>
                        <div>
                            <x-input-label for="modelo" value="Modelo" />
                            <x-text-input id="modelo" name="modelo" type="text" class="mt-1 block w-full"
                                value="{{ old('modelo', $equipo->modelo) }}" />
                        </div>
                        <div>
                            <x-input-label for="nro_serie" value="Número de serie" />
                            <x-text-input id="nro_serie" name="nro_serie" type="text" class="mt-1 block w-full"
                                value="{{ old('nro_serie', $equipo->nro_serie) }}" />
                        </div>
                        <div>
                            <x-input-label for="etiqueta" value="Etiqueta interna" />
                            <x-text-input id="etiqueta" name="etiqueta" type="text" class="mt-1 block w-full font-mono"
                                value="{{ old('etiqueta', $equipo->etiqueta) }}" />
                            <x-input-error :messages="$errors->get('etiqueta')" class="mt-1" />
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <x-primary-button>Guardar cambios</x-primary-button>
                        <a href="{{ route('equipos.show', $equipo) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
