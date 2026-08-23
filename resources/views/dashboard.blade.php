<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Dashboard Analitik') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Ringkasan performa penjualan dan inventaris toko Anda hari ini</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                    <i class="fa-solid fa-store"></i>
                    <span>Buka Kasir (POS)</span>
                </a>
                <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold shadow-sm transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Produk</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- KPI METRICS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Penjualan Hari Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penjualan Hari Ini</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        Rp {{ number_format($todaySales, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-600 font-bold flex items-center mt-1">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> {{ $todayOrdersCount }} Transaksi Lunas
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-rupiah-sign"></i>
                </div>
            </div>

            <!-- Total Transaksi Hari Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pesanan Hari Ini</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ $todayOrdersCount }} <span class="text-sm font-semibold text-slate-400">Order</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium mt-1 block">
                        Cash & QRIS Gateway
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>

            <!-- Penjualan Bulan Ini -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Omset Bulan Ini</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        Rp {{ number_format($monthSales, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-blue-600 font-bold mt-1 block">
                        Periode {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>

            <!-- Total Produk Aktif -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Produk</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ $totalProducts }} <span class="text-sm font-semibold text-slate-400">Item</span>
                    </div>
                    <span class="text-[11px] text-slate-500 font-semibold mt-1 block">
                        Siap untuk dijual
                    </span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

        </div>

        <!-- PAYMENT METHOD BREAKDOWN & LOW STOCK ALERTS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Payment Method Split -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-wallet text-blue-600"></i>
                    <span>Metode Pembayaran Hari Ini</span>
                </h3>

                <div class="space-y-3 pt-2">
                    <!-- Cash -->
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-money-bill"></i>
                            </div>
                            <div>
                                <div class="text-xs font-extrabold text-slate-800">Tunai (Cash)</div>
                                <div class="text-[11px] text-slate-400">Kas Masuk</div>
                            </div>
                        </div>
                        <div class="font-extrabold text-sm text-slate-900">
                            Rp {{ number_format($cashSalesToday, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- QRIS -->
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="text-xs font-extrabold text-slate-800">QRIS Gateway</div>
                                <div class="text-[11px] text-slate-400">Mutasi Otomatis</div>
                            </div>
                        </div>
                        <div class="font-extrabold text-sm text-slate-900">
                            Rp {{ number_format($qrisSalesToday, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Warnings -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                        <span>Peringatan Stok Menipis (&le; 5)</span>
                    </h3>
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                        Kelola Stok &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 uppercase tracking-wider">
                                <th class="pb-2">Produk</th>
                                <th class="pb-2">SKU</th>
                                <th class="pb-2">Sisa Stok</th>
                                <th class="pb-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lowStockProducts as $low)
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-800">{{ $low->name }}</td>
                                    <td class="py-2.5 text-slate-500 font-mono">{{ $low->sku }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-md font-extrabold {{ $low->stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $low->stock <= 0 ? 'HABIS (0)' : $low->stock . ' Unit' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <a href="{{ route('products.edit', $low->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold">
                                            Tambah Stok
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 font-medium">
                                        <i class="fa-regular fa-circle-check text-emerald-500 text-lg mr-1"></i> Semua stok aman terkendali!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RECENT ORDERS TABLE -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-slate-700"></i>
                    <span>5 Transaksi Penjualan Terakhir</span>
                </h3>
                <a href="{{ route('admin.history') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    Lihat Semua Riwayat &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-100 uppercase tracking-wider">
                            <th class="pb-2.5">No. Order</th>
                            <th class="pb-2.5">Waktu</th>
                            <th class="pb-2.5">Pelanggan</th>
                            <th class="pb-2.5">Item</th>
                            <th class="pb-2.5">Metode</th>
                            <th class="pb-2.5">Total</th>
                            <th class="pb-2.5">Status</th>
                            <th class="pb-2.5 text-right">Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $ro)
                            <tr>
                                <td class="py-3 font-extrabold text-slate-900 font-mono">{{ $ro->order_id }}</td>
                                <td class="py-3 text-slate-500">{{ $ro->created_at->format('d/m H:i') }}</td>
                                <td class="py-3 font-medium text-slate-700">{{ $ro->customer_name }}</td>
                                <td class="py-3 text-slate-500">
                                    {{ $ro->items->sum('qty') }} item
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase {{ $ro->payment_method === 'QRIS' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $ro->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 font-extrabold text-slate-900">
                                    Rp {{ number_format($ro->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold {{ $ro->status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $ro->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('pos.receipt', $ro->order_id) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Struk">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    Belum ada transaksi penjualan yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
