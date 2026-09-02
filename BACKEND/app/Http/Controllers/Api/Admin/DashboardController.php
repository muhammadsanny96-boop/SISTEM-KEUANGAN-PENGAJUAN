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

        // Recent Submissions
        $recentSubmissions = Submission::with(['user', 'division', 'category'])
            ->latest()
            ->take(8)
            ->get();

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
            ],
        ]);
    }
}
