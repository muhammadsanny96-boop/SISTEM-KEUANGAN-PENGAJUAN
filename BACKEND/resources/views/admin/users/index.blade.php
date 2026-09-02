@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('page_title', 'Data Karyawan')
@section('page_subtitle', 'Kelola akun pengguna dan penempatan divisi karyawan PT Jamkrida Kalsel')

@section('content')

<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    editId: null,
    editName: '',
    editEmail: '',
    editPhone: '',
    editRole: '',
    editDivisionId: '',
    openEdit(id, name, email, phone, role, divisionId) {
        this.editId = id;
        this.editName = name;
        this.editEmail = email;
        this.editPhone = phone;
        this.editRole = role;
        this.editDivisionId = divisionId;
        this.showEditModal = true;
    }
}">
    {{-- Header Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Daftar Karyawan</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Total terdaftar: <strong class="text-slate-800">{{ $users->total() }} Pengguna</strong></p>
        </div>
        <div>
            <button @click="showCreateModal = true" class="btn btn-primary shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Karyawan Baru
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 w-12">#</th>
                        <th class="py-3.5 px-4">Nama Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Email & No. HP</th>
                        <th class="py-3.5 px-4">Divisi</th>
                        <th class="py-3.5 px-4">Jabatan / Role</th>
                        <th class="py-3.5 px-4">Tanggal Terdaftar</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $i => $u)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-slate-400 text-xs">{{ $users->firstItem() + $i }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg {{ $u->isAdmin() ? 'bg-slate-900' : 'bg-blue-600' }} text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $u->jabatan }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800">{{ $u->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $u->phone ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->division)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $u->division->nama_divisi }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-900 text-white">
                                        Administrator
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kepala Divisi
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $u->created_at->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEdit({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ addslashes($u->phone ?? '') }}', '{{ $u->role }}', '{{ $u->division_id ?? '' }}')"
                                            class="btn btn-secondary btn-sm">
                                        Edit
                                    </button>
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}"
                                              onsubmit="return confirm('Hapus akun pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-xs sm:text-sm">Belum ada data user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="card w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Akun Kepala Divisi / Admin</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="form-control" placeholder="Nama lengkap...">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="form-control" placeholder="email@jamkridakalsel.co.id">
                    </div>
                    <div>
                        <label class="form-label">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ role: 'user' }">
                    <div>
                        <label class="form-label">Hak Akses (Role) <span class="text-red-500">*</span></label>
                        <select name="role" x-model="role" required class="form-control font-semibold">
                            <option value="user">Kepala Divisi</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">
                            Divisi
                            <span x-show="role === 'user'" class="text-red-500">*</span>
                        </label>
                        <select name="division_id" class="form-control" :required="role === 'user'">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->id }}">{{ $d->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="form-control" placeholder="Minimal 8 karakter...">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary px-5">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="card w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Edit Data Akun Pengguna</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>
            <form :action="`{{ url('admin/users') }}/${editId}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editName" required class="form-control">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="editEmail" required class="form-control">
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" x-model="editPhone" class="form-control">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Hak Akses (Role) <span class="text-red-500">*</span></label>
                        <select name="role" x-model="editRole" required class="form-control font-semibold">
                            <option value="user">Kepala Divisi</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Divisi</label>
                        <select name="division_id" x-model="editDivisionId" class="form-control" :required="editRole === 'user'">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->id }}">{{ $d->nama_divisi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Password Baru (Kosongkan jika tidak ingin ganti)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary px-5">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
