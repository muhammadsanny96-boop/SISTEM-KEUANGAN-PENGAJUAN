@extends('layouts.app')

@section('title', 'Dashboard Pegawai')
@section('page_title', 'Dashboard Pegawai')
@section('page_subtitle', 'Selamat datang di Sistem Pengajuan Barang PT Jamkrida Kalsel')

@section('content')

{{-- Welcome Banner --}}
<div class="card bg-linear-to-r from-blue-900 to-blue-600 text-white p-5 sm:p-6 mb-6 border-none shadow-md">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white rounded-xl flex items-center justify-center p-1.5 shrink-0 shadow-sm">
                <img src="{{ asset('images/logo-jamkrida.png') }}" alt="Logo Jamkrida" class="max-w-full max-h-full object-contain">
            </div>
            <div>
                <div class="text-xs text-blue-200 font-semibold mb-0.5">
                    DIVISI: {{ strtoupper($user->division->nama_divisi ?? 'UMUM') }} &bull; PT JAMKRIDA KALSEL
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight">
                    Selamat Datang, {{ $user->name }}
                </h2>
                <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-xl leading-relaxed">
                    Gunakan aplikasi ini untuk mengajukan kebutuhan barang operasional, perlengkapan kantor, atau penggantian barang rusak untuk divisi Anda.
                </p>
            </div>
        </div>
        <div class="shrink-0 w-full sm:w-auto">
            <a href="{{ route('user.submissions.create') }}" class="btn bg-white hover:bg-slate-50 text-blue-900 font-bold px-4 py-2.5 w-full sm:w-auto shadow-sm">
                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Pengajuan Baru
            </a>
        </div>
    </div>
</div>

{{-- Division Monthly Expenses --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
    <div class="card p-5 border-l-4 border-l-blue-600">
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600">
            Pengeluaran Divisi Bulan Ini ({{ $currentMonthName }})
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1.5">
            Rp {{ number_format($divisionExpenseThisMonth, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Total pengadaan aktif Divisi {{ $user->division->nama_divisi ?? 'Anda' }}
        </div>
    </div>

    <div class="card p-5 border-l-4 border-l-sky-500">
        <div class="text-xs font-bold uppercase tracking-wider text-sky-600">
            Estimasi Anggaran Bulan Depan ({{ $nextMonthName }})
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1.5">
            Rp {{ number_format($divisionExpenseNextMonth, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Rencana alokasi kebutuhan pengadaan bulan depan
        </div> 
    </div>
</div>

{{-- Metric Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs font-semibold text-slate-500">Total Pengajuan</div>
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight mt-0.5">{{ $totalSubmissions }}</div>
            <div class="text-[11px] text-slate-400">Semua riwayat</div>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs font-semibold text-slate-500">Sedang Diproses</div>
            <div class="text-xl sm:text-2xl font-extrabold text-amber-600 leading-tight mt-0.5">{{ $inProgressCount }}</div>
            <div class="text-[11px] text-slate-400">Dalam proses</div>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs font-semibold text-slate-500">Disetujui</div>
            <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 leading-tight mt-0.5">{{ $approvedCount }}</div>
            <div class="text-[11px] text-slate-400">Siap pengadaan</div>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs font-semibold text-slate-500">Ditolak</div>
            <div class="text-xl sm:text-2xl font-extrabold text-rose-600 leading-tight mt-0.5">{{ $rejectedCount }}</div>
            <div class="text-[11px] text-slate-400">Tidak disetujui</div>
        </div>
    </div>
</div>

{{-- Recent Submissions --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Pengajuan Barang Terbaru</h3>
            <p class="text-xs text-slate-500 mt-0.5">Riwayat pengajuan pengadaan barang dari divisi Anda</p>
        </div>
        <a href="{{ route('user.submissions.index') }}" class="btn btn-secondary btn-sm">
            Lihat Semua ({{ $totalSubmissions }}) &rarr;
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">No. Pengajuan</th>
                    <th class="py-3 px-4">Nama Barang</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Jumlah</th>
                    <th class="py-3 px-4 text-right">Total Biaya</th>
                    <th class="py-3 px-4">Prioritas</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentSubmissions as $submission)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-blue-600 text-xs">
                            <a href="{{ route('user.submissions.show', $submission->id) }}" class="hover:underline">
                                {{ $submission->nomor_pengajuan }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $submission->nama_barang }}</div>
                            <div class="text-[11px] text-slate-500">{{ $submission->jenis_pengajuan }}</div>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $submission->category->nama_kategori ?? '-' }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $submission->jumlah }} {{ $submission->satuan }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            {{ $submission->formatted_total_biaya }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $pMap = ['Rendah'=>'badge-rendah','Sedang'=>'badge-sedang','Tinggi'=>'badge-tinggi','Mendesak'=>'badge-mendesak'];
                            @endphp
                            <span class="badge {{ $pMap[$submission->prioritas] ?? 'badge-sedang' }}">{{ $submission->prioritas }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai'];
                            @endphp
                            <span class="badge {{ $sMap[$submission->status] ?? 'badge-menunggu' }}">{{ $submission->status }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-xs">{{ $submission->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('user.submissions.show', $submission->id) }}" class="btn btn-secondary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 text-xs sm:text-sm">
                            Belum ada pengajuan barang. Silakan klik tombol <strong>Buat Pengajuan Baru</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
