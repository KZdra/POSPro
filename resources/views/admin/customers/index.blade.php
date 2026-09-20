<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Manajemen Member & Pelanggan (CRM)') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Kelola data basis pelanggan dan akumulasi poin loyalitas belanja</p>
            </div>
            
            <div class="flex items-center space-x-3">
                <button 
                    type="button" 
                    onclick="openCreateCustomerModal()" 
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2"
                >
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah Member Baru</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center space-x-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-2xl space-y-1">
                <div class="flex items-center space-x-2 mb-1">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                    <span>Terdapat kesalahan pada input Anda:</span>
                </div>
                <ul class="list-disc pl-5 font-medium space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nama member, nomor telepon/WA, atau email..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                    >
                </div>
                <div class="flex items-center space-x-2">
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Cari</span>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('customers.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Customer List Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-users text-blue-600"></i>
                    <span>Daftar Member Terdaftar ({{ $customers->total() }})</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                            <th class="py-3.5 px-4 font-bold rounded-l-xl">No</th>
                            <th class="py-3.5 px-4 font-bold">Nama Member</th>
                            <th class="py-3.5 px-4 font-bold">No. Telepon / WA</th>
                            <th class="py-3.5 px-4 font-bold">Email</th>
                            <th class="py-3.5 px-4 font-bold text-center">Poin Loyalitas</th>
                            <th class="py-3.5 px-4 font-bold">Alamat</th>
                            <th class="py-3.5 px-4 font-bold">Terdaftar</th>
                            <th class="py-3.5 px-4 font-bold text-center rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customers as $index => $customer)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-medium text-slate-400">
                                    {{ $customers->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-slate-900">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">
                                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div>{{ $customer->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-normal">ID: #CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700 font-mono">
                                    {{ $customer->phone ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $customer->email ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full font-extrabold text-xs">
                                        <i class="fa-solid fa-coins text-amber-500 mr-1.5"></i>
                                        {{ number_format($customer->points, 0, ',', '.') }} pts
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-[200px] truncate" title="{{ $customer->address }}">
                                    {{ $customer->address ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 font-medium text-[11px]">
                                    {{ $customer->created_at ? $customer->created_at->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center space-x-1.5 whitespace-nowrap">
                                    <button 
                                        type="button" 
                                        onclick="openEditCustomerModal({{ json_encode($customer) }})" 
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 rounded-lg text-xs font-bold transition"
                                        title="Edit Data"
                                    >
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ $customer->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-lg text-xs font-bold transition"
                                            title="Hapus Pelanggan"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-address-book text-4xl mb-3 text-slate-300 block"></i>
                                    <p class="font-bold">Belum ada data pelanggan atau member terdaftar.</p>
                                    <p class="text-[11px] mt-1">Klik tombol "Tambah Member Baru" di pojok kanan atas untuk menambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Customer Modal -->
    <div id="createCustomerModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-user-plus text-blue-600"></i>
                    <span>Tambah Member / Pelanggan</span>
                </h4>
                <button type="button" onclick="closeCreateCustomerModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white" placeholder="Contoh: Budi Pratama">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                    <input type="text" name="phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white font-mono" placeholder="Contoh: 081234567890">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white" placeholder="budi@example.com">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white" placeholder="Alamat rumah / domisili..."></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" onclick="closeCreateCustomerModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-blue-600/30 transition">
                        Simpan Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-user-pen text-blue-600"></i>
                    <span>Edit Data Member</span>
                </h4>
                <button type="button" onclick="closeEditCustomerModal()" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editCustomerForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="editName" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                    <input type="text" id="editPhone" name="phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white font-mono">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Email</label>
                    <input type="email" id="editEmail" name="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-slate-700 mb-1">Alamat</label>
                    <textarea id="editAddress" name="address" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" onclick="closeEditCustomerModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold rounded-xl shadow-md shadow-blue-600/30 transition">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateCustomerModal() {
            document.getElementById('createCustomerModal').classList.remove('hidden');
        }

        function closeCreateCustomerModal() {
            document.getElementById('createCustomerModal').classList.add('hidden');
        }

        function openEditCustomerModal(customer) {
            const form = document.getElementById('editCustomerForm');
            form.action = `/customers/${customer.id}`;
            document.getElementById('editName').value = customer.name || '';
            document.getElementById('editPhone').value = customer.phone || '';
            document.getElementById('editEmail').value = customer.email || '';
            document.getElementById('editAddress').value = customer.address || '';
            document.getElementById('editCustomerModal').classList.remove('hidden');
        }

        function closeEditCustomerModal() {
            document.getElementById('editCustomerModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
