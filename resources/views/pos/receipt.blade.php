<x-app-layout>
    <div class="py-8 bg-slate-100 min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center p-4">
        
        <!-- Thermal Receipt Container -->
        <div class="w-full max-w-sm bg-white p-6 rounded-3xl shadow-xl border border-slate-200 text-slate-800 text-xs font-mono select-none" id="printableReceipt">
            
            <!-- Store Header -->
            <div class="text-center space-y-1 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl mx-auto shadow-md mb-2">
                    <i class="fa-solid fa-store"></i>
                </div>
                <h2 class="text-base font-extrabold text-slate-900 tracking-tight font-sans uppercase">{{ \App\Models\Setting::get('store_name', 'POSPRO STORE') }}</h2>
                @if(\App\Models\Setting::get('store_address'))
                    <p class="text-[10px] text-slate-500 font-sans">{{ \App\Models\Setting::get('store_address') }}</p>
                @endif
                @if(\App\Models\Setting::get('store_phone'))
                    <p class="text-[10px] text-slate-500 font-sans">Telp: {{ \App\Models\Setting::get('store_phone') }}</p>
                @endif
            </div>

            <!-- Receipt Info Meta -->
            <div class="border-t border-b border-dashed border-slate-300 py-2.5 my-3 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Order</span>
                    <span class="font-bold text-slate-900">{{ $order->order_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu</span>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kasir</span>
                    <span>{{ $order->user ? $order->user->name : 'Kasir 01' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Pelanggan</span>
                    <span class="font-bold text-slate-900">{{ $order->customer_name }}</span>
                </div>
                @if($order->order_type)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tipe Pesanan</span>
                        <span class="font-bold text-slate-900">
                            {{ str_replace('_', ' ', $order->order_type)}}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Itemized List -->
            <div class="space-y-2 my-3">
                @foreach($order->items as $item)
                    <div class="text-[11px]">
                        <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                        @if(!empty($item->notes))
                            <div class="text-[10px] text-amber-700 font-bold pl-1 border-l-2 border-amber-400 my-0.5">
                                Note: {{ $item->notes }}
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-500 text-[10px] mt-0.5">
                            <span>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Calculation Totals -->
            <div class="border-t border-dashed border-slate-300 pt-3 my-3 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-slate-500">Subtotal</span>
                    <span>Rp {{ number_format($order->base_total, 0, ',', '.') }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="flex justify-between text-red-600">
                        <span>
                            Diskon {{ $order->discount_percent > 0 ? '(' . floatval($order->discount_percent) . '%)' : '' }}
                            @if($order->coupon_code)
                                <strong class="text-blue-600">[{{ $order->coupon_code }}]</strong>
                            @endif
                        </span>
                        <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                    </div>
                @endif

                @if($order->points_discount > 0)
                    <div class="flex justify-between text-amber-600 font-semibold">
                        <span>Diskon Poin ({{ $order->points_redeemed }} pts)</span>
                        <span>-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                    </div>
                @endif

                @if($order->service > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Layanan ({{ floatval($order->service_percent) }}%)</span>
                        <span>+Rp {{ number_format($order->service, 0, ',', '.') }}</span>
                    </div>
                @endif

                @if($order->tax > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>Pajak PPN ({{ floatval($order->tax_percent) }}%)</span>
                        <span>+Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                    </div>
                @endif

                @if($order->unique_code > 0 && $order->payment_method === 'QRIS')
                    <div class="flex justify-between text-blue-600">
                        <span>Kode Unik</span>
                        <span>+Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Payment Details (Cash / QRIS / Split / EDC / Transfer) -->
            <div class="border-t border-dashed border-slate-300 pt-2.5 my-2 space-y-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-slate-500">Metode Bayar</span>
                    <span class="font-extrabold uppercase {{ $order->payment_method === 'QRIS' ? 'text-blue-600' : ($order->payment_method === 'SPLIT' ? 'text-purple-600' : 'text-slate-900') }}">
                        {{ $order->payment_method === 'SPLIT' ? 'SPLIT PAYMENT' : $order->payment_method }}
                    </span>
                </div>

                @if($order->payment_method === 'SPLIT' || $order->is_split_payment)
                    @if(!empty($order->payment_details))
                        @foreach($order->payment_details as $split)
                            <div class="flex justify-between text-[10px] pl-2 text-slate-600 font-semibold">
                                <span>&bull; {{ $split['method'] ?? '-' }}</span>
                                <span>Rp {{ number_format($split['amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                    @if($order->cash_received > 0)
                        <div class="flex justify-between text-[10px] pl-2 text-slate-500 pt-1 border-t border-slate-100">
                            <span>Tunai Diterima</span>
                            <span>Rp {{ number_format($order->cash_received, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] pl-2 font-bold text-slate-800">
                            <span>Kembalian</span>
                            <span>Rp {{ number_format($order->cash_change, 0, ',', '.') }}</span>
                        </div>
                    @endif
                @elseif($order->payment_method === 'CASH')
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tunai Diterima</span>
                        <span>Rp {{ number_format($order->cash_received, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900">
                        <span>Kembalian</span>
                        <span>Rp {{ number_format($order->cash_change, 0, ',', '.') }}</span>
                    </div>
                @elseif($order->payment_method === 'QRIS')
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status QRIS</span>
                        <span class="font-extrabold {{ $order->status === 'PAID' ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $order->status === 'PAID' ? 'LUNAS (PAID)' : 'MENUNGGU' }}
                        </span>
                    </div>
                @else
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="font-extrabold text-emerald-600">LUNAS ({{ $order->payment_method }})</span>
                    </div>
                @endif

                @if($order->points_earned > 0)
                    <div class="flex justify-between text-emerald-600 pt-1 border-t border-slate-100 font-semibold">
                        <span>Poin Member Didapat</span>
                        <span class="font-bold">+{{ $order->points_earned }} pts</span>
                    </div>
                @endif
            </div>

            <!-- Footer Message -->
            <div class="text-center pt-4 mt-4 border-t border-dashed border-slate-300 space-y-1 text-[10px] text-slate-400 font-sans">
                <p class="font-bold text-slate-600 whitespace-pre-line">{{ \App\Models\Setting::get('receipt_footer', 'Terima Kasih Atas Kunjungan Anda!') }}</p>
                <p class="text-[9px] text-slate-300 mt-2">Powered by POSPro</p>
            </div>
        </div>

        @php
            $storeName = \App\Models\Setting::get('store_name', 'POSPRO STORE');
            $storeAddress = \App\Models\Setting::get('store_address');
            $storePhone = \App\Models\Setting::get('store_phone');

            $waLines = [];
            $waLines[] = "*STRUK DIGITAL - " . strtoupper($storeName) . "*";
            if ($storeAddress) $waLines[] = $storeAddress;
            if ($storePhone) $waLines[] = "Telp: " . $storePhone;
            $waLines[] = "--------------------------------";
            $waLines[] = "No. Order : #" . $order->order_id;
            $waLines[] = "Waktu     : " . $order->created_at->format('d/m/Y H:i');
            $waLines[] = "Pelanggan : " . $order->customer_name;
            $waLines[] = "Kasir     : " . ($order->user ? $order->user->name : 'Kasir');
            if ($order->order_type) {
                $waLines[] = "Tipe      : " . str_replace('_', ' ', $order->order_type);
            }
            $waLines[] = "--------------------------------";
            foreach($order->items as $item) {
                $waLines[] = $item->qty . "x " . $item->product_name . " (Rp " . number_format($item->subtotal, 0, ',', '.') . ")";
                if (!empty($item->notes)) {
                    $waLines[] = "   Catatan: " . $item->notes;
                }
            }
            $waLines[] = "--------------------------------";
            $waLines[] = "Subtotal  : Rp " . number_format($order->base_total, 0, ',', '.');
            if ($order->discount > 0) {
                $waLines[] = "Diskon    : -Rp " . number_format($order->discount, 0, ',', '.');
            }
            if ($order->points_discount > 0) {
                $waLines[] = "Diskon Poin: -Rp " . number_format($order->points_discount, 0, ',', '.');
            }
            if ($order->service > 0) {
                $waLines[] = "Layanan   : +Rp " . number_format($order->service, 0, ',', '.');
            }
            if ($order->tax > 0) {
                $waLines[] = "Pajak PPN : +Rp " . number_format($order->tax, 0, ',', '.');
            }
            $waLines[] = "*TOTAL    : Rp " . number_format($order->grand_total, 0, ',', '.') . "*";
            $waLines[] = "Metode    : " . $order->payment_method;
            if ($order->points_earned > 0) {
                $waLines[] = "Poin Didapat: +" . $order->points_earned . " pts";
            }
            $waLines[] = "--------------------------------";
            $waLines[] = \App\Models\Setting::get('receipt_footer', 'Terima Kasih Atas Kunjungan Anda!');

            $fullWaText = implode("\n", $waLines);
            $initialPhone = ($order->customer && $order->customer->phone) ? $order->customer->phone : '';
        @endphp

        <!-- Print & Navigation Actions (Hidden during print) -->
        <div class="flex flex-wrap items-center justify-center gap-3 mt-6 no-print">
            <button 
                onclick="window.print()" 
                class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl shadow-lg transition active:scale-95 flex items-center space-x-2"
            >
                <i class="fa-solid fa-print"></i>
                <span>Cetak Struk Kasir</span>
            </button>

            <!-- WhatsApp Share Button (Feature 5) -->
            <button 
                onclick="shareWhatsAppReceipt()" 
                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 transition active:scale-95 flex items-center space-x-2"
            >
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Kirim WhatsApp</span>
            </button>

            @if(\App\Models\Setting::get('enable_kitchen_receipt', '1') == '1')
                <a 
                    href="{{ route('pos.kitchen-receipt', $order->order_id) }}" 
                    class="px-5 py-3 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-amber-600/30 transition active:scale-95 flex items-center space-x-2"
                >
                    <i class="fa-solid fa-utensils"></i>
                    <span>Struk Dapur (KOT)</span>
                </a>
            @endif

            <a 
                href="{{ route('pos.index') }}" 
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition active:scale-95 flex items-center space-x-2"
            >
                <i class="fa-solid fa-plus"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>

        <script>
            function shareWhatsAppReceipt() {
                let defaultPhone = '{{ $initialPhone }}';
                let waText = @json($fullWaText);
                let phone = defaultPhone;

                if (!phone) {
                    phone = prompt('Masukkan nomor WhatsApp pelanggan (contoh: 08123456789):', '');
                }

                if (phone) {
                    // Normalize phone to international format 62xxx
                    let cleanPhone = phone.replace(/[^0-9]/g, '');
                    if (cleanPhone.startsWith('0')) {
                        cleanPhone = '62' + cleanPhone.substring(1);
                    } else if (!cleanPhone.startsWith('62')) {
                        cleanPhone = '62' + cleanPhone;
                    }
                    const url = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(waText)}`;
                    window.open(url, '_blank');
                }
            }
        </script>
    </div>

    <!-- Thermal Print CSS (58mm / 80mm Paper Optimizations) -->
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            nav, header, footer, .no-print {
                display: none !important;
            }
            #printableReceipt {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }
            .min-h-\[calc\(100vh-4rem\)\] {
                min-height: auto !important;
                padding: 0 !important;
            }
        }
    </style>
</x-app-layout>
