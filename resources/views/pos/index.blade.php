<x-app-layout>
    <div class="h-[calc(100vh-4rem)] flex flex-col md:flex-row overflow-hidden bg-slate-100 relative" x-data="posApp()" x-init="initApp()">
        
        <!-- LEFT AREA: Product Catalog & Touch Browser (Full width on mobile, 70% on desktop) -->
        <div class="flex-1 flex flex-col h-full overflow-hidden border-r border-slate-200">
            
            <!-- Top Action & Filter Bar -->
            <div class="bg-white p-3 md:p-4 border-b border-slate-200 shadow-sm flex flex-col sm:flex-row gap-2.5 items-center justify-between">
                <!-- Search & Barcode Input -->
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        @keydown.enter="handleBarcodeScan()" 
                        placeholder="Cari menu / scan barcode..." 
                        class="w-full pl-9 pr-4 py-2 md:py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner"
                    >
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <!-- Clock, Held Orders & Fullscreen -->
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-between sm:justify-end">
                    <!-- Live Clock -->
                    <div class="flex items-center space-x-1.5 px-2.5 py-1.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600">
                        <i class="fa-regular fa-clock text-blue-500"></i>
                        <span x-text="liveTime"></span>
                    </div>

                    <!-- Hold Order Recall Button -->
                    <button 
                        @click="openHoldModal = true" 
                        class="relative px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-[11px] font-bold transition flex items-center space-x-1.5"
                    >
                        <i class="fa-solid fa-pause"></i>
                        <span class="hidden sm:inline">Pesanan Tertahan</span>
                        <span class="sm:hidden">Tahan</span>
                        <span x-show="heldOrders.length > 0" x-text="heldOrders.length" class="ml-1 px-1.5 py-0.5 bg-amber-500 text-white rounded-full text-[10px] font-extrabold animate-pulse"></span>
                    </button>

                    <!-- Fullscreen Toggle Button -->
                    <button @click="toggleFullscreen()" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition" title="Layar Penuh">
                        <i class="fa-solid fa-expand text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Categories Horizontal Slider -->
            <div class="bg-white px-3 md:px-4 py-2.5 border-b border-slate-200 overflow-x-auto flex space-x-2 no-scrollbar">
                <button 
                    @click="selectedCategory = null" 
                    :class="selectedCategory === null ? 'bg-slate-900 text-white shadow-md shadow-slate-900/30' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-3.5 py-1.5 md:py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center space-x-1.5"
                >
                    <i class="fa-solid fa-grid-2"></i>
                    <span>Semua</span>
                    <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px]" :class="selectedCategory === null ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ $products->count() }}
                    </span>
                </button>

                @foreach($categories as $category)
                    <button 
                        @click="selectedCategory = {{ $category->id }}" 
                        :class="selectedCategory === {{ $category->id }} ? '{{ $category->color_solid_badge }} shadow-md' : '{{ $category->color_badge_class }} hover:opacity-90'"
                        class="px-3.5 py-1.5 md:py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center space-x-1.5 border"
                    >
                        <i class="fa-solid {{ $category->icon ?: 'fa-tag' }}"></i>
                        <span>{{ $category->name }}</span>
                        <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px]" :class="selectedCategory === {{ $category->id }} ? 'bg-black/20 text-white' : 'bg-white/80 text-slate-800'">
                            {{ $category->products_count }}
                        </span>
                    </button>
                @endforeach
            </div>

            <!-- Product Grid Catalog (Touch Friendly & Responsive 2 cols on mobile) -->
            <div class="flex-1 overflow-y-auto p-3 md:p-5 pb-24 md:pb-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div 
                            @click="addToCart(product)" 
                            class="group relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-150 cursor-pointer active:scale-95 flex flex-col justify-between select-none"
                            :class="product.manage_stock && product.stock <= 0 ? 'opacity-50 pointer-events-none' : ''"
                        >
                            <!-- Product Image / Placeholder -->
                            <div class="relative w-full h-28 sm:h-36 bg-slate-100 overflow-hidden">
                                <template x-if="product.image_url">
                                    <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </template>
                                <template x-if="!product.image_url">
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50">
                                        <i class="fa-solid fa-mug-saucer text-2xl md:text-3xl mb-1"></i>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">No Image</span>
                                    </div>
                                </template>

                                <!-- Category Badge -->
                                <template x-if="product.category">
                                    <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white text-[9px] font-bold" x-text="product.category.name"></span>
                                </template>

                                <!-- Stock / Unlimited Badge -->
                                <template x-if="!product.manage_stock">
                                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-purple-600 text-white shadow-sm flex items-center space-x-1">
                                        <i class="fa-solid fa-check text-[8px]"></i>
                                        <span>Ready</span>
                                    </span>
                                </template>
                                <template x-if="product.manage_stock">
                                    <span 
                                        class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md text-[9px] font-extrabold shadow-sm"
                                        :class="{
                                            'bg-red-500 text-white': product.stock <= 0,
                                            'bg-amber-500 text-white': product.stock > 0 && product.stock <= 5,
                                            'bg-emerald-500 text-white': product.stock > 5
                                        }"
                                        x-text="product.stock <= 0 ? 'HABIS' : 'Stok: ' + product.stock"
                                    ></span>
                                </template>
                            </div>

                            <!-- Product Info -->
                            <div class="p-2.5 md:p-3.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="product.sku || '-'"></div>
                                    <h4 class="font-bold text-xs md:text-sm text-slate-800 line-clamp-2 leading-snug mt-0.5" x-text="product.name"></h4>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="font-extrabold text-blue-600 text-xs md:text-base" x-text="'Rp ' + formatNumber(product.price)"></span>
                                    <div class="w-6 h-6 md:w-8 md:h-8 rounded-lg md:rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-plus text-[10px] md:text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="filteredProducts.length === 0" class="py-16 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-4xl mb-2 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-600">Tidak ada produk ditemukan</p>
                    <p class="text-xs text-slate-400">Coba ganti kategori atau kata kunci pencarian</p>
                </div>
            </div>
        </div>


        <!-- RIGHT / DRAWER AREA: Cart & Order Summary (Desktop sidebar & Mobile Drawer) -->
        <div 
            class="fixed inset-y-0 right-0 z-40 w-full sm:w-[400px] md:w-[380px] lg:w-[420px] md:static md:z-30 bg-white flex flex-col h-full shadow-2xl md:shadow-xl border-l border-slate-200 transition-transform duration-300 ease-in-out"
            :class="mobileCartOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
        >
            <!-- Cart Header -->
            <div class="p-3.5 md:p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-xs md:text-sm">Keranjang Pesanan</h3>
                        <p class="text-[10px] md:text-[11px] text-slate-500 font-medium">Kasir: <strong class="text-slate-700">{{ Auth::check() ? Auth::user()->name : 'Kasir 01' }}</strong></p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Clear Cart -->
                    <button 
                        @click="clearCart()" 
                        x-show="cart.length > 0"
                        class="text-xs font-bold text-red-500 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition"
                        title="Kosongkan Keranjang"
                    >
                        <i class="fa-solid fa-trash-can mr-1"></i> Reset
                    </button>

                    <!-- Close Cart Drawer (Mobile only) -->
                    <button 
                        @click="mobileCartOpen = false" 
                        class="md:hidden w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Customer Indicator Badge in Cart -->
            <div class="px-4 py-2.5 bg-blue-50/50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-user-tag text-blue-500 text-xs"></i>
                    <span class="text-xs font-bold text-slate-700" x-text="customerName ? customerName : 'Pelanggan: Belum Diisi'"></span>
                </div>
                <button @click="openCustomerModal = true" class="text-[11px] font-extrabold text-blue-600 hover:underline">
                    <span x-text="customerName ? 'Ganti' : '+ Masukkan Nama'"></span>
                </button>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-3.5 md:p-4 space-y-3 divide-y divide-slate-100">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center text-slate-300 py-12">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 text-2xl mb-2">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-500">Keranjang Masih Kosong</p>
                        <p class="text-[11px] text-slate-400 max-w-[180px] mt-0.5">Ketuk menu di katalog untuk menambah item</p>
                    </div>
                </template>

                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="pt-3 first:pt-0 flex items-center justify-between">
                        <!-- Name & Price -->
                        <div class="flex-1 pr-2">
                            <h5 class="font-bold text-xs text-slate-800 line-clamp-1" x-text="item.name"></h5>
                            <div class="text-[10px] font-semibold text-slate-400 mt-0.5">
                                @ Rp <span x-text="formatNumber(item.price)"></span>
                            </div>
                        </div>

                        <!-- Stepper Quantity (+ / -) Touch Friendly -->
                        <div class="flex items-center space-x-1.5 bg-slate-100 p-1 rounded-xl">
                            <button 
                                @click="decreaseQty(index)" 
                                class="w-7 h-7 rounded-lg bg-white text-slate-700 shadow-sm hover:bg-slate-200 active:scale-95 flex items-center justify-center font-bold text-xs transition"
                            >
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </button>
                            <span class="w-5 text-center font-extrabold text-xs text-slate-800" x-text="item.qty"></span>
                            <button 
                                @click="increaseQty(index)" 
                                class="w-7 h-7 rounded-lg bg-white text-slate-700 shadow-sm hover:bg-slate-200 active:scale-95 flex items-center justify-center font-bold text-xs transition"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Subtotal & Trash -->
                        <div class="text-right pl-2 min-w-[70px]">
                            <div class="font-extrabold text-xs text-slate-900" x-text="'Rp ' + formatNumber(item.price * item.qty)"></div>
                            <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 text-[10px] mt-1 transition">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Cart Calculation & Bottom Payment Action -->
            <div class="p-3.5 md:p-4 bg-slate-50 border-t border-slate-200 space-y-3">
                <!-- Subtotal, Discount %, Service %, Tax % Breakdown -->
                <div class="space-y-2 text-xs">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-slate-500 font-semibold">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-800" x-text="'Rp ' + formatNumber(subtotal)"></span>
                    </div>

                    <!-- Discount (%) with Quick Presets -->
                    <div class="space-y-1.5 pt-1 border-t border-slate-200/60">
                        <div class="flex justify-between items-center text-slate-600 font-bold">
                            <span class="flex items-center space-x-1">
                                <span>Diskon</span>
                                <span class="text-[10px] text-red-600 font-extrabold" x-show="discountPercent > 0" x-text="'(-' + discountPercent + '%)'"></span>
                            </span>
                            <div class="flex items-center space-x-1.5">
                                <div class="relative w-16">
                                    <input 
                                        type="number" 
                                        x-model.number="discountPercent" 
                                        min="0"
                                        max="100"
                                        placeholder="0"
                                        class="w-full text-right pr-5 py-1 text-xs font-extrabold text-red-600 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-blue-500"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-1.5 flex items-center text-slate-400 font-bold text-[10px]">%</span>
                                </div>
                                <span class="text-xs font-extrabold text-red-600 min-w-[70px] text-right" x-text="'-Rp ' + formatNumber(discountAmount)"></span>
                            </div>
                        </div>

                        <!-- Quick Discount Pills -->
                        <div class="flex space-x-1 justify-end">
                            <button type="button" @click="discountPercent = 0" class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="discountPercent === 0 ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">0%</button>
                            <button type="button" @click="discountPercent = 5" class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="discountPercent === 5 ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'">5%</button>
                            <button type="button" @click="discountPercent = 10" class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="discountPercent === 10 ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'">10%</button>
                            <button type="button" @click="discountPercent = 20" class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="discountPercent === 20 ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'">20%</button>
                            <button type="button" @click="discountPercent = 50" class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="discountPercent === 50 ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100'">50%</button>
                        </div>
                    </div>

                    <!-- Service Charge (If Enabled) -->
                    <template x-if="enableService">
                        <div class="flex justify-between items-center text-slate-500 font-semibold pt-1 border-t border-slate-200/60">
                            <span x-text="'Biaya Layanan (' + serviceRate + '%)'"></span>
                            <span class="font-bold text-slate-800" x-text="'+Rp ' + formatNumber(serviceAmount)"></span>
                        </div>
                    </template>

                    <!-- PPN Tax (If Enabled) -->
                    <template x-if="enableTax">
                        <div class="flex justify-between items-center text-slate-500 font-semibold">
                            <span x-text="'Pajak PPN (' + taxRate + '%)'"></span>
                            <span class="font-bold text-slate-800" x-text="'+Rp ' + formatNumber(taxAmount)"></span>
                        </div>
                    </template>

                    <!-- Grand Total -->
                    <div class="flex justify-between text-sm md:text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Tagihan</span>
                        <span class="text-blue-600 text-base md:text-lg" x-text="'Rp ' + formatNumber(grandTotal)"></span>
                    </div>
                </div>

                <!-- Bottom Action Buttons -->
                <div class="grid grid-cols-4 gap-2 pt-1">
                    <!-- Hold Order Button -->
                    <button 
                        @click="holdCurrentOrder()" 
                        :disabled="cart.length === 0"
                        class="col-span-1 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex flex-col items-center justify-center"
                        title="Tahan Pesanan"
                    >
                        <i class="fa-solid fa-pause text-xs mb-0.5"></i>
                        <span class="text-[9px]">Tahan</span>
                    </button>

                    <!-- Main Pay Button (Prompts customer modal if empty) -->
                    <button 
                        @click="triggerCheckout()" 
                        :disabled="cart.length === 0"
                        class="col-span-3 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-xl text-xs md:text-sm transition-all shadow-lg shadow-blue-600/30 active:scale-98 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center space-x-2"
                    >
                        <i class="fa-solid fa-cash-register"></i>
                        <span>BAYAR PESANAN</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MOBILE FLOATING ACTION BAR (Only visible on small/portrait screens when cart has items) -->
        <div 
            x-show="cart.length > 0" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            class="md:hidden fixed bottom-0 inset-x-0 z-30 p-3 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-2xl flex items-center justify-between"
        >
            <div @click="mobileCartOpen = true" class="cursor-pointer">
                <div class="flex items-center space-x-1.5">
                    <span class="px-2 py-0.5 bg-blue-600 text-white rounded-md text-xs font-extrabold" x-text="totalItemCount + ' Item'"></span>
                    <span class="text-xs text-slate-400 font-medium">Total</span>
                </div>
                <div class="font-extrabold text-base text-slate-900" x-text="'Rp ' + formatNumber(grandTotal)"></div>
            </div>

            <button 
                @click="mobileCartOpen = true" 
                class="px-5 py-2.5 bg-blue-600 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 flex items-center space-x-2"
            >
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Lihat Keranjang</span>
            </button>
        </div>


        <!-- MODAL 1: WAJIB NAMA PELANGGAN (Mandatory Customer Name Modal) -->
        <div 
            x-show="openCustomerModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.away="openCustomerModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4">
                <div class="text-center space-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-slate-900">Nama Pelanggan / Meja</h3>
                    <p class="text-xs text-slate-500 font-medium">Mohon masukkan identitas pelanggan untuk pencetakan struk</p>
                </div>

                <!-- Input Name -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Pelanggan / No. Meja (Wajib) *</label>
                        <input 
                            type="text" 
                            x-model="customerName" 
                            id="customerNameInput"
                            @keydown.enter="confirmCustomerName()"
                            placeholder="Contoh: Meja 05 / Kak Sarah / Take Away" 
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-0 transition"
                        >
                    </div>

                    <!-- Quick Suggestions (Meja & Tipe Order) -->
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Preset Cepat:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <button @click="setCustomerPreset('Dine In')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Dine In
                            </button>
                            <button @click="setCustomerPreset('Take Away')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Take Away
                            </button>
                            <button @click="setCustomerPreset('Meja 01')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Meja 01
                            </button>
                            <button @click="setCustomerPreset('Meja 02')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Meja 02
                            </button>
                            <button @click="setCustomerPreset('Meja 03')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Meja 03
                            </button>
                            <button @click="setCustomerPreset('Online/Ojol')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                Ojol
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Action -->
                <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
                    <button @click="openCustomerModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </button>
                    <button @click="confirmCustomerName()" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95">
                        Lanjut Bayar &rarr;
                    </button>
                </div>
            </div>
        </div>


        <!-- MODAL 2: PAYMENT REGISTRATION (Touch Screen Cash & QRIS) -->
        <div 
            x-show="showPaymentModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 md:p-4"
            style="display: none;"
        >
            <div 
                @click.away="showPaymentModal = false"
                class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col my-auto max-h-[92vh]"
            >
                <!-- Modal Header -->
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-base md:text-lg text-slate-900">Pembayaran Kasir</h3>
                        <p class="text-[11px] text-slate-500 font-medium">Pelanggan: <strong class="text-blue-600" x-text="customerName"></strong></p>
                    </div>
                    <button @click="showPaymentModal = false" class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 hover:bg-slate-300 flex items-center justify-center transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Total Amount Banner -->
                <div class="px-5 py-3.5 bg-blue-600 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-blue-200 tracking-wider">Total Tagihan</span>
                        <div class="text-2xl md:text-3xl font-extrabold mt-0.5" x-text="'Rp ' + formatNumber(grandTotal)"></div>
                    </div>
                    <div class="text-right text-xs text-blue-100">
                        <div>Jumlah Item: <strong class="text-white" x-text="totalItemCount"></strong></div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-5 md:p-6 space-y-4 md:space-y-6 overflow-y-auto">
                    <!-- Payment Method Switcher Tabs -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <button 
                            @click="paymentMethod = 'CASH'" 
                            :class="paymentMethod === 'CASH' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-3 md:p-4 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 md:space-x-3"
                        >
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg md:text-xl shadow-md shadow-blue-500/20">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-base font-extrabold">Tunai (Cash)</div>
                                <div class="text-[10px] md:text-xs font-normal text-slate-400">Bayar tunai langsung</div>
                            </div>
                        </button>

                        <button 
                            @click="paymentMethod = 'QRIS'" 
                            :class="paymentMethod === 'QRIS' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-3 md:p-4 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 md:space-x-3"
                        >
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg md:text-xl shadow-md shadow-emerald-500/20">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-base font-extrabold">QRIS Otomatis</div>
                                <div class="text-[10px] md:text-xs font-normal text-slate-400">GoPay, OVO, Dana, dll</div>
                            </div>
                        </button>
                    </div>

                    <!-- CASH PAYMENT SECTION: Touch Numpad & Quick Cash -->
                    <div x-show="paymentMethod === 'CASH'" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Left: Quick Amounts & Calculations -->
                            <div class="space-y-2.5 md:space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Uang Diterima (Rp)</label>
                                    <input 
                                        type="number" 
                                        x-model.number="cashReceived" 
                                        class="w-full text-xl md:text-2xl font-extrabold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-2 focus:border-blue-600 focus:bg-white focus:ring-0 transition"
                                    >
                                </div>

                                <!-- Quick Cash Presets -->
                                <div class="grid grid-cols-3 gap-1.5 md:gap-2">
                                    <button @click="setCash(grandTotal)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        Uang Pas
                                    </button>
                                    <button @click="setCash(10000)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        10.000
                                    </button>
                                    <button @click="setCash(20000)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        20.000
                                    </button>
                                    <button @click="setCash(50000)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        50.000
                                    </button>
                                    <button @click="setCash(100000)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        100.000
                                    </button>
                                    <button @click="setCash(200000)" class="py-2 md:py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-extrabold text-slate-800 transition">
                                        200.000
                                    </button>
                                </div>

                                <!-- Live Change (Kembalian) Box -->
                                <div 
                                    class="p-3 md:p-4 rounded-2xl border-2 transition-all flex items-center justify-between"
                                    :class="cashChange >= 0 ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-red-50 border-red-300 text-red-900'"
                                >
                                    <div>
                                        <span class="text-[10px] md:text-xs font-bold uppercase tracking-wider">Kembalian</span>
                                        <div class="text-xl md:text-2xl font-extrabold mt-0.5" x-text="cashChange >= 0 ? 'Rp ' + formatNumber(cashChange) : 'Kurang Rp ' + formatNumber(Math.abs(cashChange))"></div>
                                    </div>
                                    <i class="fa-solid fa-coins text-xl md:text-2xl" :class="cashChange >= 0 ? 'text-emerald-500' : 'text-red-400'"></i>
                                </div>
                            </div>

                            <!-- Right: Touch Screen Numpad -->
                            <div class="grid grid-cols-3 gap-1.5 md:gap-2 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                                <template x-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]">
                                    <button 
                                        @click="numpadPress(num)" 
                                        class="py-2.5 md:py-3 bg-white hover:bg-slate-200 rounded-xl font-extrabold text-base md:text-lg text-slate-800 shadow-sm transition active:scale-95"
                                        x-text="num"
                                    ></button>
                                </template>
                                <button @click="numpadPress('000')" class="py-2.5 md:py-3 bg-white hover:bg-slate-200 rounded-xl font-extrabold text-xs md:text-sm text-slate-800 shadow-sm transition active:scale-95">000</button>
                                <button @click="numpadPress(0)" class="py-2.5 md:py-3 bg-white hover:bg-slate-200 rounded-xl font-extrabold text-base md:text-lg text-slate-800 shadow-sm transition active:scale-95">0</button>
                                <button @click="numpadBackspace()" class="py-2.5 md:py-3 bg-red-100 hover:bg-red-200 rounded-xl font-extrabold text-sm md:text-base text-red-600 shadow-sm transition active:scale-95">
                                    <i class="fa-solid fa-delete-left"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- QRIS SECTION INFO -->
                    <div x-show="paymentMethod === 'QRIS'" class="p-5 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 text-base">Generate QRIS Dinamis</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Sistem akan me-request QRIS dengan kode unik ke Mini Payment Gateway. Pelanggan dapat scan dan bayar secara instan.
                        </p>
                    </div>

                    <!-- Hidden Form for submission -->
                    <form id="checkoutForm" action="{{ route('pos.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="items" :value="JSON.stringify(cart)">
                        <input type="hidden" name="payment_method" :value="paymentMethod">
                        <input type="hidden" name="customer_name" :value="customerName">
                        <input type="hidden" name="discount" :value="discountAmount">
                        <input type="hidden" name="discount_percent" :value="discountPercent">
                        <input type="hidden" name="service" :value="serviceAmount">
                        <input type="hidden" name="service_percent" :value="enableService ? serviceRate : 0">
                        <input type="hidden" name="tax" :value="taxAmount">
                        <input type="hidden" name="tax_percent" :value="enableTax ? taxRate : 0">
                        <input type="hidden" name="cash_received" :value="cashReceived">
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button @click="showPaymentModal = false" class="px-4 py-2 font-bold text-xs text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button 
                        @click="submitCheckout()" 
                        :disabled="paymentMethod === 'CASH' && cashChange < 0"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs md:text-sm rounded-xl shadow-lg shadow-blue-600/30 transition active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex items-center space-x-2"
                    >
                        <i class="fa-solid fa-circle-check"></i>
                        <span x-text="paymentMethod === 'CASH' ? 'Proses & Cetak Struk' : 'Lanjutkan ke QRIS'"></span>
                    </button>
                </div>
            </div>
        </div>


        <!-- MODAL 3: HELD ORDERS (Parkir Pesanan) -->
        <div 
            x-show="openHoldModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.away="openHoldModal = false" class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-pause text-amber-500"></i>
                        <span>Daftar Pesanan Tertahan</span>
                    </h3>
                    <button @click="openHoldModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto space-y-2">
                    <template x-if="heldOrders.length === 0">
                        <p class="text-center text-xs text-slate-400 py-8">Tidak ada pesanan yang tertahan.</p>
                    </template>

                    <template x-for="(order, idx) in heldOrders" :key="idx">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                            <div>
                                <h5 class="font-bold text-xs text-slate-800" x-text="order.customerName || 'Pelanggan #' + (idx+1)"></h5>
                                <p class="text-[11px] text-slate-400" x-text="order.items.length + ' item • Rp ' + formatNumber(order.total)"></p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="resumeHeldOrder(idx)" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">
                                    Lanjutkan
                                </button>
                                <button @click="deleteHeldOrder(idx)" class="p-1.5 text-slate-400 hover:text-red-500 rounded-xl transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js POS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posApp', () => ({
                allProducts: @json($products),
                searchQuery: '',
                selectedCategory: null,
                cart: [],
                customerName: '',
                discountPercent: 0,
                enableTax: {{ $taxSettings['enable_tax'] ? 'true' : 'false' }},
                taxRate: {{ $taxSettings['tax_rate'] }},
                enableService: {{ $taxSettings['enable_service'] ? 'true' : 'false' }},
                serviceRate: {{ $taxSettings['service_rate'] }},
                paymentMethod: 'CASH',
                cashReceived: 0,
                showPaymentModal: false,
                openCustomerModal: false,
                openHoldModal: false,
                mobileCartOpen: false,
                heldOrders: [],
                liveTime: '',

                initApp() {
                    try {
                        const saved = localStorage.getItem('pos_held_orders');
                        if (saved) this.heldOrders = JSON.parse(saved);
                    } catch(e) {}

                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);

                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'F8') {
                            e.preventDefault();
                            if (this.cart.length > 0) this.triggerCheckout();
                        } else if (e.key === 'Escape') {
                            this.showPaymentModal = false;
                            this.openCustomerModal = false;
                            this.openHoldModal = false;
                        }
                    });
                },

                updateTime() {
                    const now = new Date();
                    this.liveTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                get filteredProducts() {
                    return this.allProducts.filter(p => {
                        const matchCat = this.selectedCategory === null || p.category_id === this.selectedCategory;
                        const q = this.searchQuery.toLowerCase().trim();
                        const matchSearch = !q || p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q)) || (p.barcode && p.barcode.toLowerCase().includes(q));
                        return matchCat && matchSearch;
                    });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get discountAmount() {
                    return Math.round(this.subtotal * ((this.discountPercent || 0) / 100));
                },

                get subtotalAfterDiscount() {
                    return Math.max(0, this.subtotal - this.discountAmount);
                },

                get serviceAmount() {
                    return this.enableService ? Math.round(this.subtotalAfterDiscount * (this.serviceRate / 100)) : 0;
                },

                get taxAmount() {
                    return this.enableTax ? Math.round((this.subtotalAfterDiscount + this.serviceAmount) * (this.taxRate / 100)) : 0;
                },

                get grandTotal() {
                    return this.subtotalAfterDiscount + this.serviceAmount + this.taxAmount;
                },

                get totalItemCount() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },

                get cashChange() {
                    return (this.cashReceived || 0) - this.grandTotal;
                },

                addToCart(product) {
                    if (product.manage_stock && product.stock <= 0) {
                        Toast.fire({ icon: 'error', title: 'Stok produk fisik habis!' });
                        return;
                    }

                    const existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        if (!product.manage_stock || existing.qty < product.stock) {
                            existing.qty++;
                            playBeep('beep');
                        } else {
                            Toast.fire({ icon: 'warning', title: 'Maksimal stok tercapai!' });
                        }
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            stock: product.stock,
                            manage_stock: product.manage_stock,
                            qty: 1
                        });
                        playBeep('beep');
                    }
                },

                increaseQty(index) {
                    const item = this.cart[index];
                    if (!item.manage_stock || item.qty < item.stock) {
                        item.qty++;
                        playBeep('beep');
                    } else {
                        Toast.fire({ icon: 'warning', title: 'Stok tidak mencukupi!' });
                    }
                },

                decreaseQty(index) {
                    if (this.cart[index].qty > 1) {
                        this.cart[index].qty--;
                        playBeep('beep');
                    } else {
                        this.removeFromCart(index);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    this.cart = [];
                    this.discountPercent = 0;
                    this.customerName = '';
                },

                handleBarcodeScan() {
                    if (!this.searchQuery) return;
                    const found = this.allProducts.find(p => 
                        (p.barcode && p.barcode.toLowerCase() === this.searchQuery.toLowerCase().trim()) ||
                        (p.sku && p.sku.toLowerCase() === this.searchQuery.toLowerCase().trim())
                    );

                    if (found) {
                        this.addToCart(found);
                        this.searchQuery = '';
                    }
                },

                triggerCheckout() {
                    if (this.cart.length === 0) return;

                    // Mandatory customer name verification
                    if (!this.customerName || this.customerName.trim() === '') {
                        this.openCustomerModal = true;
                        this.$nextTick(() => {
                            const el = document.getElementById('customerNameInput');
                            if (el) el.focus();
                        });
                    } else {
                        this.openPaymentModal();
                    }
                },

                confirmCustomerName() {
                    if (!this.customerName || this.customerName.trim() === '') {
                        Toast.fire({ icon: 'warning', title: 'Nama pelanggan wajib diisi!' });
                        return;
                    }
                    this.openCustomerModal = false;
                    this.openPaymentModal();
                },

                setCustomerPreset(preset) {
                    this.customerName = preset;
                },

                openPaymentModal() {
                    this.cashReceived = this.grandTotal;
                    this.showPaymentModal = true;
                    this.mobileCartOpen = false;
                },

                setCash(amount) {
                    this.cashReceived = amount;
                    playBeep('beep');
                },

                numpadPress(val) {
                    let cur = String(this.cashReceived || '');
                    if (cur === '0' || cur === String(this.grandTotal)) cur = '';
                    this.cashReceived = parseInt(cur + val) || 0;
                    playBeep('beep');
                },

                numpadBackspace() {
                    let cur = String(this.cashReceived || '');
                    this.cashReceived = parseInt(cur.slice(0, -1)) || 0;
                    playBeep('beep');
                },

                submitCheckout() {
                    if (!this.customerName || this.customerName.trim() === '') {
                        this.showPaymentModal = false;
                        this.openCustomerModal = true;
                        return;
                    }

                    if (this.paymentMethod === 'CASH' && this.cashChange < 0) {
                        Toast.fire({ icon: 'error', title: 'Nominal uang diterima kurang!' });
                        return;
                    }

                    document.getElementById('checkoutForm').submit();
                },

                holdCurrentOrder() {
                    if (this.cart.length === 0) return;
                    this.heldOrders.push({
                        customerName: this.customerName || 'Pelanggan #' + (this.heldOrders.length + 1),
                        items: [...this.cart],
                        discount: this.discount,
                        total: this.grandTotal,
                        time: new Date().toLocaleTimeString('id-ID')
                    });
                    localStorage.setItem('pos_held_orders', JSON.stringify(this.heldOrders));
                    this.clearCart();
                    this.mobileCartOpen = false;
                    Toast.fire({ icon: 'success', title: 'Pesanan berhasil ditahan!' });
                },

                resumeHeldOrder(index) {
                    const held = this.heldOrders[index];
                    this.cart = held.items;
                    this.customerName = held.customerName;
                    this.discount = held.discount;
                    this.heldOrders.splice(index, 1);
                    localStorage.setItem('pos_held_orders', JSON.stringify(this.heldOrders));
                    this.openHoldModal = false;
                    Toast.fire({ icon: 'info', title: 'Pesanan dilanjutkan' });
                },

                deleteHeldOrder(index) {
                    this.heldOrders.splice(index, 1);
                    localStorage.setItem('pos_held_orders', JSON.stringify(this.heldOrders));
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        document.exitFullscreen().catch(() => {});
                    }
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            }));
        });
    </script>
</x-app-layout>
