@extends('layouts.app')

@section('title', 'Rekap Pengeluaran & Log')
@section('page_title', 'Rekapitulasi Pengeluaran & Log Anggaran')
@section('page_subtitle', 'Laporan alokasi pengeluaran barang per divisi dan riwayat log aktivitas PT Jamkrida Kalsel')

@section('content')

{{-- Financial Summary KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card p-5 border-l-4 border-l-blue-600">
        <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600">
            Estimasi Diajukan ({{ $currentMonthName }})
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
            Rp {{ number_format($totalExpenseThisMonth, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Total usulan dari seluruh divisi
        </div>
    </div>

    <div class="card p-5 border-l-4 border-l-emerald-600">
        <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">
            Realisasi Pembelian ({{ $currentMonthName }})
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-1">
            Rp {{ number_format($realizedExpenseThisMonth, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Pengadaan disetujui & selesai
        </div>
    </div>

    <div class="card p-5 border-l-4 {{ $savingsThisMonth >= 0 ? 'border-l-teal-500' : 'border-l-rose-500' }}">
        <div class="text-[11px] font-bold uppercase tracking-wider {{ $savingsThisMonth >= 0 ? 'text-teal-600' : 'text-rose-600' }}">
            Efisiensi Anggaran (Bulan Ini)
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold {{ $savingsThisMonth >= 0 ? 'text-teal-700' : 'text-rose-700' }} mt-1">
            {{ $savingsThisMonth >= 0 ? '+Rp ' : '-Rp ' }}{{ number_format(abs($savingsThisMonth), 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            {{ $savingsThisMonth >= 0 ? '🎉 Penghematan dari estimasi' : '⚠️ Kelebihan biaya dari estimasi' }}
        </div>
    </div>

    <div class="card p-5 border-l-4 border-l-purple-600">
        <div class="text-[11px] font-bold uppercase tracking-wider text-purple-600">
            Total Realisasi Pengadaan
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-purple-700 mt-1">
            Rp {{ number_format($totalAllTimeExpense, 0, ',', '.') }}
        </div>
        <div class="text-xs text-slate-500 mt-1">
            Total Efisiensi: <strong class="{{ $totalAllTimeSavings >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $totalAllTimeSavings >= 0 ? '+Rp ' : '-Rp ' }}{{ number_format(abs($totalAllTimeSavings), 0, ',', '.') }}</strong>
        </div>
    </div>
</div>

{{-- SECTION 1: REKAPITULASI DANA PER DIVISI --}}
<div class="card mb-6 overflow-hidden">
    <div class="card-header">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Rekapitulasi Pengeluaran Per Divisi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Perbandingan alokasi biaya pengadaan dan realisasi per Kepala Divisi</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">Nama Divisi & Kepala Divisi</th>
                    <th class="py-3.5 px-4 text-center">Item Bulan Ini</th>
                    <th class="py-3.5 px-4 text-right">Estimasi Diajukan ({{ $currentMonthName }})</th>
                    <th class="py-3.5 px-4 text-right">Realisasi Pembelian</th>
                    <th class="py-3.5 px-4 text-right">Selisih (Hemat/Over)</th>
                    <th class="py-3.5 px-4 text-center">Item Bulan Depan</th>
                    <th class="py-3.5 px-4 text-right">Estimasi Bulan Depan ({{ $nextMonthName }})</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($divisionExpenseData as $report)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $report['nama_divisi'] }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                <span class="text-blue-600">👤</span>
                                <span>Kepala Divisi: <strong>{{ $report['head_user'] }}</strong></span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-blue-600">
                            {{ $report['this_month_count'] }} item
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($report['this_month_total'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-semibold text-emerald-700">
                            Rp {{ number_format($report['this_month_realized'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold">
                            @if($report['this_month_selisih'] > 0)
                                <span class="text-emerald-600">+Rp {{ number_format($report['this_month_selisih'], 0, ',', '.') }} <span class="text-[10px] font-normal">(Hemat)</span></span>
                            @elseif($report['this_month_selisih'] < 0)
                                <span class="text-rose-600">-Rp {{ number_format(abs($report['this_month_selisih']), 0, ',', '.') }} <span class="text-[10px] font-normal">(Over)</span></span>
                            @else
                                <span class="text-slate-400">Rp 0</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-sky-600">
                            {{ $report['next_month_count'] }} item
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($report['next_month_total'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('admin.submissions.index', ['division_id' => $report['id']]) }}"
                               class="btn btn-secondary btn-sm">
                                Lihat Data
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data divisi terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION 2: DAFTAR RINCIAN PENGADAAN & BIAYA --}}
<div class="card mb-6 overflow-hidden">
    <div class="card-header">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Rincian Pengeluaran & Selisih Pengadaan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar item pengadaan, perbandingan harga pengajuan vs harga beli riil, dan status nota bukti sah</p>
        </div>
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex items-center gap-2">
            <select name="month" class="form-control py-1 px-2 text-xs" onchange="this.form.submit()">
                <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                <option value="{{ $currentMonth }}" {{ $selectedMonth === $currentMonth ? 'selected' : '' }}>Bulan Ini ({{ $currentMonthName }})</option>
                <option value="{{ $nextMonth }}" {{ $selectedMonth === $nextMonth ? 'selected' : '' }}>Bulan Depan ({{ $nextMonthName }})</option>
            </select>
            <select name="division_id" class="form-control py-1 px-2 text-xs" onchange="this.form.submit()">
                <option value="all">Semua Divisi</option>
                @foreach($divisions as $div)
                    <option value="{{ $div->id }}" {{ $selectedDivision == $div->id ? 'selected' : '' }}>{{ $div->nama_divisi }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control py-1 px-2 text-xs" onchange="this.form.submit()">
                <option value="all">Semua Status</option>
                @foreach(['Menunggu','Diproses','Disetujui','Selesai','Ditolak'] as $st)
                    <option value="{{ $st }}" {{ $selectedStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">No. Pengajuan</th>
                    <th class="py-3.5 px-4">Nama Barang</th>
                    <th class="py-3.5 px-4">Divisi & Pemohon</th>
                    <th class="py-3.5 px-4">Jumlah</th>
                    <th class="py-3.5 px-4 text-right">Harga Pengajuan</th>
                    <th class="py-3.5 px-4 text-right">Harga Beli Riil</th>
                    <th class="py-3.5 px-4 text-right">Selisih</th>
                    <th class="py-3.5 px-4 text-center">Bukti Nota Sah</th>
                    <th class="py-3.5 px-4 text-center">Target Bulan</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($submissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-blue-600 text-xs">
                            <a href="{{ route('admin.submissions.show', $sub->id) }}" class="hover:underline">
                                {{ $sub->nomor_pengajuan }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $sub->nama_barang }}</div>
                            <div class="text-[11px] text-slate-500">{{ $sub->category->nama_kategori ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700">
                                {{ $sub->division->nama_divisi ?? '-' }}
                            </span>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $sub->user->name ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $sub->jumlah }} {{ $sub->satuan }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="font-bold text-slate-900">{{ $sub->formatted_total_biaya }}</div>
                            <div class="text-[10px] text-slate-400">@ {{ $sub->formatted_harga_satuan }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($sub->biaya_realisasi !== null)
                                <div class="font-bold text-emerald-700">{{ $sub->formatted_biaya_realisasi }}</div>
                                <div class="text-[10px] text-slate-500">@ {{ $sub->formatted_harga_beli_satuan }}</div>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($sub->biaya_realisasi !== null)
                                @php $sel = $sub->selisih_biaya; @endphp
                                @if($sel > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        +Rp {{ number_format($sel, 0, ',', '.') }} (Hemat)
                                    </span>
                                @elseif($sel < 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        -Rp {{ number_format(abs($sel), 0, ',', '.') }} (Over)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600">
                                        Rp 0 (Sesuai)
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($sub->bukti_pembelian)
                                <a href="{{ asset('storage/' . $sub->bukti_pembelian) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                    <span>{{ $sub->isProofPdf() ? '📄' : '📷' }}</span>
                                    <span>Lihat Bukti</span>
                                </a>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($sub->target_bulan === $currentMonth)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Bulan Ini
                                </span>
                            @elseif($sub->target_bulan === $nextMonth)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    Bulan Depan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-600">
                                    {{ $sub->target_bulan_label }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai']; @endphp
                            <span class="badge {{ $sMap[$sub->status] ?? 'badge-menunggu' }}">{{ $sub->status }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('admin.submissions.show', $sub->id) }}" class="btn btn-secondary btn-sm">
                                Tinjau
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center text-slate-400 text-xs sm:text-sm">
                            Tidak ada data pengeluaran pengadaan barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($submissions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $submissions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

{{-- SECTION 3: AUDIT LOG TIMELINE STREAM --}}
<div class="card overflow-hidden">
    <div class="card-header">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Log Riwayat Aktivitas & Anggaran</h3>
            <p class="text-xs text-slate-500 mt-0.5">Catatan riwayat perubahan status, nominal biaya, dan pengajuan baru</p>
        </div>
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex items-center gap-2">
            <select name="log_division" class="form-control py-1 px-2 text-xs" onchange="this.form.submit()">
                <option value="all">Semua Divisi</option>
                @foreach($divisions as $div)
                    <option value="{{ $div->id }}" {{ request('log_division') == $div->id ? 'selected' : '' }}>{{ $div->nama_divisi }}</option>
                @endforeach
            </select>
            <select name="log_month" class="form-control py-1 px-2 text-xs" onchange="this.form.submit()">
                <option value="all">Semua Bulan</option>
                <option value="{{ $currentMonth }}" {{ request('log_month') === $currentMonth ? 'selected' : '' }}>Bulan Ini ({{ $currentMonthName }})</option>
                <option value="{{ $nextMonth }}" {{ request('log_month') === $nextMonth ? 'selected' : '' }}>Bulan Depan ({{ $nextMonthName }})</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">Waktu</th>
                    <th class="py-3.5 px-4">Tipe Aktivitas</th>
                    <th class="py-3.5 px-4">No. Pengajuan</th>
                    <th class="py-3.5 px-4">Divisi</th>
                    <th class="py-3.5 px-4">User</th>
                    <th class="py-3.5 px-4 text-right">Nominal</th>
                    <th class="py-3.5 px-4">Periode</th>
                    <th class="py-3.5 px-4">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($expenseLogs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 text-slate-500 text-xs whitespace-nowrap">
                            {{ $log->created_at->translatedFormat('d/m/Y, H:i') }}
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $bClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                if (str_contains($log->tipe, 'Baru')) { $bClass = 'bg-blue-50 text-blue-700 border-blue-200'; }
                                elseif (str_contains($log->tipe, 'Persetujuan')) { $bClass = 'bg-emerald-50 text-emerald-700 border-emerald-200'; }
                                elseif (str_contains($log->tipe, 'Realisasi')) { $bClass = 'bg-purple-50 text-purple-700 border-purple-200'; }
                                elseif (str_contains($log->tipe, 'Penolakan') || str_contains($log->tipe, 'Pembatalan')) { $bClass = 'bg-rose-50 text-rose-700 border-rose-200'; }
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $bClass }}">
                                {{ $log->tipe }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-blue-600 text-xs">
                            @if($log->submission)
                                <a href="{{ route('admin.submissions.show', $log->submission_id) }}" class="hover:underline">
                                    {{ $log->submission->nomor_pengajuan }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-700">
                            {{ $log->division->nama_divisi ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">
                            {{ $log->user->name ?? 'Sistem' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            {{ $log->formatted_nominal }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs">
                            {{ $log->bulan_periode_label }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 text-xs max-w-xs">
                            {{ $log->keterangan }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400 text-xs sm:text-sm">
                            Belum ada catatan log aktivitas pengeluaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenseLogs->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $expenseLogs->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection
