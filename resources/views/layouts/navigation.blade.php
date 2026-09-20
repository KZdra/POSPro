<nav x-data="{ open: false }" class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-6">
                <!-- Logo / Brand -->
                <div class="shrink-0 flex items-center space-x-3">
                    <a href="{{ route('pos.index') }}" class="flex items-center space-x-2 text-blue-600 font-extrabold text-xl tracking-tight">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20">
                            <i class="fa-solid fa-cash-register text-lg"></i>
                        </div>
                        <span class="text-slate-900 font-bold">POS<span class="text-blue-600">Pro</span></span>
                    </a>
                </div>

                <!-- Navigation Links (Grouped into clean, spacious menus) -->
                <div class="hidden space-x-2 lg:space-x-3 sm:flex items-center">
                    <!-- 1. Kasir (POS) - Highlighted Primary Button -->
                    <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-bold transition-all {{ request()->routeIs('pos.index') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-store mr-1.5"></i> {{ __('Kasir (POS)') }}
                    </a>

                    <!-- 2. Dashboard (Admin Only) -->
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-chart-pie mr-1.5 text-slate-400"></i> {{ __('Dashboard') }}
                        </a>

                        <!-- 3. Dropdown Katalog (Produk, Kategori, Kupon) -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                            <button 
                                @click="open = !open" 
                                type="button"
                                class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs(['products.*', 'categories.*', 'coupons.*']) ? 'text-blue-600 bg-blue-50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                            >
                                <i class="fa-solid fa-boxes-stacked mr-1.5 text-slate-400"></i>
                                <span>{{ __('Katalog') }}</span>
                                <i class="fa-solid fa-chevron-down ml-1.5 text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div 
                                x-show="open" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                class="absolute left-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/80 p-1.5 z-50 space-y-0.5" 
                                style="display: none;"
                            >
                                <a href="{{ route('products.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('products.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-box-open"></i></div>
                                    <div>
                                        <div>{{ __('Data Produk & Stok') }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">Katalog menu & min stock</div>
                                    </div>
                                </a>
                                <a href="{{ route('categories.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('categories.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-layer-group"></i></div>
                                    <div>
                                        <div>{{ __('Kategori Menu') }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">Pengelompokan produk</div>
                                    </div>
                                </a>
                                <a href="{{ route('coupons.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('coupons.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-ticket"></i></div>
                                    <div>
                                        <div>{{ __('Kupon Promo') }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">Voucher diskon & QR code</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Dropdown Operasional (Riwayat Transaksi & Shift Kasir) -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                        <button 
                            @click="open = !open" 
                            type="button"
                            class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs(['admin.history', 'admin.shifts.*']) ? 'text-blue-600 bg-blue-50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                        >
                            <i class="fa-solid fa-clock-rotate-left mr-1.5 text-slate-400"></i>
                            <span>{{ __('Operasional & Kas') }}</span>
                            <i class="fa-solid fa-chevron-down ml-1.5 text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div 
                            x-show="open" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200/80 p-1.5 z-50 space-y-0.5" 
                            style="display: none;"
                        >
                            <a href="{{ route('admin.history') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('admin.history') ? 'bg-blue-50 text-blue-600' : '' }}">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-receipt"></i></div>
                                <div>
                                    <div>{{ __('Riwayat Penjualan') }}</div>
                                    <div class="text-[10px] font-normal text-slate-400">Laporan omset, laba & export</div>
                                </div>
                            </a>
                            @if(\App\Models\Setting::get('enable_shifts', '1') == '1')
                            <a href="{{ route('admin.shifts.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('admin.shifts.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-vault"></i></div>
                                <div>
                                    <div>{{ __('Shift Kasir & Drawer') }}</div>
                                    <div class="text-[10px] font-normal text-slate-400">Modal awal & rekonsiliasi kas</div>
                                </div>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- 5. Dropdown Pelanggan & Staff (Admin Only) -->
                    @if(Auth::check() && Auth::user()->isAdmin())
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                            <button 
                                @click="open = !open" 
                                type="button"
                                class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs(['customers.*', 'users.*']) ? 'text-blue-600 bg-blue-50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                            >
                                <i class="fa-solid fa-users mr-1.5 text-slate-400"></i>
                                <span>{{ __('Member & Staff') }}</span>
                                <i class="fa-solid fa-chevron-down ml-1.5 text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div 
                                x-show="open" 
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                class="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200/80 p-1.5 z-50 space-y-0.5" 
                                style="display: none;"
                            >
                                <a href="{{ route('customers.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-id-card"></i></div>
                                    <div>
                                        <div>{{ __('Member / CRM') }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">Poin loyalitas & kontak</div>
                                    </div>
                                </a>
                                <a href="{{ route('users.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('users.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shrink-0"><i class="fa-solid fa-users-gear"></i></div>
                                    <div>
                                        <div>{{ __('User & Kasir') }}</div>
                                        <div class="text-[10px] font-normal text-slate-400">Kelola akun & hak akses</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- 6. Pengaturan Toko (Admin Only) -->
                        <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.settings') ? 'text-blue-600 bg-blue-50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-gear mr-1.5 text-slate-400"></i> {{ __('Toko') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings / User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-slate-200 text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition ease-in-out duration-150">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs mr-2">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <div class="text-left">
                                    <div class="leading-tight">{{ Auth::user()->name }}</div>
                                    <div class="text-[10px] uppercase font-extrabold {{ Auth::user()->isAdmin() ? 'text-purple-600' : 'text-blue-600' }}">
                                        {{ Auth::user()->role }}
                                    </div>
                                </div>

                                <div class="ms-2">
                                    <svg class="fill-current h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if(Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('admin.settings')">
                                    <i class="fa-solid fa-gear mr-2 text-slate-400"></i> {{ __('Pengaturan Toko') }}
                                </x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="route('profile.edit')">
                                <i class="fa-regular fa-user mr-2 text-slate-400"></i> {{ __('Profil Saya') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 hover:text-red-700">
                                    <i class="fa-solid fa-arrow-right-from-bracket mr-2 text-red-400"></i> {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                        <i class="fa-solid fa-lock mr-1.5"></i> Login
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-slate-200 px-4 pt-2 pb-3 space-y-1">
        <!-- Kasir & Dashboard -->
        <x-responsive-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.index')">
            <i class="fa-solid fa-store mr-2 text-blue-600"></i> {{ __('Kasir (POS)') }}
        </x-responsive-nav-link>

        @auth
            @if(Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <i class="fa-solid fa-chart-pie mr-2 text-slate-400"></i> {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <!-- Section: Katalog -->
                <div class="pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-3">Katalog & Stok</div>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    <i class="fa-solid fa-box-open mr-2 text-blue-500"></i> {{ __('Data Produk & Stok') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    <i class="fa-solid fa-layer-group mr-2 text-emerald-500"></i> {{ __('Kategori') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('coupons.index')" :active="request()->routeIs('coupons.*')">
                    <i class="fa-solid fa-ticket mr-2 text-amber-500"></i> {{ __('Kupon Promo') }}
                </x-responsive-nav-link>
            @endif

            <!-- Section: Operasional -->
            <div class="pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-3">Operasional & Kas</div>
            <x-responsive-nav-link :href="route('admin.history')" :active="request()->routeIs('admin.history')">
                <i class="fa-solid fa-clock-rotate-left mr-2 text-blue-500"></i> {{ __('Riwayat Penjualan') }}
            </x-responsive-nav-link>
            @if(\App\Models\Setting::get('enable_shifts', '1') == '1')
            <x-responsive-nav-link :href="route('admin.shifts.index')" :active="request()->routeIs('admin.shifts.*')">
                <i class="fa-solid fa-vault mr-2 text-emerald-500"></i> {{ __('Shift Kasir & Drawer') }}
            </x-responsive-nav-link>
            @endif

            @if(Auth::user()->isAdmin())
                <!-- Section: Pelanggan & Staff -->
                <div class="pt-2 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-3">Member & Pengaturan</div>
                <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">
                    <i class="fa-solid fa-id-card mr-2 text-amber-500"></i> {{ __('Member / CRM') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <i class="fa-solid fa-users-gear mr-2 text-indigo-500"></i> {{ __('User & Kasir') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">
                    <i class="fa-solid fa-gear mr-2 text-slate-400"></i> {{ __('Pengaturan Toko') }}
                </x-responsive-nav-link>
            @endif

            <div class="pt-4 pb-1 border-t border-slate-200">
                <div class="font-bold text-sm text-slate-800">{{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</div>
                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
                <div class="mt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <x-responsive-nav-link :href="route('login')">
                <i class="fa-solid fa-lock mr-2"></i> {{ __('Login') }}
            </x-responsive-nav-link>
        @endauth
    </div>
</nav>
