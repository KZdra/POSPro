<x-app-layout>
    <div class="py-12 bg-slate-100 min-h-[calc(100vh-4rem)] flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden text-center">
            
            <!-- Header Status -->
            <div class="p-6 bg-slate-900 text-white">
                <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-xs font-extrabold uppercase tracking-wider">
                    QRIS Dinamis Aktif
                </span>
                <h2 class="text-2xl font-extrabold mt-3 tracking-tight">Scan & Bayar Sekarang</h2>
                <p class="text-xs text-slate-400 mt-1">Dukung GoPay, OVO, Dana, ShopeePay, BCA, LinkAja, & Mobile Banking</p>
            </div>

            <div class="p-8 space-y-6">
                <!-- Amount Banner -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Tagihan (Termasuk Kode Unik)</span>
                    <div class="text-3xl font-extrabold text-blue-700 mt-1">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </div>
                    @if($order->unique_code > 0)
                        <p class="text-[11px] text-blue-600 font-semibold mt-1">
                            *Wajib transfer sesuai digit terakhir (+{{ $order->unique_code }}) agar otomatis lunas!
                        </p>
                    @endif
                </div>

                <!-- QRIS Code Image Box -->
                <div class="p-4 bg-white border-2 border-slate-200 rounded-3xl inline-block shadow-inner">
                    @if(!empty($qris_image))
                        <img src="data:image/png;base64,{{ $qris_image }}" alt="QRIS Code" class="w-64 h-64 object-contain mx-auto rounded-xl">
                    @else
                        <div class="w-64 h-64 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-xl">
                            <i class="fa-solid fa-qrcode text-5xl mb-2 text-slate-300"></i>
                            <span class="text-xs font-bold">QRIS Code</span>
                        </div>
                    @endif
                </div>

                <!-- Live Status Spinner -->
                <div id="statusContainer" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center space-x-3">
                    <div id="statusSpinner" class="w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                    <span id="statusText" class="text-sm font-bold text-slate-700">
                        Menunggu konfirmasi pembayaran...
                    </span>
                </div>

                <!-- Meta Details -->
                <div class="text-xs text-slate-500 space-y-1">
                    <div>No. Order: <strong class="text-slate-800">{{ $order->order_id }}</strong></div>
                    <div>Pelanggan: <strong class="text-slate-800">{{ $order->customer_name }}</strong></div>
                </div>

                <!-- Manual Refresh & Cancel Actions -->
                <div class="flex space-x-3 pt-2">
                    <a href="{{ route('pos.index') }}" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal / Kembali ke Kasir
                    </a>
                    <button onclick="checkManualStatus()" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-blue-600/30">
                        Cek Status Sekarang
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Polling Script -->
    <script>
        const orderId = "{{ $order->order_id }}";
        let isPaid = false;

        function checkPaymentStatus() {
            if (isPaid) return;

            fetch(`/status/${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'PAID') {
                        isPaid = true;
                        
                        // Play success chime
                        if (typeof playBeep === 'function') playBeep('success');

                        const container = document.getElementById('statusContainer');
                        const spinner = document.getElementById('statusSpinner');
                        const text = document.getElementById('statusText');

                        container.className = "p-4 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-center justify-center space-x-3";
                        spinner.className = "fa-solid fa-circle-check text-emerald-500 text-xl";
                        text.className = "text-sm font-extrabold text-emerald-700";
                        text.innerText = "PEMBAYARAN BERHASIL DITERIMA!";

                        Toast.fire({
                            icon: 'success',
                            title: 'Pembayaran QRIS Berhasil!'
                        });

                        setTimeout(() => {
                            window.location.href = `/print-receipt/${orderId}`;
                        }, 1500);
                    }
                })
                .catch(err => console.error("Error polling payment status:", err));
        }

        // Poll every 2.5 seconds
        const pollInterval = setInterval(checkPaymentStatus, 2500);

        function checkManualStatus() {
            checkPaymentStatus();
            Toast.fire({
                icon: 'info',
                title: 'Memeriksa mutasi pembayaran...'
            });
        }
    </script>
</x-app-layout>
