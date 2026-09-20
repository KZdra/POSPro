<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-900 leading-tight flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-sm shadow-md shadow-blue-500/30">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    <span>{{ __('Pengaturan Sistem & Toko') }}</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1 font-medium">Konfigurasi profil usaha, aktifkan/nonaktifkan fitur POS, pajak resto, dan integrasi payment gateway QRIS</p>
            </div>

            @if(session('success'))
                <div class="px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-2 animate-bounce shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" 
        x-data="{ 
            activeTab: 'profile',
            
            // POS Feature Toggles
            enableShifts: {{ $settings['enable_shifts'] == '1' ? 'true' : 'false' }},
            enablePettyCash: {{ $settings['enable_petty_cash'] == '1' ? 'true' : 'false' }},
            enablePoints: {{ $settings['enable_points'] == '1' ? 'true' : 'false' }},
            enableSplitPayment: {{ $settings['enable_split_payment'] == '1' ? 'true' : 'false' }},

            // Tax & Restaurant
            enableTax: {{ $settings['enable_tax'] == '1' ? 'true' : 'false' }}, 
            enableService: {{ $settings['enable_service'] == '1' ? 'true' : 'false' }}, 
            enableOrderTypes: {{ $settings['enable_order_types'] == '1' ? 'true' : 'false' }},
            serviceChargeOnTakeaway: {{ $settings['service_charge_on_takeaway'] == '1' ? 'true' : 'false' }},
            taxOnTakeaway: {{ $settings['tax_on_takeaway'] == '1' ? 'true' : 'false' }},
            enableKitchenReceipt: {{ $settings['enable_kitchen_receipt'] == '1' ? 'true' : 'false' }},

            // Gateway Config
            gatewayUrl: '{{ addslashes($settings['payment_gateway_url']) }}',
            gatewayApiKey: '{{ addslashes($settings['payment_gateway_api_key']) }}',
            backendApiKey: '{{ addslashes($settings['backend_api_key']) }}',
            showGatewayKey: false,
            showBackendKey: false,
            copiedTarget: null,

            generateRandomKey(length = 24, prefix = '') {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let result = prefix;
                for (let i = 0; i < length; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            },

            copyToClipboard(text, target) {
                if (!text) return;
                navigator.clipboard.writeText(text).then(() => {
                    this.copiedTarget = target;
                    setTimeout(() => { this.copiedTarget = null; }, 2000);
                });
            }
        }"
    >
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- MODERN TAB NAVIGATION BAR -->
            <div class="bg-white p-2 rounded-3xl border border-slate-200/90 shadow-sm flex flex-wrap sm:flex-nowrap gap-1.5">
                <!-- Tab 1: Profil Toko -->
                <button 
                    type="button" 
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold'"
                    class="flex-1 py-3 px-3.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer select-none"
                >
                    <i class="fa-solid fa-store" :class="activeTab === 'profile' ? 'text-white' : 'text-blue-500'"></i>
                    <span>Profil & Struk</span>
                </button>

                <!-- Tab 2: Fitur Kasir POS -->
                <button 
                    type="button" 
                    @click="activeTab = 'features'"
                    :class="activeTab === 'features' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold'"
                    class="flex-1 py-3 px-3.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer select-none relative"
                >
                    <i class="fa-solid fa-bolt" :class="activeTab === 'features' ? 'text-white' : 'text-amber-500'"></i>
                    <span>Fitur Operasional POS</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 absolute top-2 right-2 ring-2 ring-white"></span>
                </button>

                <!-- Tab 3: Pajak & Resto -->
                <button 
                    type="button" 
                    @click="activeTab = 'restaurant'"
                    :class="activeTab === 'restaurant' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold'"
                    class="flex-1 py-3 px-3.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer select-none"
                >
                    <i class="fa-solid fa-utensils" :class="activeTab === 'restaurant' ? 'text-white' : 'text-rose-500'"></i>
                    <span>Pajak & Layanan Resto</span>
                </button>

                <!-- Tab 4: QRIS Payment Gateway -->
                <button 
                    type="button" 
                    @click="activeTab = 'gateway'"
                    :class="activeTab === 'gateway' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 font-black' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-bold'"
                    class="flex-1 py-3 px-3.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer select-none"
                >
                    <i class="fa-solid fa-qrcode" :class="activeTab === 'gateway' ? 'text-white' : 'text-emerald-500'"></i>
                    <span>Gateway QRIS</span>
                </button>
            </div>

            <!-- TAB CONTENT CONTAINER -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8">

                <!-- ================================================================= -->
                <!-- TAB 1: PROFIL & CATATAN STRUK TOKO -->
                <!-- ================================================================= -->
                <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-store text-blue-600"></i>
                                <span>Identitas Usaha & Struk Kasir</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Informasi ini dicetak pada kop header dan catatan kaki struk kasir.</p>
                        </div>
                        <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold text-[11px] rounded-xl border border-blue-200/60">
                            Struk Kasir
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Store Name -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Tempat / Cafe / Toko <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="store_name" 
                                value="{{ old('store_name', $settings['store_name']) }}" 
                                required 
                                placeholder="Contoh: Kopi Kenangan / Resto Nusantara" 
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-sm font-black text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                            >
                            <p class="text-[11px] text-slate-400 mt-1 font-medium">Nama ini akan dicetak di header utama struk kasir dan judul sistem POS.</p>
                        </div>

                        <!-- Store Phone -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Nomor Telepon / WhatsApp
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </div>
                                <input 
                                    type="text" 
                                    name="store_phone" 
                                    value="{{ old('store_phone', $settings['store_phone']) }}" 
                                    placeholder="Contoh: 0812-3456-7890" 
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                            </div>
                        </div>

                        <!-- Store Address -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap Toko
                            </label>
                            <textarea 
                                name="store_address" 
                                rows="2" 
                                placeholder="Contoh: Jl. Sudirman No. 123, Blok A, Jakarta Selatan" 
                                class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >{{ old('store_address', $settings['store_address']) }}</textarea>
                        </div>

                        <!-- Receipt Footer -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>Pesan Catatan Kaki Struk (Receipt Footer)</span>
                                <span class="text-[10px] text-slate-400 font-normal">Gunakan baris baru (Enter) untuk multi-baris</span>
                            </label>
                            <textarea 
                                name="receipt_footer" 
                                rows="3" 
                                placeholder="Contoh: Terima Kasih Atas Kunjungan Anda!&#10;Follow Instagram kami @cafepos&#10;Barang yang dibeli tidak dapat ditukar" 
                                class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-mono font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1 font-medium">Pesan di atas akan dicetak pada bagian paling bawah struk kasir.</p>
                        </div>
                    </div>
                </div>

                <!-- ================================================================= -->
                <!-- TAB 2: FITUR KASIR & OPERASIONAL POS (TOGGLES SHIFT, PETTY CASH, DLL) -->
                <!-- ================================================================= -->
                <div x-show="activeTab === 'features'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-bolt text-amber-500"></i>
                            <span>Aktivasi Fitur Operasional POS</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Aktifkan atau nonaktifkan fitur kasir sesuai alur operasional dan kebutuhan bisnis Anda.</p>
                    </div>

                    <div class="space-y-4">

                        <!-- TOGGLE 1: MANAJEMEN SHIFT KASIR & DRAWER -->
                        <div 
                            @click="enableShifts = !enableShifts"
                            class="p-5 rounded-3xl border-2 transition-all duration-200 cursor-pointer select-none"
                            :class="enableShifts ? 'bg-blue-50/40 border-blue-300 shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 transition shadow-sm"
                                        :class="enableShifts ? 'bg-blue-600 text-white shadow-blue-500/30' : 'bg-slate-200 text-slate-500'">
                                        <i class="fa-solid fa-vault"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-slate-900">Manajemen Shift Kasir & Laci Drawer</h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full transition"
                                                :class="enableShifts ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="enableShifts ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                                <span x-text="enableShifts ? 'AKTIF' : 'NONAKTIF'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed">
                                            <strong class="font-bold">Bila Aktif:</strong> Kasir wajib melakukan <span class="underline font-bold text-slate-800">Buka Shift</span> (input modal awal laci) dan <span class="underline font-bold text-slate-800">Tutup Shift</span> (rekonsiliasi fisik uang laci).
                                            <br>
                                            <strong class="font-bold">Bila Dinonaktifkan:</strong> Kasir dapat langsung transaksi bebas tanpa perlu repot membuka/menutup shift kasir.
                                        </p>
                                    </div>
                                </div>

                                <!-- iOS Style Switch Toggle Button -->
                                <div class="shrink-0 flex items-center" @click.stop="enableShifts = !enableShifts">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableShifts ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableShifts ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableShifts ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_shifts" :value="enableShifts ? '1' : '0'">
                                </div>
                            </div>
                        </div>

                        <!-- TOGGLE 2: KAS MASUK / KAS KELUAR (PETTY CASH) -->
                        <div 
                            @click="enablePettyCash = !enablePettyCash"
                            class="p-5 rounded-3xl border-2 transition-all duration-200 cursor-pointer select-none"
                            :class="enablePettyCash ? 'bg-indigo-50/40 border-indigo-300 shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 transition shadow-sm"
                                        :class="enablePettyCash ? 'bg-indigo-600 text-white shadow-indigo-500/30' : 'bg-slate-200 text-slate-500'">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-slate-900">Kas Masuk & Keluar Operasional (Petty Cash)</h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full transition"
                                                :class="enablePettyCash ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="enablePettyCash ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                                <span x-text="enablePettyCash ? 'AKTIF' : 'NONAKTIF'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed">
                                            Memungkinkan kasir mencatat pengeluaran belanja kecil mendadak (misal: beli es batu, gas elpiji, plastik kresek) atau penambahan uang modal kembalian langsung dari laci kasir.
                                        </p>
                                        <template x-if="!enableShifts">
                                            <div class="mt-2 text-[11px] font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5 border border-amber-200">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                <span>Catatan: Tombol Petty Cash otomatis tersembunyi di POS bila Shift dinonaktifkan.</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- iOS Style Switch Toggle Button -->
                                <div class="shrink-0 flex items-center" @click.stop="enablePettyCash = !enablePettyCash">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enablePettyCash ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enablePettyCash ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enablePettyCash ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_petty_cash" :value="enablePettyCash ? '1' : '0'">
                                </div>
                            </div>
                        </div>

                        <!-- TOGGLE 3: POIN MEMBER & LOYALTY -->
                        <div 
                            @click="enablePoints = !enablePoints"
                            class="p-5 rounded-3xl border-2 transition-all duration-200 cursor-pointer select-none"
                            :class="enablePoints ? 'bg-amber-50/40 border-amber-300 shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 transition shadow-sm"
                                        :class="enablePoints ? 'bg-amber-500 text-white shadow-amber-500/30' : 'bg-slate-200 text-slate-500'">
                                        <i class="fa-solid fa-coins"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-slate-900">Program Poin Member & Loyalty</h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full transition"
                                                :class="enablePoints ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="enablePoints ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                                <span x-text="enablePoints ? 'AKTIF' : 'NONAKTIF'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed">
                                            Pelanggan terdaftar mendapatkan reward 1 Poin per kelipatan belanja Rp 10.000. Kasir dapat menukarkan poin member menjadi potongan belanja langsung (1 Poin = Potongan Rp 1.000). Bila dinonaktifkan, opsi tukar poin disembunyikan.
                                        </p>
                                    </div>
                                </div>

                                <!-- iOS Style Switch Toggle Button -->
                                <div class="shrink-0 flex items-center" @click.stop="enablePoints = !enablePoints">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enablePoints ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enablePoints ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enablePoints ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_points" :value="enablePoints ? '1' : '0'">
                                </div>
                            </div>
                        </div>

                        <!-- TOGGLE 4: SPLIT PAYMENT (PISAH PEMBAYARAN) -->
                        <div 
                            @click="enableSplitPayment = !enableSplitPayment"
                            class="p-5 rounded-3xl border-2 transition-all duration-200 cursor-pointer select-none"
                            :class="enableSplitPayment ? 'bg-purple-50/40 border-purple-300 shadow-sm' : 'bg-slate-50 border-slate-200 hover:border-slate-300'"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-start space-x-3.5">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg shrink-0 transition shadow-sm"
                                        :class="enableSplitPayment ? 'bg-purple-600 text-white shadow-purple-500/30' : 'bg-slate-200 text-slate-500'">
                                        <i class="fa-solid fa-code-branch"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-black text-slate-900">Metode Split Payment (Pisah Pembayaran)</h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full transition"
                                                :class="enableSplitPayment ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="enableSplitPayment ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>
                                                <span x-text="enableSplitPayment ? 'AKTIF' : 'NONAKTIF'"></span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed">
                                            Memungkinkan kasir menerima 2 metode bayar berbeda untuk 1 transaksi (misalnya: pelanggan membayar sebagian tunai Rp 50.000, sisanya bayar via QRIS / Transfer / Debit EDC).
                                        </p>
                                    </div>
                                </div>

                                <!-- iOS Style Switch Toggle Button -->
                                <div class="shrink-0 flex items-center" @click.stop="enableSplitPayment = !enableSplitPayment">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableSplitPayment ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableSplitPayment ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableSplitPayment ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_split_payment" :value="enableSplitPayment ? '1' : '0'">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================================================================= -->
                <!-- TAB 3: PAJAK PPN, SERVICE CHARGE, TIPE PESANAN RESTO & STRUK DAPUR -->
                <!-- ================================================================= -->
                <div x-show="activeTab === 'restaurant'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-utensils text-rose-500"></i>
                            <span>Pajak Restoran, Layanan & Tiket Dapur</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Atur persentase pajak daerah/PPN, biaya servis, perilaku pesanan take away, dan struk dapur.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- PAJAK PPN RESTO (PB1) -->
                        <div class="p-5 bg-slate-50/70 border border-slate-200 rounded-3xl space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-black">
                                        <i class="fa-solid fa-percent"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900">Pajak Pertambahan Nilai (PPN / PB1)</h4>
                                        <span class="text-[10px] text-slate-500 font-semibold" x-text="enableTax ? 'Dikenakan ke struk transaksi' : 'Tanpa PPN (Rp 0)'"></span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center" @click.stop="enableTax = !enableTax">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableTax ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableTax ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableTax ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_tax" :value="enableTax ? '1' : '0'">
                                </div>
                            </div>

                            <div x-show="enableTax" x-transition class="pt-2 border-t border-slate-200">
                                <label class="block text-[11px] font-black text-slate-700 uppercase mb-1">Tarif Pajak PPN (%)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="tax_rate" 
                                        value="{{ old('tax_rate', $settings['tax_rate']) }}" 
                                        step="0.1" 
                                        min="0" 
                                        max="100" 
                                        placeholder="11" 
                                        class="w-full px-4 py-2.5 pr-10 bg-white border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 font-black text-xs">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- BIAYA LAYANAN (SERVICE CHARGE) -->
                        <div class="p-5 bg-slate-50/70 border border-slate-200 rounded-3xl space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-black">
                                        <i class="fa-solid fa-bell-concierge"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900">Biaya Layanan (Service Charge)</h4>
                                        <span class="text-[10px] text-slate-500 font-semibold" x-text="enableService ? 'Dikenakan ke struk transaksi' : 'Tanpa service charge (Rp 0)'"></span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center" @click.stop="enableService = !enableService">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableService ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableService ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableService ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_service" :value="enableService ? '1' : '0'">
                                </div>
                            </div>

                            <div x-show="enableService" x-transition class="pt-2 border-t border-slate-200">
                                <label class="block text-[11px] font-black text-slate-700 uppercase mb-1">Tarif Service Charge (%)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="service_rate" 
                                        value="{{ old('service_rate', $settings['service_rate']) }}" 
                                        step="0.1" 
                                        min="0" 
                                        max="100" 
                                        placeholder="5" 
                                        class="w-full px-4 py-2.5 pr-10 bg-white border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 font-black text-xs">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- TIPE PESANAN (DINE IN / TAKE AWAY) -->
                        <div class="md:col-span-2 p-5 bg-slate-50/70 border border-slate-200 rounded-3xl space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-black">
                                        <i class="fa-solid fa-chair"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900">Pilihan Dine In / Take Away di Layar Kasir</h4>
                                        <span class="text-[11px] text-slate-500 font-semibold" x-text="enableOrderTypes ? 'Aktif: Kasir dapat memilih Makan di Tempat atau Bawa Pulang' : 'Nonaktif: Mode Retail Sederhana (Tanpa pilihan tipe pesanan)'"></span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center" @click.stop="enableOrderTypes = !enableOrderTypes">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableOrderTypes ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableOrderTypes ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableOrderTypes ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_order_types" :value="enableOrderTypes ? '1' : '0'">
                                </div>
                            </div>

                            <!-- Sub-rules Take Away -->
                            <div x-show="enableOrderTypes" x-transition class="pt-3 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="p-3.5 bg-white rounded-2xl border border-slate-200 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-black text-slate-800 block">Service Charge pada Take Away</span>
                                        <span class="text-[10px] text-slate-500 font-semibold" x-text="serviceChargeOnTakeaway ? 'Tetap Dikenakan' : 'Bebas Service Charge (Rp 0)'"></span>
                                    </div>
                                    <div class="shrink-0 flex items-center" @click.stop="serviceChargeOnTakeaway = !serviceChargeOnTakeaway">
                                        <div 
                                            class="w-12 h-7 rounded-full p-0.5 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                            :class="serviceChargeOnTakeaway ? 'bg-emerald-500' : 'bg-slate-300'"
                                        >
                                            <div 
                                                class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                                :class="serviceChargeOnTakeaway ? 'translate-x-5' : 'translate-x-0'"
                                            >
                                                <i class="fa-solid text-[8px] font-black" :class="serviceChargeOnTakeaway ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                            </div>
                                        </div>
                                        <input type="hidden" name="service_charge_on_takeaway" :value="serviceChargeOnTakeaway ? '1' : '0'">
                                    </div>
                                </div>

                                <div class="p-3.5 bg-white rounded-2xl border border-slate-200 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-black text-slate-800 block">Pajak PPN pada Take Away</span>
                                        <span class="text-[10px] text-slate-500 font-semibold" x-text="taxOnTakeaway ? 'Tetap Dikenakan PPN' : 'Bebas Pajak (Rp 0)'"></span>
                                    </div>
                                    <div class="shrink-0 flex items-center" @click.stop="taxOnTakeaway = !taxOnTakeaway">
                                        <div 
                                            class="w-12 h-7 rounded-full p-0.5 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                            :class="taxOnTakeaway ? 'bg-emerald-500' : 'bg-slate-300'"
                                        >
                                            <div 
                                                class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                                :class="taxOnTakeaway ? 'translate-x-5' : 'translate-x-0'"
                                            >
                                                <i class="fa-solid text-[8px] font-black" :class="taxOnTakeaway ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                            </div>
                                        </div>
                                        <input type="hidden" name="tax_on_takeaway" :value="taxOnTakeaway ? '1' : '0'">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CETAK STRUK DAPUR / BAR (KOT) -->
                        <div class="md:col-span-2 p-5 bg-amber-50/50 border border-amber-200/80 rounded-3xl space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center text-xs font-black">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900">Tombol Cetak Struk Dapur / KOT (Kitchen Order Ticket)</h4>
                                        <span class="text-[11px] text-slate-600 font-semibold" x-text="enableKitchenReceipt ? 'Aktif: Tombol Cetak Struk Dapur muncul di modal sukses pembayaran kasir & riwayat' : 'Nonaktif: Hanya mencetak struk kasir reguler'"></span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center" @click.stop="enableKitchenReceipt = !enableKitchenReceipt">
                                    <div 
                                        class="w-14 h-8 rounded-full p-1 transition-colors duration-200 ease-in-out cursor-pointer shadow-inner flex items-center"
                                        :class="enableKitchenReceipt ? 'bg-emerald-500' : 'bg-slate-300'"
                                    >
                                        <div 
                                            class="w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out flex items-center justify-center"
                                            :class="enableKitchenReceipt ? 'translate-x-6' : 'translate-x-0'"
                                        >
                                            <i class="fa-solid text-[9px] font-black" :class="enableKitchenReceipt ? 'fa-check text-emerald-600' : 'fa-xmark text-slate-400'"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="enable_kitchen_receipt" :value="enableKitchenReceipt ? '1' : '0'">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================================================================= -->
                <!-- TAB 4: INTEGRASI MINI PAYMENT GATEWAY QRIS -->
                <!-- ================================================================= -->
                <div x-show="activeTab === 'gateway'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-qrcode text-emerald-600"></i>
                                <span>Konfigurasi Payment Gateway QRIS</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">Integrasi REST API microservice QRIS dinamis dan verifikasi keamanan webhook callback.</p>
                        </div>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-black text-[11px] rounded-xl border border-emerald-200">
                            Microservice QRIS
                        </span>
                    </div>

                    <div class="space-y-4">
                        <!-- Gateway URL -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase mb-1.5">Payment Gateway Endpoint URL <span class="text-rose-500">*</span></label>
                            <div class="space-y-2">
                                <input 
                                    type="url" 
                                    name="payment_gateway_url" 
                                    x-model="gatewayUrl" 
                                    required
                                    placeholder="http://localhost:3000/api/v1/qris/generate" 
                                    class="w-full px-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-xs font-mono font-bold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                                <!-- Quick Presets -->
                                <div class="flex items-center space-x-2 text-[11px]">
                                    <span class="text-slate-400 font-bold">Preset Cepat:</span>
                                    <button type="button" @click="gatewayUrl = 'http://localhost:3000/api/v1/qris/generate'" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-100 text-slate-700 hover:text-blue-700 font-mono font-bold rounded-lg transition cursor-pointer">
                                        Localhost (Port 3000)
                                    </button>
                                    <button type="button" @click="gatewayUrl = 'http://localhost:5000/api/v1/qris/generate'" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-100 text-slate-700 hover:text-blue-700 font-mono font-bold rounded-lg transition cursor-pointer">
                                        Localhost (Port 5000)
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Keys in 2 columns -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                            <!-- Gateway API Key -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black text-slate-700 uppercase">Gateway API Key <span class="text-slate-400 font-normal lowercase">(x-api-key)</span></label>
                                    <button 
                                        type="button" 
                                        @click="gatewayApiKey = generateRandomKey(24, 'gw_key_')" 
                                        class="text-[10px] font-black text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 cursor-pointer"
                                    >
                                        <i class="fa-solid fa-arrows-rotate text-[9px]"></i>
                                        <span>Generate Key</span>
                                    </button>
                                </div>

                                <div class="relative flex items-center">
                                    <input 
                                        :type="showGatewayKey ? 'text' : 'password'" 
                                        name="payment_gateway_api_key" 
                                        x-model="gatewayApiKey" 
                                        placeholder="Contoh: secret_key_hp_123" 
                                        class="w-full pl-3.5 pr-20 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-blue-500"
                                    >
                                    <div class="absolute right-1.5 flex items-center space-x-1">
                                        <button type="button" @click="showGatewayKey = !showGatewayKey" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                                            <i class="fa-solid" :class="showGatewayKey ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                        <button type="button" @click="copyToClipboard(gatewayApiKey, 'gatewayKey')" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg cursor-pointer" title="Salin Key">
                                            <i class="fa-solid" :class="copiedTarget === 'gatewayKey' ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium">Header autentikasi yang dikirimkan saat POS merequest pembuatan QRIS baru.</p>
                            </div>

                            <!-- Backend Webhook Secret Key -->
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-black text-slate-700 uppercase">Backend Webhook Secret <span class="text-slate-400 font-normal lowercase">(x-api-key)</span></label>
                                    <button 
                                        type="button" 
                                        @click="backendApiKey = generateRandomKey(24, 'wh_sec_')" 
                                        class="text-[10px] font-black text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 cursor-pointer"
                                    >
                                        <i class="fa-solid fa-arrows-rotate text-[9px]"></i>
                                        <span>Generate Secret</span>
                                    </button>
                                </div>

                                <div class="relative flex items-center">
                                    <input 
                                        :type="showBackendKey ? 'text' : 'password'" 
                                        name="backend_api_key" 
                                        x-model="backendApiKey" 
                                        placeholder="Contoh: secret_backend_123" 
                                        class="w-full pl-3.5 pr-20 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-blue-500"
                                    >
                                    <div class="absolute right-1.5 flex items-center space-x-1">
                                        <button type="button" @click="showBackendKey = !showBackendKey" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg cursor-pointer">
                                            <i class="fa-solid" :class="showBackendKey ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                        <button type="button" @click="copyToClipboard(backendApiKey, 'backendKey')" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg cursor-pointer" title="Salin Secret">
                                            <i class="fa-solid" :class="copiedTarget === 'backendKey' ? 'fa-check text-emerald-600' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium">Kunci rahasia untuk memverifikasi callback webhook pelunasan dari Gateway.</p>
                            </div>
                        </div>

                        <!-- Webhook Callback URL Box -->
                        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="space-y-0.5">
                                <span class="text-[10px] font-black text-emerald-900 uppercase tracking-wider block">URL Webhook Callback (Daftarkan di Server Gateway Anda):</span>
                                <code class="text-xs font-mono font-black text-emerald-800 break-all select-all">{{ url('/callbacks/payment') }}</code>
                            </div>
                            <button 
                                type="button" 
                                @click="copyToClipboard('{{ url('/callbacks/payment') }}', 'webhookUrl')"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer"
                            >
                                <i class="fa-solid" :class="copiedTarget === 'webhookUrl' ? 'fa-check' : 'fa-copy'"></i>
                                <span x-text="copiedTarget === 'webhookUrl' ? 'Tersalin!' : 'Salin URL'"></span>
                            </button>
                        </div>

                        <!-- QRIS Expiry -->
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase mb-1">Durasi Kedaluwarsa QRIS (Expires in Minutes) <span class="text-rose-500">*</span></label>
                            <div class="relative max-w-xs">
                                <input 
                                    type="number" 
                                    name="qris_expires_minutes" 
                                    value="{{ old('qris_expires_minutes', $settings['qris_expires_minutes']) }}" 
                                    min="1" 
                                    max="1440" 
                                    required
                                    class="w-full px-4 py-2.5 pr-16 bg-slate-50 border-2 border-slate-200 rounded-2xl text-sm font-black text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                >
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 font-bold text-xs">Menit</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1 font-medium">Batas waktu pelanggan menyelesaikan pembayaran QRIS sebelum kedaluwarsa (rekomendasi: 10 - 15 menit).</p>
                        </div>
                    </div>
                </div>

                <!-- STICKY BOTTOM SAVE ACTION BAR -->
                <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-100">
                    <div class="text-xs text-slate-400 font-medium hidden sm:block">
                        Pastikan data pengaturan toko dan toggle fitur sudah sesuai kebutuhan.
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase tracking-wider rounded-2xl shadow-lg shadow-blue-600/30 transition active:scale-95 flex items-center justify-center space-x-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-sm"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
