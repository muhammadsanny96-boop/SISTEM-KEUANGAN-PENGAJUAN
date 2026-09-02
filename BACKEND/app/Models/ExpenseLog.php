<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
    'user_id',
    'division_id',
    'tipe',
    'nominal',
    'bulan_periode',
    'keterangan',
])]
class ExpenseLog extends Model
{
    use HasFactory;

    /**
     * Get the submission associated with the log.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the user/admin who triggered the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the division associated with the expense.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Format nominal as Indonesian Rupiah.
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->nominal, 0, ',', '.');
    }

    /**
     * Scope to filter by month period.
     */
    public function scopeFilterMonth(Builder $query, ?string $month): Builder
    {
        if (blank($month) || $month === 'all') {
            return $query;
        }

        return $query->where('bulan_periode', $month);
    }

    /**
     * Scope to filter by division.
     */
    public function scopeFilterDivision(Builder $query, ?string $divisionId): Builder
    {
        if (blank($divisionId) || $divisionId === 'all') {
            return $query;
        }

        return $query->where('division_id', $divisionId);
    }
}
