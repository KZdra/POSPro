<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Master Kategori Menu') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Kelola kategori untuk pengelompokan menu di layar kasir</p>
            </div>
            
            <a href="{{ route('categories.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Kategori</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            
            <table id="categoriesTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Icon & Warna</th>
                        <th class="py-3 px-4">Nama Kategori</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Warna Tema</th>
                        <th class="py-3 px-4">Total Menu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categories as $cat)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <div class="w-9 h-9 rounded-xl {{ $cat->color_icon_bg }} flex items-center justify-center text-sm shadow-sm">
                                    <i class="fa-solid {{ $cat->icon ?: 'fa-tag' }}"></i>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 text-sm">
                                {{ $cat->name }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-400">
                                {{ $cat->slug }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-lg font-bold text-[11px] border {{ $cat->color_badge_class }} uppercase">
                                    {{ $cat->color ?: 'blue' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg font-extrabold text-[11px]">
                                    {{ $cat->products_count }} Produk
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('categories.edit', $cat->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form id="del-cat-{{ $cat->id }}" action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete('del-cat-{{ $cat->id }}')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#categoriesTable').DataTable({
                language: {
                    search: "Cari Kategori:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ kategori",
                    paginate: { next: "&rarr;", previous: "&larr;" },
                    zeroRecords: "Kategori tidak ditemukan"
                }
            });
        });
    </script>
</x-app-layout>
