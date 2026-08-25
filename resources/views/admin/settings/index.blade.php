<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                {{ __('Pengaturan Toko, Pajak & Layanan') }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur identitas tempat, tarif PPN, biaya layanan, durasi expired QRIS, dan catatan struk</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
            
            <form 
                action="{{ route('admin.settings.update') }}" 
                method="POST" 
                class="space-y-6" 
                x-data="{ 
                    enableTax: {{ $settings['enable_tax'] == '1' ? 'true' : 'false' }}, 
                    enableService: {{ $settings['enable_service'] == '1' ? 'true' : 'false' }}, 
                    enableOrderTypes: {{ $settings['enable_order_types'] == '1' ? 'true' : 'false' }},
                    serviceChargeOnTakeaway: {{ $settings['service_charge_on_takeaway'] == '1' ? 'true' : 'false' }},
                    taxOnTakeaway: {{ $settings['tax_on_takeaway'] == '1' ? 'true' : 'false' }},
                    enableKitchenReceipt: {{ $settings['enable_kitchen_receipt'] == '1' ? 'true' : 'false' }},
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
                @csrf

                <!-- SECTION 1: INFORMASI TOKO -->
                <div class="space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                        <i class="fa-solid fa-store text-blue-600"></i>
                        <span>Informasi Identitas Usaha</span>
                    </h3>

                    <!-- Store Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Tempat / Cafe / Toko *</label>
                        <input 
                            type="text" 
                            name="store_name" 
                            value="{{ old('store_name', $settings['store_name']) }}" 
                            required 
                            placeholder="Contoh: Kopi Kenangan / Resto Nusantara" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Nama ini akan dicetak di header struk kasir dan judul aplikasi.</p>
                    </div>

                    <!-- Store Address -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Lengkap Toko</label>
                        <textarea 
                            name="store_address" 
                            rows="2" 
                            placeholder="Contoh: Jl. Sudirman No. 123, Blok A, Jakarta Selatan" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >{{ old('store_address', $settings['store_address']) }}</textarea>
                    </div>

                    <!-- Store Phone -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Telepon / WhatsApp</label>
                        <input 
                            type="text" 
                            name="store_phone" 
                            value="{{ old('store_phone', $settings['store_phone']) }}" 
                            placeholder="Contoh: 0812-3456-7890" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                    </div>
                </div>

                <!-- SECTION 2: PAJAK PPN & BIAYA LAYANAN -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                        <i class="fa-solid fa-percent text-blue-600"></i>
                        <span>Pajak (PPN) & Biaya Layanan (Service Charge)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- PPN Tax Box -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-extrabold text-slate-900 block">Pajak Pertambahan Nilai (PPN)</span>
                                    <span class="text-[11px] text-slate-400" x-text="enableTax ? 'Pajak PPN Aktif pada Transaksi' : 'Pajak PPN Dinonaktifkan'"></span>
                                </div>
                                <div class="flex items-center">
                                    <button 
                                        type="button" 
                                        @click="enableTax = !enableTax" 
                                        :class="enableTax ? 'bg-blue-600' : 'bg-slate-300'" 
                                        class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                    >
                                        <span 
                                            :class="enableTax ? 'translate-x-5' : 'translate-x-0'" 
                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                    <input type="hidden" name="enable_tax" :value="enableTax ? '1' : '0'">
                                </div>
                            </div>

                            <!-- Tax Rate Input -->
                            <div x-show="enableTax" x-transition class="pt-2 border-t border-slate-200/60">
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Persentase Tarif PPN (%)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        name="tax_rate" 
                                        value="{{ old('tax_rate', $settings['tax_rate']) }}" 
                                        class="w-full px-4 py-2 pr-8 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500"
                                        placeholder="11"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 font-bold text-xs">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Service Charge Box -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-extrabold text-slate-900 block">Biaya Layanan (Service Charge)</span>
                                    <span class="text-[11px] text-slate-400" x-text="enableService ? 'Biaya Layanan Aktif' : 'Biaya Layanan Dinonaktifkan'"></span>
                                </div>
                                <div class="flex items-center">
                                    <button 
                                        type="button" 
                                        @click="enableService = !enableService" 
                                        :class="enableService ? 'bg-blue-600' : 'bg-slate-300'" 
                                        class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                    >
                                        <span 
                                            :class="enableService ? 'translate-x-5' : 'translate-x-0'" 
                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                    <input type="hidden" name="enable_service" :value="enableService ? '1' : '0'">
                                </div>
                            </div>

                            <!-- Service Rate Input -->
                            <div x-show="enableService" x-transition class="pt-2 border-t border-slate-200/60">
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Persentase Service Charge (%)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        name="service_rate" 
                                        value="{{ old('service_rate', $settings['service_rate']) }}" 
                                        class="w-full px-4 py-2 pr-8 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500"
                                        placeholder="5"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 font-bold text-xs">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: TIPE PESANAN (DINE IN VS TAKE AWAY) -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-utensils text-blue-600"></i>
                            <span>Pilihan Tipe Pesanan (Dine In vs Take Away)</span>
                        </h3>
                        <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-extrabold text-[10px] rounded-full">
                            F&B / Warung Mode
                        </span>
                    </div>

                    <!-- Master Order Types Toggle -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-extrabold text-slate-900 block">Pilihan Dine In / Take Away di Layar Kasir</span>
                                <span class="text-[11px] text-slate-400" x-text="enableOrderTypes ? 'Aktif: Kasir dapat memilih Dine In (Makan di Tempat) atau Take Away (Bawa Pulang)' : 'Nonaktif: Mode Warung/Retail Murni (Tanpa pilihan Dine In / Take Away)'"></span>
                            </div>
                            <div class="flex items-center">
                                <button 
                                    type="button" 
                                    @click="enableOrderTypes = !enableOrderTypes" 
                                    :class="enableOrderTypes ? 'bg-blue-600' : 'bg-slate-300'" 
                                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                >
                                    <span 
                                        :class="enableOrderTypes ? 'translate-x-5' : 'translate-x-0'" 
                                        class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <input type="hidden" name="enable_order_types" :value="enableOrderTypes ? '1' : '0'">
                            </div>
                        </div>

                        <!-- Sub-rules for Take Away (Service Charge & Tax) -->
                        <div x-show="enableOrderTypes" x-transition class="pt-3 border-t border-slate-200/60 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- Service Charge on Takeaway -->
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-extrabold text-slate-800 block">Biaya Layanan pada Take Away</span>
                                    <span class="text-[10px] text-slate-400 block" x-text="serviceChargeOnTakeaway ? 'Take Away TETAP dikenakan Service Charge' : 'Take Away BEBAS Biaya Layanan (Rp 0)'"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="serviceChargeOnTakeaway = !serviceChargeOnTakeaway" 
                                    :class="serviceChargeOnTakeaway ? 'bg-blue-600' : 'bg-slate-300'" 
                                    class="relative inline-flex h-6 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                >
                                    <span 
                                        :class="serviceChargeOnTakeaway ? 'translate-x-4' : 'translate-x-0'" 
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <input type="hidden" name="service_charge_on_takeaway" :value="serviceChargeOnTakeaway ? '1' : '0'">
                            </div>

                            <!-- Tax on Takeaway -->
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="text-[11px] font-extrabold text-slate-800 block">Pajak PPN pada Take Away</span>
                                    <span class="text-[10px] text-slate-400 block" x-text="taxOnTakeaway ? 'Take Away TETAP dikenakan PPN' : 'Take Away BEBAS Pajak PPN (Rp 0)'"></span>
                                </div>
                                <button 
                                    type="button" 
                                    @click="taxOnTakeaway = !taxOnTakeaway" 
                                    :class="taxOnTakeaway ? 'bg-blue-600' : 'bg-slate-300'" 
                                    class="relative inline-flex h-6 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                >
                                    <span 
                                        :class="taxOnTakeaway ? 'translate-x-4' : 'translate-x-0'" 
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <input type="hidden" name="tax_on_takeaway" :value="taxOnTakeaway ? '1' : '0'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: STRUK DAPUR / BAR (KITCHEN ORDER TICKET) -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                        <i class="fa-solid fa-utensils text-amber-600"></i>
                        <span>Pengaturan Struk Dapur / Bar (Kitchen Order Ticket)</span>
                    </h3>

                    <div class="p-4 bg-amber-50/50 rounded-2xl border border-amber-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-extrabold text-slate-900 block">Tombol & Fitur Cetak Struk Dapur</span>
                                <span class="text-[11px] text-slate-500" x-text="enableKitchenReceipt ? 'Aktif: Tombol Cetak Struk Dapur muncul setelah kasir menyelesaikan transaksi' : 'Nonaktif: Hanya mencetak struk kasir reguler'"></span>
                            </div>
                            <div class="flex items-center">
                                <button 
                                    type="button" 
                                    @click="enableKitchenReceipt = !enableKitchenReceipt" 
                                    :class="enableKitchenReceipt ? 'bg-amber-600' : 'bg-slate-300'" 
                                    class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner"
                                >
                                    <span 
                                        :class="enableKitchenReceipt ? 'translate-x-5' : 'translate-x-0'" 
                                        class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                                <input type="hidden" name="enable_kitchen_receipt" :value="enableKitchenReceipt ? '1' : '0'">
                            </div>
                        </div>
                <!-- SECTION 4: QRIS PAYMENT GATEWAY INTEGRATION -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-qrcode text-emerald-600"></i>
                            <span>Integrasi Mini Payment Gateway QRIS</span>
                        </h3>
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold text-[10px] rounded-full">
                            API & Webhook
                        </span>
                    </div>

                    <!-- Gateway URL -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Payment Gateway Endpoint URL *</label>
                        <div class="space-y-1.5">
                            <input 
                                type="url" 
                                name="payment_gateway_url" 
                                x-model="gatewayUrl" 
                                required
                                placeholder="http://localhost:3000/api/v1/qris/generate" 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                            <!-- Quick Presets -->
                            <div class="flex items-center space-x-2 text-[10px]">
                                <span class="text-slate-400 font-bold">Preset Cepat:</span>
                                <button type="button" @click="gatewayUrl = 'http://localhost:3000/api/v1/qris/generate'" class="px-2 py-0.5 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 font-mono rounded-md transition cursor-pointer">
                                    Local (Port 3000)
                                </button>
                                <button type="button" @click="gatewayUrl = 'http://localhost:5000/api/v1/qris/generate'" class="px-2 py-0.5 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 font-mono rounded-md transition cursor-pointer">
                                    Local (Port 5000)
                                </button>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Endpoint REST API Node.js yang memproses pembuatan QRIS dinamis.</p>
                    </div>

                    <!-- Gateway API Key & Backend Secret in 2 cols -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Gateway API Key -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 uppercase">Gateway API Key <span class="text-slate-400 font-normal lowercase">(x-api-key)</span></label>
                                <button 
                                    type="button" 
                                    @click="gatewayApiKey = generateRandomKey(24, 'gw_key_')" 
                                    class="text-[10px] font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 cursor-pointer"
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
                            <p class="text-[10px] text-slate-400">Kunci API yang dikirim ke Node.js Gateway saat membuat QRIS baru.</p>
                        </div>

                        <!-- Backend Webhook Secret Key -->
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-700 uppercase">Backend Webhook Secret <span class="text-slate-400 font-normal lowercase">(x-api-key)</span></label>
                                <button 
                                    type="button" 
                                    @click="backendApiKey = generateRandomKey(24, 'wh_sec_')" 
                                    class="text-[10px] font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 cursor-pointer"
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
                            <p class="text-[10px] text-slate-400">Kunci rahasia untuk memverifikasi request callback dari Payment Gateway.</p>
                        </div>
                    </div>

                    <!-- Webhook Callback URL Box -->
                    <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="space-y-0.5">
                            <span class="text-[10px] font-black text-blue-900 uppercase tracking-wider block">URL Webhook Callback (Untuk Gateway Anda):</span>
                            <code class="text-xs font-mono font-black text-blue-700 break-all select-all">{{ url('/callbacks/payment') }}</code>
                        </div>
                        <button 
                            type="button" 
                            @click="copyToClipboard('{{ url('/callbacks/payment') }}', 'webhookUrl')"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-[11px] rounded-xl shadow-sm transition flex items-center space-x-1 self-start sm:self-auto cursor-pointer"
                        >
                            <i class="fa-solid" :class="copiedTarget === 'webhookUrl' ? 'fa-check' : 'fa-copy'"></i>
                            <span x-text="copiedTarget === 'webhookUrl' ? 'Tersalin!' : 'Salin URL'"></span>
                        </button>
                    </div>

                    <!-- QRIS Expiry -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Durasi Kedaluwarsa QRIS (Expires in Minutes) *</label>
                        <div class="relative max-w-xs">
                            <input 
                                type="number" 
                                name="qris_expires_minutes" 
                                value="{{ old('qris_expires_minutes', $settings['qris_expires_minutes']) }}" 
                                min="1" 
                                max="1440" 
                                required
                                class="w-full px-4 py-2.5 pr-16 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            >
                            <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 font-bold text-xs">Menit</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Nilai dikirimkan dalam payload <code>expires_in_minutes</code> saat membuat QRIS baru (rekomendasi: 10 - 30 menit).</p>
                    </div>
                </div>

                <!-- SECTION 5: CATATAN STRUK -->
                <div class="space-y-4 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-2 flex items-center space-x-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i>
                        <span>Pesan Catatan Kaki Struk (Receipt Footer)</span>
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kaki Struk</label>
                        <textarea 
                            name="receipt_footer" 
                            rows="2" 
                            placeholder="Contoh: Terima Kasih Atas Kunjungan Anda!\nFollow Instagram kami @cafepos" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                    </div>
                </div>

                <!-- Save Action -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
