<x-guest-layout>
    <!-- Header Title -->
    <div class="mb-6 text-center">
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Masuk ke Sistem POS</h2>
        <p class="text-xs text-slate-500 mt-1">Gunakan akun kasir atau admin untuk melanjutkan</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-xs" :status="session('status')" />

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPass: false, email: '{{ old('email') }}', password: '' }">
        @csrf

        <!-- Email Input -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Email Akun</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-envelope text-xs"></i>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    x-model="email"
                    required 
                    autofocus 
                    placeholder="nama@pos.com"
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
        </div>

        <!-- Password Input with Show/Hide Toggle -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-blue-600 hover:text-blue-700 transition" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-lock text-xs"></i>
                </div>
                <input 
                    id="password" 
                    :type="showPass ? 'text' : 'password'" 
                    name="password" 
                    x-model="password"
                    required 
                    placeholder="••••••••"
                    class="w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                <button 
                    type="button" 
                    @click="showPass = !showPass" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                    tabindex="-1"
                >
                    <i class="fa-solid text-xs" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded-lg border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-xs font-semibold text-slate-600">Ingat sesi saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all active:scale-98 flex items-center justify-center space-x-2"
            >
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Masuk Sekarang</span>
            </button>
        </div>

        <!-- Quick Fill Account Demo Pills -->
        <div class="pt-4 mt-4 border-t border-slate-100 space-y-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block text-center">Akun Cepat (Klik untuk Isi):</span>
            <div class="grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="email = 'admin@pos.com'; password = 'password'"
                    class="p-2 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-left transition"
                >
                    <div class="font-extrabold text-xs text-purple-900 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-1 text-[10px] text-purple-600"></i> Super Admin
                    </div>
                    <div class="text-[10px] text-purple-700 font-mono mt-0.5">admin@pos.com</div>
                </button>

                <button 
                    type="button" 
                    @click="email = 'kasir@pos.com'; password = 'password'"
                    class="p-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl text-left transition"
                >
                    <div class="font-extrabold text-xs text-blue-900 flex items-center">
                        <i class="fa-solid fa-cash-register mr-1 text-[10px] text-blue-600"></i> Kasir 01
                    </div>
                    <div class="text-[10px] text-blue-700 font-mono mt-0.5">kasir@pos.com</div>
                </button>
            </div>
        </div>
    </form>
</x-guest-layout>
