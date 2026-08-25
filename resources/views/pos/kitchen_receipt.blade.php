<x-app-layout>
    <div class="py-8 bg-slate-100 min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center p-4">
        
        <!-- Kitchen Order Ticket Container (Optimized for Chef/Barista) -->
        <div class="w-full max-w-sm bg-white p-6 rounded-3xl shadow-xl border border-slate-200 text-slate-900 font-mono select-none" id="printableKitchenReceipt">
            
            <!-- Kitchen Header -->
            <div class="text-center space-y-1 mb-4 border-b-2 border-black pb-3">
                <div class="inline-block px-3 py-1 bg-black text-white text-xs font-black uppercase tracking-wider rounded-md mb-1">
                    *** STRUK DAPUR / BAR (KOT) ***
                </div>
                <h2 class="text-lg font-black tracking-tight font-sans uppercase">{{ \App\Models\Setting::get('store_name', 'POSPRO') }}</h2>
            </div>

            <!-- Ticket Info Meta -->
            <div class="border-b-2 border-dashed border-black pb-2.5 mb-3 space-y-1.5 text-xs">
                <div class="flex justify-between items-center">
                    <span class="font-bold">No. Order</span>
                    <span class="font-black text-sm bg-slate-100 px-2 py-0.5 rounded">{{ $order->order_id }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-bold">Pelanggan / Meja</span>
                    <span class="font-black text-base bg-black text-white px-2.5 py-0.5 rounded font-sans uppercase">
                        {{ $order->customer_name }}
                    </span>
                </div>
                <div class="flex justify-between text-[11px] text-slate-600 pt-1">
                    <span>Waktu: {{ $order->created_at->format('d/m/Y H:i:s') }}</span>
                    <span>Kasir: {{ $order->user ? $order->user->name : 'Kasir 01' }}</span>
                </div>
            </div>

            <!-- Items & Special Cooking Requests (NO PRICES!) -->
            <div class="space-y-3 my-3">
                @foreach($order->items as $index => $item)
                    <div class="border-b border-slate-200 pb-2.5 last:border-0 last:pb-0">
                        <div class="flex items-start justify-between">
                            <span class="text-lg font-black bg-black text-white w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mr-2">
                                {{ $item->qty }}x
                            </span>
                            <div class="flex-1">
                                <h4 class="font-black text-sm text-slate-900 leading-snug font-sans uppercase">
                                    {{ $item->product_name }}
                                </h4>
                                @if(!empty($item->notes))
                                    <div class="mt-1.5 p-1.5 bg-amber-100 border border-amber-300 rounded-lg text-amber-950 text-xs font-black font-sans flex items-start space-x-1.5">
                                        <i class="fa-solid fa-pen text-[10px] mt-0.5 text-amber-700"></i>
                                        <span>NOTE: {{ $item->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Global Order Notes (if any) -->
            @if($order->notes)
                <div class="mt-3 p-2 bg-slate-100 border border-slate-300 rounded-xl text-xs">
                    <span class="font-black uppercase block text-[10px] text-slate-500">Catatan Tambahan Order:</span>
                    <p class="font-bold text-slate-800 font-sans mt-0.5">{{ $order->notes }}</p>
                </div>
            @endif

            <!-- Footer Marker -->
            <div class="text-center pt-4 mt-4 border-t-2 border-black text-[10px] font-black tracking-widest text-slate-600 uppercase">
                *** SIAPKAN SEGERA ***
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-3 mt-6 no-print">
            <button 
                onclick="window.print()" 
                class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl shadow-lg shadow-amber-600/30 transition active:scale-95 flex items-center space-x-2"
            >
                <i class="fa-solid fa-print"></i>
                <span>Cetak Struk Dapur</span>
            </button>

            <a 
                href="{{ route('pos.receipt', $order->order_id) }}" 
                class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-2"
            >
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Struk Kasir</span>
            </a>

            <a 
                href="{{ route('pos.index') }}" 
                class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-2"
            >
                <i class="fa-solid fa-plus"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
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
            #printableKitchenReceipt {
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
