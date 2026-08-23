<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('categories.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Edit Kategori') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui nama dan icon kategori {{ $category->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
            
            <form action="{{ route('categories.update', $category->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Kategori *</label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $category->name) }}" 
                        required 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Icon Class -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Icon FontAwesome</label>
                    <input 
                        type="text" 
                        name="icon" 
                        value="{{ old('icon', $category->icon) }}" 
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Pilihan: fa-mug-hot, fa-utensils, fa-cookie-bite, fa-glass-water, fa-cake-candles, fa-box, fa-tags, fa-burger, fa-pizza-slice</p>
                </div>

                <!-- Standard Color Theme Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Warna Aksen Kategori (Standar POS)</label>
                    <select name="color" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="amber" {{ old('color', $category->color) == 'amber' ? 'selected' : '' }}>🟤 Cokelat / Amber (Kopi, Espresso, Roti Gandum)</option>
                        <option value="orange" {{ old('color', $category->color) == 'orange' ? 'selected' : '' }}>🟠 Oranye (Makanan Utama, Fast Food, Ayam, Gorengan)</option>
                        <option value="emerald" {{ old('color', $category->color) == 'emerald' ? 'selected' : '' }}>🟢 Hijau / Emerald (Salad, Makanan Sehat, Snack, Matcha)</option>
                        <option value="blue" {{ old('color', $category->color) == 'blue' ? 'selected' : '' }}>🔵 Biru (Minuman Dingin, Jus, Air Mineral, Umum)</option>
                        <option value="teal" {{ old('color', $category->color) == 'teal' ? 'selected' : '' }}>🔷 Teal / Cyan (Mocktail, Es Campur, Minuman Segar)</option>
                        <option value="purple" {{ old('color', $category->color) == 'purple' ? 'selected' : '' }}>🟣 Ungu (Dessert, Pastry, Kue, Es Krim, Signature)</option>
                        <option value="rose" {{ old('color', $category->color) == 'rose' ? 'selected' : '' }}>🔴 Merah / Rose (Promo, Best Seller, Pedas, Daging)</option>
                        <option value="yellow" {{ old('color', $category->color) == 'yellow' ? 'selected' : '' }}>🟡 Kuning (Sarapan / Breakfast, Keju, Extra Topping)</option>
                        <option value="slate" {{ old('color', $category->color) == 'slate' ? 'selected' : '' }}>⚫ Abu-abu / Dark (Perlengkapan, Takeaway, Merchandise)</option>
                    </select>
                </div>

                <!-- Submit & Cancel Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('categories.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Perbarui Kategori</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
