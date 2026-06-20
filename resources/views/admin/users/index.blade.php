<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Usuarios
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8" x-data="{ filtro: '' }">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filtro -->
            <div class="mb-4">
                <input
                    type="text"
                    x-model="filtro"
                    placeholder="Filtrar por email..."
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm w-72"
                />
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($users as $user)
                            {{-- Los formularios van fuera del <tr> pero los inputs usan el atributo form= --}}
                            @unless ($user->is(auth()->user()))
                                <form
                                    id="form-{{ $user->id }}"
                                    method="POST"
                                    action="{{ route('admin.users.role.update', $user) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                </form>
                            @endunless

                            <tr
                                class="{{ $user->is(auth()->user()) ? 'bg-gray-50' : '' }}"
                                x-show="filtro === '' || '{{ strtolower($user->email) }}'.includes(filtro.toLowerCase())"
                            >
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->is(auth()->user()))
                                        <span class="ml-2 text-xs text-gray-400">(vos)</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ $user->email }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->is(auth()->user()))
                                        <span class="text-gray-400 italic">{{ $user->getRoleNames()->first() ?? '—' }}</span>
                                    @else
                                        <select
                                            name="role"
                                            form="form-{{ $user->id }}"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                        >
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}" {{ $user->hasRole($role) ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @unless ($user->is(auth()->user()))
                                        <button
                                            type="submit"
                                            form="form-{{ $user->id }}"
                                            class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition"
                                        >
                                            Aplicar
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
