<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Manajemen Kupon & Voucher Promo') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Buat kupon diskon global atau khusus kategori (Kopi, Makanan, dll) yang dapat diketik atau discan QR di kasir</p>
            </div>

            <button 
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-coupon-modal'))"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2 self-start sm:self-auto active:scale-95 cursor-pointer"
            >
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Kupon Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" x-data="couponManager()" @open-coupon-modal.window="openNewCouponModal()">
        
        <!-- Flash Alert -->
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

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-300 rounded-2xl text-red-800 text-xs font-extrabold space-y-1">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
                    <span>Terdapat kesalahan pada input kupon:</span>
                </div>
                <ul class="list-disc list-inside text-red-700 pl-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Coupons Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3">Kode Kupon</th>
                            <th class="py-3 px-3">Judul Promo</th>
                            <th class="py-3 px-3">Berlaku Untuk</th>
                            <th class="py-3 px-3">Nilai Diskon</th>
                            <th class="py-3 px-3">Ketentuan</th>
                            <th class="py-3 px-3">Penggunaan</th>
                            <th class="py-3 px-3">Masa Berlaku</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-slate-50 transition">
                                <!-- Code & QR -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 font-mono font-black text-xs rounded-xl">
                                            {{ $coupon->code }}
                                        </span>
                                        <button 
                                            type="button" 
                                            @click="showQrModal('{{ $coupon->code }}', '{{ $coupon->title }}', '{{ $coupon->discount_type === 'PERCENT' ? floatval($coupon->discount_value) . '%' : 'Rp ' . number_format($coupon->discount_value, 0, ',', '.') }}')" 
                                            class="p-1.5 bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 rounded-lg transition cursor-pointer"
                                            title="Lihat / Cetak QR Code"
                                        >
                                            <i class="fa-solid fa-qrcode text-sm"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Title -->
                                <td class="py-3 px-3 font-bold text-slate-800">
                                    {{ $coupon->title }}
                                </td>

                                <!-- Target Category -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($coupon->category)
                                        <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 font-bold text-[10px] rounded-lg inline-flex items-center space-x-1">
                                            <i class="fa-solid fa-filter text-[9px]"></i>
                                            <span>{{ $coupon->category->name }}</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-[10px] rounded-lg">
                                            Semua Menu
                                        </span>
                                    @endif
                                </td>

                                <!-- Discount Value -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if($coupon->discount_type === 'PERCENT')
                                        <span class="font-extrabold text-red-600 text-sm">
                                            Diskon {{ floatval($coupon->discount_value) }}%
                                        </span>
                                        @if($coupon->max_discount_amount)
                                            <span class="block text-[10px] text-slate-400">Maks. Rp {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}</span>
                                        @endif
                                    @else
                                        <span class="font-extrabold text-emerald-600 text-sm">
                                            Potongan Rp {{ number_format($coupon->discount_value, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Min Order -->
                                <td class="py-3 px-3 whitespace-nowrap text-slate-600">
                                    @if($coupon->min_order_amount > 0)
                                        <span>Min. Rp {{ number_format($coupon->min_order_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-400">Tanpa Min. Belanja</span>
                                    @endif
                                </td>

                                <!-- Usage stats -->
                                <td class="py-3 px-3 whitespace-nowrap text-slate-600">
                                    <span class="font-bold text-slate-800">{{ $coupon->used_count }}</span>
                                    @if($coupon->usage_limit)
                                        <span class="text-slate-400">/ {{ $coupon->usage_limit }} kuota</span>
                                    @else
                                        <span class="text-slate-400">/ &infin; kuota</span>
                                    @endif
                                </td>

                                <!-- Expiry Date -->
                                <td class="py-3 px-3 whitespace-nowrap text-slate-600">
                                    @if($coupon->expires_at)
                                        <span class="{{ $coupon->expires_at->isPast() ? 'text-red-500 font-bold' : '' }}">
                                            {{ $coupon->expires_at->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">Selamanya</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-3 whitespace-nowrap">
                                    @if(!$coupon->is_active)
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-500 font-bold rounded-lg text-[10px]">Nonaktif</span>
                                    @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                        <span class="px-2.5 py-1 bg-red-100 text-red-700 font-bold rounded-lg text-[10px]">Kedaluwarsa</span>
                                    @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-[10px]">Habis Kuota</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-[10px]">Aktif</span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3 text-right whitespace-nowrap space-x-1">
                                    <button 
                                        type="button" 
                                        @click="editCoupon({{ json_encode($coupon) }})" 
                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition cursor-pointer"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon {{ $coupon->code }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-ticket-simple text-4xl mb-2 text-slate-300"></i>
                                    <p class="font-bold text-sm text-slate-600">Belum ada kupon promo</p>
                                    <p class="text-xs text-slate-400 mb-3">Klik tombol di bawah untuk membuat promo diskon pertama Anda.</p>
                                    <button 
                                        type="button" 
                                        onclick="window.dispatchEvent(new CustomEvent('open-coupon-modal'))" 
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md cursor-pointer"
                                    >
                                        + Buat Kupon Baru
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL 1: FORM CREATE / EDIT COUPON -->
        <div 
            x-show="openFormModal" 
            x-cloak 
            x-transition 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div @click.outside="openFormModal = false" class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden text-left">
                <!-- Header -->
                <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-ticket-simple"></i>
                        </div>
                        <h3 class="font-extrabold text-sm" x-text="isEdit ? 'Edit Kupon Promo' : 'Tambah Kupon Promo Baru'"></h3>
                    </div>
                    <button @click="openFormModal = false" class="text-slate-400 hover:text-white text-lg cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Form -->
                <form :action="formUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Code & Title -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Kupon *</label>
                            <input 
                                type="text" 
                                name="code" 
                                x-model="formData.code" 
                                required 
                                placeholder="Contoh: HEMAT10" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black uppercase text-blue-600 focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul / Keterangan *</label>
                            <input 
                                type="text" 
                                name="title" 
                                x-model="formData.title" 
                                required 
                                placeholder="Contoh: Promo Diskon Kopi" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Target Category -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Berlaku untuk Kategori</label>
                        <select name="category_id" x-model="formData.category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kategori (Global)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Pilih kategori jika kupon ini hanya berlaku untuk menu tertentu (misal: hanya kopi atau hanya makanan).</p>
                    </div>

                    <!-- Discount Type & Value -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Diskon *</label>
                            <select name="discount_type" x-model="formData.discount_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-blue-500">
                                <option value="PERCENT">Persentase (%)</option>
                                <option value="FIXED">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1" x-text="formData.discount_type === 'PERCENT' ? 'Nilai Diskon (%) *' : 'Nilai Potongan (Rp) *'"></label>
                            <input 
                                type="number" 
                                step="any" 
                                name="discount_value" 
                                x-model="formData.discount_value" 
                                required 
                                :placeholder="formData.discount_type === 'PERCENT' ? '10' : '15000'" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-extrabold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Min Order & Max Discount (For Percent) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Min. Belanja (Rp)</label>
                            <input 
                                type="number" 
                                name="min_order_amount" 
                                x-model="formData.min_order_amount" 
                                placeholder="0 (Tanpa Minimal)" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <div x-show="formData.discount_type === 'PERCENT'">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Maks. Potongan (Rp)</label>
                            <input 
                                type="number" 
                                name="max_discount_amount" 
                                x-model="formData.max_discount_amount" 
                                placeholder="Kosongkan jika tanpa batas" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Usage Limit & Expiry Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Batas Kuota Penggunaan</label>
                            <input 
                                type="number" 
                                name="usage_limit" 
                                x-model="formData.usage_limit" 
                                placeholder="Kosongkan jika unlimited" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Kedaluwarsa</label>
                            <input 
                                type="date" 
                                name="expires_at" 
                                x-model="formData.expires_at" 
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Active Switch -->
                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" name="is_active" id="isActiveCheck" value="1" x-model="formData.is_active" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <label for="isActiveCheck" class="text-xs font-bold text-slate-700 cursor-pointer">Kupon Status Aktif (Bisa digunakan di Kasir)</label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="openFormModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 cursor-pointer">
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Buat Kupon'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: QR CODE VIEWER & PRINTER -->
        <div 
            x-show="openQrModal" 
            x-cloak 
            x-transition 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div @click.outside="openQrModal = false" class="bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-slate-200 p-6 text-center space-y-4">
                <div class="space-y-1">
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 font-extrabold text-[10px] uppercase tracking-wider rounded-full border border-blue-200">
                        QR Scan Voucher
                    </span>
                    <h3 class="text-lg font-black text-slate-900 pt-1" x-text="qrData.code"></h3>
                    <p class="text-xs text-slate-500" x-text="qrData.title + ' (' + qrData.discount + ')'"></p>
                </div>

                <!-- QR Image Box -->
                <div class="p-4 bg-white border-2 border-slate-200 rounded-2xl inline-block shadow-inner">
                    <img :src="qrImageUrl" alt="Coupon QR" class="w-48 h-48 mx-auto object-contain">
                </div>

                <p class="text-[11px] text-slate-400">
                    Arahkan barcode scanner kasir atau kamera ke QR code ini untuk menerapkan diskon secara instan di POS.
                </p>

                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="openQrModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" @click="window.open(qrImageUrl, '_blank')" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition cursor-pointer">
                        Buka / Simpan Gambar
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine Manager Script -->
    <script>
        function couponManager() {
            return {
                openFormModal: false,
                openQrModal: false,
                isEdit: false,
                formUrl: "{{ route('coupons.store') }}",
                formData: {
                    id: null,
                    code: '',
                    title: '',
                    category_id: '',
                    discount_type: 'PERCENT',
                    discount_value: '',
                    min_order_amount: '',
                    max_discount_amount: '',
                    usage_limit: '',
                    expires_at: '',
                    is_active: true,
                },
                qrData: {
                    code: '',
                    title: '',
                    discount: ''
                },
                get qrImageUrl() {
                    if (!this.qrData.code) return '';
                    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(this.qrData.code)}`;
                },

                init() {
                    window.addEventListener('open-coupon-modal', (e) => {
                        this.openNewCouponModal();
                    });
                },

                openNewCouponModal() {
                    this.isEdit = false;
                    this.formUrl = "{{ route('coupons.store') }}";
                    this.formData = {
                        id: null,
                        code: '',
                        title: '',
                        category_id: '',
                        discount_type: 'PERCENT',
                        discount_value: '',
                        min_order_amount: '',
                        max_discount_amount: '',
                        usage_limit: '',
                        expires_at: '',
                        is_active: true,
                    };
                    this.openFormModal = true;
                },

                editCoupon(coupon) {
                    this.isEdit = true;
                    this.formUrl = `/admin/coupons/${coupon.id}`;
                    this.formData = {
                        id: coupon.id,
                        code: coupon.code,
                        title: coupon.title,
                        category_id: coupon.category_id || '',
                        discount_type: coupon.discount_type,
                        discount_value: coupon.discount_value,
                        min_order_amount: coupon.min_order_amount || '',
                        max_discount_amount: coupon.max_discount_amount || '',
                        usage_limit: coupon.usage_limit || '',
                        expires_at: coupon.expires_at ? coupon.expires_at.split('T')[0] : '',
                        is_active: coupon.is_active == 1,
                    };
                    this.openFormModal = true;
                },

                showQrModal(code, title, discount) {
                    this.qrData = { code, title, discount };
                    this.openQrModal = true;
                }
            };
        }
    </script>
</x-app-layout>
