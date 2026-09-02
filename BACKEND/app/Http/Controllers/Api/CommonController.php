<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use Illuminate\Http\JsonResponse;

class CommonController extends Controller
{
    /**
     * Get list of divisions and categories for forms and dropdowns.
     */
    public function getOptions(): JsonResponse
    {
        $divisions = Division::orderBy('nama_divisi')->get();
        $categories = Category::orderBy('nama_kategori')->get();

        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        return response()->json([
            'status' => 'success',
            'divisions' => $divisions,
            'categories' => $categories,
            'months' => [
                ['value' => $currentMonth, 'label' => "Bulan Ini ({$currentMonthName})"],
                ['value' => $nextMonth, 'label' => "Bulan Depan ({$nextMonthName})"],
            ],
        ]);
    }
}
