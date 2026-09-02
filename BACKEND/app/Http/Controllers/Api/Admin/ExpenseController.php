<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\ExpenseLog;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');

        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        $selectedMonth = $request->query('month', 'all');
        $selectedDivision = $request->query('division_id', 'all');
        $selectedStatus = $request->query('status', 'all');

        $activeStatuses = ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'];
        $approvedStatuses = ['Disetujui', 'Selesai'];

        // 1. High Level Metrics
        $totalExpenseThisMonth = (float) Submission::where('target_bulan', $currentMonth)
            ->whereIn('status', $activeStatuses)
            ->sum('total_biaya');

        $realizedExpenseThisMonth = (float) Submission::where('target_bulan', $currentMonth)
            ->whereIn('status', $approvedStatuses)
            ->sum(DB::raw('COALESCE(biaya_realisasi, total_biaya)'));

        $savingsThisMonth = (float) Submission::where('target_bulan', $currentMonth)
            ->whereIn('status', $approvedStatuses)
            ->whereNotNull('biaya_realisasi')
            ->sum(DB::raw('total_biaya - biaya_realisasi'));

        $projectedExpenseNextMonth = (float) Submission::where('target_bulan', $nextMonth)
            ->whereIn('status', $activeStatuses)
            ->sum('total_biaya');

        $realizedExpenseNextMonth = (float) Submission::where('target_bulan', $nextMonth)
            ->whereIn('status', $approvedStatuses)
            ->sum(DB::raw('COALESCE(biaya_realisasi, total_biaya)'));

        $totalAllTimeExpense = (float) Submission::whereIn('status', $approvedStatuses)
            ->sum(DB::raw('COALESCE(biaya_realisasi, total_biaya)'));

        $totalAllTimeSavings = (float) Submission::whereIn('status', $approvedStatuses)
            ->whereNotNull('biaya_realisasi')
            ->sum(DB::raw('total_biaya - biaya_realisasi'));

        $totalLogsCount = ExpenseLog::count();

        // 2. Breakdown per Division
        $divisions = Division::with('headUser')->orderBy('nama_divisi')->get();
        $divisionExpenseData = $divisions->map(function (Division $division) use ($currentMonth, $nextMonth, $activeStatuses, $approvedStatuses) {
            $thisMonthSubs = Submission::where('division_id', $division->id)
                ->where('target_bulan', $currentMonth)
                ->whereIn('status', $activeStatuses);

            $nextMonthSubs = Submission::where('division_id', $division->id)
                ->where('target_bulan', $nextMonth)
                ->whereIn('status', $activeStatuses);

            $thisMonthTotal = (float) (clone $thisMonthSubs)->sum('total_biaya');
            $thisMonthRealized = (float) Submission::where('division_id', $division->id)
                ->where('target_bulan', $currentMonth)
                ->whereIn('status', $approvedStatuses)
                ->sum(DB::raw('COALESCE(biaya_realisasi, total_biaya)'));

            $thisMonthSelisih = (float) Submission::where('division_id', $division->id)
                ->where('target_bulan', $currentMonth)
                ->whereIn('status', $approvedStatuses)
                ->whereNotNull('biaya_realisasi')
                ->sum(DB::raw('total_biaya - biaya_realisasi'));

            $thisMonthCount = (int) (clone $thisMonthSubs)->count();

            $nextMonthTotal = (float) (clone $nextMonthSubs)->sum('total_biaya');
            $nextMonthRealized = (float) Submission::where('division_id', $division->id)
                ->where('target_bulan', $nextMonth)
                ->whereIn('status', $approvedStatuses)
                ->sum(DB::raw('COALESCE(biaya_realisasi, total_biaya)'));

            $nextMonthCount = (int) (clone $nextMonthSubs)->count();

            return [
                'id' => $division->id,
                'nama_divisi' => $division->nama_divisi,
                'head_user' => $division->headUser?->name ?? 'Belum Ditentukan',
                'this_month_total' => $thisMonthTotal,
                'this_month_realized' => $thisMonthRealized,
                'this_month_selisih' => $thisMonthSelisih,
                'this_month_count' => $thisMonthCount,
                'next_month_total' => $nextMonthTotal,
                'next_month_realized' => $nextMonthRealized,
                'next_month_count' => $nextMonthCount,
            ];
        });

        // 3. Itemized Submissions
        $submissionsQuery = Submission::with(['division', 'category', 'user'])
            ->latest();

        if ($selectedMonth !== 'all' && ! empty($selectedMonth)) {
            $submissionsQuery->where('target_bulan', $selectedMonth);
        }

        if ($selectedDivision !== 'all' && ! empty($selectedDivision)) {
            $submissionsQuery->where('division_id', $selectedDivision);
        }

        if ($selectedStatus !== 'all' && ! empty($selectedStatus)) {
            $submissionsQuery->where('status', $selectedStatus);
        }

        $submissions = $submissionsQuery->paginate(10);

        // 4. Audit Logs
        $logsQuery = ExpenseLog::with(['submission', 'user', 'division'])
            ->latest();

        if ($request->filled('log_month') && $request->query('log_month') !== 'all') {
            $logsQuery->where('bulan_periode', $request->query('log_month'));
        }

        if ($request->filled('log_division') && $request->query('log_division') !== 'all') {
            $logsQuery->where('division_id', $request->query('log_division'));
        }

        if ($request->filled('log_type') && $request->query('log_type') !== 'all') {
            $logsQuery->where('tipe', $request->query('log_type'));
        }

        $expenseLogs = $logsQuery->paginate(12);

        return response()->json([
            'status' => 'success',
            'data' => [
                'metrics' => [
                    'current_month' => $currentMonth,
                    'next_month' => $nextMonth,
                    'current_month_name' => $currentMonthName,
                    'next_month_name' => $nextMonthName,
                    'total_expense_this_month' => $totalExpenseThisMonth,
                    'realized_expense_this_month' => $realizedExpenseThisMonth,
                    'savings_this_month' => $savingsThisMonth,
                    'projected_expense_next_month' => $projectedExpenseNextMonth,
                    'realized_expense_next_month' => $realizedExpenseNextMonth,
                    'total_all_time_expense' => $totalAllTimeExpense,
                    'total_all_time_savings' => $totalAllTimeSavings,
                    'total_logs_count' => $totalLogsCount,
                ],
                'division_expense_data' => $divisionExpenseData,
                'submissions' => $submissions,
                'expense_logs' => $expenseLogs,
            ],
        ]);
    }
}
