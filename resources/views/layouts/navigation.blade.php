<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link :href="route('ordenes.index')" :active="request()->routeIs('ordenes.index') || request()->routeIs('ordenes.show') || request()->routeIs('ordenes.create')">
                        Órdenes
                    </x-nav-link>

                    @if (auth()->user()->hasRole('administrador'))
                        <x-nav-dropdown title="Clientes y Empresas"
                            :active="request()->routeIs('clientes.*') || request()->routeIs('empresas.*')">
                            <x-dropdown-link :href="route('clientes.create')">+ Nuevo cliente</x-dropdown-link>
                            <x-dropdown-link :href="route('clientes.index')">Ver clientes</x-dropdown-link>
                            <div class="border-t border-gray-100 my-1"></div>
                            <x-dropdown-link :href="route('empresas.create')">+ Nueva empresa</x-dropdown-link>
                            <x-dropdown-link :href="route('empresas.index')">Ver empresas</x-dropdown-link>
                        </x-nav-dropdown>

                        <x-nav-dropdown title="Equipos"
                            :active="request()->routeIs('equipos.*') || request()->routeIs('admin.tipos-equipos.*')">
                            <x-dropdown-link :href="route('equipos.create')">+ Nuevo equipo</x-dropdown-link>
                            <x-dropdown-link :href="route('equipos.index')">Ver equipos</x-dropdown-link>
                            <div class="border-t border-gray-100 my-1"></div>
                            <x-dropdown-link :href="route('admin.tipos-equipos.index')">Tipos de equipo</x-dropdown-link>
                        </x-nav-dropdown>

                        <x-nav-dropdown title="Búsqueda"
                            :active="request()->routeIs('ordenes.buscar') || request()->routeIs('ordenes.mias')">
                            <x-dropdown-link :href="route('ordenes.buscar')">Búsqueda avanzada</x-dropdown-link>
                            <x-dropdown-link :href="route('ordenes.mias')">Mis órdenes asignadas</x-dropdown-link>
                            <x-dropdown-link :href="route('ordenes.index')">Ver todas las órdenes</x-dropdown-link>
                        </x-nav-dropdown>
                    @elseif (auth()->user()->hasRole('tecnico'))
                        <x-nav-link :href="route('ordenes.mias')" :active="request()->routeIs('ordenes.mias')">
                            Mis órdenes
                        </x-nav-link>

                        <x-nav-dropdown title="Clientes y Empresas"
                            :active="request()->routeIs('clientes.*') || request()->routeIs('empresas.*')">
                            <x-dropdown-link :href="route('clientes.create')">+ Nuevo cliente</x-dropdown-link>
                            <x-dropdown-link :href="route('clientes.index')">Ver clientes</x-dropdown-link>
                            <div class="border-t border-gray-100 my-1"></div>
                            <x-dropdown-link :href="route('empresas.create')">+ Nueva empresa</x-dropdown-link>
                            <x-dropdown-link :href="route('empresas.index')">Ver empresas</x-dropdown-link>
                        </x-nav-dropdown>

                        <x-nav-dropdown title="Equipos" :active="request()->routeIs('equipos.*')">
                            <x-dropdown-link :href="route('equipos.create')">+ Nuevo equipo</x-dropdown-link>
                            <x-dropdown-link :href="route('equipos.index')">Ver equipos</x-dropdown-link>
                        </x-nav-dropdown>

                        <x-nav-dropdown title="Búsqueda" :active="request()->routeIs('ordenes.buscar')">
                            <x-dropdown-link :href="route('ordenes.buscar')">Búsqueda avanzada</x-dropdown-link>
                            <x-dropdown-link :href="route('ordenes.index')">Ver todas las órdenes</x-dropdown-link>
                        </x-nav-dropdown>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @role('administrador')
                        <x-dropdown-link :href="route('admin.users.index')">
                            Gestión de Usuarios
                        </x-dropdown-link>
                        @endrole

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('ordenes.index')" :active="request()->routeIs('ordenes.index')">
                Órdenes
            </x-responsive-nav-link>

            @if (auth()->user()->hasRole('administrador'))
                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Clientes y Empresas</div>
                <x-responsive-nav-link :href="route('clientes.create')">+ Nuevo cliente</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.index')">Ver clientes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('empresas.create')">+ Nueva empresa</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('empresas.index')" :active="request()->routeIs('empresas.index')">Ver empresas</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Equipos</div>
                <x-responsive-nav-link :href="route('equipos.create')">+ Nuevo equipo</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('equipos.index')" :active="request()->routeIs('equipos.index')">Ver equipos</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.tipos-equipos.index')" :active="request()->routeIs('admin.tipos-equipos.*')">Tipos de equipo</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Búsqueda</div>
                <x-responsive-nav-link :href="route('ordenes.buscar')" :active="request()->routeIs('ordenes.buscar')">Búsqueda avanzada</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('ordenes.mias')" :active="request()->routeIs('ordenes.mias')">Mis órdenes asignadas</x-responsive-nav-link>
            @elseif (auth()->user()->hasRole('tecnico'))
                <x-responsive-nav-link :href="route('ordenes.mias')" :active="request()->routeIs('ordenes.mias')">Mis órdenes</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Clientes y Empresas</div>
                <x-responsive-nav-link :href="route('clientes.create')">+ Nuevo cliente</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.index')">Ver clientes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('empresas.create')">+ Nueva empresa</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('empresas.index')" :active="request()->routeIs('empresas.index')">Ver empresas</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Equipos</div>
                <x-responsive-nav-link :href="route('equipos.create')">+ Nuevo equipo</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('equipos.index')" :active="request()->routeIs('equipos.index')">Ver equipos</x-responsive-nav-link>

                <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Búsqueda</div>
                <x-responsive-nav-link :href="route('ordenes.buscar')" :active="request()->routeIs('ordenes.buscar')">Búsqueda avanzada</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @role('administrador')
                <x-responsive-nav-link :href="route('admin.users.index')">
                    Gestión de Usuarios
                </x-responsive-nav-link>
                @endrole

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
