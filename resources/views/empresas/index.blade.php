<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Empresas</h2>
            <a href="{{ route('empresas.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                + Nueva empresa
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <form method="GET" class="mb-4 flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Buscar por nombre, razón social o CUIT..."
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-72">
                <button type="submit" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-sm hover:bg-gray-200">Buscar</button>
                @if(request('q'))
                    <a href="{{ route('empresas.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
                @endif
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razón social</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CUIT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clientes</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($empresas as $empresa)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $empresa->nombre }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $empresa->razon_social }}</td>
                                <td class="px-6 py-4 text-gray-500 font-mono">{{ $empresa->cuit }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $empresa->clientes_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('empresas.edit', $empresa) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No hay empresas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $empresas->links() }}</div>
        </div>
    </div>
</x-app-layout>
