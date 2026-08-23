<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 leading-tight">
                    {{ __('Kelola User & Kasir') }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">Daftar pengguna dengan hak akses Admin Toko dan Kasir POS</p>
            </div>
            
            <a href="{{ route('users.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Kasir / Admin</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            
            <table id="usersTable" class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3 px-4">Nama User</th>
                        <th class="py-3 px-4">Email Login</th>
                        <th class="py-3 px-4">Peran (Role)</th>
                        <th class="py-3 px-4">Dibuat Pada</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-800 text-sm">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div>{{ $u->name }}</div>
                                        @if(Auth::id() === $u->id)
                                            <span class="text-[10px] text-blue-600 font-bold">(Akun Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-medium">
                                {{ $u->email }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    <i class="fa-solid {{ $u->role === 'admin' ? 'fa-shield-halved' : 'fa-cash-register' }} mr-1"></i>
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('users.edit', $u->id) }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit User">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if(Auth::id() !== $u->id)
                                    <form id="del-user-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('del-user-{{ $u->id }}')" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus User">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#usersTable').DataTable({
                language: {
                    search: "Cari User:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ user",
                    paginate: { next: "&rarr;", previous: "&larr;" },
                    zeroRecords: "User tidak ditemukan"
                }
            });
        });
    </script>
</x-app-layout>
