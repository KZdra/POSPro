<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Master Produk & Menu') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Daftar inventaris menu (Mode Cafe / Unlimited & Mode Retail / Stok Fisik)</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('categories.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-2">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Kelola Kategori</span>
                </a>
                <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Produk Baru</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            
            <table id="productsTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3">Foto</th>
                        <th class="py-3 px-3">SKU</th>
                        <th class="py-3 px-3">Nama Produk</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3">Harga Beli</th>
                        <th class="py-3 px-3">Harga Jual</th>
                        <th class="py-3 px-3">Margin/Profit</th>
                        <th class="py-3 px-3">Status Stok</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-50 transition">
                            <!-- Image Thumbnail -->
                            <td class="py-3 px-3">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-11 h-11 object-cover rounded-xl border border-slate-200 shadow-sm">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-300 flex items-center justify-center border border-slate-200">
                                        <i class="fa-solid fa-image text-sm"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- SKU -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-500">
                                {{ $product->sku ?: '-' }}
                            </td>

                            <!-- Name -->
                            <td class="py-3 px-3">
                                <span class="font-extrabold text-slate-900 text-sm block">{{ $product->name }}</span>
                                @if($product->barcode)
                                    <span class="text-[10px] text-slate-400 font-mono">Barcode: {{ $product->barcode }}</span>
                                @endif
                            </td>

                            <!-- Category -->
                            <td class="py-3 px-3">
                                @if($product->category)
                                    <span class="px-2.5 py-1 rounded-lg font-bold text-[11px] border {{ $product->category->color_badge_class }}">
                                        <i class="fa-solid {{ $product->category->icon ?: 'fa-tag' }} mr-1"></i>
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tanpa Kategori</span>
                                @endif
                            </td>

                            <!-- Cost Price -->
                            <td class="py-3 px-3 text-slate-500 font-medium">
                                Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                            </td>

                            <!-- Selling Price -->
                            <td class="py-3 px-3 font-extrabold text-slate-900 text-sm">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>

                            <!-- Profit Margin -->
                            <td class="py-3 px-3 font-bold">
                                @php
                                    $profit = $product->price - $product->cost_price;
                                @endphp
                                <span class="text-emerald-600">
                                    +Rp {{ number_format($profit, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Stock Indicator (Cafe vs Warung) -->
                            <td class="py-3 px-3">
                                @if(!$product->manage_stock)
                                    <span class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-extrabold text-[11px] border border-purple-200">
                                        <i class="fa-solid fa-infinity mr-1"></i> Unlimited (Cafe)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg font-extrabold text-[11px] {{ $product->stock <= 0 ? 'bg-red-100 text-red-700' : ($product->stock <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-800') }}">
                                        {{ $product->stock <= 0 ? 'HABIS (0)' : $product->stock . ' Unit' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-3 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('products.edit', $product->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Produk">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form id="del-prod-{{ $product->id }}" action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('del-prod-{{ $product->id }}')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus Produk">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Laravel Pagination Links -->
            @if($products->hasPages())
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $products->links() }}
                </div>
            @endif

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#productsTable').DataTable({
                paging: false,
                info: false,
                language: {
                    search: "Cari cepat di halaman ini:",
                    zeroRecords: "Tidak ada produk yang cocok"
                }
            });
        });
    </script>
</x-app-layout>
