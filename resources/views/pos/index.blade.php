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

                <!-- Clock, Shift Status, Held Orders & Fullscreen -->
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-between sm:justify-end">
                    <!-- Live Clock -->
                    <div class="flex items-center space-x-1.5 px-2.5 py-1.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-600">
                        <i class="fa-regular fa-clock text-blue-500"></i>
                        <span x-text="liveTime"></span>
                    </div>

                    <!-- Cashier Shift Status Button (If Feature Enabled) -->
                    @if($featureSettings['enable_shifts'])
                        @if($activeShift)
                            <button 
                                @click="openCloseShiftModal = true" 
                                class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-[11px] font-bold transition flex items-center space-x-1.5 shadow-sm cursor-pointer"
                                title="Shift Kasir Aktif - Klik untuk Tutup Shift"
                            >
                                <i class="fa-solid fa-circle text-[7px] text-emerald-500 animate-pulse"></i>
                                <span>Shift Kasir</span>
                                <span class="text-[10px] text-emerald-600 font-extrabold hidden md:inline">#{{ $activeShift->id }}</span>
                            </button>

                            @if($featureSettings['enable_petty_cash'])
                                <!-- Petty Cash In/Out Button -->
                                <button 
                                    @click="openMovementModal = true" 
                                    class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 rounded-xl text-[11px] font-bold transition flex items-center space-x-1.5 shadow-sm cursor-pointer"
                                    title="Catat Kas Masuk / Kas Keluar Operasional (Petty Cash)"
                                >
                                    <i class="fa-solid fa-money-bill-transfer text-indigo-600"></i>
                                    <span class="hidden md:inline">Kas Masuk/Keluar</span>
                                    <span class="md:hidden">Petty Kas</span>
                                </button>
                            @endif
                        @else
                            <button 
                                @click="openShiftModal = true" 
                                class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-[11px] font-bold transition flex items-center space-x-1.5 shadow-sm cursor-pointer"
                                title="Shift Kasir Belum Dibuka"
                            >
                                <i class="fa-solid fa-lock text-amber-600"></i>
                                <span>Buka Shift</span>
                            </button>
                        @endif
                    @endif

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
            <div class="px-4 py-2 bg-blue-50/50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-user-tag text-blue-500 text-xs"></i>
                    <div class="text-xs">
                        <div class="font-bold text-slate-800 flex items-center space-x-1.5">
                            <span x-text="customerName ? customerName : 'Pelanggan: Belum Diisi'"></span>
                            <template x-if="selectedCustomerId">
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-coins text-amber-600 text-[8px]"></i>
                                    <span x-text="selectedCustomerPoints + ' pts'"></span>
                                </span>
                            </template>
                        </div>
                        <template x-if="selectedCustomerId && grandTotal >= 10000">
                            <div class="text-[9px] text-emerald-600 font-bold">
                                +<span x-text="Math.floor(grandTotal / 10000)"></span> pts didapat dari order ini
                            </div>
                        </template>
                    </div>
                </div>
                <div class="flex items-center space-x-1.5">
                    <button @click="openCustomerModal = true" class="text-[11px] font-extrabold text-blue-600 hover:underline">
                        <span x-text="customerName ? 'Ganti' : '+ Member / Meja'"></span>
                    </button>
                    <template x-if="customerName">
                        <button @click="resetCustomer()" class="text-slate-400 hover:text-red-500 ml-0.5" title="Hapus Pelanggan">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Dine In / Take Away Order Type Selector (Only if enableOrderTypes is true) -->
            <template x-if="enableOrderTypes">
                <div class="px-3.5 py-2 bg-slate-100/70 border-b border-slate-200/80">
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/80 rounded-xl">
                        <button 
                            type="button" 
                            @click="orderType = 'DINE_IN'" 
                            :class="orderType === 'DINE_IN' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="py-1.5 px-2 rounded-lg text-xs font-extrabold transition-all flex items-center justify-center space-x-1.5 select-none cursor-pointer"
                        >
                            <i class="fa-solid fa-utensils text-[11px]"></i>
                            <span>Dine In</span>
                        </button>
                        <button 
                            type="button" 
                            @click="orderType = 'TAKE_AWAY'" 
                            :class="orderType === 'TAKE_AWAY' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="py-1.5 px-2 rounded-lg text-xs font-extrabold transition-all flex items-center justify-center space-x-1.5 select-none cursor-pointer"
                        >
                            <i class="fa-solid fa-bag-shopping text-[11px]"></i>
                            <span>Take Away</span>
                        </button>
                    </div>
                </div>
            </template>

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
                            <!-- Item Notes (Only for items without stock management / cafe items) -->
                            <template x-if="!item.manage_stock">
                                <div class="mt-1">
                                    <button 
                                        type="button" 
                                        @click="openNoteEditor(index)" 
                                        class="text-[10px] px-2 py-0.5 rounded-md font-bold transition flex items-center space-x-1"
                                        :class="item.notes ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 hover:bg-slate-200 text-blue-600'"
                                    >
                                        <i class="fa-solid fa-pen text-[8px]"></i>
                                        <span x-text="item.notes ? 'Note: ' + item.notes : '+ Catatan Menu'"></span>
                                    </button>
                                </div>
                            </template>
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
                <!-- Subtotal, Discount, Service, Tax Breakdown -->
                <div class="space-y-2 text-xs">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-slate-500 font-semibold">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-800" x-text="'Rp ' + formatNumber(subtotal)"></span>
                    </div>

                    <!-- Kupon & Diskon Trigger Row -->
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            <i class="fa-solid fa-ticket text-blue-600 text-xs"></i>
                            <span class="font-bold text-slate-700">Kupon & Diskon</span>
                        </div>

                        <!-- 1. Coupon Applied Status -->
                        <template x-if="appliedCoupon">
                            <div class="flex items-center space-x-1.5">
                                <span class="text-xs font-black text-blue-700" x-text="'-Rp ' + formatNumber(discountAmount)"></span>
                                <button 
                                    type="button" 
                                    @click="openDiscountModal = true" 
                                    style="background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;"
                                    class="text-[10px] px-2 py-0.5 rounded-md font-mono font-black hover:bg-blue-200 transition"
                                    x-text="appliedCoupon.coupon_code"
                                ></button>
                                <button type="button" @click="removeCoupon()" class="text-slate-400 hover:text-red-500 text-xs transition" title="Hapus Kupon">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>

                        <!-- 2. Manual Discount Applied Status -->
                        <template x-if="!appliedCoupon && discountPercent > 0">
                            <div class="flex items-center space-x-1.5">
                                <span class="text-xs font-black text-red-600" x-text="'-Rp ' + formatNumber(discountAmount)"></span>
                                <button 
                                    type="button" 
                                    @click="openDiscountModal = true" 
                                    class="text-[10px] px-2 py-0.5 bg-red-100 text-red-800 border border-red-200 rounded-md font-bold hover:bg-red-200 transition"
                                    x-text="discountPercent + '%'"
                                ></button>
                                <button type="button" @click="discountPercent = 0" class="text-slate-400 hover:text-red-500 text-xs transition" title="Hapus Diskon">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </template>

                        <!-- 3. No Discount Applied Status -->
                        <template x-if="!appliedCoupon && discountPercent === 0">
                            <button 
                                type="button" 
                                @click="openDiscountModal = true" 
                                class="text-xs font-extrabold text-blue-600 hover:text-blue-700 hover:underline flex items-center space-x-1 transition"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Pilih / Scan Kupon</span>
                            </button>
                        </template>
                    </div>

                    @if($featureSettings['enable_points'])
                    <!-- Tukar Poin Member Row (Feature 2) -->
                    <template x-if="selectedCustomerId && selectedCustomerPoints > 0">
                        <div class="pt-2 border-t border-slate-200/60 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-1.5">
                                    <i class="fa-solid fa-coins text-amber-500 text-xs"></i>
                                    <span class="font-bold text-slate-700">Tukar Poin Member</span>
                                    <span class="text-[10px] text-slate-400 font-semibold">(Ada <span x-text="selectedCustomerPoints"></span>)</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="redeemPointsEnabled" @change="onToggleRedeemPoints()" class="sr-only peer">
                                    <div class="w-7 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                            </div>
                            <template x-if="redeemPointsEnabled">
                                <div class="p-2 bg-amber-50/80 border border-amber-200 rounded-xl space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-amber-900 font-medium text-[11px]">Tukar (1 pt = Rp 1.000):</span>
                                        <div class="flex items-center space-x-1">
                                            <input 
                                                type="number" 
                                                x-model.number="pointsToRedeem" 
                                                @input="sanitizePointsToRedeem()"
                                                min="1" 
                                                :max="maxRedeemablePoints"
                                                class="w-16 px-1.5 py-0.5 bg-white border border-amber-300 rounded-lg text-xs font-bold text-right text-amber-900 focus:ring-1 focus:ring-amber-500 focus:outline-none"
                                            />
                                            <button 
                                                type="button" 
                                                @click="pointsToRedeem = maxRedeemablePoints"
                                                class="px-1.5 py-0.5 bg-amber-200 hover:bg-amber-300 text-amber-900 font-extrabold rounded-md text-[10px]"
                                            >Max</button>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center text-xs font-bold text-amber-900 pt-1 border-t border-amber-200/60">
                                        <span>Potongan Poin:</span>
                                        <span class="text-emerald-700" x-text="'-Rp ' + formatNumber(pointsDiscountAmount)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    @endif

                    <!-- Service Charge (If Enabled) -->
                    <template x-if="enableService">
                        <div class="flex justify-between items-center text-slate-500 font-semibold pt-1 border-t border-slate-200/60">
                            <div class="flex items-center space-x-1.5">
                                <span x-text="'Biaya Layanan (' + serviceRate + '%)'"></span>
                                <template x-if="isTakeAway && !serviceChargeOnTakeaway">
                                    <span class="text-[9px] px-1.5 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold rounded-md">Bebas Take Away</span>
                                </template>
                            </div>
                            <span class="font-bold" :class="isTakeAway && !serviceChargeOnTakeaway ? 'text-emerald-600' : 'text-slate-800'" x-text="(isTakeAway && !serviceChargeOnTakeaway) ? 'Rp 0' : '+Rp ' + formatNumber(serviceAmount)"></span>
                        </div>
                    </template>

                    <!-- PPN Tax (If Enabled) -->
                    <template x-if="enableTax">
                        <div class="flex justify-between items-center text-slate-500 font-semibold">
                            <div class="flex items-center space-x-1.5">
                                <span x-text="'Pajak PPN (' + taxRate + '%)'"></span>
                                <template x-if="isTakeAway && !taxOnTakeaway">
                                    <span class="text-[9px] px-1.5 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold rounded-md">Bebas PPN</span>
                                </template>
                            </div>
                            <span class="font-bold" :class="isTakeAway && !taxOnTakeaway ? 'text-emerald-600' : 'text-slate-800'" x-text="(isTakeAway && !taxOnTakeaway) ? 'Rp 0' : '+Rp ' + formatNumber(taxAmount)"></span>
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


        <!-- MODAL 1: WAJIB NAMA PELANGGAN / MEMBER CRM (Customer & Loyalty Member Modal) -->
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
            <div @click.away="openCustomerModal = false" class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden space-y-4">
                <div class="p-6 pb-2 text-center space-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl mx-auto mb-2 shadow-inner">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-slate-900">Identitas Pelanggan & Member CRM</h3>
                    <p class="text-xs text-slate-500 font-medium">Pilih member terdaftar untuk kumpulkan poin atau masukkan identitas meja</p>
                </div>

                <!-- Tabs: Member CRM vs Guest / Meja -->
                <div class="px-6">
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-2xl">
                        <button 
                            type="button" 
                            @click="customerTab = 'member'"
                            :class="customerTab === 'member' ? 'bg-white text-blue-700 shadow-sm font-extrabold' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="py-2 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1.5"
                        >
                            <i class="fa-solid fa-crown text-amber-500 text-[11px]"></i>
                            <span>Member CRM (Poin)</span>
                        </button>
                        <button 
                            type="button" 
                            @click="customerTab = 'guest'"
                            :class="customerTab === 'guest' ? 'bg-white text-blue-700 shadow-sm font-extrabold' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="py-2 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1.5"
                        >
                            <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                            <span>Tamu / No. Meja</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 pb-6 space-y-4">
                    <!-- TAB 1: MEMBER CRM -->
                    <div x-show="customerTab === 'member'" class="space-y-3">
                        <!-- Search Bar -->
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                type="text" 
                                x-model="customerSearch" 
                                placeholder="Cari nama member atau nomor HP/WA..." 
                                class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                            >
                        </div>

                        <!-- Member List Scrollable -->
                        <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 divide-y divide-slate-100">
                            <template x-for="m in filteredCustomerList" :key="m.id">
                                <div class="p-2.5 bg-slate-50 hover:bg-blue-50/70 border border-slate-200/70 rounded-xl flex items-center justify-between transition cursor-pointer" @click="selectMember(m)">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-xs">
                                            <span x-text="m.name.charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <div class="text-xs font-extrabold text-slate-900" x-text="m.name"></div>
                                            <div class="text-[10px] text-slate-500 font-mono" x-text="m.phone || 'Tanpa No. HP'"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-md text-[10px] font-extrabold">
                                            <i class="fa-solid fa-coins text-amber-500 text-[8px] mr-0.5"></i>
                                            <span x-text="m.points + ' pts'"></span>
                                        </span>
                                        <button type="button" class="px-2.5 py-1 bg-blue-600 text-white rounded-lg text-[10px] font-extrabold hover:bg-blue-700 transition">
                                            Pilih
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="filteredCustomerList.length === 0">
                                <div class="py-6 text-center text-slate-400">
                                    <p class="text-xs font-semibold">Tidak menemukan member dengan kata kunci tersebut.</p>
                                </div>
                            </template>
                        </div>

                        <!-- Quick Register Toggle -->
                        <div class="pt-2 border-t border-slate-100">
                            <button 
                                type="button" 
                                @click="showNewMemberForm = !showNewMemberForm" 
                                class="text-xs font-extrabold text-blue-600 hover:text-blue-700 flex items-center space-x-1.5"
                            >
                                <i class="fa-solid" :class="showNewMemberForm ? 'fa-minus' : 'fa-plus'"></i>
                                <span x-text="showNewMemberForm ? 'Tutup Pendaftaran Cepat' : '+ Daftarkan Member Baru Cepat'"></span>
                            </button>

                            <!-- Quick Form -->
                            <div x-show="showNewMemberForm" class="mt-3 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2.5">
                                <div class="text-[11px] font-bold text-slate-700">Form Pendaftaran Member Cepat:</div>
                                <input 
                                    type="text" 
                                    x-model="newMemberName" 
                                    placeholder="Nama Lengkap Member *" 
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold"
                                >
                                <input 
                                    type="text" 
                                    x-model="newMemberPhone" 
                                    placeholder="Nomor Telepon / WhatsApp (Opsional)" 
                                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold font-mono"
                                >
                                <div class="flex justify-end pt-1">
                                    <button 
                                        type="button" 
                                        @click="quickRegisterMember()" 
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-sm transition flex items-center space-x-1.5"
                                    >
                                        <i class="fa-solid fa-user-check"></i>
                                        <span>Daftarkan & Pilih</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: GUEST / MEJA -->
                    <div x-show="customerTab === 'guest'" class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Tamu / No. Meja (Wajib) *</label>
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

                    <!-- Modal Actions -->
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
                    <div class="grid grid-cols-2 {{ $featureSettings['enable_split_payment'] ? 'md:grid-cols-5' : 'md:grid-cols-4' }} gap-2">
                        <button 
                            @click="selectPaymentMethod('CASH')" 
                            :class="paymentMethod === 'CASH' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-2.5 md:p-3 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 cursor-pointer"
                        >
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base md:text-lg shadow-md shadow-blue-500/20 shrink-0">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-sm font-extrabold">Tunai</div>
                                <div class="text-[9px] md:text-[10px] font-normal text-slate-400">Uang Tunai</div>
                            </div>
                        </button>

                        <button 
                            @click="selectPaymentMethod('QRIS')" 
                            :class="paymentMethod === 'QRIS' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-2.5 md:p-3 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 cursor-pointer"
                        >
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base md:text-lg shadow-md shadow-emerald-500/20 shrink-0">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-sm font-extrabold">QRIS</div>
                                <div class="text-[9px] md:text-[10px] font-normal text-slate-400">Semua E-Wallet</div>
                            </div>
                        </button>

                        <button 
                            @click="selectPaymentMethod('DEBIT')" 
                            :class="paymentMethod === 'DEBIT' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-2.5 md:p-3 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 cursor-pointer"
                        >
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-base md:text-lg shadow-md shadow-indigo-500/20 shrink-0">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-sm font-extrabold">EDC / Debit</div>
                                <div class="text-[9px] md:text-[10px] font-normal text-slate-400">Gesek Kartu</div>
                            </div>
                        </button>

                        <button 
                            @click="selectPaymentMethod('TRANSFER')" 
                            :class="paymentMethod === 'TRANSFER' ? 'bg-blue-50 border-blue-600 text-blue-700 ring-2 ring-blue-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-2.5 md:p-3 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 cursor-pointer"
                        >
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-base md:text-lg shadow-md shadow-teal-500/20 shrink-0">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-sm font-extrabold">Transfer</div>
                                <div class="text-[9px] md:text-[10px] font-normal text-slate-400">Bank Langsung</div>
                            </div>
                        </button>

                        @if($featureSettings['enable_split_payment'])
                        <button 
                            @click="selectPaymentMethod('SPLIT')" 
                            :class="paymentMethod === 'SPLIT' ? 'bg-purple-50 border-purple-600 text-purple-700 ring-2 ring-purple-500' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'"
                            class="p-2.5 md:p-3 rounded-2xl border-2 text-left font-bold transition flex items-center space-x-2.5 cursor-pointer"
                        >
                            <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base md:text-lg shadow-md shadow-purple-500/20 shrink-0">
                                <i class="fa-solid fa-code-branch"></i>
                            </div>
                            <div>
                                <div class="text-xs md:text-sm font-extrabold">Split Bayar</div>
                                <div class="text-[9px] md:text-[10px] font-normal text-slate-400">Pisah Metode</div>
                            </div>
                        </button>
                        @endif
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

                    <!-- DEBIT / EDC SECTION INFO -->
                    <div x-show="paymentMethod === 'DEBIT'" class="p-5 bg-indigo-50/70 rounded-2xl border border-indigo-200 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-2xl mx-auto shadow-inner">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 text-base">Pembayaran EDC / Kartu Debit & Kredit</h4>
                        <p class="text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
                            Gesek / tap kartu debit atau kredit pelanggan pada mesin EDC kasir. Setelah transaksi berhasil disetujui di mesin EDC, tekan tombol <strong>Selesaikan Transaksi</strong>.
                        </p>
                    </div>

                    <!-- TRANSFER SECTION INFO -->
                    <div x-show="paymentMethod === 'TRANSFER'" class="p-5 bg-teal-50/70 rounded-2xl border border-teal-200 text-center space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-2xl mx-auto shadow-inner">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-800 text-base">Pembayaran Transfer Rekening Bank</h4>
                        <p class="text-xs text-slate-600 max-w-sm mx-auto leading-relaxed">
                            Minta pelanggan melakukan transfer ke rekening resmi toko. Pastikan kasir telah memverifikasi bukti transfer atau mutasi rekening sebelum menyelesaikan transaksi.
                        </p>
                    </div>

                    <!-- SPLIT PAYMENT SECTION (Feature 3) -->
                    <div x-show="paymentMethod === 'SPLIT'" class="space-y-4">
                        <div class="p-4 bg-purple-50/70 rounded-2xl border border-purple-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shadow-sm">
                                        <i class="fa-solid fa-code-branch"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-purple-950 text-sm">Split Payment (Pisah Pembayaran)</h4>
                                        <p class="text-[11px] text-purple-700">Bagi tagihan ke 2 metode berbeda (misal: Tunai + EDC)</p>
                                    </div>
                                </div>
                                <button type="button" @click="autoBalanceSplit()" class="px-2.5 py-1 bg-purple-200 hover:bg-purple-300 text-purple-900 rounded-lg text-xs font-extrabold transition">
                                    Bagi 50:50
                                </button>
                            </div>

                            <!-- Split Line 1 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 p-3 bg-white rounded-xl border border-purple-100 shadow-sm">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Metode Bagian 1</label>
                                    <select x-model="splitMethod1" @change="onSplitMethodChange()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-extrabold focus:border-purple-600 focus:bg-white focus:ring-0 transition">
                                        <option value="CASH">Tunai (Cash)</option>
                                        <option value="DEBIT">Kartu EDC / Debit</option>
                                        <option value="TRANSFER">Transfer Bank</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nominal Bagian 1 (Rp)</label>
                                    <input 
                                        type="number" 
                                        x-model.number="splitAmount1" 
                                        @input="onSplitAmount1Change()"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 text-right focus:border-purple-600 focus:bg-white focus:ring-0 transition"
                                    >
                                </div>
                            </div>

                            <!-- Split Line 2 -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 p-3 bg-white rounded-xl border border-purple-100 shadow-sm">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Metode Bagian 2</label>
                                    <select x-model="splitMethod2" @change="onSplitMethodChange()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-extrabold focus:border-purple-600 focus:bg-white focus:ring-0 transition">
                                        <option value="DEBIT">Kartu EDC / Debit</option>
                                        <option value="TRANSFER">Transfer Bank</option>
                                        <option value="CASH">Tunai (Cash)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nominal Bagian 2 (Rp)</label>
                                    <input 
                                        type="number" 
                                        x-model.number="splitAmount2" 
                                        @input="onSplitAmount2Change()"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 text-right focus:border-purple-600 focus:bg-white focus:ring-0 transition"
                                    >
                                </div>
                            </div>

                            <!-- Split Balance Indicator -->
                            <div class="p-3 rounded-xl border flex items-center justify-between text-xs"
                                :class="splitDifference === 0 ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-rose-50 border-rose-300 text-rose-900'">
                                <div class="flex items-center space-x-2 font-bold">
                                    <i class="fa-solid" :class="splitDifference === 0 ? 'fa-check-circle text-emerald-600' : 'fa-triangle-exclamation text-rose-600'"></i>
                                    <span x-text="splitDifference === 0 ? 'Jumlah Pembayaran Pas!' : (splitDifference > 0 ? 'Kurang Rp ' + formatNumber(splitDifference) : 'Lebih Rp ' + formatNumber(Math.abs(splitDifference)))"></span>
                                </div>
                                <div class="font-mono font-extrabold">
                                    Total Split: Rp <span x-text="formatNumber(splitTotal)"></span> / <span x-text="formatNumber(grandTotal)"></span>
                                </div>
                            </div>

                            <!-- Cash Portion Received Input (If either split method is CASH) -->
                            <template x-if="splitCashPortion > 0">
                                <div class="p-3 bg-white rounded-xl border border-purple-200 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <label class="font-extrabold text-slate-800">Uang Tunai Diterima Kasir (Rp):</label>
                                        <span class="text-[11px] text-purple-700 font-bold">Porsi Tunai: Rp <span x-text="formatNumber(splitCashPortion)"></span></span>
                                    </div>
                                    <input 
                                        type="number" 
                                        x-model.number="cashReceived" 
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-base font-extrabold text-slate-900 focus:border-purple-600 focus:bg-white focus:ring-0 transition"
                                    >
                                    <div class="flex justify-between items-center text-xs font-bold pt-1 border-t border-slate-100">
                                        <span class="text-slate-600">Kembalian Tunai:</span>
                                        <span class="font-mono text-emerald-600 font-extrabold" x-text="cashChange >= 0 ? 'Rp ' + formatNumber(cashChange) : 'Kurang Rp ' + formatNumber(Math.abs(cashChange))"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden Form for submission -->
                    <form id="checkoutForm" action="{{ route('pos.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="items" :value="JSON.stringify(cart)">
                        <input type="hidden" name="payment_method" :value="paymentMethod">
                        <input type="hidden" name="order_type" :value="enableOrderTypes ? orderType : ''">
                        <input type="hidden" name="customer_name" :value="customerName">
                        <input type="hidden" name="customer_id" :value="selectedCustomerId">
                        <input type="hidden" name="discount" :value="discountAmount">
                        <input type="hidden" name="discount_percent" :value="discountPercent">
                        <input type="hidden" name="coupon_code" :value="appliedCoupon ? appliedCoupon.coupon_code : ''">
                        <input type="hidden" name="points_to_redeem" :value="redeemPointsEnabled ? pointsToRedeem : 0">
                        <input type="hidden" name="split_method_1" :value="splitMethod1">
                        <input type="hidden" name="split_amount_1" :value="splitAmount1">
                        <input type="hidden" name="split_method_2" :value="splitMethod2">
                        <input type="hidden" name="split_amount_2" :value="splitAmount2">
                        <input type="hidden" name="service" :value="serviceAmount">
                        <input type="hidden" name="service_percent" :value="activeServiceRate">
                        <input type="hidden" name="tax" :value="taxAmount">
                        <input type="hidden" name="tax_percent" :value="activeTaxRate">
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
                        :disabled="(paymentMethod === 'CASH' && cashChange < 0) || (paymentMethod === 'SPLIT' && (splitDifference !== 0 || (splitCashPortion > 0 && cashChange < 0)))"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs md:text-sm rounded-xl shadow-lg shadow-blue-600/30 transition active:scale-95 disabled:opacity-50 disabled:pointer-events-none flex items-center space-x-2"
                    >
                        <i class="fa-solid fa-circle-check"></i>
                        <span x-text="paymentMethod === 'CASH' ? 'Proses & Cetak Struk' : (paymentMethod === 'QRIS' ? 'Lanjutkan ke QRIS' : (paymentMethod === 'DEBIT' ? 'Selesaikan Bayar EDC' : (paymentMethod === 'TRANSFER' ? 'Selesaikan Bayar Transfer' : 'Selesaikan Split Payment')))"></span>
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


        <!-- MODAL 4: ITEM NOTES EDITOR (Khusus menu tanpa stok / cafe) -->
        <div 
            x-show="openNoteModal" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div @click.away="openNoteModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4 text-left">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-pen-to-square text-amber-500"></i>
                        <span>Catatan: <span class="text-blue-600" x-text="editingItemName"></span></span>
                    </h3>
                    <button @click="openNoteModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Note Input -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Request Khusus Dapur / Bar</label>
                    <input 
                        type="text" 
                        x-model="itemNoteInput" 
                        id="itemNoteInputField"
                        @keydown.enter.prevent="saveItemNote()"
                        placeholder="Contoh: Less Sugar, Es Sedikit, Tanpa Bawang" 
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500 transition"
                    >
                </div>

                <!-- Quick Note Presets -->
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Preset Cepat:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" @click="appendNotePreset('Less Sugar')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Less Sugar</button>
                        <button type="button" @click="appendNotePreset('Normal Sugar')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Normal Sugar</button>
                        <button type="button" @click="appendNotePreset('Es Sedikit')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Es Sedikit</button>
                        <button type="button" @click="appendNotePreset('Tanpa Es')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Tanpa Es</button>
                        <button type="button" @click="appendNotePreset('Panas')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Panas</button>
                        <button type="button" @click="appendNotePreset('Pedas Sedang')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Pedas Sedang</button>
                        <button type="button" @click="appendNotePreset('Ekstra Pedas')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Ekstra Pedas</button>
                        <button type="button" @click="appendNotePreset('Tanpa Bawang')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Tanpa Bawang</button>
                        <button type="button" @click="appendNotePreset('Pisah Sambal')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">Pisah Sambal</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="itemNoteInput = ''; saveItemNote();" class="text-xs font-bold text-red-500 hover:underline">
                        Hapus Catatan
                    </button>
                    <div class="flex space-x-2">
                        <button type="button" @click="openNoteModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Batal
                        </button>
                        <button type="button" @click="saveItemNote()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- MODAL 5: KUPON, VOUCHER PROMO & DISKON MANUAL -->
        <div 
            x-show="openDiscountModal" 
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        >
            <div @click.away="closeDiscountModal()" class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden text-left">
                <!-- Header -->
                <div class="p-5 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-blue-600/30">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm">Kupon & Diskon Tagihan</h3>
                            <p class="text-[11px] text-slate-400">Pilih voucher diskon promo atau tentukan diskon manual</p>
                        </div>
                    </div>
                    <button @click="closeDiscountModal()" class="text-slate-400 hover:text-white text-lg transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="flex border-b border-slate-200 bg-slate-50 px-4 pt-2">
                    <button 
                        type="button" 
                        @click="discountTab = 'coupon'" 
                        class="px-4 py-2.5 text-xs font-extrabold border-b-2 transition flex items-center space-x-1.5"
                        :class="discountTab === 'coupon' ? 'border-blue-600 text-blue-600 bg-white rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i class="fa-solid fa-ticket-simple"></i>
                        <span>Voucher & QR Kupon</span>
                    </button>
                    <button 
                        type="button" 
                        @click="discountTab = 'manual'" 
                        class="px-4 py-2.5 text-xs font-extrabold border-b-2 transition flex items-center space-x-1.5"
                        :class="discountTab === 'manual' ? 'border-blue-600 text-blue-600 bg-white rounded-t-xl' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    >
                        <i class="fa-solid fa-percent"></i>
                        <span>Diskon Manual (%)</span>
                    </button>
                </div>

                <!-- Tab 1: Voucher & QR Scan -->
                <div x-show="discountTab === 'coupon'" x-cloak class="p-5 space-y-4 max-h-[70vh] overflow-y-auto" style="background-color: #f8fafc;">
                    <!-- Manual Typing & Camera Trigger -->
                    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider" style="color: #0f172a;">Input / Scan Kode Voucher</label>
                        <div class="flex space-x-2">
                            <div class="relative flex-1">
                                <input 
                                    type="text" 
                                    x-model="couponCodeInput" 
                                    @keydown.enter.prevent="applyCoupon()" 
                                    placeholder="Contoh: COFFEE2026 / HEMAT20K" 
                                    style="background-color: #ffffff; color: #1e3a8a; border: 2px solid #cbd5e1;"
                                    class="w-full pl-3.5 pr-2 py-2.5 rounded-xl text-xs font-black uppercase placeholder:text-slate-400 focus:outline-none focus:border-blue-600 transition"
                                >
                            </div>
                            <button 
                                type="button" 
                                @click="applyCoupon()" 
                                :disabled="!couponCodeInput || couponLoading"
                                style="background-color: #2563eb; color: #ffffff;"
                                class="px-5 py-2.5 text-xs font-black rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 disabled:opacity-50 flex items-center space-x-1 cursor-pointer hover:bg-blue-700"
                            >
                                <span x-show="!couponLoading">Pakai</span>
                                <span x-show="couponLoading"><i class="fa-solid fa-spinner animate-spin"></i></span>
                            </button>
                            <button 
                                type="button" 
                                @click="toggleQrScanner()" 
                                class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition shadow-sm flex items-center space-x-1.5 cursor-pointer"
                                :style="isScanningQr ? 'background-color: #dc2626; color: #ffffff;' : 'background-color: #0f172a; color: #ffffff;'"
                                title="Buka Kamera untuk Scan QR"
                            >
                                <i class="fa-solid" :class="isScanningQr ? 'fa-video-slash' : 'fa-camera'"></i>
                                <span x-text="isScanningQr ? 'Tutup' : 'Scan QR'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Camera Scanner Box -->
                    <div x-show="isScanningQr" x-cloak class="p-4 bg-slate-900 rounded-2xl text-center space-y-3 shadow-lg">
                        <div class="text-white text-xs font-black flex items-center justify-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                            <span class="tracking-wide uppercase">Arahkan Kamera ke QR Code Voucher</span>
                        </div>
                        <div id="qr-camera-reader" class="w-full max-w-xs mx-auto overflow-hidden rounded-2xl bg-black border-2 border-blue-500 shadow-inner"></div>
                        <p class="text-[11px] text-slate-300 font-medium">Pindai QR kupon yang dicetak atau dari layar HP customer.</p>
                    </div>

                    <!-- Available Active Coupons List -->
                    <div class="space-y-2.5 pt-1">
                        <div class="flex items-center justify-between px-1">
                            <span class="text-xs font-black uppercase tracking-wider" style="color: #0f172a;">Voucher Tersedia</span>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full" style="background-color: #dbeafe; color: #1e40af;" x-text="activeCoupons.length + ' Kupon Aktif'"></span>
                        </div>

                        <div class="space-y-2.5">
                            <template x-if="activeCoupons.length === 0">
                                <div class="p-6 bg-white border border-slate-200 rounded-2xl text-center text-slate-400 text-xs">
                                    <i class="fa-solid fa-ticket text-2xl mb-1 text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Tidak ada voucher aktif</p>
                                    <p class="text-[11px] text-slate-400">Buat voucher promo baru di menu Admin Kupon.</p>
                                </div>
                            </template>

                            <template x-for="cp in activeCoupons" :key="cp.id">
                                <div 
                                    class="p-4 rounded-2xl transition-all flex items-center justify-between shadow-sm"
                                    :style="appliedCoupon && appliedCoupon.coupon_code === cp.code ? 'background-color: #eff6ff; border: 2px solid #2563eb; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);' : 'background-color: #ffffff; border: 2px solid #e2e8f0;'"
                                >
                                    <!-- Coupon Details -->
                                    <div class="space-y-1.5 pr-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <!-- Code Badge with Signature Royal Blue Background & White Text -->
                                            <span 
                                                style="background-color: #2563eb; color: #ffffff; font-family: monospace; font-weight: 900;" 
                                                class="px-3 py-1 text-xs rounded-lg shadow-sm inline-block" 
                                                x-text="cp.code"
                                            ></span>
                                            
                                            <!-- Category Badge with High Contrast -->
                                            <template x-if="cp.category">
                                                <span 
                                                    style="background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;" 
                                                    class="px-2.5 py-0.5 font-black text-[10px] rounded-md inline-flex items-center space-x-1"
                                                >
                                                    <i class="fa-solid fa-filter text-[9px]"></i>
                                                    <span x-text="cp.category.name"></span>
                                                </span>
                                            </template>
                                            <template x-if="!cp.category">
                                                <span 
                                                    style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;" 
                                                    class="px-2.5 py-0.5 font-black text-[10px] rounded-md inline-flex items-center space-x-1"
                                                >
                                                    <i class="fa-solid fa-layer-group text-[9px]"></i>
                                                    <span>Semua Menu</span>
                                                </span>
                                            </template>
                                        </div>

                                        <h5 class="font-extrabold text-xs" style="color: #0f172a;" x-text="cp.title"></h5>

                                        <div class="flex items-center space-x-2 text-[11px]">
                                            <span 
                                                style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;" 
                                                class="font-black px-2 py-0.5 rounded-md inline-block" 
                                                x-text="cp.discount_type === 'PERCENT' ? 'Hemat ' + parseFloat(cp.discount_value) + '%' : 'Potongan Rp ' + formatNumber(cp.discount_value)"
                                            ></span>
                                            <span style="color: #64748b; font-weight: 600;" x-show="cp.min_order_amount > 0" x-text="'Min. Belanja Rp ' + formatNumber(cp.min_order_amount)"></span>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="flex-shrink-0">
                                        <template x-if="appliedCoupon && appliedCoupon.coupon_code === cp.code">
                                            <span 
                                                style="background-color: #059669; color: #ffffff;" 
                                                class="px-3.5 py-2 font-black text-xs rounded-xl shadow-md flex items-center space-x-1.5"
                                            >
                                                <i class="fa-solid fa-circle-check text-sm"></i>
                                                <span>Terpasang</span>
                                            </span>
                                        </template>
                                        <template x-if="!appliedCoupon || appliedCoupon.coupon_code !== cp.code">
                                            <button 
                                                type="button" 
                                                @click="applyCouponDirect(cp.code)" 
                                                style="background-color: #2563eb; color: #ffffff;" 
                                                class="px-4 py-2 font-black text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95 flex items-center space-x-1 hover:bg-blue-700 cursor-pointer"
                                            >
                                                <span>Gunakan</span>
                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Diskon Manual -->
                <div x-show="discountTab === 'manual'" x-cloak class="p-6 space-y-4 bg-white">
                    <div style="background-color: #fffbeb; border: 1px solid #fde68a; color: #92400e;" class="p-3.5 rounded-2xl text-xs font-medium flex items-start space-x-2">
                        <i class="fa-solid fa-circle-info text-amber-600 text-sm mt-0.5"></i>
                        <span>Diskon manual memotong total belanja berdasarkan persentase (%). Memasang diskon manual akan menonaktifkan voucher promo.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wider mb-1.5" style="color: #0f172a;">Persentase Diskon (%)</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                x-model.number="tempDiscountPercent" 
                                min="0" 
                                max="100" 
                                placeholder="0" 
                                style="background-color: #f8fafc; border: 2px solid #cbd5e1; color: #dc2626;"
                                class="w-full pl-4 pr-10 py-3 rounded-xl text-lg font-black focus:bg-white focus:outline-none focus:border-red-500 transition"
                            >
                            <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 font-black text-base">%</span>
                        </div>
                    </div>

                    <!-- Quick Preset Pills -->
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Preset Cepat:</span>
                        <div class="grid grid-cols-5 gap-2">
                            <button type="button" @click="tempDiscountPercent = 0" class="py-2.5 rounded-xl text-xs font-black transition cursor-pointer" :style="tempDiscountPercent === 0 ? 'background-color: #0f172a; color: #ffffff;' : 'background-color: #f1f5f9; color: #334155; border: 1px solid #e2e8f0;'">0%</button>
                            <button type="button" @click="tempDiscountPercent = 5" class="py-2.5 rounded-xl text-xs font-black transition cursor-pointer" :style="tempDiscountPercent === 5 ? 'background-color: #dc2626; color: #ffffff;' : 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;'">5%</button>
                            <button type="button" @click="tempDiscountPercent = 10" class="py-2.5 rounded-xl text-xs font-black transition cursor-pointer" :style="tempDiscountPercent === 10 ? 'background-color: #dc2626; color: #ffffff;' : 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;'">10%</button>
                            <button type="button" @click="tempDiscountPercent = 20" class="py-2.5 rounded-xl text-xs font-black transition cursor-pointer" :style="tempDiscountPercent === 20 ? 'background-color: #dc2626; color: #ffffff;' : 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;'">20%</button>
                            <button type="button" @click="tempDiscountPercent = 50" class="py-2.5 rounded-xl text-xs font-black transition cursor-pointer" :style="tempDiscountPercent === 50 ? 'background-color: #dc2626; color: #ffffff;' : 'background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;'">50%</button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button type="button" @click="applyManualDiscount()" style="background-color: #dc2626; color: #ffffff;" class="w-full py-3.5 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition active:scale-95 cursor-pointer">
                            Terapkan Diskon Manual
                        </button>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-white border-t border-slate-200 flex items-center justify-between">
                    <button type="button" @click="clearAllDiscounts()" style="color: #dc2626;" class="text-xs font-black hover:underline cursor-pointer">
                        Hapus Semua Diskon
                    </button>
                    <button type="button" @click="closeDiscountModal()" style="background-color: #0f172a; color: #ffffff;" class="px-6 py-2.5 font-black text-xs rounded-xl shadow-md transition active:scale-95 cursor-pointer hover:bg-slate-800">
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 5: BUKA SHIFT KASIR -->
        <div 
            x-show="openShiftModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;"
        >
            <div @click.away="openShiftModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4">
                <div class="text-center space-y-1">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl mx-auto mb-2 shadow-inner">
                        <i class="fa-solid fa-lock-open"></i>
                    </div>
                    <h3 class="font-extrabold text-lg text-slate-900">Buka Shift Kasir Baru</h3>
                    <p class="text-xs text-slate-500 font-medium">Masukkan uang modal awal (Cash In) di laci kasir sebelum memulai transaksi</p>
                </div>

                <form action="{{ route('pos.shift.open') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 uppercase mb-1">Modal Awal Laci (Rp)</label>
                        <input 
                            type="number" 
                            name="opening_cash" 
                            id="openingCashInput"
                            value="100000" 
                            required 
                            class="w-full text-xl font-extrabold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-2.5 focus:border-blue-600 focus:bg-white focus:ring-0 transition"
                        >
                    </div>

                    <!-- Quick amount presets -->
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 50000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">50.000</button>
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 100000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">100.000</button>
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 200000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">200.000</button>
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 300000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">300.000</button>
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 500000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">500.000</button>
                        <button type="button" onclick="document.getElementById('openingCashInput').value = 1000000" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 transition">1.000.000</button>
                    </div>

                    <div class="flex items-center space-x-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="openShiftModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition active:scale-95">
                            Buka Shift Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 7: KAS MASUK & KAS KELUAR OPERASIONAL (PETTY CASH - Feature 1) -->
        @if($activeShift)
            <div 
                x-show="openMovementModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                style="display: none;"
            >
                <div @click.away="openMovementModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4">
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mx-auto mb-2 shadow-inner">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <h3 class="font-extrabold text-lg text-slate-900">Kas Masuk / Kas Keluar</h3>
                        <p class="text-xs text-slate-500 font-medium">Catat pengeluaran kecil kasir atau penambahan modal kas</p>
                    </div>

                    <form action="{{ route('pos.shift.movement') }}" method="POST" class="space-y-4">
                        @csrf
                        <!-- Movement Type Selection -->
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl">
                            <button 
                                type="button" 
                                @click="movementType = 'CASH_OUT'" 
                                :class="movementType === 'CASH_OUT' ? 'bg-rose-600 text-white shadow-md font-extrabold' : 'text-slate-600 font-bold hover:text-slate-900'"
                                class="py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1.5"
                            >
                                <i class="fa-solid fa-arrow-up-from-bracket text-xs"></i>
                                <span>Kas Keluar (Biaya)</span>
                            </button>
                            <button 
                                type="button" 
                                @click="movementType = 'CASH_IN'" 
                                :class="movementType === 'CASH_IN' ? 'bg-emerald-600 text-white shadow-md font-extrabold' : 'text-slate-600 font-bold hover:text-slate-900'"
                                class="py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center space-x-1.5"
                            >
                                <i class="fa-solid fa-arrow-down-to-bracket text-xs"></i>
                                <span>Kas Masuk (Modal)</span>
                            </button>
                        </div>
                        <input type="hidden" name="type" :value="movementType">

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <input 
                                type="number" 
                                name="amount" 
                                id="movementAmountInput"
                                required 
                                min="100"
                                placeholder="Contoh: 25000"
                                class="w-full text-xl font-extrabold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-2.5 focus:border-indigo-600 focus:bg-white focus:ring-0 transition"
                            >
                        </div>

                        <!-- Quick Nominal Presets -->
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" onclick="document.getElementById('movementAmountInput').value = 10000" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition">10.000</button>
                            <button type="button" onclick="document.getElementById('movementAmountInput').value = 25000" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition">25.000</button>
                            <button type="button" onclick="document.getElementById('movementAmountInput').value = 50000" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition">50.000</button>
                            <button type="button" onclick="document.getElementById('movementAmountInput').value = 100000" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700 transition">100.000</button>
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase mb-1">Keperluan / Keterangan <span class="text-rose-500">*</span></label>
                            <input 
                                type="text" 
                                name="reason" 
                                id="movementReasonInput"
                                required 
                                placeholder="Contoh: Beli es batu, beli gas 3kg, tambah kembalian..."
                                class="w-full text-xs font-semibold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl px-3 py-2.5 focus:border-indigo-600 focus:bg-white focus:ring-0 transition"
                            >
                            <!-- Quick Reason Presets -->
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                <button type="button" onclick="document.getElementById('movementReasonInput').value = 'Beli Es Batu Kristal'" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px] text-slate-600 font-semibold">+ Es Batu</button>
                                <button type="button" onclick="document.getElementById('movementReasonInput').value = 'Beli Tabung Gas Elpiji'" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px] text-slate-600 font-semibold">+ Gas Elpiji</button>
                                <button type="button" onclick="document.getElementById('movementReasonInput').value = 'Beli Plastik Kresek / Kantong'" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px] text-slate-600 font-semibold">+ Kantong/Plastik</button>
                                <button type="button" onclick="document.getElementById('movementReasonInput').value = 'Tambah Uang Pecahan Kembalian'" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 rounded text-[10px] text-slate-600 font-semibold">+ Pecahan Kembalian</button>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openMovementModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-indigo-600/30 transition active:scale-95">
                                Simpan Kas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- MODAL 6: TUTUP SHIFT & REKONSILIASI KAS -->
        @if($activeShift)
            <div 
                x-show="openCloseShiftModal" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                style="display: none;"
            >
                <div @click.away="openCloseShiftModal = false" class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 p-6 space-y-4">
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-2 shadow-inner">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h3 class="font-extrabold text-lg text-slate-900">Tutup Shift & Rekonsiliasi Kas</h3>
                        <p class="text-xs text-slate-500 font-medium">Hitung uang fisik di laci kasir dan bandingkan dengan sistem</p>
                    </div>

                    <!-- Summary Info Box -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Kasir:</span>
                            <strong class="text-slate-800">{{ $activeShift->user ? $activeShift->user->name : Auth::user()->name }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Waktu Buka:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $activeShift->opened_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600 border-t border-slate-200 pt-2">
                            <span>Modal Awal (Cash In):</span>
                            <strong class="font-mono">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Penjualan Tunai:</span>
                            <strong class="font-mono text-emerald-600">+Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</strong>
                        </div>
                        @php
                            $shiftCashIn = $activeShift->totalCashIn();
                            $shiftCashOut = $activeShift->totalCashOut();
                            $expectedCash = $activeShift->opening_cash + $activeShift->cash_sales + $shiftCashIn - $shiftCashOut;
                        @endphp
                        @if($shiftCashIn > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Kas Masuk Operasional:</span>
                                <strong class="font-mono text-emerald-600">+Rp {{ number_format($shiftCashIn, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                        @if($shiftCashOut > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>Kas Keluar Operasional (Petty Cash):</span>
                                <strong class="font-mono text-rose-600">-Rp {{ number_format($shiftCashOut, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                        <div class="flex justify-between text-slate-600">
                            <span>Penjualan Non-Tunai (QRIS/EDC/Transfer):</span>
                            <strong class="font-mono text-purple-600">Rp {{ number_format($activeShift->non_cash_sales, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between text-sm font-extrabold text-blue-900 bg-blue-50/80 p-2.5 rounded-xl border border-blue-200">
                            <span>Total Kas Seharusnya di Laci:</span>
                            <span class="font-mono text-blue-700">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('pos.shift.close') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-extrabold text-slate-700 uppercase mb-1">
                                Uang Fisik Kasir di Laci (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="closing_cash_actual" 
                                x-model.number="actualClosingCash"
                                required 
                                placeholder="Hitung dan masukkan uang fisik..." 
                                class="w-full text-xl font-extrabold text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-2.5 focus:border-blue-600 focus:bg-white focus:ring-0 transition"
                            >
                        </div>

                        <!-- Live Difference Calculator -->
                        <div class="p-3.5 rounded-2xl border-2 transition flex items-center justify-between"
                            :class="(actualClosingCash - {{ $expectedCash }}) === 0 ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : ((actualClosingCash - {{ $expectedCash }}) < 0 ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-blue-50 border-blue-300 text-blue-900')"
                        >
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Selisih Rekonsiliasi (Discrepancy)</span>
                                <div class="text-lg font-extrabold mt-0.5">
                                    <template x-if="actualClosingCash === 0 && !actualClosingCash">
                                        <span>Masukkan uang fisik...</span>
                                    </template>
                                    <template x-if="actualClosingCash > 0 || actualClosingCash === 0">
                                        <span x-text="(actualClosingCash - {{ $expectedCash }}) === 0 ? 'Pas / Sesuai (Rp 0)' : ((actualClosingCash - {{ $expectedCash }}) < 0 ? 'Kurang Rp ' + formatNumber(Math.abs(actualClosingCash - {{ $expectedCash }})) : 'Lebih Rp ' + formatNumber(actualClosingCash - {{ $expectedCash }}))"></span>
                                    </template>
                                </div>
                            </div>
                            <i class="fa-solid text-2xl" :class="(actualClosingCash - {{ $expectedCash }}) === 0 ? 'fa-circle-check text-emerald-500' : ((actualClosingCash - {{ $expectedCash }}) < 0 ? 'fa-circle-exclamation text-rose-500' : 'fa-circle-info text-blue-500')"></i>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Catatan Tutup Shift / Alasan Selisih (Opsional):</label>
                            <textarea name="closing_notes" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" placeholder="Contoh: Selisih Rp 500 karena tidak ada uang koin kembalian..."></textarea>
                        </div>

                        <div class="flex items-center space-x-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="openCloseShiftModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-600/30 transition active:scale-95">
                                Tutup Shift & Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>

    <!-- Alpine.js POS Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posApp', () => ({
                allProducts: @json($products),
                activeCoupons: @json($activeCoupons ?? []),
                searchQuery: '',
                selectedCategory: null,
                cart: [],
                customerName: '',
                discountPercent: 0,
                tempDiscountPercent: 0,
                couponCodeInput: '',
                appliedCoupon: null,
                couponLoading: false,
                openDiscountModal: false,
                discountTab: 'coupon',
                isScanningQr: false,
                html5QrCodeScanner: null,
                openNoteModal: false,
                editingItemIndex: null,
                editingItemName: '',
                itemNoteInput: '',
                enableTax: {{ $taxSettings['enable_tax'] ? 'true' : 'false' }},
                taxRate: {{ $taxSettings['tax_rate'] }},
                enableService: {{ $taxSettings['enable_service'] ? 'true' : 'false' }},
                serviceRate: {{ $taxSettings['service_rate'] }},
                enableOrderTypes: {{ $taxSettings['enable_order_types'] ? 'true' : 'false' }},
                serviceChargeOnTakeaway: {{ $taxSettings['service_charge_on_takeaway'] ? 'true' : 'false' }},
                taxOnTakeaway: {{ $taxSettings['tax_on_takeaway'] ? 'true' : 'false' }},
                orderType: 'DINE_IN',
                paymentMethod: 'CASH',
                cashReceived: 0,
                showPaymentModal: false,
                openCustomerModal: false,
                openHoldModal: false,
                openShiftModal: false,
                openCloseShiftModal: false,
                openMovementModal: false,
                movementType: 'CASH_OUT',
                actualClosingCash: 0,
                selectedCustomerId: null,
                selectedCustomerPoints: 0,
                redeemPointsEnabled: false,
                pointsToRedeem: 0,
                splitMethod1: 'CASH',
                splitAmount1: 0,
                splitMethod2: 'DEBIT',
                splitAmount2: 0,
                customerTab: 'member',
                customerSearch: '',
                newMemberName: '',
                newMemberPhone: '',
                showNewMemberForm: false,
                allCustomers: @json($customers ?? []),
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
                            this.openNoteModal = false;
                            this.openShiftModal = false;
                            this.openCloseShiftModal = false;
                            this.openMovementModal = false;
                            this.closeDiscountModal();
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

                get filteredCustomerList() {
                    const q = (this.customerSearch || '').toLowerCase().trim();
                    if (!q) return this.allCustomers.slice(0, 15);
                    return this.allCustomers.filter(c => 
                        (c.name && c.name.toLowerCase().includes(q)) || 
                        (c.phone && c.phone.includes(q))
                    ).slice(0, 15);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                get discountAmount() {
                    if (this.appliedCoupon) {
                        return this.appliedCoupon.discount_amount || 0;
                    }
                    return Math.round(this.subtotal * ((this.discountPercent || 0) / 100));
                },

                get subtotalAfterCoupon() {
                    return Math.max(0, this.subtotal - this.discountAmount);
                },

                get maxRedeemablePoints() {
                    if (!this.selectedCustomerId || !this.selectedCustomerPoints) return 0;
                    return Math.min(this.selectedCustomerPoints, Math.ceil(this.subtotalAfterCoupon / 1000));
                },

                get pointsDiscountAmount() {
                    if (!this.redeemPointsEnabled) return 0;
                    const pts = parseInt(this.pointsToRedeem) || 0;
                    return Math.min(this.subtotalAfterCoupon, pts * 1000);
                },

                get subtotalAfterDiscount() {
                    return Math.max(0, this.subtotalAfterCoupon - this.pointsDiscountAmount);
                },

                get isTakeAway() {
                    return this.enableOrderTypes && this.orderType === 'TAKE_AWAY';
                },

                get activeServiceRate() {
                    if (!this.enableService) return 0;
                    if (this.isTakeAway && !this.serviceChargeOnTakeaway) return 0;
                    return this.serviceRate;
                },

                get serviceAmount() {
                    return Math.round(this.subtotalAfterDiscount * (this.activeServiceRate / 100));
                },

                get activeTaxRate() {
                    if (!this.enableTax) return 0;
                    if (this.isTakeAway && !this.taxOnTakeaway) return 0;
                    return this.taxRate;
                },

                get taxAmount() {
                    return Math.round((this.subtotalAfterDiscount + this.serviceAmount) * (this.activeTaxRate / 100));
                },

                get grandTotal() {
                    return this.subtotalAfterDiscount + this.serviceAmount + this.taxAmount;
                },

                get totalItemCount() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },

                get splitTotal() {
                    return (parseFloat(this.splitAmount1) || 0) + (parseFloat(this.splitAmount2) || 0);
                },

                get splitDifference() {
                    return Math.round(this.grandTotal - this.splitTotal);
                },

                get splitCashPortion() {
                    let total = 0;
                    if (this.splitMethod1 === 'CASH') total += (parseFloat(this.splitAmount1) || 0);
                    if (this.splitMethod2 === 'CASH') total += (parseFloat(this.splitAmount2) || 0);
                    return total;
                },

                get cashChange() {
                    if (this.paymentMethod === 'SPLIT') {
                        if (this.splitCashPortion <= 0) return 0;
                        return (this.cashReceived || 0) - this.splitCashPortion;
                    }
                    return (this.cashReceived || 0) - this.grandTotal;
                },

                addToCart(product) {
                    if (product.manage_stock && product.stock <= 0) {
                        Toast.fire({ icon: 'error', title: 'Stok produk fisik habis!' });
                        return;
                    }

                    const existing = this.cart.find(i => i.id === product.id && !i.notes);
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
                            qty: 1,
                            notes: ''
                        });
                        playBeep('beep');
                    }

                    // Revalidate coupon if exists
                    if (this.appliedCoupon) {
                        this.recalculateCoupon();
                    }
                },

                openNoteEditor(index) {
                    this.editingItemIndex = index;
                    this.editingItemName = this.cart[index].name;
                    this.itemNoteInput = this.cart[index].notes || '';
                    this.openNoteModal = true;
                    this.$nextTick(() => {
                        const el = document.getElementById('itemNoteInputField');
                        if (el) el.focus();
                    });
                },

                appendNotePreset(preset) {
                    if (!this.itemNoteInput) {
                        this.itemNoteInput = preset;
                    } else if (!this.itemNoteInput.includes(preset)) {
                        this.itemNoteInput += ', ' + preset;
                    }
                },

                saveItemNote() {
                    if (this.editingItemIndex !== null && this.cart[this.editingItemIndex]) {
                        this.cart[this.editingItemIndex].notes = this.itemNoteInput.trim();
                    }
                    this.openNoteModal = false;
                },

                closeDiscountModal() {
                    this.openDiscountModal = false;
                    this.stopQrScanner();
                },

                applyCoupon() {
                    if (!this.couponCodeInput || this.couponCodeInput.trim() === '') return;
                    if (this.subtotal <= 0) {
                        Toast.fire({ icon: 'warning', title: 'Keranjang belanja masih kosong!' });
                        return;
                    }

                    this.couponLoading = true;
                    fetch("{{ route('pos.validate-coupon') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            code: this.couponCodeInput.trim(),
                            subtotal: this.subtotal,
                            items: this.cart
                        })
                    })
                    .then(res => res.json().then(data => ({ status: res.status, body: data })))
                    .then(({ status, body }) => {
                        this.couponLoading = false;
                        if (status === 200 && body.valid) {
                            this.appliedCoupon = body;
                            this.discountPercent = 0;
                            playBeep('success');
                            Toast.fire({
                                icon: 'success',
                                title: `Kupon ${body.coupon_code} berhasil diterapkan! (Hemat Rp ${this.formatNumber(body.discount_amount)})`
                            });
                            this.closeDiscountModal();
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: body.message || 'Kupon tidak valid!'
                            });
                        }
                    })
                    .catch(err => {
                        this.couponLoading = false;
                        Toast.fire({ icon: 'error', title: 'Gagal memvalidasi kupon.' });
                    });
                },

                applyCouponDirect(code) {
                    this.couponCodeInput = code;
                    this.applyCoupon();
                },

                applyManualDiscount() {
                    this.discountPercent = Math.min(100, Math.max(0, parseInt(this.tempDiscountPercent) || 0));
                    this.appliedCoupon = null;
                    this.couponCodeInput = '';
                    Toast.fire({ icon: 'success', title: `Diskon manual ${this.discountPercent}% diterapkan` });
                    this.closeDiscountModal();
                },

                clearAllDiscounts() {
                    this.appliedCoupon = null;
                    this.couponCodeInput = '';
                    this.discountPercent = 0;
                    this.tempDiscountPercent = 0;
                    Toast.fire({ icon: 'info', title: 'Semua diskon dibersihkan' });
                    this.closeDiscountModal();
                },

                recalculateCoupon() {
                    if (!this.appliedCoupon) return;
                    fetch("{{ route('pos.validate-coupon') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            code: this.appliedCoupon.coupon_code,
                            subtotal: this.subtotal,
                            items: this.cart
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.valid) {
                            this.appliedCoupon = data;
                        } else {
                            this.removeCoupon();
                            Toast.fire({ icon: 'warning', title: data.message });
                        }
                    });
                },

                removeCoupon() {
                    this.appliedCoupon = null;
                    this.couponCodeInput = '';
                    this.discountPercent = 0;
                    Toast.fire({ icon: 'info', title: 'Kupon telah dihapus' });
                },

                toggleQrScanner() {
                    if (this.isScanningQr) {
                        this.stopQrScanner();
                    } else {
                        this.startQrScanner();
                    }
                },

                startQrScanner() {
                    if (typeof Html5Qrcode === 'undefined') {
                        Toast.fire({ icon: 'error', title: 'Modul pemindai kamera tidak tersedia' });
                        return;
                    }
                    this.isScanningQr = true;
                    this.$nextTick(() => {
                        try {
                            this.html5QrCodeScanner = new Html5Qrcode("qr-camera-reader");
                            const config = { fps: 10, qrbox: { width: 220, height: 220 } };
                            this.html5QrCodeScanner.start(
                                { facingMode: "environment" },
                                config,
                                (decodedText) => {
                                    playBeep('success');
                                    this.couponCodeInput = decodedText.toUpperCase().trim();
                                    this.stopQrScanner();
                                    this.applyCoupon();
                                },
                                (errorMessage) => {}
                            ).catch((err) => {
                                Toast.fire({ icon: 'error', title: 'Gagal membuka kamera. Pastikan izin kamera aktif!' });
                                this.isScanningQr = false;
                            });
                        } catch(e) {
                            this.isScanningQr = false;
                        }
                    });
                },

                stopQrScanner() {
                    if (this.html5QrCodeScanner && this.isScanningQr) {
                        this.html5QrCodeScanner.stop().then(() => {
                            this.html5QrCodeScanner.clear();
                        }).catch(() => {});
                    }
                    this.isScanningQr = false;
                },

                increaseQty(index) {
                    const item = this.cart[index];
                    if (!item.manage_stock || item.qty < item.stock) {
                        item.qty++;
                        playBeep('beep');
                        if (this.appliedCoupon) this.recalculateCoupon();
                    } else {
                        Toast.fire({ icon: 'warning', title: 'Stok tidak mencukupi!' });
                    }
                },

                decreaseQty(index) {
                    if (this.cart[index].qty > 1) {
                        this.cart[index].qty--;
                        playBeep('beep');
                        if (this.appliedCoupon) this.recalculateCoupon();
                    } else {
                        this.removeFromCart(index);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    if (this.appliedCoupon) this.recalculateCoupon();
                },

                clearCart() {
                    this.cart = [];
                    this.discountPercent = 0;
                    this.tempDiscountPercent = 0;
                    this.appliedCoupon = null;
                    this.couponCodeInput = '';
                    this.customerName = '';
                    this.selectedCustomerId = null;
                    this.selectedCustomerPoints = 0;
                    this.redeemPointsEnabled = false;
                    this.pointsToRedeem = 0;
                },

                handleBarcodeScan() {
                    if (!this.searchQuery) return;
                    
                    // Check if it's a product barcode
                    const found = this.allProducts.find(p => 
                        (p.barcode && p.barcode.toLowerCase() === this.searchQuery.toLowerCase().trim()) ||
                        (p.sku && p.sku.toLowerCase() === this.searchQuery.toLowerCase().trim())
                    );

                    if (found) {
                        this.addToCart(found);
                        this.searchQuery = '';
                    } else {
                        // Check if cashier scanned a coupon QR code!
                        this.couponCodeInput = this.searchQuery.toUpperCase().trim();
                        this.searchQuery = '';
                        this.applyCoupon();
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
                    this.selectedCustomerId = null;
                    this.selectedCustomerPoints = 0;
                    this.customerName = preset;
                    if (this.enableOrderTypes) {
                        if (preset === 'Take Away' || preset === 'Online/Ojol') {
                            this.orderType = 'TAKE_AWAY';
                        } else if (preset === 'Dine In' || preset.startsWith('Meja')) {
                            this.orderType = 'DINE_IN';
                        }
                    }
                },

                selectMember(member) {
                    this.selectedCustomerId = member.id;
                    this.customerName = member.name;
                    this.selectedCustomerPoints = member.points || 0;
                    this.redeemPointsEnabled = false;
                    this.pointsToRedeem = 0;
                    this.openCustomerModal = false;
                    Toast.fire({ icon: 'success', title: 'Member ' + member.name + ' (' + (member.points || 0) + ' pts) dipilih!' });
                },

                resetCustomer() {
                    this.selectedCustomerId = null;
                    this.customerName = '';
                    this.selectedCustomerPoints = 0;
                    this.redeemPointsEnabled = false;
                    this.pointsToRedeem = 0;
                },

                onToggleRedeemPoints() {
                    if (this.redeemPointsEnabled) {
                        this.pointsToRedeem = this.maxRedeemablePoints;
                    } else {
                        this.pointsToRedeem = 0;
                    }
                },

                sanitizePointsToRedeem() {
                    if (this.pointsToRedeem < 0) this.pointsToRedeem = 0;
                    if (this.pointsToRedeem > this.maxRedeemablePoints) this.pointsToRedeem = this.maxRedeemablePoints;
                },

                selectPaymentMethod(method) {
                    this.paymentMethod = method;
                    if (method === 'SPLIT') {
                        this.autoBalanceSplit();
                    } else if (method === 'CASH') {
                        this.cashReceived = this.grandTotal;
                    }
                },

                autoBalanceSplit() {
                    const half = Math.floor(this.grandTotal / 2);
                    this.splitAmount1 = half;
                    this.splitAmount2 = this.grandTotal - half;
                    if (this.splitCashPortion > 0) {
                        this.cashReceived = this.splitCashPortion;
                    }
                },

                onSplitAmount1Change() {
                    const amt1 = parseFloat(this.splitAmount1) || 0;
                    this.splitAmount2 = Math.max(0, this.grandTotal - amt1);
                    if (this.splitCashPortion > 0) {
                        this.cashReceived = this.splitCashPortion;
                    }
                },

                onSplitAmount2Change() {
                    const amt2 = parseFloat(this.splitAmount2) || 0;
                    this.splitAmount1 = Math.max(0, this.grandTotal - amt2);
                    if (this.splitCashPortion > 0) {
                        this.cashReceived = this.splitCashPortion;
                    }
                },

                onSplitMethodChange() {
                    if (this.splitCashPortion > 0) {
                        this.cashReceived = this.splitCashPortion;
                    }
                },

                async quickRegisterMember() {
                    const name = (this.newMemberName || '').trim();
                    if (!name) {
                        Toast.fire({ icon: 'warning', title: 'Nama member wajib diisi!' });
                        return;
                    }
                    try {
                        const res = await fetch('{{ route("pos.customers.quick-create") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: name,
                                phone: this.newMemberPhone
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.customer) {
                            this.allCustomers.unshift(data.customer);
                            this.selectMember(data.customer);
                            this.newMemberName = '';
                            this.newMemberPhone = '';
                            this.showNewMemberForm = false;
                        } else {
                            Toast.fire({ icon: 'error', title: data.message || 'Gagal mendaftar member.' });
                        }
                    } catch(e) {
                        Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem.' });
                    }
                },

                openPaymentModal() {
                    if (this.paymentMethod === 'SPLIT') {
                        this.autoBalanceSplit();
                    } else {
                        this.cashReceived = this.grandTotal;
                    }
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

                    if (this.paymentMethod === 'SPLIT') {
                        if (this.splitDifference !== 0) {
                            Toast.fire({ icon: 'error', title: 'Jumlah split payment harus pas dengan total tagihan!' });
                            return;
                        }
                        if (this.splitCashPortion > 0 && this.cashChange < 0) {
                            Toast.fire({ icon: 'error', title: 'Nominal uang tunai fisik diterima kurang dari porsi tunai!' });
                            return;
                        }
                    }

                    document.getElementById('checkoutForm').submit();
                },

                holdCurrentOrder() {
                    if (this.cart.length === 0) return;
                    this.heldOrders.push({
                        customerName: this.customerName || 'Pelanggan #' + (this.heldOrders.length + 1),
                        items: [...this.cart],
                        discountPercent: this.discountPercent,
                        appliedCoupon: this.appliedCoupon,
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
                    this.discountPercent = held.discountPercent || 0;
                    this.appliedCoupon = held.appliedCoupon || null;
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

