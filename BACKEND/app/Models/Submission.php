<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'nomor_pengajuan',
    'user_id',
    'division_id',
    'category_id',
    'nama_barang',
    'jumlah',
    'satuan',
    'harga_satuan',
    'total_biaya',
    'harga_beli_satuan',
    'target_bulan',
    'biaya_realisasi',
    'tanggal_realisasi',
    'jenis_pengajuan',
    'prioritas',
    'alasan',
    'foto_barang',
    'bukti_pembelian',
    'status',
])]
class Submission extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_satuan' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'harga_beli_satuan' => 'decimal:2',
            'biaya_realisasi' => 'decimal:2',
            'tanggal_realisasi' => 'date',
        ];
    }

    /**
     * Boot model events.
     */
    protected static function booted(): void
    {
        static::creating(function (Submission $submission) {
            if (empty($submission->nomor_pengajuan)) {
                $datePart = now()->format('Ymd');
                $randomPart = strtoupper(Str::random(4));
                $submission->nomor_pengajuan = "PB-{$datePart}-{$randomPart}";
            }

            if (empty($submission->target_bulan)) {
                $submission->target_bulan = now()->format('Y-m');
            }

            if (empty($submission->total_biaya) && ! empty($submission->harga_satuan) && ! empty($submission->jumlah)) {
                $submission->total_biaya = (float) $submission->harga_satuan * (int) $submission->jumlah;
            }
        });

        static::updating(function (Submission $submission) {
            if ($submission->isDirty(['harga_satuan', 'jumlah']) && ! $submission->isDirty('total_biaya')) {
                $submission->total_biaya = (float) ($submission->harga_satuan ?? 0) * (int) ($submission->jumlah ?? 1);
            }
        });
    }

    /**
     * Get the user who created the submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the division of the submission.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the category of the submission.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all replies/conversations on this submission.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SubmissionReply::class)->oldest();
    }

    /**
     * Get all expense audit logs on this submission.
     */
    public function expenseLogs(): HasMany
    {
        return $this->hasMany(ExpenseLog::class)->latest();
    }

    /**
     * Check if submission can be edited or deleted by user.
     */
    public function isPending(): bool
    {
        return $this->status === 'Menunggu';
    }

    /**
     * Formatted Indonesian Rupiah helpers.
     */
    public function getFormattedHargaSatuanAttribute(): string
    {
        return 'Rp '.number_format((float) ($this->harga_satuan ?? 0), 0, ',', '.');
    }

    public function getFormattedTotalBiayaAttribute(): string
    {
        return 'Rp '.number_format((float) ($this->total_biaya ?? 0), 0, ',', '.');
    }

    public function getFormattedHargaBeliSatuanAttribute(): string
    {
        if ($this->harga_beli_satuan === null) {
            return '-';
        }

        return 'Rp '.number_format((float) $this->harga_beli_satuan, 0, ',', '.');
    }

    public function getFormattedBiayaRealisasiAttribute(): string
    {
        if ($this->biaya_realisasi === null) {
            return '-';
        }

        return 'Rp '.number_format((float) $this->biaya_realisasi, 0, ',', '.');
    }

    /**
     * Calculate cost difference (Estimasi - Realisasi).
     */
    public function getSelisihBiayaAttribute(): float
    {
        if ($this->biaya_realisasi === null) {
            return 0.0;
        }

        return (float) $this->total_biaya - (float) $this->biaya_realisasi;
    }

    /**
     * Status of variance: hemat (savings), over (over budget), sesuai (exact match), pending.
     */
    public function getSelisihStatusAttribute(): string
    {
        if ($this->biaya_realisasi === null) {
            return 'pending';
        }

        $selisih = $this->selisih_biaya;
        if ($selisih > 0) {
            return 'hemat';
        }
        if ($selisih < 0) {
            return 'over';
        }

        return 'sesuai';
    }

    /**
     * Formatted Indonesian Rupiah with indicator for cost difference.
     */
    public function getFormattedSelisihBiayaAttribute(): string
    {
        if ($this->biaya_realisasi === null) {
            return '-';
        }

        $selisih = $this->selisih_biaya;
        if ($selisih > 0) {
            return '+Rp '.number_format($selisih, 0, ',', '.').' (Hemat)';
        }
        if ($selisih < 0) {
            return '-Rp '.number_format(abs($selisih), 0, ',', '.').' (Over)';
        }

        return 'Rp 0 (Sesuai)';
    }

    /**
     * Formatted Indonesian date for purchase realization.
     */
    public function getFormattedTanggalRealisasiAttribute(): string
    {
        if (empty($this->tanggal_realisasi)) {
            return '-';
        }

        try {
            return Carbon::parse($this->tanggal_realisasi)->translatedFormat('d F Y');
        } catch (\Exception) {
            return (string) $this->tanggal_realisasi;
        }
    }

    /**
     * Check if proof of purchase is an image.
     */
    public function isProofImage(): bool
    {
        if (empty($this->bukti_pembelian)) {
            return false;
        }

        $ext = strtolower(pathinfo($this->bukti_pembelian, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Check if proof of purchase is a PDF.
     */
    public function isProofPdf(): bool
    {
        if (empty($this->bukti_pembelian)) {
            return false;
        }

        $ext = strtolower(pathinfo($this->bukti_pembelian, PATHINFO_EXTENSION));

        return $ext === 'pdf';
    }

    /**
     * Human-readable label for target month.
     */
    public function getTargetBulanLabelAttribute(): string
    {
        if (empty($this->target_bulan)) {
            return '-';
        }

        try {
            return Carbon::createFromFormat('Y-m', $this->target_bulan)->translatedFormat('F Y');
        } catch (\Exception) {
            return $this->target_bulan;
        }
    }

    /**
     * Scope to search submissions.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nomor_pengajuan', 'like', "%{$term}%")
                ->orWhere('nama_barang', 'like', "%{$term}%")
                ->orWhere('alasan', 'like', "%{$term}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope to filter by status.
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
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

    /**
     * Scope to filter by category.
     */
    public function scopeFilterCategory(Builder $query, ?string $categoryId): Builder
    {
        if (blank($categoryId) || $categoryId === 'all') {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeFilterPriority(Builder $query, ?string $priority): Builder
    {
        if (blank($priority) || $priority === 'all') {
            return $query;
        }

        return $query->where('prioritas', $priority);
    }

    /**
     * Scope to filter by target month.
     */
    public function scopeFilterTargetMonth(Builder $query, ?string $month): Builder
    {
        if (blank($month) || $month === 'all') {
            return $query;
        }

        return $query->where('target_bulan', $month);
    }
}
