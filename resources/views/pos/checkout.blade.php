<x-app-layout>
    <div class="py-8 md:py-12 bg-slate-100 min-h-[calc(100vh-4rem)] flex items-center justify-center p-4" x-data="checkoutApp()">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden text-center">
            
            <!-- Header Status -->
            <div class="p-6 bg-slate-900 text-white">
                <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-xs font-extrabold uppercase tracking-wider">
                    QRIS Dinamis Aktif
                </span>
                <h2 class="text-2xl font-extrabold mt-3 tracking-tight">Scan & Bayar Sekarang</h2>
                <p class="text-xs text-slate-400 mt-1">Dukung GoPay, OVO, Dana, ShopeePay, BCA, LinkAja, & Mobile Banking</p>
            </div>

            <div class="p-6 md:p-8 space-y-6">
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
                        Menunggu konfirmasi otomatis gateway...
                    </span>
                </div>

                <!-- Meta Details -->
                <div class="text-xs text-slate-500 space-y-1">
                    <div>No. Order: <strong class="text-slate-800">{{ $order->order_id }}</strong></div>
                    <div>Pelanggan: <strong class="text-slate-800">{{ $order->customer_name }}</strong></div>
                </div>

                <!-- FALLBACK / MANUAL CONFIRMATION CARD -->
                <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl text-left space-y-2">
                    <div class="flex items-center space-x-2 text-amber-800 font-extrabold text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                        <span>Pelanggan Sudah Bayar Tapi Belum Terbaca?</span>
                    </div>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        Jika webhook payment gateway mengalami gangguan atau mutasi tertunda, Kasir dapat memfoto bukti bayar di HP pelanggan dan melakukan konfirmasi manual.
                    </p>
                    <button 
                        type="button"
                        @click="openManualModal = true" 
                        class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-amber-600/30 transition flex items-center justify-center space-x-2 active:scale-95"
                    >
                        <i class="fa-solid fa-camera"></i>
                        <span>Konfirmasi Manual & Foto Bukti Bayar</span>
                    </button>
                </div>

                <!-- Manual Refresh & Cancel Actions -->
                <div class="flex space-x-3 pt-2">
                    <form action="{{ route('pos.orders.cancel', $order->order_id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi QRIS ini? Stok produk akan otomatis dikembalikan ke inventori.')">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 shadow-sm">
                            <i class="fa-solid fa-ban"></i>
                            <span>Batalkan & Kembalikan Stok</span>
                        </button>
                    </form>
                    <button onclick="checkManualStatus()" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-blue-600/30 flex items-center justify-center space-x-1.5">
                        <i class="fa-solid fa-rotate"></i>
                        <span>Cek Status Sekarang</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- MODAL MANUAL SETTLEMENT + KAMERA FOTO BUKTI -->
        <div 
            x-show="openManualModal" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div 
                @click.outside="openManualModal = false"
                class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden text-left"
            >
                <!-- Modal Header -->
                <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-900 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm">Konfirmasi Manual Pembayaran</h3>
                            <p class="text-[11px] text-slate-400">Order #{{ $order->order_id }} (Rp {{ number_format($order->grand_total, 0, ',', '.') }})</p>
                        </div>
                    </div>
                    <button @click="openManualModal = false" class="text-slate-400 hover:text-white text-lg">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="manualSettleForm" action="{{ route('pos.manual-settle', $order->order_id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf

                    <!-- Camera / File Capture Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                            Ambil Foto Bukti Transfer / Layar HP Pelanggan
                        </label>
                        
                        <!-- File/Camera Native Trigger -->
                        <div class="relative">
                            <input 
                                type="file" 
                                id="paymentProofInput"
                                name="payment_proof" 
                                accept="image/*" 
                                capture="environment"
                                @change="previewImage($event)"
                                class="hidden"
                            >
                            
                            <!-- Custom Trigger Button (Works on HP, Tablet & PC) -->
                            <div class="grid grid-cols-2 gap-2">
                                <button 
                                    type="button" 
                                    @click="document.getElementById('paymentProofInput').click()" 
                                    class="py-3 px-4 bg-blue-50 border-2 border-dashed border-blue-300 hover:bg-blue-100 rounded-2xl flex flex-col items-center justify-center text-blue-700 transition"
                                >
                                    <i class="fa-solid fa-camera text-xl mb-1"></i>
                                    <span class="text-xs font-extrabold">Buka Kamera / Galeri</span>
                                    <span class="text-[10px] text-blue-500">HP, Tablet & Komputer</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="startWebcam()" 
                                    class="py-3 px-4 bg-slate-50 border-2 border-dashed border-slate-300 hover:bg-slate-100 rounded-2xl flex flex-col items-center justify-center text-slate-700 transition"
                                >
                                    <i class="fa-solid fa-video text-xl mb-1 text-slate-600"></i>
                                    <span class="text-xs font-extrabold">Webcam Langsung</span>
                                    <span class="text-[10px] text-slate-400">Ambil dari Browser</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Live Webcam Stream (If activated) -->
                    <div x-show="useWebcam" x-cloak class="space-y-2 bg-slate-900 p-3 rounded-2xl text-center">
                        <video id="webcamVideo" autoplay playsinline class="w-full max-h-48 rounded-xl bg-black mx-auto"></video>
                        <canvas id="webcamCanvas" class="hidden"></canvas>
                        <button 
                            type="button" 
                            @click="snapWebcam()" 
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center space-x-2 mx-auto"
                        >
                            <i class="fa-solid fa-camera-retro"></i>
                            <span>Jepret Foto Sekarang</span>
                        </button>
                    </div>

                    <input type="hidden" name="payment_proof_base64" :value="capturedBase64">

                    <!-- Image Preview Area -->
                    <div x-show="previewUrl" x-cloak class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-center">
                        <div class="text-[11px] font-bold text-slate-500 mb-1.5 flex items-center justify-between">
                            <span>Pratinjau Foto Bukti:</span>
                            <button type="button" @click="clearPreview()" class="text-red-500 text-[10px] hover:underline font-bold">Hapus Foto</button>
                        </div>
                        <img :src="previewUrl" alt="Bukti Transfer" class="max-h-48 mx-auto rounded-xl border border-slate-300 shadow-sm object-contain">
                    </div>

                    <!-- Notes Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Verifikasi (Opsional)</label>
                        <input 
                            type="text" 
                            name="notes" 
                            placeholder="Contoh: Sudah dicek via mutasi / screen HP pelanggan" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        >
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button 
                            type="button" 
                            @click="openManualModal = false; stopWebcam();" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="confirmManualSubmit()" 
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition active:scale-95 flex items-center space-x-1.5"
                        >
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Verifikasi & Cetak Struk</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Polling & Camera Alpine Script -->
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
                        }, 1200);
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

        function checkoutApp() {
            return {
                openManualModal: false,
                useWebcam: false,
                previewUrl: null,
                capturedBase64: '',
                webcamStream: null,

                previewImage(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.previewUrl = URL.createObjectURL(file);
                        this.capturedBase64 = '';
                        this.stopWebcam();
                    }
                },

                startWebcam() {
                    this.useWebcam = true;
                    const video = document.getElementById('webcamVideo');
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                            .then(stream => {
                                this.webcamStream = stream;
                                video.srcObject = stream;
                            })
                            .catch(err => {
                                Toast.fire({ icon: 'warning', title: 'Tidak dapat mengakses webcam browser. Gunakan opsi Buka Kamera.' });
                                this.useWebcam = false;
                            });
                    }
                },

                snapWebcam() {
                    const video = document.getElementById('webcamVideo');
                    const canvas = document.getElementById('webcamCanvas');
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                    this.capturedBase64 = dataUrl;
                    this.previewUrl = dataUrl;
                    this.stopWebcam();
                },

                stopWebcam() {
                    if (this.webcamStream) {
                        this.webcamStream.getTracks().forEach(track => track.stop());
                        this.webcamStream = null;
                    }
                    this.useWebcam = false;
                },

                clearPreview() {
                    this.previewUrl = null;
                    this.capturedBase64 = '';
                    const input = document.getElementById('paymentProofInput');
                    if (input) input.value = '';
                    this.stopWebcam();
                },

                confirmManualSubmit() {
                    Swal.fire({
                        title: 'Konfirmasi Pelunasan Manual?',
                        text: 'Pastikan bukti bayar di HP pelanggan atau mutasi sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Konfirmasi Lunas',
                        cancelButtonText: 'Cek Kembali',
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#64748b'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('manualSettleForm').submit();
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>
