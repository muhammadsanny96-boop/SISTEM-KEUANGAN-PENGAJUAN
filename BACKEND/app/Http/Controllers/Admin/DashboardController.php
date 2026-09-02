<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Admin dashboard with system-wide analytics, financial overview, and visual charts.
     */
    public function index(Request $request)
    {
        // 1. Hitung Jumlah Status Pengajuan
        $totalSubmissions = Submission::count();
        $pendingCount = Submission::where('status', 'Menunggu')->count();
        $inProgressCount = Submission::where('status', 'Diproses')->count();
        $approvedCount = Submission::where('status', 'Disetujui')->count();
        $rejectedCount = Submission::where('status', 'Ditolak')->count();
        $completedCount = Submission::where('status', 'Selesai')->count();

        $totalUsers = User::where('role', 'user')->count();
        $totalDivisions = Division::count();
        $totalCategories = Category::count();

        // 2. Format Periode Bulan Ini & Bulan Depan
        $currentMonthCode = now()->format('Y-m'); // "2026-09"
        $nextMonthCode = now()->addMonth()->format('Y-m'); // "2026-10"
        
        $currentMonthName = now()->translatedFormat('F Y'); // "September 2026"
        $nextMonthName = now()->addMonth()->translatedFormat('F Y'); // "Oktober 2026"
        
        $currentMonthText = now()->translatedFormat('F'); // "September"
        $nextMonthText = now()->addMonth()->translatedFormat('F'); // "Oktober"

        $activeStatuses = ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'];
        $approvedStatuses = ['Disetujui', 'Selesai'];

        // 3. Hitung Usulan Bulan Ini (Mencakup format 2026-09 maupun 'September 2026')
        $expenseThisMonth = (float) Submission::where(function ($q) use ($currentMonthCode, $currentMonthText) {
            $q->where('target_bulan', $currentMonthCode)
              ->orWhere('target_bulan', 'LIKE', "%$currentMonthText%");
        })->whereIn('status', $activeStatuses)->sum('total_biaya');

        // 4. Hitung Realisasi Faktur Sah Bulan Ini
        $realizedThisMonth = (float) Submission::where(function ($q) use ($currentMonthCode, $currentMonthText) {
            $q->where('target_bulan', $currentMonthCode)
              ->orWhere('target_bulan', 'LIKE', "%$currentMonthText%");
        })->whereIn('status', $approvedStatuses)
          ->whereNotNull('biaya_realisasi')
          ->sum('biaya_realisasi');

        // Jika belum ada biaya realisasi, gunakan total_biaya pengajuan yang sudah disetujui
        if ($realizedThisMonth == 0) {
            $realizedThisMonth = (float) Submission::where(function ($q) use ($currentMonthCode, $currentMonthText) {
                $q->where('target_bulan', $currentMonthCode)
                  ->orWhere('target_bulan', 'LIKE', "%$currentMonthText%");
            })->whereIn('status', $approvedStatuses)->sum('total_biaya');
        }

        // 5. Hitung Proyeksi Bulan Depan (Termasuk yang baru kamu buat!)
        $expenseNextMonth = (float) Submission::where(function ($q) use ($nextMonthCode, $nextMonthText) {
            $q->where('target_bulan', $nextMonthCode)
              ->orWhere('target_bulan', 'LIKE', "%$nextMonthText%");
        })->whereIn('status', $activeStatuses)->sum('total_biaya');

        // 6. Generate Data 12 Bulan (Januari - Desember) untuk Diagram Kolom React
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $tahunIni = now()->year;
        $monthlyStats = [];

        foreach ($namaBulan as $num => $bulan) {
            $bulanKode = sprintf('%04d-%02d', $tahunIni, $num);

            $usulanBulan = (float) Submission::where(function ($q) use ($bulanKode, $bulan) {
                $q->where('target_bulan', $bulanKode)
                  ->orWhere('target_bulan', 'LIKE', "%$bulan%");
            })->whereIn('status', $activeStatuses)->sum('total_biaya');

            $realisasiBulan = (float) Submission::where(function ($q) use ($bulanKode, $bulan) {
                $q->where('target_bulan', $bulanKode)
                  ->orWhere('target_bulan', 'LIKE', "%$bulan%");
            })->whereIn('status', $approvedStatuses)->sum('biaya_realisasi');

            $monthlyStats[] = [
                'name' => substr($bulan, 0, 3), // "Jan", "Feb", dll.
                'fullMonth' => $bulan,
                'usulan' => $usulanBulan,
                'realisasi' => $realisasiBulan,
            ];
        }

        // 7. Data Statistik Divisi & Pengajuan Terbaru
        $divisions = Division::withCount('submissions')->orderBy('nama_divisi')->get();
        $recentSubmissions = Submission::with(['user', 'division', 'category'])
            ->latest()
            ->take(8)
            ->get();

        // JIKA DIPANGGIL OLEH REACT FRONTEND (API JSON)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'counts' => [
                        'total' => $totalSubmissions,
                        'pending' => $pendingCount,
                        'in_progress' => $inProgressCount,
                        'approved' => $approvedCount,
                        'rejected' => $rejectedCount,
                        'completed' => $completedCount,
                        'users' => $totalUsers,
                        'divisions' => $totalDivisions,
                        'categories' => $totalCategories,
                    ],
                    'finances' => [
                        'expense_this_month' => $expenseThisMonth,
                        'realized_this_month' => $realizedThisMonth,
                        'expense_next_month' => $expenseNextMonth,
                        'current_month_name' => $currentMonthName,
                        'next_month_name' => $nextMonthName,
                    ],
                    'division_stats' => $divisions,
                    'recent_submissions' => $recentSubmissions,
                    'monthly_stats' => $monthlyStats, // <-- DATA 12 BULAN LENGKAP
                ]
            ]);
        }

        // JIKA DIPANGGIL OLEH BLADE TEMPLATE (Fallback)
        return view('admin.dashboard', compact(
            'totalSubmissions', 'pendingCount', 'inProgressCount', 'approvedCount',
            'rejectedCount', 'completedCount', 'totalUsers', 'totalDivisions',
            'totalCategories', 'expenseThisMonth', 'realizedThisMonth', 'expenseNextMonth',
            'currentMonthName', 'nextMonthName', 'divisions', 'recentSubmissions'
        ));
    }
}
