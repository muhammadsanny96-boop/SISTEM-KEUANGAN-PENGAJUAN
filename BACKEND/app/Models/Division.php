<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['nama_divisi', 'deskripsi'])]
class Division extends Model
{
    use HasFactory;

    /**
     * Get all users in this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the single designated division head (Kepala Divisi).
     */
    public function headUser(): HasOne
    {
        return $this->hasOne(User::class)->where('role', 'user');
    }

    /**
     * Get all submissions from this division.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get all expense logs for this division.
     */
    public function expenseLogs(): HasMany
    {
        return $this->hasMany(ExpenseLog::class);
    }

    /**
     * Calculate total approved/active expenses for a specific month.
     */
    public function totalExpensesForMonth(string $month): float
    {
        return (float) $this->submissions()
            ->where('target_bulan', $month)
            ->whereIn('status', ['Menunggu', 'Diproses', 'Disetujui', 'Selesai'])
            ->sum('total_biaya');
    }

    /**
     * Calculate realized (approved/completed) expenses for a specific month.
     */
    public function realizedExpensesForMonth(string $month): float
    {
        return (float) $this->submissions()
            ->where('target_bulan', $month)
            ->whereIn('status', ['Disetujui', 'Selesai'])
            ->sum('total_biaya');
    }
}
