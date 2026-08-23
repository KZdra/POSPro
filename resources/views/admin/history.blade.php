<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Riwayat Transaksi Penjualan') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Laporan lengkap penjualan berdasarkan rentang tanggal & metode pembayaran</p>
            </div>

            <!-- PDF Export Buttons -->
            <div class="flex items-center space-x-2 no-print self-start sm:self-auto">
                <a 
                    href="{{ route('admin.history.pdf', array_merge(request()->all(), ['stream' => 1])) }}" 
                    target="_blank" 
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2"
                >
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Cetak PDF Laporan</span>
                </a>
                <a 
                    href="{{ route('admin.history.pdf', request()->all()) }}" 
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold shadow-md transition flex items-center space-x-2"
                >
                    <i class="fa-solid fa-download"></i>
                    <span>Download PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Revenue -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Omset</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Paid Orders Count -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Transaksi Lunas</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900">
                        {{ $paidCount }} Pesanan
                    </span>
                </div>
            </div>

            <!-- Cash Transactions -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bayar Tunai</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900">
                        {{ $cashCount }} Trx
                    </span>
                </div>
            </div>

            <!-- QRIS Transactions -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center space-x-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Bayar QRIS</span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900">
                        {{ $qrisCount }} Trx
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Toolbar (Date Range + Presets) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4 no-print" x-data="dateRangePicker()">
            
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

        <!-- DataTable of Orders -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <table id="historyTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3">Waktu Transaksi</th>
                        <th class="py-3 px-3">No. Order</th>
                        <th class="py-3 px-3">Pelanggan / Meja</th>
                        <th class="py-3 px-3">Kasir</th>
                        <th class="py-3 px-3">Rincian Menu</th>
                        <th class="py-3 px-3">Metode</th>
                        <th class="py-3 px-3">Total Bayar</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3 whitespace-nowrap text-slate-600 font-medium">
                                <span class="font-bold text-slate-900 block">{{ $order->created_at->format('d M Y') }}</span>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $order->created_at->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-extrabold text-blue-600">
                                {{ $order->order_id }}
                            </td>
                            <td class="py-3 px-3 font-bold text-slate-800">
                                {{ $order->customer_name }}
                            </td>
                            <td class="py-3 px-3 text-slate-500 font-medium">
                                {{ $order->user ? $order->user->name : '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="space-y-1 max-w-xs">
                                    @foreach($order->items as $item)
                                        <div class="text-[11px] text-slate-700">
                                            <span class="font-bold text-slate-900">{{ $item->qty }}x</span> {{ $item->product_name }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $order->payment_method === 'QRIS' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ $order->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-extrabold text-slate-900 text-sm whitespace-nowrap">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold {{ $order->status === 'PAID' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right no-print whitespace-nowrap">
                                <a href="{{ route('pos.receipt', $order->order_id) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition inline-flex items-center space-x-1.5 shadow-sm" title="Cetak Ulang Struk">
                                    <i class="fa-solid fa-print text-slate-500"></i>
                                    <span>Struk</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- Script for Date Range Presets & DataTable -->
    <script>
        function dateRangePicker() {
            return {
                startDate: '{{ request('start_date') }}',
                endDate: '{{ request('end_date') }}',

                formatDate(date) {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                },

                setRange(type) {
                    const today = new Date();
                    if (type === 'today') {
                        this.startDate = this.formatDate(today);
                        this.endDate = this.formatDate(today);
                    } else if (type === 'yesterday') {
                        const yest = new Date(today);
                        yest.setDate(yest.getDate() - 1);
                        this.startDate = this.formatDate(yest);
                        this.endDate = this.formatDate(yest);
                    } else if (type === 'last7days') {
                        const past = new Date(today);
                        past.setDate(past.getDate() - 6);
                        this.startDate = this.formatDate(past);
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
                }
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            $('#historyTable').DataTable({
                pageLength: 20,
                order: [[0, 'desc']],
                language: {
                    search: "Cari Order / Pelanggan / Kasir:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ transaksi",
                    paginate: { next: "&rarr;", previous: "&larr;" },
                    zeroRecords: "Tidak ada transaksi pada rentang filter ini"
                }
            });
        });
    </script>
</x-app-layout>
