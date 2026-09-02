@extends('layouts.app')

@section('title', 'Detail Pengajuan - ' . $submission->nomor_pengajuan)
@section('page_title', 'Detail Pengajuan Barang')
@section('page_subtitle', 'Informasi pengajuan nomor ' . $submission->nomor_pengajuan)

@section('content')

@php
    $currentMonth = now()->format('Y-m');
    $nextMonth = now()->addMonth()->format('Y-m');
@endphp

<div class="max-w-5xl mx-auto">
    {{-- Header Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <a href="{{ route('user.submissions.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-500 hover:text-blue-600 transition-colors">
            &larr; Kembali ke Daftar Pengajuan
        </a>
        <div class="flex items-center gap-2">
            @if($submission->isPending())
                <a href="{{ route('user.submissions.edit', $submission->id) }}" class="btn btn-secondary btn-sm">
                    ✏️ Edit Pengajuan
                </a>
                <form method="POST" action="{{ route('user.submissions.destroy', $submission->id) }}"
                      onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus pengajuan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">🗑 Batalkan</button>
                </form>
            @else
                <span class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium border border-slate-200">
                    Pengajuan sedang / sudah diproses admin
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Submission Details --}}
        <div class="lg:col-span-2 card p-5 sm:p-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        NOMOR PENGAJUAN
                    </div>
                    <div class="font-mono text-base sm:text-lg font-extrabold text-blue-700 mt-0.5">
                        {{ $submission->nomor_pengajuan }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $pMap = ['Rendah'=>'badge-rendah','Sedang'=>'badge-sedang','Tinggi'=>'badge-tinggi','Mendesak'=>'badge-mendesak'];
                        $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai'];
                    @endphp
                    <span class="badge {{ $pMap[$submission->prioritas] ?? 'badge-sedang' }}">{{ $submission->prioritas }}</span>
                    <span class="badge {{ $sMap[$submission->status] ?? 'badge-menunggu' }}">{{ $submission->status }}</span>
                </div>
            </div>

            {{-- Detail Grid --}}
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
                    <div class="text-xs font-semibold text-slate-500">Divisi Pemohon</div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 mt-0.5">
                        {{ $submission->division->nama_divisi ?? '-' }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500">Waktu Pengajuan</div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ $submission->created_at->translatedFormat('d F Y, H:i') }} WITA</div>
                </div>
            </div>

            {{-- Biaya Box & Realisasi --}}
            <div class="p-4 sm:p-5 bg-gradient-to-br from-emerald-50/90 to-teal-50/50 border border-emerald-200 rounded-2xl space-y-3.5">
                <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2.5">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Rincian Biaya & Realisasi Pengadaan
                    </span>
                    <span class="text-xs text-emerald-700 font-semibold">
                        Target: {{ $submission->target_bulan_label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 shadow-xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Estimasi Pengajuan</div>
                        <div class="text-sm font-bold text-slate-900 mt-1">{{ $submission->formatted_total_biaya }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">({{ $submission->jumlah }} {{ $submission->satuan }} &times; {{ $submission->formatted_harga_satuan }})</div>
                    </div>

                    <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 shadow-xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Harga Beli Aktual</div>
                        <div class="text-sm font-bold {{ $submission->biaya_realisasi !== null ? 'text-emerald-700' : 'text-slate-400' }} mt-1">
                            {{ $submission->biaya_realisasi !== null ? $submission->formatted_biaya_realisasi : 'Menunggu pembelian' }}
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            @if($submission->harga_beli_satuan)
                                @ {{ $submission->formatted_harga_beli_satuan }} / {{ $submission->satuan }}
                            @else
                                Belum direalisasi
                            @endif
                        </div>
                    </div>

                    <div class="p-3 bg-white/80 rounded-xl border border-emerald-100 shadow-xs">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Selisih Penghematan</div>
                        @if($submission->biaya_realisasi !== null)
                            @php $selisih = $submission->selisih_biaya; @endphp
                            <div class="text-sm font-extrabold {{ $selisih >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                                {{ $submission->formatted_selisih_biaya }}
                            </div>
                            <div class="text-[11px] {{ $selisih >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-0.5 font-medium">
                                {{ $selisih >= 0 ? 'Efisiensi Biaya' : 'Over Budget' }}
                            </div>
                        @else
                            <div class="text-sm font-bold text-slate-400 mt-1">-</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Setelah pembelian</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bukti Pembelian Sah (Nota / Kuitansi / Faktur) --}}
            @if($submission->bukti_pembelian)
                <div class="p-4 sm:p-5 bg-emerald-50/90 border border-emerald-200 rounded-2xl" x-data="{ zoomProof: false }">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">
                                Bukti Pembelian Sah (Nota / Kuitansi Resmi)
                            </span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            Terverifikasi Sah
                        </span>
                    </div>

                    @if($submission->isProofImage())
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <img src="{{ asset('storage/' . $submission->bukti_pembelian) }}" 
                                 alt="Bukti Pembelian" 
                                 @click="zoomProof = true"
                                 class="w-44 h-28 object-cover rounded-xl border border-emerald-300 shadow-sm cursor-pointer hover:opacity-90 transition-opacity">
                            
                            <div class="space-y-1.5 text-xs text-slate-700">
                                <div class="font-bold text-slate-900">Nota / Kuitansi Transaksi Resmi</div>
                                <div class="text-slate-500">Bukti sah pengadaan barang telah diunggah oleh Administrator.</div>
                                <div class="pt-1 flex items-center gap-2">
                                    <button @click="zoomProof = true" class="btn btn-secondary btn-sm">
                                        🔍 Pratinjau Bukti
                                    </button>
                                    <a href="{{ asset('storage/' . $submission->bukti_pembelian) }}" target="_blank" download
                                       class="btn btn-secondary btn-sm inline-flex items-center gap-1">
                                        <span>📥 Unduh Asli</span>
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
                                <div class="text-xs text-slate-500 mt-2 font-medium">Bukti Pembelian Sah - {{ $submission->nomor_pengajuan }}</div>
                            </div>
                        </div>
                    @elseif($submission->isProofPdf())
                        <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-emerald-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold text-xs">
                                    PDF
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">Dokumen Faktur / Kuitansi Sah (PDF)</div>
                                    <div class="text-[11px] text-slate-500">File bukti pembayaran pengadaan barang resmi</div>
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
                    Alasan Kebutuhan Barang
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $submission->alasan }}
                </div>
            </div>

            {{-- Foto --}}
            @if($submission->foto_barang)
                <div class="pt-2 border-t border-slate-100" x-data="{ zoom: false }">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                        Foto Barang Terlampir
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

        {{-- Right Column: Admin Replies & Status Log --}}
        <div class="card flex flex-col max-h-[640px]">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Catatan Admin & Status</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Riwayat tanggapan admin</p>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-xl">
                    <div class="flex items-center justify-between mb-1 text-xs">
                        <span class="font-bold text-blue-800">Pengajuan Dibuat</span>
                        <span class="text-slate-500">{{ $submission->created_at->format('d/m, H:i') }}</span>
                    </div>
                    <p class="text-xs text-blue-900">
                        Diajukan oleh <strong>{{ $submission->user->name ?? 'Pemohon' }}</strong> dengan estimasi <strong>{{ $submission->formatted_total_biaya }}</strong> (Target: {{ $submission->target_bulan_label }}).
                    </p>
                </div>

                @forelse($submission->replies as $reply)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-900">{{ $reply->admin->name ?? 'Admin Logistik' }}</span>
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
                        Belum ada balasan atau catatan dari admin.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
