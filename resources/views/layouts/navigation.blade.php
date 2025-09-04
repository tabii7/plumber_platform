<nav class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Left --}}
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="font-bold text-xl text-gray-800 dark:text-white">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="hidden sm:flex sm:space-x-6 sm:ml-10">
                    {{-- Everyone --}}
                    <a href="{{ route('dashboard') }}"
                       class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('dashboard') ? 'font-bold underline' : '' }}">
                        Dashboard
                    </a>

                    {{-- Admin-only --}}
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('admin.dashboard') ? 'font-bold underline' : '' }}">
                                Admin
                            </a>

                            <a href="{{ route('plumbers.index') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ str_starts_with(Route::currentRouteName(), 'plumbers.') ? 'font-bold underline' : '' }}">
                                Plumbers
                            </a>

                            {{-- Clients (resource in /admin/clients) --}}
                            <a href="{{ route('clients.index') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ str_starts_with(Route::currentRouteName(), 'clients.') ? 'font-bold underline' : '' }}">
                                Clients
                            </a>

                            {{-- Requests (admin resource in /admin/requests) --}}
                            <a href="{{ route('requests.index') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ str_starts_with(Route::currentRouteName(), 'requests.') ? 'font-bold underline' : '' }}">
                                Requests
                            </a>

                            {{-- WhatsApp --}}
                            <a href="{{ route('admin.whatsapp') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('admin.whatsapp') ? 'font-bold underline' : '' }}">
                                WhatsApp
                            </a>

                            {{-- Custom Messages (Flows) --}}
                            <a href="{{ route('admin.flows.index') }}"
                               class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ str_starts_with(Route::currentRouteName(), 'flows') ? 'font-bold underline' : '' }}">
                                Flows
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Right --}}
            <div class="flex items-center space-x-4">
                {{-- Dark Mode Toggle --}}
                <x-dark-mode-toggle />
                
                @auth
                    <span class="text-gray-700 dark:text-gray-300">Hi, {{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition-colors">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">Login</a>
                    <a href="{{ route('register') }}" class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
