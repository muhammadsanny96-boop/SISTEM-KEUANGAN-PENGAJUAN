<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get user dashboard metrics, monthly budget projections, and recent submissions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Count submissions by status for current user
        $totalSubmissions = Submission::where('user_id', $user->id)->count();
        $pendingCount = Submission::where('user_id', $user->id)->where('status', 'Menunggu')->count();
        $inProgressCount = Submission::where('user_id', $user->id)->where('status', 'Diproses')->count();
        $approvedCount = Submission::where('user_id', $user->id)->where('status', 'Disetujui')->count();
        $rejectedCount = Submission::where('user_id', $user->id)->where('status', 'Ditolak')->count();
        $completedCount = Submission::where('user_id', $user->id)->where('status', 'Selesai')->count();

        // Division Expense Summaries
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        $divisionExpenseThisMonth = 0;
        $divisionExpenseNextMonth = 0;
        $activeStatuses = ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'];

        if ($user->division_id) {
            $divisionExpenseThisMonth = (float) Submission::where('division_id', $user->division_id)
                ->where('target_bulan', $currentMonth)
                ->whereIn('status', $activeStatuses)
                ->sum('total_biaya');

            $divisionExpenseNextMonth = (float) Submission::where('division_id', $user->division_id)
                ->where('target_bulan', $nextMonth)
                ->whereIn('status', $activeStatuses)
                ->sum('total_biaya');
        }

        // Recent submissions for current user
        $recentSubmissions = Submission::where('user_id', $user->id)
            ->with(['category', 'division'])
            ->latest()
            ->take(5)
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
                ],
                'expenses' => [
                    'this_month' => $divisionExpenseThisMonth,
                    'next_month' => $divisionExpenseNextMonth,
                    'current_month_name' => $currentMonthName,
                    'next_month_name' => $nextMonthName,
                ],
                'recent_submissions' => $recentSubmissions,
            ],
        ]);
    }
}
