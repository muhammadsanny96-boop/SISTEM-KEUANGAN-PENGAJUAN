<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get system-wide analytics, counts, financial metrics, and chart data for Admin.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Format Periode Bulan Ini & Bulan Depan
        $currentMonthCode = now()->format('Y-m'); // "2026-09"
        $nextMonthCode = now()->addMonth()->format('Y-m'); // "2026-10"
        
        $currentMonthName = now()->translatedFormat('F Y'); // "September 2026"
        $nextMonthName = now()->addMonth()->translatedFormat('F Y'); // "Oktober 2026"
        
        $currentMonthText = now()->translatedFormat('F'); // "September"
        $nextMonthText = now()->addMonth()->translatedFormat('F'); // "Oktober"

        $activeStatuses = ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'];
        $approvedStatuses = ['Disetujui', 'Selesai'];

        // Cek jika admin memilih filter bulan tertentu di dashboard (misal: '2026-10' atau 'Oktober 2026')
        $selectedPeriod = $request->query('target_bulan');

        // Tentukan periode aktif (jika ada filter arsip, gunakan periode arsip)
        $periodCode = $currentMonthCode;
        $periodName = $currentMonthName;
        $periodText = $currentMonthText;
        $periodNextCode = $nextMonthCode;
        $periodNextName = $nextMonthName;
        $periodNextText = $nextMonthText;

        if (!empty($selectedPeriod)) {
            // Deteksi format YYYY-MM (misal "2026-08")
            if (preg_match('/^(\d{4})-(\d{2})$/', $selectedPeriod, $m)) {
                $cPeriod = \Carbon\Carbon::createFromDate((int)$m[1], (int)$m[2], 1);
                $periodCode = $cPeriod->format('Y-m');
                $periodName = $cPeriod->translatedFormat('F Y');
                $periodText = $cPeriod->translatedFormat('F');

                $cNext = (clone $cPeriod)->addMonth();
                $periodNextCode = $cNext->format('Y-m');
                $periodNextName = $cNext->translatedFormat('F Y');
                $periodNextText = $cNext->translatedFormat('F');
            }
        }


        // 2. Hitung Jumlah Status Pengajuan (Berdasarkan filter periode jika ada, atau seluruhnya)
        $submissionsQuery = Submission::query();
        if ($selectedPeriod) {
            $submissionsQuery->where(function ($q) use ($selectedPeriod) {
                $q->where('target_bulan', $selectedPeriod)
                  ->orWhere('target_bulan', 'LIKE', "%$selectedPeriod%")
                  ->orWhere('tanggal_realisasi', 'LIKE', "$selectedPeriod%");
            });
        }

        $totalSubmissions = (clone $submissionsQuery)->count();
        $pendingCount = (clone $submissionsQuery)->where('status', 'Menunggu')->count();
        $inProgressCount = (clone $submissionsQuery)->where('status', 'Diproses')->count();
        $approvedCount = (clone $submissionsQuery)->where('status', 'Disetujui')->count();
        $rejectedCount = (clone $submissionsQuery)->where('status', 'Ditolak')->count();
        $completedCount = (clone $submissionsQuery)->where('status', 'Selesai')->count();

        $totalUsers = User::where('role', 'user')->count();
        $totalDivisions = Division::count();
        $totalCategories = Category::count();

        // 3. Hitung Usulan Berdasarkan Periode Terpilih
        $expenseThisMonth = (float) Submission::where(function ($q) use ($periodCode, $periodText) {
            $q->where('target_bulan', $periodCode)
              ->orWhere('target_bulan', 'LIKE', "%$periodText%")
              ->orWhere('tanggal_realisasi', 'LIKE', "$periodCode%");
        })->whereIn('status', $activeStatuses)->sum('total_biaya');

        // 4. Hitung Realisasi Berdasarkan Periode Terpilih
        $realizedThisMonth = (float) Submission::where(function ($q) use ($periodCode, $periodText) {
            $q->where('target_bulan', $periodCode)
              ->orWhere('target_bulan', 'LIKE', "%$periodText%")
              ->orWhere('tanggal_realisasi', 'LIKE', "$periodCode%");
        })->whereIn('status', $approvedStatuses)
          ->whereNotNull('biaya_realisasi')
          ->sum('biaya_realisasi');

        if ($realizedThisMonth == 0) {
            $realizedThisMonth = (float) Submission::where(function ($q) use ($periodCode, $periodText) {
                $q->where('target_bulan', $periodCode)
                  ->orWhere('target_bulan', 'LIKE', "%$periodText%")
                  ->orWhere('tanggal_realisasi', 'LIKE', "$periodCode%");
            })->whereIn('status', $approvedStatuses)->sum('total_biaya');
        }

        // 5. Hitung Proyeksi Bulan Berikutnya
        $expenseNextMonth = (float) Submission::where(function ($q) use ($periodNextCode, $periodNextText) {
            $q->where('target_bulan', $periodNextCode)
              ->orWhere('target_bulan', 'LIKE', "%$periodNextText%")
              ->orWhere('tanggal_realisasi', 'LIKE', "$periodNextCode%");
        })->whereIn('status', $activeStatuses)->sum('total_biaya');

        $realizedNextMonth = (float) Submission::where(function ($q) use ($periodNextCode, $periodNextText) {
            $q->where('target_bulan', $periodNextCode)
              ->orWhere('target_bulan', 'LIKE', "%$periodNextText%")
              ->orWhere('tanggal_realisasi', 'LIKE', "$periodNextCode%");
        })->whereIn('status', $approvedStatuses)
          ->whereNotNull('biaya_realisasi')
          ->sum('biaya_realisasi');


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
                  ->orWhere('target_bulan', 'LIKE', "%$bulan%")
                  ->orWhere('tanggal_realisasi', 'LIKE', "$bulanKode%");
            })->whereIn('status', $activeStatuses)->sum('total_biaya');

            $realisasiBulan = (float) Submission::where(function ($q) use ($bulanKode, $bulan) {
                $q->where('target_bulan', $bulanKode)
                  ->orWhere('target_bulan', 'LIKE', "%$bulan%")
                  ->orWhere('tanggal_realisasi', 'LIKE', "$bulanKode%");
            })->whereIn('status', $approvedStatuses)
              ->whereNotNull('biaya_realisasi')
              ->sum('biaya_realisasi');

            if ($realisasiBulan == 0) {
                $realisasiBulan = (float) Submission::where(function ($q) use ($bulanKode, $bulan) {
                    $q->where('target_bulan', $bulanKode)
                      ->orWhere('target_bulan', 'LIKE', "%$bulan%")
                      ->orWhere('tanggal_realisasi', 'LIKE', "$bulanKode%");
                })->where('status', 'Selesai')->sum('total_biaya');
            }

            $monthlyStats[] = [
                'name' => substr($bulan, 0, 3),
                'fullMonth' => $bulan,
                'usulan' => $usulanBulan,
                'realisasi' => $realisasiBulan,
            ];
        }

        // 7. Data Distribusi Anggaran Divisi BERDASARKAN PERIODE BULAN YANG SEDANG DILIHAT
        $divisions = Division::with(['submissions' => function ($q) use ($periodCode, $periodText, $activeStatuses) {
            $q->where(function ($subQ) use ($periodCode, $periodText) {
                $subQ->where('target_bulan', $periodCode)
                     ->orWhere('target_bulan', 'LIKE', "%$periodText%")
                     ->orWhere('tanggal_realisasi', 'LIKE', "$periodCode%");
            })->whereIn('status', $activeStatuses);
        }])->orderBy('nama_divisi')->get()->map(function ($d) {
            $totalBiaya = (float) $d->submissions->sum('total_biaya');
            $totalRealisasi = (float) $d->submissions->whereIn('status', ['Disetujui', 'Selesai'])->sum('biaya_realisasi');
            return [
                'id' => $d->id,
                'nama_divisi' => $d->nama_divisi,
                'submissions_count' => $d->submissions->count(),
                'total_biaya' => $totalBiaya,
                'total_realisasi' => $totalRealisasi,
            ];
        });

        // 8. Pengajuan Terbaru Masuk
        $recentSubmissionsQuery = Submission::with(['user', 'division', 'category'])->latest();
        if ($selectedPeriod) {
            $recentSubmissionsQuery->where(function ($q) use ($selectedPeriod) {
                $q->where('target_bulan', $selectedPeriod)
                  ->orWhere('target_bulan', 'LIKE', "%$selectedPeriod%")
                  ->orWhere('tanggal_realisasi', 'LIKE', "$selectedPeriod%");
            });
        }
        $recentSubmissions = $recentSubmissionsQuery->take(8)->get();

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
                    'realized_next_month' => $realizedNextMonth,
                    'current_month_name' => $periodName,
                    'next_month_name' => $periodNextName,
                ],
                'division_stats' => $divisions,
                'recent_submissions' => $recentSubmissions,
                'monthly_stats' => $monthlyStats,
            ],
        ]);
    }
}
