<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('users.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Edit Pengguna') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui data atau hak akses user {{ $user->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
            
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $user->name) }}" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Login *</label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email', $user->email) }}" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Hak Akses) *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center space-x-3 cursor-pointer hover:bg-slate-100">
                            <input type="radio" name="role" value="kasir" class="text-blue-600 focus:ring-blue-500" {{ $user->role === 'kasir' ? 'checked' : '' }}>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Kasir POS</span>
                                <span class="text-[10px] text-slate-400">Melayani transaksi kasir</span>
                            </div>
                        </label>

                        <label class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center space-x-3 cursor-pointer hover:bg-slate-100">
                            <input type="radio" name="role" value="admin" class="text-blue-600 focus:ring-blue-500" {{ $user->role === 'admin' ? 'checked' : '' }}>
                            <div>
                                <span class="font-bold text-xs text-slate-800 block">Super Admin</span>
                                <span class="text-[10px] text-slate-400">Akses master produk & laporan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Password (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Masukkan password baru jika ingin diganti" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Submit Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Perbarui Pengguna</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
