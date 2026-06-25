<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orden #{{ $orden->id }}</h2>
                <x-estado-badge :estado="$orden->estado" />
            </div>
            <div class="flex gap-2">
                @if($puedeEditar)
                    <a href="{{ route('ordenes.edit', $orden) }}"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition">
                        Editar
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <!-- Cabecera -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-6 sm:grid-cols-4 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Cliente</dt>
                        <dd class="mt-1">
                            @role('tecnico|administrador')
                                <a href="{{ route('clientes.show', $orden->cliente) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    {{ $orden->cliente->nombre_completo }}
                                </a>
                            @else
                                <span class="text-gray-900 font-medium">{{ $orden->cliente->nombre_completo }}</span>
                            @endrole
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Equipo</dt>
                        <dd class="mt-1">
                            @role('tecnico|administrador')
                                <a href="{{ route('equipos.show', $orden->equipo) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $orden->equipo->etiqueta }}
                                </a>
                            @else
                                <span class="text-gray-900 font-mono">{{ $orden->equipo->etiqueta }}</span>
                            @endrole
                            <span class="text-gray-600 ml-1">{{ $orden->equipo->descripcion }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Técnico</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ $orden->tecnicoPrincipal->first()?->name ?? '—' }}
                            @if(!$orden->estaAsignada())
                                @role('tecnico|administrador')
                                <form method="POST" action="{{ route('ordenes.tomar', $orden) }}" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded hover:bg-amber-200 font-medium">
                                        Tomar
                                    </button>
                                </form>
                                @endrole
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Ingreso</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->fecha_ingreso->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Trabajo solicitado</dt>
                        <dd class="mt-1 text-gray-900 sm:col-span-3">{{ $orden->trabajo_solicitado }}</dd>
                    </div>
                    @if($orden->accesorios)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 font-medium">Accesorios</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->accesorios }}</dd>
                    </div>
                    @endif
                    @if($orden->detalles)
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 font-medium">Detalles</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->detalles }}</dd>
                    </div>
                    @endif
                    @if($orden->trabajo_realizado)
                    <div class="sm:col-span-4">
                        <dt class="text-gray-500 font-medium">Trabajo realizado</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->trabajo_realizado }}</dd>
                    </div>
                    @endif
                    @if($orden->fecha_terminado)
                    <div>
                        <dt class="text-gray-500 font-medium">Terminado</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->fecha_terminado->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($orden->fecha_retirado)
                    <div>
                        <dt class="text-gray-500 font-medium">Retirado</dt>
                        <dd class="mt-1 text-gray-900">{{ $orden->fecha_retirado->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Costos -->
            @role('tecnico|administrador')
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-3 gap-6 text-sm">
                    <div>
                        <dt class="text-gray-500 font-medium">Mano de obra</dt>
                        <dd class="mt-1 text-gray-900">${{ number_format((float) $orden->costo_mano_obra, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Repuestos</dt>
                        <dd class="mt-1 text-gray-900">${{ number_format((float) $orden->costo_repuestos, 2, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Total</dt>
                        <dd class="mt-1 text-lg font-semibold text-indigo-700">${{ number_format($orden->costo_total, 2, ',', '.') }}</dd>
                    </div>
                </div>
            </div>
            @else
                @if($orden->costo_total > 0)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between text-sm">
                    <dt class="text-gray-500 font-medium">Total a abonar</dt>
                    <dd class="text-lg font-semibold text-indigo-700">${{ number_format($orden->costo_total, 2, ',', '.') }}</dd>
                </div>
                @endif
            @endrole

            <!-- Cambiar estado -->
            @if($puedeEditar)
            <div class="bg-white shadow-sm sm:rounded-lg p-6" x-data="{ abierto: false }">
                <button type="button" @click="abierto = !abierto"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    <span x-text="abierto ? '▲ Cerrar' : '▼ Cambiar estado'"></span>
                </button>
                <div x-show="abierto" x-cloak class="mt-4">
                    <form method="POST" action="{{ route('ordenes.cambiarEstado', $orden) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div>
                            <x-input-label value="Nuevo estado" />
                            <select name="estado_id"
                                class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ $orden->estado_id === $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-48">
                            <x-input-label value="Nota (opcional)" />
                            <x-text-input name="nota" type="text" class="mt-1 block w-full" placeholder="Ej: pendiente de pieza..." />
                        </div>
                        <x-primary-button>Aplicar</x-primary-button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Asignar técnico (solo admin) -->
            @role('administrador')
            <div class="bg-white shadow-sm sm:rounded-lg p-6" x-data="{ abierto: false }">
                <button type="button" @click="abierto = !abierto"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    <span x-text="abierto ? '▲ Cerrar' : '▼ Asignar técnico'"></span>
                </button>
                <div x-show="abierto" x-cloak class="mt-4">
                    <form method="POST" action="{{ route('ordenes.asignarTecnico', $orden) }}" class="flex gap-3 items-end">
                        @csrf
                        <div>
                            <x-input-label value="Técnico" />
                            <select name="user_id"
                                class="mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                @foreach($tecnicos as $tec)
                                    <option value="{{ $tec->id }}" {{ $orden->tecnicoPrincipal->first()?->id === $tec->id ? 'selected' : '' }}>
                                        {{ $tec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>Asignar</x-primary-button>
                    </form>
                </div>
            </div>
            @endrole

            <!-- Historial de estados -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Historial de estados</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($orden->historialEstados as $h)
                        <li class="px-6 py-3 flex items-start gap-4 text-sm">
                            <span class="mt-0.5"><x-estado-badge :estado="$h->estado" /></span>
                            <div class="flex-1">
                                @if($h->nota)<span class="text-gray-700">{{ $h->nota }}</span>@endif
                            </div>
                            <div class="text-gray-400 text-xs whitespace-nowrap">
                                {{ $h->user->name }} · {{ $h->created_at->format('d/m/Y H:i') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Arreglos con terceros (información interna, oculta al cliente) -->
            @role('tecnico|administrador')
            @if($puedeEditar || $orden->arreglosTerceros->isNotEmpty())
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Arreglos con terceros</h3>
                </div>

                @if($orden->arreglosTerceros->isNotEmpty())
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Llevado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recibido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Importe</th>
                            @if($puedeEditar)<th class="px-6 py-3"></th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($orden->arreglosTerceros as $arreglo)
                        <tr>
                            <td class="px-6 py-3">{{ $arreglo->proveedor->nombre }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $arreglo->descripcion }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $arreglo->fecha_llevado?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $arreglo->fecha_recibido?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $arreglo->importe ? '$'.number_format($arreglo->importe, 2) : '—' }}</td>
                            @if($puedeEditar)
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="{{ route('arreglos.destroy', [$orden, $arreglo]) }}"
                                    onsubmit="return confirm('¿Eliminar este arreglo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Eliminar</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                @if($puedeEditar)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200" x-data="{ abierto: false }">
                    <button type="button" @click="abierto = !abierto"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <span x-text="abierto ? '▲ Cancelar' : '+ Registrar arreglo con tercero'"></span>
                    </button>
                    <form x-show="abierto" x-cloak method="POST" action="{{ route('arreglos.store', $orden) }}"
                        class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @csrf
                        <div class="sm:col-span-2">
                            <x-input-label value="Proveedor *" />
                            <select name="proveedor_id" required
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">— seleccionar —</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label value="Descripción *" />
                            <x-text-input name="descripcion" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label value="Fecha que se llevó" />
                            <x-text-input name="fecha_llevado" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label value="Fecha que se recibió" />
                            <x-text-input name="fecha_recibido" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label value="Importe cobrado" />
                            <x-text-input name="importe" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="0.00" />
                        </div>
                        <div class="flex items-end">
                            <x-primary-button>Registrar</x-primary-button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            @endif
            @endrole

            <!-- Adjuntos -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Archivos adjuntos</h3>
                </div>

                @if($orden->adjuntos->isNotEmpty())
                <div class="p-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach($orden->adjuntos as $adj)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        @if($adj->esImagen())
                            <a href="{{ $adj->url }}" target="_blank">
                                <img src="{{ $adj->url }}" alt="{{ $adj->nombre_original }}"
                                    class="w-full h-32 object-cover hover:opacity-90 transition">
                            </a>
                        @else
                            <a href="{{ $adj->url }}" target="_blank"
                                class="flex items-center justify-center h-32 bg-gray-50 hover:bg-gray-100 transition">
                                <span class="text-gray-400 text-sm">📄 {{ $adj->nombre_original }}</span>
                            </a>
                        @endif
                        <div class="px-2 py-1 text-xs text-gray-500 flex items-center justify-between">
                            <span class="truncate">{{ $adj->descripcion ?? $adj->nombre_original }}</span>
                            @if($puedeEditar)
                            <form method="POST" action="{{ route('adjuntos.destroy', [$orden, $adj]) }}"
                                onsubmit="return confirm('¿Eliminar archivo?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 ml-1">✕</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($puedeEditar)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200" x-data="{ abierto: false }">
                    <button type="button" @click="abierto = !abierto"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <span x-text="abierto ? '▲ Cancelar' : '+ Subir archivos'"></span>
                    </button>
                    <form x-show="abierto" x-cloak method="POST" action="{{ route('adjuntos.store', $orden) }}"
                        enctype="multipart/form-data" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <x-input-label value="Descripción (opcional)" />
                            <x-text-input name="descripcion" type="text" class="mt-1 block w-full"
                                placeholder="Ej: Foto de daño, Comprobante de pago..." />
                        </div>
                        <div>
                            <input type="file" name="archivos[]" multiple accept="image/*,application/pdf"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-1 text-xs text-gray-400">JPG, PNG, PDF — máx. 10 MB por archivo.</p>
                        </div>
                        <x-primary-button>Subir</x-primary-button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Antecedentes (solo técnico/admin) -->
            @role('tecnico|administrador')
            <!-- Otras órdenes de este equipo (incluye las de otros clientes) -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Otras órdenes de este equipo</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $ordenesEquipo->count() }}</span>
                </div>
                @if($ordenesEquipo->isNotEmpty())
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($ordenesEquipo as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">#{{ $o->id }}</td>
                            <td class="px-6 py-3 text-gray-700">
                                {{ $o->cliente->nombre_completo }}
                                @if($o->cliente_id !== $orden->cliente_id)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">otro cliente</span>
                                @endif
                            </td>
                            <td class="px-6 py-3"><x-estado-badge :estado="$o->estado" /></td>
                            <td class="px-6 py-3 text-gray-500">{{ $o->fecha_ingreso->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-right"><a href="{{ route('ordenes.show', $o) }}" class="text-indigo-600 hover:text-indigo-800">Ver</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="px-6 py-6 text-center text-gray-400 text-sm">Este equipo no tiene otras órdenes registradas.</p>
                @endif
            </div>

            <!-- Otras órdenes de este cliente -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Otras órdenes de este cliente</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $ordenesCliente->count() }}</span>
                </div>
                @if($ordenesCliente->isNotEmpty())
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingreso</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($ordenesCliente as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">#{{ $o->id }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                <span class="font-mono text-xs text-gray-400">{{ $o->equipo->etiqueta }}</span>
                                <span class="ml-1">{{ $o->equipo->descripcion }}</span>
                            </td>
                            <td class="px-6 py-3"><x-estado-badge :estado="$o->estado" /></td>
                            <td class="px-6 py-3 text-gray-500">{{ $o->fecha_ingreso->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-right"><a href="{{ route('ordenes.show', $o) }}" class="text-indigo-600 hover:text-indigo-800">Ver</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="px-6 py-6 text-center text-gray-400 text-sm">Este cliente no tiene otras órdenes registradas.</p>
                @endif
            </div>
            @endrole

        </div>
    </div>
</x-app-layout>
