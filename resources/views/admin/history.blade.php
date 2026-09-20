<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Riwayat Transaksi Penjualan') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Laporan lengkap penjualan, pembatalan (void), cetak ulang struk kasir & dapur</p>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap items-center gap-2 no-print self-start sm:self-auto">
                <a 
                    href="{{ route('admin.history.csv', request()->all()) }}" 
                    class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-emerald-600/30 transition flex items-center space-x-1.5"
                    title="Export data laporan ke spreadsheet Excel / CSV"
                >
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export Excel / CSV</span>
                </a>
                <a 
                    href="{{ route('admin.history.pdf', array_merge(request()->all(), ['stream' => 1])) }}" 
                    target="_blank" 
                    class="px-3.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-1.5"
                >
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Cetak PDF</span>
                </a>
                <a 
                    href="{{ route('admin.history.pdf', request()->all()) }}" 
                    class="px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold shadow-md transition flex items-center space-x-1.5"
                >
                    <i class="fa-solid fa-download"></i>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="historyApp()">
        
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl text-emerald-800 text-xs font-extrabold flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-300 rounded-2xl text-red-800 text-xs font-extrabold flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- Total Revenue -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Omset</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Total Gross Profit -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Laba Kotor</span>
                    <span class="text-sm sm:text-base font-extrabold text-emerald-600">
                        Rp {{ number_format($totalGrossProfit ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-[9px] text-slate-400 block">Margin: {{ $profitMargin ?? 0 }}%</span>
                </div>
            </div>

            <!-- Paid Orders Count -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Trx Lunas</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">
                        {{ $paidCount }} Pesanan
                    </span>
                </div>
            </div>

            <!-- Cash Transactions -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bayar Tunai</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">
                        {{ $cashCount }} Trx
                    </span>
                </div>
            </div>

            <!-- QRIS Transactions -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bayar QRIS</span>
                    <span class="text-sm sm:text-base font-extrabold text-slate-900">
                        {{ $qrisCount }} Trx
                    </span>
                </div>
            </div>

            <!-- Voided Orders -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-base shrink-0">
                    <i class="fa-solid fa-ban"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Batal (Void)</span>
                    <span class="text-sm sm:text-base font-extrabold text-red-600">
                        {{ $voidCount ?? 0 }} Trx
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Toolbar (Date Range + Presets) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4 no-print">
            
            <!-- Quick Date Preset Buttons -->
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Pilihan Rentang Tanggal Cepat:</span>
                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                    <button type="button" @click="setRange('today')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        Hari Ini
                    </button>
                    <button type="button" @click="setRange('yesterday')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        Kemarin
                    </button>
                    <button type="button" @click="setRange('last7days')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        7 Hari Terakhir
                    </button>
                    <button type="button" @click="setRange('thisMonth')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        Bulan Ini
                    </button>
                    <button type="button" @click="setRange('lastMonth')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        Bulan Lalu
                    </button>
                    <button type="button" @click="setRange('allTime')" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-xl text-xs font-bold transition">
                        Semua Waktu
                    </button>
                </div>
            </div>

            <!-- Date Range Form -->
            <form id="filterForm" action="{{ route('admin.history') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end pt-3 border-t border-slate-100">
                <!-- Start Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        id="startDateInput"
                        x-model="startDate"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        id="endDateInput"
                        x-model="endDate"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-blue-500"
                    >
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Metode Bayar</label>
                    <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Metode</option>
                        <option value="CASH" {{ request('payment_method') === 'CASH' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="QRIS" {{ request('payment_method') === 'QRIS' ? 'selected' : '' }}>QRIS Gateway</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Pembayaran</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Lunas (PAID)</option>
                        <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Menunggu (PENDING)</option>
                        <option value="VOID" {{ request('status') === 'VOID' ? 'selected' : '' }}>Dibatalkan (VOID)</option>
                        <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Batal QRIS (CANCELLED)</option>
                    </select>
                </div>

                <!-- Submit & Reset Actions -->
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition">
                        <i class="fa-solid fa-filter mr-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.history') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition flex items-center justify-center" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="historyTable" class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-3">Order Info</th>
                            <th class="py-3.5 px-3">Pelanggan</th>
                            <th class="py-3.5 px-3">Item Pesanan</th>
                            <th class="py-3.5 px-3">Pembayaran</th>
                            <th class="py-3.5 px-3 text-right">Total</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-3 text-right no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50 transition {{ in_array($order->status, ['VOID', 'CANCELLED']) ? 'bg-red-50/40 opacity-75' : '' }}">
                                <!-- Order Info -->
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-800 block text-xs">{{ $order->order_id }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                    @if($order->user)
                                        <span class="text-[9px] text-blue-600 font-bold block">Kasir: {{ $order->user->name }}</span>
                                    @endif
                                </td>

                                <!-- Customer & Type -->
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-800 text-xs">{{ $order->customer_name }}</div>
                                    @if($order->order_type)
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded-md text-[9px] font-bold {{ $order->order_type === 'DINE_IN' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            <i class="fa-solid {{ $order->order_type === 'DINE_IN' ? 'fa-chair' : 'fa-bag-shopping' }} mr-1"></i>
                                            {{ str_replace('_', ' ', $order->order_type) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Items Summary -->
                                <td class="py-3 px-3 max-w-xs">
                                    <ul class="space-y-1">
                                        @foreach($order->items as $item)
                                            <li class="text-[11px] leading-tight">
                                                <span class="font-bold text-slate-800">{{ $item->qty }}x</span> {{ $item->product_name }}
                                                @if(!empty($item->notes))
                                                    <span class="block text-[9px] text-amber-700 font-semibold pl-2 border-l-2 border-amber-400">
                                                        Note: {{ $item->notes }}
                                                    </span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <!-- Payment Method & Settlement -->
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold {{ $order->payment_method === 'CASH' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ $order->payment_method }}
                                    </span>
                                    @if($order->coupon_code)
                                        <div class="text-[10px] text-indigo-600 font-bold mt-1">
                                            <i class="fa-solid fa-ticket"></i> Kupon: {{ $order->coupon_code }}
                                        </div>
                                    @endif
                                    @if($order->discount > 0)
                                        <div class="text-[9px] text-red-500 font-bold">
                                            Hemat: Rp {{ number_format($order->discount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Grand Total -->
                                <td class="py-3 px-3 text-right">
                                    <span class="font-extrabold text-xs text-slate-900 {{ in_array($order->status, ['VOID', 'CANCELLED']) ? 'line-through text-slate-400' : '' }}">
                                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Status & Void Details -->
                                <td class="py-3 px-3">
                                    @if($order->status === 'PAID')
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            LUNAS
                                        </span>
                                        @if($order->settlement_type === 'MANUAL_CASHIER' && $order->payment_method === 'QRIS')
                                            <span class="block text-[9px] text-amber-600 font-bold mt-1">
                                                <i class="fa-solid fa-user-check"></i> Verif Manual
                                            </span>
                                        @endif
                                    @elseif($order->status === 'VOID')
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-red-100 text-red-800 border border-red-200">
                                            DIBATALKAN (VOID)
                                        </span>
                                        <div class="text-[10px] text-red-600 mt-1 max-w-[140px] leading-tight">
                                            <strong>Alasan:</strong> {{ $order->void_reason ?? '-' }}
                                            @if($order->voidedByUser)
                                                <span class="block text-[9px] text-slate-400">Oleh: {{ $order->voidedByUser->name }}</span>
                                            @endif
                                        </div>
                                    @elseif($order->status === 'CANCELLED')
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-slate-200 text-slate-700 border border-slate-300">
                                            BATAL QRIS
                                        </span>
                                        @if($order->notes)
                                            <span class="block text-[9px] text-slate-400 mt-1">{{ $order->notes }}</span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                            MENUNGGU
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3 text-right no-print whitespace-nowrap space-x-1">
                                    @if($order->payment_proof)
                                        <a 
                                            href="{{ $order->payment_proof_url ?? asset('storage/' . $order->payment_proof) }}" 
                                            target="_blank" 
                                            class="px-2 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl font-bold text-xs transition inline-flex items-center space-x-1 shadow-sm"
                                            title="Lihat Bukti Foto Bayar"
                                        >
                                            <i class="fa-solid fa-image"></i>
                                        </a>
                                    @endif

                                    <!-- Thermal Print Kasir -->
                                    <a href="{{ route('pos.receipt', $order->order_id) }}" target="_blank" class="px-2 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-bold text-xs transition inline-flex items-center space-x-1 shadow-sm" title="Cetak Struk Kasir">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>

                                    <!-- Thermal Print Dapur (KOT) -->
                                    @if(\App\Models\Setting::get('enable_kitchen_receipt', '1') == '1')
                                        <a href="{{ route('pos.kitchen-receipt', $order->order_id) }}" target="_blank" class="px-2 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl font-bold text-xs transition inline-flex items-center space-x-1 shadow-sm" title="Cetak Struk Dapur / KOT">
                                            <i class="fa-solid fa-utensils"></i>
                                        </a>
                                    @endif

                                    <!-- Void Button (Only for Admin and if not already VOID or CANCELLED) -->
                                    @if(auth()->user()->isAdmin() && !in_array($order->status, ['VOID', 'CANCELLED']))
                                        <button 
                                            type="button" 
                                            @click="openVoidModal('{{ $order->order_id }}', '{{ $order->customer_name }}', '{{ number_format($order->grand_total, 0, ',', '.') }}')" 
                                            class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-bold text-xs transition inline-flex items-center space-x-1 shadow-sm"
                                            title="Batalkan / Void Transaksi Ini (Khusus Admin)"
                                        >
                                            <i class="fa-solid fa-ban"></i>
                                            <span>Void</span>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Laravel Pagination Links -->
            @if($orders->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL VOID / PEMBATALAN TRANSAKSI -->
        <div 
            x-show="showVoidModal" 
            x-cloak 
            x-transition 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div @click.outside="showVoidModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden text-left space-y-4">
                <!-- Header -->
                <div class="p-5 bg-red-600 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm">Void / Pembatalan Pesanan</h3>
                            <p class="text-[11px] text-red-100" x-text="'No. Order: #' + selectedOrderId"></p>
                        </div>
                    </div>
                    <button @click="showVoidModal = false" class="text-red-200 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Form -->
                <form :action="voidActionUrl" method="POST" class="p-6 pt-0 space-y-4">
                    @csrf

                    <div class="p-3 bg-red-50 border border-red-200 rounded-2xl text-[11px] text-red-700 space-y-1">
                        <p class="font-bold flex items-center space-x-1">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Informasi Tindakan:</span>
                        </p>
                        <p>Total transaksi <strong x-text="'Rp ' + selectedOrderTotal"></strong> akan dikeluarkan dari omset penjualan dan stok produk yang dikelola akan otomatis <strong>dikembalikan</strong> ke sistem.</p>
                    </div>

                    <!-- Void Reason Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Alasan Pembatalan (Wajib) *</label>
                        <textarea 
                            name="void_reason" 
                            rows="3" 
                            required 
                            placeholder="Contoh: Salah input meja / Pelanggan membatalkan pesanan / Salah ketik menu" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                        ></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showVoidModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-red-600/30 transition active:scale-95 flex items-center space-x-1.5">
                            <i class="fa-solid fa-ban"></i>
                            <span>Ya, Batalkan Transaksi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function historyPage() {
            return {
                startDate: '{{ request('start_date') }}',
                endDate: '{{ request('end_date') }}',
                activeRange: '{{ request('date') ? 'today' : (request('start_date') ? 'custom' : 'all') }}',
                showVoidModal: false,
                selectedOrderId: '',
                selectedOrderCustomer: '',
                selectedOrderTotal: '',
                voidActionUrl: '',

                formatDate(d) {
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                },

                setPreset(type) {
                    const today = new Date();
                    this.activeRange = type;

                    if (type === 'today') {
                        this.startDate = this.formatDate(today);
                        this.endDate = this.formatDate(today);
                    } else if (type === 'yesterday') {
                        const yesterday = new Date();
                        yesterday.setDate(today.getDate() - 1);
                        this.startDate = this.formatDate(yesterday);
                        this.endDate = this.formatDate(yesterday);
                    } else if (type === 'thisWeek') {
                        const firstDayOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1)));
                        this.startDate = this.formatDate(firstDayOfWeek);
                        this.endDate = this.formatDate(today);
                    } else if (type === 'thisMonth') {
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                        this.startDate = this.formatDate(firstDay);
                        this.endDate = this.formatDate(today);
                    } else if (type === 'lastMonth') {
                        const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                        this.startDate = this.formatDate(firstDayLastMonth);
                        this.endDate = this.formatDate(lastDayLastMonth);
                    } else if (type === 'allTime') {
                        this.startDate = '';
                        this.endDate = '';
                    }

                    this.$nextTick(() => {
                        document.getElementById('filterForm').submit();
                    });
                },

                openVoidModal(orderId, customer, total) {
                    this.selectedOrderId = orderId;
                    this.selectedOrderCustomer = customer;
                    this.selectedOrderTotal = total;
                    this.voidActionUrl = `/admin/orders/${orderId}/void`;
                    this.showVoidModal = true;
                }
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            $('#historyTable').DataTable({
                paging: false,
                info: false,
                order: [],
                language: {
                    search: "Cari cepat di halaman ini:",
                    zeroRecords: "Tidak ada data yang cocok dengan pencarian"
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
