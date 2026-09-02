@extends('layouts.app')

@section('title', 'Daftar Pengajuan Masuk')
@section('page_title', 'Daftar Pengajuan Barang')
@section('page_subtitle', 'Kelola permohonan pengadaan barang dari seluruh divisi PT Jamkrida Kalsel')

@section('content')

@php
    $currentMonth = now()->format('Y-m');
    $nextMonth = now()->addMonth()->format('Y-m');
    $currentMonthName = now()->translatedFormat('F Y');
    $nextMonthName = now()->addMonth()->translatedFormat('F Y');
@endphp

{{-- Header Action Bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h2 class="text-base sm:text-lg font-bold text-slate-900">Data Pengajuan Masuk</h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Total data: <strong class="text-slate-800">{{ $submissions->total() }} Pengajuan</strong></p>
    </div>
    <div>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary shadow-sm">
            📊 Rekap Pengeluaran & Log
        </a>
    </div>
</div>

{{-- Filter Toolbar --}}
<div class="card p-4 sm:p-5 mb-5">
    <form method="GET" action="{{ route('admin.submissions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
        <div class="lg:col-span-2">
            <label class="form-label">Pencarian Barang / User / No.</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Ketik kata kunci...">
        </div>
        <div>
            <label class="form-label">Target Bulan</label>
            <select name="target_bulan" class="form-control">
                <option value="">Semua Bulan</option>
                <option value="{{ $currentMonth }}" {{ request('target_bulan') === $currentMonth ? 'selected' : '' }}>Bulan Ini ({{ $currentMonthName }})</option>
                <option value="{{ $nextMonth }}" {{ request('target_bulan') === $nextMonth ? 'selected' : '' }}>Bulan Depan ({{ $nextMonthName }})</option>
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach(['Menunggu','Diproses','Disetujui','Ditolak','Selesai'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Divisi</label>
            <select name="division_id" class="form-control">
                <option value="">Semua Divisi</option>
                @foreach($divisions as $div)
                    <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>{{ $div->nama_divisi }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary flex-1">Filter</button>
            <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3.5 px-4">No. Pengajuan</th>
                    <th class="py-3.5 px-4">Pemohon</th>
                    <th class="py-3.5 px-4">Divisi</th>
                    <th class="py-3.5 px-4">Nama Barang</th>
                    <th class="py-3.5 px-4">Jumlah</th>
                    <th class="py-3.5 px-4 text-right">Total Biaya</th>
                    <th class="py-3.5 px-4 text-center">Target Bulan</th>
                    <th class="py-3.5 px-4">Prioritas</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Tanggal</th>
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
                            <div class="font-bold text-slate-900">{{ $sub->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $sub->user->email ?? '' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700">
                                {{ $sub->division->nama_divisi ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $sub->nama_barang }}</div>
                            <div class="text-[11px] text-slate-500">{{ $sub->jenis_pengajuan }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900">{{ $sub->jumlah }} {{ $sub->satuan }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                            {{ $sub->formatted_total_biaya }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($sub->target_bulan === $currentMonth)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Bulan Ini ({{ $sub->target_bulan_label }})
                                </span>
                            @elseif($sub->target_bulan === $nextMonth)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                    Bulan Depan ({{ $sub->target_bulan_label }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-600">
                                    {{ $sub->target_bulan_label }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php $pMap = ['Rendah'=>'badge-rendah','Sedang'=>'badge-sedang','Tinggi'=>'badge-tinggi','Mendesak'=>'badge-mendesak']; @endphp
                            <span class="badge {{ $pMap[$sub->prioritas] ?? 'badge-sedang' }}">{{ $sub->prioritas }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            @php $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai']; @endphp
                            <span class="badge {{ $sMap[$sub->status] ?? 'badge-menunggu' }}">{{ $sub->status }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $sub->created_at->format('d/m/Y') }}</td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('admin.submissions.show', $sub->id) }}" class="btn btn-primary btn-sm">
                                Tinjau
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-400 text-xs sm:text-sm">
                            Tidak ada pengajuan yang sesuai dengan filter.
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

@endsection
