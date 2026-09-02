<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin dashboard with system-wide analytics, financial overview, and visual charts.
     */
    public function index(Request $request): View
    {
        // System wide stats
        $totalSubmissions = Submission::count();
        $pendingCount = Submission::where('status', 'Menunggu')->count();
        $inProgressCount = Submission::where('status', 'Diproses')->count();
        $approvedCount = Submission::where('status', 'Disetujui')->count();
        $rejectedCount = Submission::where('status', 'Ditolak')->count();
        $completedCount = Submission::where('status', 'Selesai')->count();

        $totalUsers = User::where('role', 'user')->count();
        $totalDivisions = Division::count();
        $totalCategories = Category::count();

        // Financial Expenses Metrics
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        $activeStatuses = ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'];
        $approvedStatuses = ['Disetujui', 'Selesai'];

        $expenseThisMonth = (float) Submission::where('target_bulan', $currentMonth)
            ->whereIn('status', $activeStatuses)
            ->sum('total_biaya');

        $realizedThisMonth = (float) Submission::where('target_bulan', $currentMonth)
            ->whereIn('status', $approvedStatuses)
            ->sum('total_biaya');

        $expenseNextMonth = (float) Submission::where('target_bulan', $nextMonth)
            ->whereIn('status', $activeStatuses)
            ->sum('total_biaya');

        // Chart Data 1: Submissions count per division
        $divisions = Division::withCount('submissions')->orderBy('nama_divisi')->get();
        $divisionLabels = $divisions->pluck('nama_divisi')->toArray();
        $divisionCounts = $divisions->pluck('submissions_count')->toArray();

        // Chart Data 2: Submissions count per status
        $statusLabels = ['Menunggu', 'Diproses', 'Disetujui', 'Ditolak', 'Selesai'];
        $statusCounts = [
            $pendingCount,
            $inProgressCount,
            $approvedCount,
            $rejectedCount,
            $completedCount,
        ];

        // Recent Submissions
        $recentSubmissions = Submission::with(['user', 'division', 'category'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalSubmissions',
            'pendingCount',
            'inProgressCount',
            'approvedCount',
            'rejectedCount',
            'completedCount',
            'totalUsers',
            'totalDivisions',
            'totalCategories',
            'expenseThisMonth',
            'realizedThisMonth',
            'expenseNextMonth',
            'currentMonthName',
            'nextMonthName',
            'divisionLabels',
            'divisionCounts',
            'statusLabels',
            'statusCounts',
            'recentSubmissions'
        ));
    }
}
