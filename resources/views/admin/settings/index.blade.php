<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                {{ __('Pengaturan Toko, Pajak & Layanan') }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Atur identitas tempat, tarif PPN, biaya layanan (service charge), dan catatan struk</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
            
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6" x-data="{ enableTax: {{ $settings['enable_tax'] == '1' ? 'true' : 'false' }}, enableService: {{ $settings['enable_service'] == '1' ? 'true' : 'false' }} }">
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

                <!-- SECTION 2: PAJAK PPN & BIAYA LAYANAN (SERVICE CHARGE) -->
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

                <!-- SECTION 3: CATATAN STRUK -->
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
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
