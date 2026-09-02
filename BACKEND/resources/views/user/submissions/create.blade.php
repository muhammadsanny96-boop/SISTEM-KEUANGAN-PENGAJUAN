@extends('layouts.app')

@section('title', 'Buat Pengajuan Barang')
@section('page_title', 'Form Pengajuan Barang Baru')
@section('page_subtitle', 'Isi data barang yang dibutuhkan untuk divisi Anda di PT Jamkrida Kalsel')

@section('content')

<div class="max-w-3xl mx-auto" x-data="{
    jumlah: {{ old('jumlah', 1) }},
    hargaSatuan: {{ old('harga_satuan', 0) }},
    get totalBiaya() {
        return (parseFloat(this.jumlah) || 0) * (parseFloat(this.hargaSatuan) || 0);
    },
    formatRupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    }
}">
    {{-- Header Link --}}
    <div class="mb-4">
        <a href="{{ route('user.submissions.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            &larr; Kembali ke Daftar Pengajuan
        </a>
    </div>

    {{-- Form Card --}}
    <div class="card p-6 sm:p-8">
        <div class="mb-6 pb-4 border-b border-slate-100">
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Formulir Pengajuan Pengadaan Barang</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Divisi: <strong class="text-slate-700">{{ $user->division->nama_divisi ?? 'Umum' }}</strong> &bull; Pemohon: <strong class="text-slate-700">{{ $user->name }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('user.submissions.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label for="nama_barang" class="form-label">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang') }}"
                       class="form-control" required
                       placeholder="Contoh: Kertas HVS A4, Tinta Printer Epson, Mouse Wireless...">
                @error('nama_barang') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="form-label">Kategori Barang <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jenis_pengajuan" class="form-label">Jenis Pengajuan <span class="text-red-500">*</span></label>
                    <select name="jenis_pengajuan" id="jenis_pengajuan" class="form-control" required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach(['Barang Habis','Barang Rusak','Barang Baru','Barang Perlu Diganti','Barang Perlu Dibeli'] as $jenis)
                            <option value="{{ $jenis }}" {{ old('jenis_pengajuan') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                        @endforeach
                    </select>
                    @error('jenis_pengajuan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="jumlah" class="form-label">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah" id="jumlah" x-model="jumlah"
                           class="form-control" required min="1">
                    @error('jumlah') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="satuan" class="form-label">Satuan <span class="text-red-500">*</span></label>
                    <select name="satuan" id="satuan" class="form-control" required>
                        @foreach(['Pcs','Unit','Box','Rim','Lusin','Set','Meter','Liter','Kg','Buah','Paket'] as $sat)
                            <option value="{{ $sat }}" {{ old('satuan') == $sat ? 'selected' : '' }}>{{ $sat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="prioritas" class="form-label">Prioritas <span class="text-red-500">*</span></label>
                    <select name="prioritas" id="prioritas" class="form-control" required>
                        @foreach(['Rendah','Sedang','Tinggi','Mendesak'] as $p)
                            <option value="{{ $p }}" {{ old('prioritas', 'Sedang') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="harga_satuan" class="form-label">Estimasi Harga Satuan (Rp)</label>
                    <input type="number" name="harga_satuan" id="harga_satuan" x-model="hargaSatuan"
                           class="form-control" min="0" step="1000" placeholder="Contoh: 50000">
                    <span class="text-[11px] text-slate-400 mt-1 block">Perkiraan harga per satuan barang</span>
                    @error('harga_satuan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="target_bulan" class="form-label">Target Bulan Pengeluaran <span class="text-red-500">*</span></label>
                    <select name="target_bulan" id="target_bulan" class="form-control" required>
                        <option value="{{ $currentMonth }}" {{ old('target_bulan', $currentMonth) === $currentMonth ? 'selected' : '' }}>
                            Bulan Ini ({{ $currentMonthName }})
                        </option>
                        <option value="{{ $nextMonth }}" {{ old('target_bulan') === $nextMonth ? 'selected' : '' }}>
                            Bulan Depan ({{ $nextMonthName }})
                        </option>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Bulan anggaran belanja yang dituju</span>
                    @error('target_bulan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Live Subtotal Preview --}}
            <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">TOTAL ESTIMASI BIAYA</div>
                    <div class="text-xs text-emerald-600 mt-0.5">Jumlah (<span x-text="jumlah">1</span>) &times; Harga Satuan</div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-emerald-700" x-text="formatRupiah(totalBiaya)">
                    Rp 0
                </div>
            </div>

            <div>
                <label for="alasan" class="form-label">Alasan / Keterangan Kebutuhan <span class="text-red-500">*</span></label>
                <textarea name="alasan" id="alasan" rows="3" class="form-control" required
                          placeholder="Jelaskan untuk apa barang ini dibutuhkan atau kronologi kerusakan (minimal 10 karakter)...">{{ old('alasan') }}</textarea>
                @error('alasan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="foto_barang" class="form-label">Foto Barang / Bukti Kerusakan (Opsional)</label>
                <input type="file" name="foto_barang" id="foto_barang" accept="image/*" class="form-control file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <span class="text-[11px] text-slate-400 mt-1 block">Format gambar: JPG, PNG, atau JPEG (maksimal 2MB).</span>
                @error('foto_barang') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a href="{{ route('user.submissions.index') }}" class="btn btn-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-6">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
