<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('products.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Tambah Produk / Menu Baru') }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Mendukung mode Cafe/Resto (Stok Unlimited) maupun mode Warung/Retail (Stok Terbatas)</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
            
            <!-- Global Form Errors Alert -->
            @if($errors->any())
                <div class="p-4 mb-6 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-700 space-y-1">
                    <div class="font-bold flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-sm"></i> Terdapat kesalahan pada pengisian data:
                    </div>
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ price: {{ old('price', 0) }}, cost: {{ old('cost_price', 0) }}, manageStock: {{ old('manage_stock', '1') == '1' ? 'true' : 'false' }}, isActive: {{ old('is_active', '1') == '1' ? 'true' : 'false' }} }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Basic Info & Stock Setting -->
                    <div class="space-y-4">
                        <!-- Product Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk / Menu *</label>
                            <input 
                                type="text" 
                                name="name" 
                                value="{{ old('name') }}" 
                                placeholder="Contoh: Es Kopi Gula Aren" 
                                required 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori Menu</label>
                            <select 
                                name="category_id" 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- SKU & Barcode -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode SKU</label>
                                <input 
                                    type="text" 
                                    name="sku" 
                                    value="{{ old('sku') }}" 
                                    placeholder="Otomatis jika kosong" 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Barcode Scanner</label>
                                <input 
                                    type="text" 
                                    name="barcode" 
                                    value="{{ old('barcode') }}" 
                                    placeholder="8991234..." 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                            </div>
                        </div>

                        <!-- STOCK MANAGEMENT TOGGLE (Modern iOS Switch) -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-extrabold text-slate-900 block">Kelola Stok Barang</span>
                                    <span class="text-[11px] text-slate-500" x-text="manageStock ? 'Mode Retail/Warung: Stok berkurang saat terjual' : 'Mode Cafe/Resto: Stok selalu siap (Unlimited)'"></span>
                                </div>
                                <div class="flex items-center">
                                    <button 
                                        type="button" 
                                        @click="manageStock = !manageStock" 
                                        :class="manageStock ? 'bg-blue-600' : 'bg-slate-300'" 
                                        class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                    >
                                        <span 
                                            :class="manageStock ? 'translate-x-5' : 'translate-x-0'" 
                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                    <input type="hidden" name="manage_stock" :value="manageStock ? '1' : '0'">
                                </div>
                            </div>

                            <!-- Stock Input (Visible only if manageStock is true) -->
                            <div x-show="manageStock" x-transition class="pt-2 border-t border-slate-200/60">
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jumlah Stok Fisik</label>
                                <input 
                                    type="number" 
                                    name="stock" 
                                    value="{{ old('stock', 50) }}" 
                                    min="0" 
                                    class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Right: Pricing & Photo -->
                    <div class="space-y-4">
                        
                        <!-- Pricing Grid -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga Modal / Beli (Rp)</label>
                                <input 
                                    type="number" 
                                    name="cost_price" 
                                    x-model.number="cost" 
                                    placeholder="0" 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Harga Jual (Rp) *</label>
                                <input 
                                    type="number" 
                                    name="price" 
                                    x-model.number="price" 
                                    placeholder="0" 
                                    required 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                            </div>
                        </div>

                        <!-- Live Margin Indicator -->
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-500">Estimasi Margin Keuntungan:</span>
                            <span class="font-extrabold text-emerald-600" x-text="'Rp ' + (price - cost).toLocaleString('id-ID')"></span>
                        </div>

                        <!-- Upload Photo File -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Upload Foto Produk (File)</label>
                            <input 
                                type="file" 
                                name="image" 
                                accept="image/*" 
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            >
                        </div>

                        <!-- Image URL Alternative -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Atau Link Foto URL (Unsplash/Web)</label>
                            <input 
                                type="text" 
                                name="image_url" 
                                value="{{ old('image_url') }}" 
                                placeholder="https://..." 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                        </div>

                        <!-- Active Toggle (Modern iOS Switch) -->
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 block">Tampilkan di Kasir</span>
                                <span class="text-[11px] text-slate-400" x-text="isActive ? 'Produk aktif dan tampil di katalog' : 'Produk disembunyikan'"></span>
                            </div>
                            <div class="flex items-center">
                                <button 
                                    type="button" 
                                    @click="isActive = !isActive" 
                                    :class="isActive ? 'bg-blue-600' : 'bg-slate-300'" 
                                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                >
                                    <span 
                                        :class="isActive ? 'translate-x-5' : 'translate-x-0'" 
                                        class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Submit Action -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Produk</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
