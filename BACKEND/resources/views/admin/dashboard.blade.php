@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('page_title', 'Dashboard Administrator')
@section('page_subtitle', 'Ringkasan pengajuan barang dan pengeluaran divisi PT Jamkrida Kalsel')

@section('content')

{{-- Financial Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
    {{-- Bulan Ini --}}
    <div class="card bg-linear-to-r from-blue-900 to-blue-600 p-5 sm:p-6 text-white border-none shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-bold tracking-wider text-blue-200 uppercase">
                    Total Pengeluaran Bulan Ini ({{ $currentMonthName }})
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">
                    Rp {{ number_format($expenseThisMonth, 0, ',', '.') }}
                </div>
                <div class="text-xs text-blue-100 mt-1">
                    Realisasi Selesai / Disetujui: <strong class="text-white">Rp {{ number_format($realizedThisMonth, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.expenses.index') }}" class="btn bg-white hover:bg-slate-50 text-blue-900 font-bold px-3.5 py-2 text-xs shadow-sm">
                    Lihat Rekap &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- Bulan Depan --}}
    <div class="card bg-linear-to-r from-slate-900 to-sky-700 p-5 sm:p-6 text-white border-none shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-[11px] font-bold tracking-wider text-sky-200 uppercase">
                    Estimasi Pengeluaran Bulan Depan ({{ $nextMonthName }})
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-1">
                    Rp {{ number_format($expenseNextMonth, 0, ',', '.') }}
                </div>
                <div class="text-xs text-sky-100 mt-1">
                    Perkiraan kebutuhan seluruh divisi
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.expenses.index') }}" class="btn bg-white/20 hover:bg-white/30 text-white border border-white/30 px-3.5 py-2 text-xs">
                    Log Anggaran &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Top 6 Stat Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-slate-500">Total Pengajuan</div>
        <div class="text-xl sm:text-2xl font-extrabold text-slate-900 my-1">{{ $totalSubmissions }}</div>
        <div class="text-[10.5px] text-slate-400">Semua divisi</div>
    </div>

    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-amber-600">Menunggu</div>
        <div class="text-xl sm:text-2xl font-extrabold text-amber-600 my-1">{{ $pendingCount }}</div>
        <div class="text-[10.5px] text-slate-400">Perlu ditinjau</div>
    </div>

    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-blue-600">Diproses</div>
        <div class="text-xl sm:text-2xl font-extrabold text-blue-600 my-1">{{ $inProgressCount }}</div>
        <div class="text-[10.5px] text-slate-400">Dalam proses</div>
    </div>

    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-emerald-600">Disetujui</div>
        <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 my-1">{{ $approvedCount }}</div>
        <div class="text-[10.5px] text-slate-400">Disetujui</div>
    </div>

    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-rose-600">Ditolak</div>
        <div class="text-xl sm:text-2xl font-extrabold text-rose-600 my-1">{{ $rejectedCount }}</div>
        <div class="text-[10.5px] text-slate-400">Ditolak</div>
    </div>

    <div class="card p-3.5 flex flex-col justify-between">
        <div class="text-[11px] font-semibold text-purple-600">Selesai</div>
        <div class="text-xl sm:text-2xl font-extrabold text-purple-600 my-1">{{ $completedCount }}</div>
        <div class="text-[10.5px] text-slate-400">Barang diterima</div>
    </div>
</div>

{{-- Secondary Info Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Karyawan</div>
            <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">{{ $totalUsers }} User</div>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Divisi</div>
            <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">{{ $totalDivisions }} Divisi</div>
        </div>
    </div>

    <div class="card p-4 flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <div>
            <div class="text-xs text-slate-500 font-medium">Total Kategori</div>
            <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">{{ $totalCategories }} Kategori</div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
    {{-- Bar Chart --}}
    <div class="card lg:col-span-2">
        <div class="card-header">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900">Pengajuan Per Divisi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Grafik jumlah permohonan dari masing-masing divisi</p>
            </div>
        </div>
        <div class="p-4 sm:p-5">
            <div class="h-60 sm:h-64 relative">
                <canvas id="divisionChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Doughnut Chart --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900">Status Pengajuan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Persentase status disposisi</p>
            </div>
        </div>
        <div class="p-4 sm:p-5 flex items-center justify-center h-60 sm:h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Submissions Table --}}
<div class="card">
    <div class="card-header">
        <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Pengajuan Barang Perlu Ditinjau</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar pengajuan terbaru yang masuk ke sistem</p>
        </div>
        <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary btn-sm">
            Lihat Semua &rarr;
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs sm:text-sm">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4">No. Pengajuan</th>
                    <th class="py-3 px-4">Pemohon</th>
                    <th class="py-3 px-4">Divisi</th>
                    <th class="py-3 px-4">Nama Barang</th>
                    <th class="py-3 px-4 text-right">Total Biaya</th>
                    <th class="py-3 px-4">Prioritas</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Tanggal</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentSubmissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-blue-600 text-xs">
                            <a href="{{ route('admin.submissions.show', $sub->id) }}" class="hover:underline">
                                {{ $sub->nomor_pengajuan }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $sub->user->name ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $sub->user->email ?? '' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700">
                                {{ $sub->division->nama_divisi ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $sub->nama_barang }}</div>
                            <div class="text-[11px] text-slate-500">{{ $sub->jumlah }} {{ $sub->satuan }} &bull; {{ $sub->category->nama_kategori ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">
                            {{ $sub->formatted_total_biaya }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $pMap = ['Rendah'=>'badge-rendah','Sedang'=>'badge-sedang','Tinggi'=>'badge-tinggi','Mendesak'=>'badge-mendesak'];
                            @endphp
                            <span class="badge {{ $pMap[$sub->prioritas] ?? 'badge-sedang' }}">{{ $sub->prioritas }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $sMap = ['Menunggu'=>'badge-menunggu','Diproses'=>'badge-diproses','Disetujui'=>'badge-disetujui','Ditolak'=>'badge-ditolak','Selesai'=>'badge-selesai'];
                            @endphp
                            <span class="badge {{ $sMap[$sub->status] ?? 'badge-menunggu' }}">{{ $sub->status }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-xs">{{ $sub->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('admin.submissions.show', $sub->id) }}" class="btn btn-primary btn-sm">
                                Tinjau
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 text-xs sm:text-sm">
                            Tidak ada pengajuan yang memerlukan tindakan saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bar Chart
    const divCtx = document.getElementById('divisionChart').getContext('2d');
    new Chart(divCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($divisionLabels) !!},
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: {!! json_encode($divisionCounts) !!},
                backgroundColor: '#2563eb',
                borderRadius: 6,
                maxBarThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#64748b' }, grid: { color: '#f1f5f9' } },
                x: { ticks: { color: '#475569', font: { weight: '600' } }, grid: { display: false } }
            }
        }
    });

    // Doughnut Chart
    const stCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(stCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($statusLabels) !!},
            datasets: [{
                data: {!! json_encode($statusCounts) !!},
                backgroundColor: ['#f59e0b', '#2563eb', '#16a34a', '#dc2626', '#7c3aed'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, font: { size: 11 }, padding: 8 }
                }
            }
        }
    });
});
</script>
@endpush
