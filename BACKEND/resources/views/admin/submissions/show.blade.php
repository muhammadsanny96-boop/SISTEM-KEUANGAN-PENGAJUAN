@extends('layouts.app')

@section('title', 'Tinjau Pengajuan - ' . $submission->nomor_pengajuan)
@section('page_title', 'Tinjau & Verifikasi Pengajuan')
@section('page_subtitle', 'Verifikasi permohonan pengadaan barang nomor ' . $submission->nomor_pengajuan)

@section('content')

@php
    $currentMonth = now()->format('Y-m');
    $nextMonth = now()->addMonth()->format('Y-m');
    $currentMonthName = now()->translatedFormat('F Y');
    $nextMonthName = now()->addMonth()->translatedFormat('F Y');
@endphp

<div class="max-w-5xl mx-auto">
    {{-- Header Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <a href="{{ route('admin.submissions.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            &larr; Kembali ke Daftar Pengajuan
        </a>
        <div class="flex items-center gap-2">
            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-200">
                {{ $submission->nomor_pengajuan }}
            </span>
            @php $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai']; @endphp
            <span class="badge {{ $sMap[$submission->status] ?? 'badge-menunggu' }}">{{ $submission->status }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Submission Details Sheet --}}
            <div class="card p-5 sm:p-6 space-y-5">
                {{-- Pemohon --}}
                <div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($submission->user->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-sm text-slate-900">{{ $submission->user->name ?? 'User Terhapus' }}</div>
                        <div class="text-xs text-slate-500">{{ $submission->user->email ?? '-' }} &bull; Divisi: <strong class="text-slate-700">{{ $submission->division->nama_divisi ?? '-' }}</strong></div>
                    </div>
                </div>

                {{-- Details Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Nama Barang</div>
                        <div class="text-sm sm:text-base font-bold text-slate-900 mt-0.5">{{ $submission->nama_barang }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Kategori</div>
                        <div class="text-sm font-semibold text-slate-700 mt-0.5">{{ $submission->category->nama_kategori ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Jumlah & Satuan</div>
                        <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $submission->jumlah }} {{ $submission->satuan }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Jenis Pengajuan</div>
                        <div class="text-sm text-slate-700 mt-0.5">{{ $submission->jenis_pengajuan }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Prioritas</div>
                        @php $pMap = ['Rendah'=>'badge-rendah','Sedang'=>'badge-sedang','Tinggi'=>'badge-tinggi','Mendesak'=>'badge-mendesak']; @endphp
                        <div class="mt-0.5">
                            <span class="badge {{ $pMap[$submission->prioritas] ?? 'badge-sedang' }}">{{ $submission->prioritas }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-500">Tanggal Masuk</div>
                        <div class="text-xs text-slate-600 mt-0.5">{{ $submission->created_at->translatedFormat('d F Y, H:i') }} WITA</div>
                    </div>
                </div>

                {{-- Biaya & Realisasi Pengadaan Box --}}
                <div class="p-4 sm:p-5 bg-gradient-to-br from-blue-50/90 to-indigo-50/50 border border-blue-200/80 rounded-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-blue-200/60 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-900">
                                Anggaran & Realisasi Pengadaan
                            </span>
                        </div>
                        <a href="{{ route('admin.expenses.index', ['division_id' => $submission->division_id]) }}" class="text-xs text-blue-600 font-semibold hover:underline">
                            Rekap Divisi {{ $submission->division->nama_divisi ?? '' }} &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100 shadow-xs">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Estimasi Pengajuan</div>
                            <div class="text-sm font-bold text-slate-900 mt-1">{{ $submission->formatted_total_biaya }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">({{ $submission->jumlah }} {{ $submission->satuan }} &times; {{ $submission->formatted_harga_satuan }})</div>
                        </div>

                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100 shadow-xs">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Harga Beli Aktual (Realisasi)</div>
                            <div class="text-sm font-bold {{ $submission->biaya_realisasi !== null ? 'text-emerald-700' : 'text-slate-400' }} mt-1">
                                {{ $submission->biaya_realisasi !== null ? $submission->formatted_biaya_realisasi : 'Belum dibeli' }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                @if($submission->harga_beli_satuan)
                                    @ {{ $submission->formatted_harga_beli_satuan }} / {{ $submission->satuan }}
                                @else
                                    Menunggu proses pengadaan
                                @endif
                            </div>
                        </div>

                        <div class="p-3 bg-white/80 rounded-xl border border-blue-100 shadow-xs">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Selisih Anggaran</div>
                            @if($submission->biaya_realisasi !== null)
                                @php $selisih = $submission->selisih_biaya; @endphp
                                <div class="text-sm font-extrabold {{ $selisih >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                                    {{ $submission->formatted_selisih_biaya }}
                                </div>
                                <div class="text-[11px] {{ $selisih >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-0.5 font-medium">
                                    {{ $selisih >= 0 ? 'Efisiensi Anggaran (Hemat)' : 'Kelebihan Biaya (Over Budget)' }}
                                </div>
                            @else
                                <div class="text-sm font-bold text-slate-400 mt-1">-</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Dihitung setelah pembelian</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-blue-200/50 text-xs">
                        <div class="text-slate-600">
                            Target Alokasi: <strong class="text-slate-900">{{ $submission->target_bulan_label }}</strong>
                        </div>
                        @if($submission->tanggal_realisasi)
                            <div class="text-emerald-700 font-semibold">
                                Tanggal Realisasi: {{ $submission->formatted_tanggal_realisasi }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Bukti Pembelian Sah (Nota / Kuitansi / Faktur) --}}
                @if($submission->bukti_pembelian)
                    <div class="p-4 sm:p-5 bg-emerald-50/90 border border-emerald-200 rounded-2xl" x-data="{ zoomProof: false }">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full bg-emerald-600"></span>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">
                                    Bukti Pembelian Sah (Nota / Kuitansi / Faktur)
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                Terverifikasi
                            </span>
                        </div>

                        @if($submission->isProofImage())
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <img src="{{ asset('storage/' . $submission->bukti_pembelian) }}" 
                                     alt="Bukti Pembelian" 
                                     @click="zoomProof = true"
                                     class="w-44 h-28 object-cover rounded-xl border border-emerald-300 shadow-sm cursor-pointer hover:opacity-90 transition-opacity">
                                
                                <div class="space-y-1.5 text-xs text-slate-700">
                                    <div class="font-bold text-slate-900">Dokumen Nota / Kuitansi Resmi Terlampir</div>
                                    <div class="text-slate-500">Klik gambar untuk memperbesar pratinjau bukti transaksi.</div>
                                    <div class="pt-1">
                                        <a href="{{ asset('storage/' . $submission->bukti_pembelian) }}" target="_blank" download
                                           class="btn btn-secondary btn-sm inline-flex items-center gap-1.5">
                                            <span>📥 Unduh Bukti Asli</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Zoom Image --}}
                            <div x-show="zoomProof" x-cloak @click.self="zoomProof = false"
                                 class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
                                <div class="relative bg-white rounded-2xl p-3 shadow-2xl max-w-4xl max-h-[90vh] flex flex-col items-center">
                                    <button @click="zoomProof = false" class="absolute -top-3 -right-3 w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center shadow cursor-pointer font-bold">✕</button>
                                    <img src="{{ asset('storage/' . $submission->bukti_pembelian) }}" class="max-w-full max-h-[80vh] object-contain rounded-xl">
                                    <div class="text-xs text-slate-500 mt-2 font-medium">Bukti Pembelian - {{ $submission->nomor_pengajuan }}</div>
                                </div>
                            </div>
                        @elseif($submission->isProofPdf())
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-emerald-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs">
                                        PDF
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Dokumen Faktur / Kuitansi (PDF)</div>
                                        <div class="text-[11px] text-slate-500">File bukti pembayaran pengadaan barang</div>
                                    </div>
                                </div>
                                <a href="{{ asset('storage/' . $submission->bukti_pembelian) }}" target="_blank"
                                   class="btn btn-primary btn-sm">
                                    Buka Dokumen PDF &rarr;
                                </a>
                            </div>
                        @else
                            <a href="{{ asset('storage/' . $submission->bukti_pembelian) }}" target="_blank" class="btn btn-secondary btn-sm">
                                📎 Lihat Bukti Pembelian
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Alasan --}}
                <div class="pt-2">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                        Alasan / Keterangan Kebutuhan
                    </div>
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $submission->alasan }}
                    </div>
                </div>

                {{-- Foto Barang Awal --}}
                @if($submission->foto_barang)
                    <div class="pt-2 border-t border-slate-100" x-data="{ zoom: false }">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Foto Barang Yang Diajukan
                        </div>
                        <img src="{{ asset('storage/' . $submission->foto_barang) }}" alt="Foto" @click="zoom = true"
                             class="w-36 h-24 object-cover rounded-lg border border-slate-200 cursor-pointer hover:opacity-90 transition-opacity">
                        <div x-show="zoom" x-cloak @click.self="zoom = false"
                             class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl p-2 shadow-2xl">
                                <button @click="zoom = false" class="absolute -top-3 -right-3 w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center shadow cursor-pointer">✕</button>
                                <img src="{{ asset('storage/' . $submission->foto_barang) }}" class="max-w-[85vw] max-h-[85vh] rounded-lg">
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Admin Reply & Status Update Form --}}
            <div class="card p-5 sm:p-6" 
                 x-data="{ 
                     selectedStatus: '{{ $submission->status }}',
                     jumlah: {{ $submission->jumlah }},
                     hargaSatuanEstimasi: {{ $submission->harga_satuan ?? 0 }},
                     hargaBeliSatuan: '{{ $submission->harga_beli_satuan ?? '' }}',
                     biayaRealisasi: '{{ $submission->biaya_realisasi ?? '' }}',
                     calculateTotal() {
                         if (this.hargaBeliSatuan && !isNaN(this.hargaBeliSatuan)) {
                             this.biayaRealisasi = parseFloat(this.hargaBeliSatuan) * this.jumlah;
                         }
                     },
                     get selisih() {
                         let totalEst = this.hargaSatuanEstimasi * this.jumlah;
                         let totalReal = parseFloat(this.biayaRealisasi);
                         if (!isNaN(totalReal) && totalReal > 0) {
                             return totalEst - totalReal;
                         }
                         return null;
                     }
                 }">
                <div class="mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Tindak Lanjut, Pengadaan, & Persetujuan Admin</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui status, masukkan harga beli riil, dan lampirkan bukti nota sah pembelian</p>
                </div>

                <form method="POST" action="{{ route('admin.submissions.reply', $submission->id) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Status Pengajuan <span class="text-red-500">*</span></label>
                            <select name="status" x-model="selectedStatus" required class="form-control font-semibold">
                                <option value="Menunggu">⚪ Menunggu (Belum diproses)</option>
                                <option value="Diproses">🟡 Diproses (Sedang pengadaan)</option>
                                <option value="Disetujui">🟢 Disetujui (Disetujui)</option>
                                <option value="Ditolak">🔴 Ditolak (Tidak disetujui)</option>
                                <option value="Selesai">🔵 Selesai (Barang dibeli & diterima)</option>
                            </select>
                            @error('status') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Prioritas <span class="text-red-500">*</span></label>
                            <select name="prioritas" required class="form-control">
                                @foreach(['Rendah','Sedang','Tinggi','Mendesak'] as $p)
                                    <option value="{{ $p }}" {{ old('prioritas', $submission->prioritas) === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Procurement & Cost Realization Section --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3.5">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span>Realisasi Pembelian & Pengadaan Barang</span>
                            <span class="text-[11px] text-blue-600 font-semibold normal-case">Jumlah: {{ $submission->jumlah }} {{ $submission->satuan }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div>
                                <label class="form-label">Harga Beli Satuan (Rp)</label>
                                <input type="number" name="harga_beli_satuan" x-model="hargaBeliSatuan" @input="calculateTotal()"
                                       value="{{ old('harga_beli_satuan', $submission->harga_beli_satuan) }}"
                                       class="form-control" min="0" step="500" placeholder="Contoh: 1200000">
                                @error('harga_beli_satuan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label">Total Realisasi Pembelian (Rp)</label>
                                <input type="number" name="biaya_realisasi" x-model="biayaRealisasi"
                                       value="{{ old('biaya_realisasi', $submission->biaya_realisasi) }}"
                                       class="form-control font-bold text-slate-900" min="0" step="500" placeholder="Otomatis terhitung...">
                                @error('biaya_realisasi') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label">Tanggal Pembelian / Realisasi</label>
                                <input type="date" name="tanggal_realisasi" 
                                       value="{{ old('tanggal_realisasi', $submission->tanggal_realisasi ? $submission->tanggal_realisasi->format('Y-m-d') : '') }}"
                                       class="form-control text-xs">
                                @error('tanggal_realisasi') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Live Savings Preview Badge --}}
                        <div x-show="selisih !== null" x-cloak class="p-2.5 rounded-xl border flex items-center justify-between text-xs transition-all"
                             :class="selisih >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                            <span class="font-semibold" x-text="selisih >= 0 ? '🎉 Efisiensi Pengadaan (Hemat):' : '⚠️ Kelebihan Biaya (Over Budget):'"></span>
                            <span class="font-extrabold text-sm" x-text="(selisih >= 0 ? '+Rp ' : '-Rp ') + Math.abs(selisih).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- Upload Bukti Pembelian Sah --}}
                        <div class="pt-2 border-t border-slate-200/80">
                            <label class="form-label">
                                Bukti Pembelian Sah (Nota / Kuitansi / Faktur / Foto Barang)
                            </label>
                            <input type="file" name="bukti_pembelian" accept=".jpg,.jpeg,.png,.webp,.pdf"
                                   class="form-control file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Format didukung: Gambar (JPG, PNG, WEBP) atau Dokumen PDF. Maksimal 5MB.</p>
                            @error('bukti_pembelian') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Target Month & Estimasi adjustment --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Penyesuaian Estimasi Awal (Rp)</label>
                            <input type="number" name="harga_satuan" value="{{ old('harga_satuan', $submission->harga_satuan) }}"
                                   class="form-control" min="0" step="1000" placeholder="0">
                        </div>
                        <div>
                            <label class="form-label">Target Alokasi Bulan</label>
                            <select name="target_bulan" class="form-control">
                                <option value="{{ $currentMonth }}" {{ old('target_bulan', $submission->target_bulan) === $currentMonth ? 'selected' : '' }}>
                                    Bulan Ini ({{ $currentMonthName }})
                                </option>
                                <option value="{{ $nextMonth }}" {{ old('target_bulan', $submission->target_bulan) === $nextMonth ? 'selected' : '' }}>
                                    Bulan Depan ({{ $nextMonthName }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">
                            Catatan / Balasan Admin
                            <span x-show="selectedStatus !== 'Menunggu'" class="text-red-500">(Wajib diisi jika status diubah)</span>
                        </label>
                        <textarea name="pesan" rows="3" class="form-control"
                                  placeholder="Tuliskan catatan, informasi pembelian, atau alasan penolakan/persetujuan...">{{ old('pesan') }}</textarea>
                        @error('pesan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="text-right pt-2">
                        <button type="submit" class="btn btn-primary px-6">
                            Simpan Perubahan & Realisasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: History Log --}}
        <div class="card flex flex-col max-h-[700px]">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Catatan & Log</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Linimasa proses pengajuan</p>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-xl">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="font-bold text-blue-800">Pengajuan Masuk</span>
                        <span class="text-slate-500">{{ $submission->created_at->format('d/m, H:i') }}</span>
                    </div>
                    <p class="text-xs text-blue-900">
                        Diajukan oleh <strong>{{ $submission->user->name ?? 'User' }}</strong> dengan estimasi <strong>{{ $submission->formatted_total_biaya }}</strong> (Target: {{ $submission->target_bulan_label }}).
                    </p>
                </div>

                @forelse($submission->replies as $reply)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-900">{{ $reply->admin->name ?? 'Admin' }}</span>
                            <span class="text-slate-400">{{ $reply->created_at->format('d/m/Y, H:i') }}</span>
                        </div>
                        @if($reply->status_setelah_balasan)
                            <div class="text-[11px] text-slate-500">
                                Status diubah &rarr; <span class="badge {{ $sMap[$reply->status_setelah_balasan] ?? 'badge-menunggu' }} text-[10px]">{{ $reply->status_setelah_balasan }}</span>
                            </div>
                        @endif
                        <div class="p-2.5 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 leading-relaxed">
                            {{ $reply->pesan }}
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada catatan atau balasan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
