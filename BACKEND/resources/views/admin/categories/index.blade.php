@extends('layouts.app')

@section('title', 'Data Kategori')
@section('page_title', 'Data Kategori Barang')
@section('page_subtitle', 'Kelola kategori dan jenis barang pengadaan di PT Jamkrida Kalsel')

@section('content')

<div x-data="{
    showCreateModal: false,
    showEditModal: false,
    editId: null,
    editNama: '',
    editDeskripsi: '',
    openEdit(id, nama, deskripsi) {
        this.editId = id;
        this.editNama = nama;
        this.editDeskripsi = deskripsi;
        this.showEditModal = true;
    }
}">
    {{-- Header Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Daftar Kategori Barang</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Total terdaftar: <strong class="text-slate-800">{{ $categories->total() }} Kategori</strong></p>
        </div>
        <div>
            <button @click="showCreateModal = true" class="btn btn-primary shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori Baru
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
                        <th class="py-3.5 px-4">Nama Kategori</th>
                        <th class="py-3.5 px-4">Deskripsi</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Pengajuan</th>
                        <th class="py-3.5 px-4">Tanggal Dibuat</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $i => $cat)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 text-slate-400 text-xs">{{ $categories->firstItem() + $i }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $cat->nama_kategori }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $cat->deskripsi ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700">
                                    {{ $cat->submissions_count }} Pengajuan
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $cat->created_at->format('d/m/Y') }}</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEdit({{ $cat->id }}, '{{ addslashes($cat->nama_kategori) }}', '{{ addslashes($cat->deskripsi ?? '') }}')"
                                            class="btn btn-secondary btn-sm">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}"
                                          onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-xs sm:text-sm">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCreateModal = false" class="card w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Kategori Baru</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" required class="form-control" placeholder="Contoh: ATK, Elektronik, Kebersihan...">
                </div>
                <div>
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" rows="3" class="form-control" placeholder="Keterangan jenis barang..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary px-5">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditModal = false" class="card w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Edit Kategori</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
            </div>
            <form :action="`{{ url('admin/categories') }}/${editId}`" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" x-model="editNama" required class="form-control">
                </div>
                <div>
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" x-model="editDeskripsi" rows="3" class="form-control"></textarea>
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
