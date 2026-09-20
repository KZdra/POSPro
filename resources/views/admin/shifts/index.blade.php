<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Manajemen Shift Kasir & Rekonsiliasi Kas') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Laporan buka/tutup shift kasir, pencatatan modal awal, dan selisih uang fisik laci kasir</p>
            </div>
            
            <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2 self-start sm:self-auto">
                <i class="fa-solid fa-cash-register"></i>
                <span>Buka Layar Kasir POS</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Active Shift Card (If Any) -->
        @if($activeShift)
            <div class="p-6 bg-gradient-to-r from-blue-900 to-indigo-900 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-xs font-extrabold uppercase tracking-wider animate-pulse">
                            <i class="fa-solid fa-circle text-[8px] mr-1 text-emerald-400"></i> Shift Aktif
                        </span>
                        <span class="text-xs text-slate-300 font-mono">Buka: {{ $activeShift->opened_at->format('d M Y, H:i') }}</span>
                    </div>
                    <h3 class="text-xl font-extrabold">Kasir: {{ $activeShift->user ? $activeShift->user->name : 'Kasir' }}</h3>
                    <div class="flex flex-wrap gap-4 pt-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Modal Awal:</span>
                            <span class="font-extrabold text-white text-sm">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Penjualan Tunai:</span>
                            <span class="font-extrabold text-emerald-400 text-sm">Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Non-Tunai (QRIS/EDC):</span>
                            <span class="font-extrabold text-purple-300 text-sm">Rp {{ number_format($activeShift->non_cash_sales, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 w-full md:w-auto">
                    <button 
                        type="button" 
                        onclick="document.getElementById('closeShiftModal').classList.remove('hidden')" 
                        class="w-full md:w-auto px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-extrabold text-xs rounded-2xl shadow-lg transition flex items-center justify-center space-x-2"
                    >
                        <i class="fa-solid fa-lock"></i>
                        <span>Tutup Shift Sekarang</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Shifts History Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                <span>Riwayat Shift & Rekonsiliasi Kas</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                            <th class="py-3 px-3">Waktu Shift</th>
                            <th class="py-3 px-3">Kasir</th>
                            <th class="py-3 px-3">Modal Awal</th>
                            <th class="py-3 px-3">Penjualan Tunai</th>
                            <th class="py-3 px-3">Penjualan Non-Tunai</th>
                            <th class="py-3 px-3">Ekspektasi Kas</th>
                            <th class="py-3 px-3">Uang Fisik Aktual</th>
                            <th class="py-3 px-3">Selisih</th>
                            <th class="py-3 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="font-bold text-slate-900 block">{{ $shift->opened_at->format('d M Y, H:i') }}</span>
                                    <span class="text-[10px] text-slate-400">s/d {{ $shift->closed_at ? $shift->closed_at->format('H:i') : 'Sekarang' }}</span>
                                </td>
                                <td class="py-3 px-3 font-bold text-slate-800">
                                    {{ $shift->user ? $shift->user->name : '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 font-mono text-emerald-600 font-bold">
                                    Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 font-mono text-purple-600 font-bold">
                                    Rp {{ number_format($shift->non_cash_sales, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                    Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-blue-700">
                                    {{ $shift->actual_cash !== null ? 'Rp ' . number_format($shift->actual_cash, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    @if($shift->difference === null)
                                        <span class="text-slate-400">-</span>
                                    @elseif($shift->difference == 0)
                                        <span class="px-2 py-0.5 rounded-md font-extrabold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            PAS (Rp 0)
                                        </span>
                                    @elseif($shift->difference > 0)
                                        <span class="px-2 py-0.5 rounded-md font-extrabold text-[10px] bg-blue-100 text-blue-800 border border-blue-200">
                                            +Rp {{ number_format($shift->difference, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md font-extrabold text-[10px] bg-red-100 text-red-800 border border-red-200">
                                            -Rp {{ number_format(abs($shift->difference), 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold {{ $shift->status === 'OPEN' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $shift->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 font-medium">
                                    Belum ada catatan shift kasir yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($shifts->hasPages())
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Tutup Shift -->
    @if($activeShift)
        <div id="closeShiftModal" class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden text-left space-y-4">
                <div class="p-5 bg-red-600 text-white flex items-center justify-between">
                    <h3 class="font-extrabold text-sm flex items-center space-x-2">
                        <i class="fa-solid fa-lock"></i>
                        <span>Tutup Shift Kasir</span>
                    </h3>
                    <button type="button" onclick="document.getElementById('closeShiftModal').classList.add('hidden')" class="text-white hover:text-red-200 text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="{{ route('pos.shift.close', $activeShift->id) }}" method="POST" class="p-6 pt-0 space-y-4">
                    @csrf
                    
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-1.5 text-xs text-slate-700">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Modal Awal:</span>
                            <span class="font-bold">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Penjualan Tunai:</span>
                            <span class="font-bold text-emerald-600">+ Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-1.5 font-extrabold text-slate-900">
                            <span>Ekspektasi Kas di Laci:</span>
                            <span>Rp {{ number_format($activeShift->opening_cash + $activeShift->cash_sales, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Total Uang Fisik Aktual di Laci (Rp) *</label>
                        <input 
                            type="number" 
                            name="actual_cash" 
                            required 
                            min="0" 
                            placeholder="Hitung seluruh uang fisik di laci kasir..." 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 focus:bg-white focus:ring-2 focus:ring-red-500"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Shift (Opsional)</label>
                        <textarea 
                            name="notes" 
                            rows="2" 
                            placeholder="Catatan kendala / pengeluaran kas kecil..." 
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" onclick="document.getElementById('closeShiftModal').classList.add('hidden')" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-md transition">
                            Tutup Shift Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
