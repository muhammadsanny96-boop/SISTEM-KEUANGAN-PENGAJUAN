<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReplyRequest;
use App\Models\ExpenseLog;
use App\Models\Submission;
use App\Models\SubmissionReply;
use App\Notifications\SubmissionStatusChangedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Display all submissions with filters and search for Admin.
     */
    public function index(Request $request): JsonResponse
    {
        $submissions = Submission::with(['user', 'division', 'category'])
            ->withCount('replies')
            ->search($request->query('search'))
            ->filterStatus($request->query('status'))
            ->filterDivision($request->query('division_id'))
            ->filterCategory($request->query('category_id'))
            ->filterPriority($request->query('prioritas'))
            ->filterTargetMonth($request->query('target_bulan'))
            ->latest()
            ->paginate($request->query('per_page', 12));

        return response()->json([
            'status' => 'success',
            'data' => $submissions,
        ]);
    }

    /**
     * Display a specific submission along with replies, cost details, and logs.
     */
    public function show(Request $request, Submission $submission): JsonResponse
    {
        $submission->load(['user.division', 'division', 'category', 'replies.admin', 'expenseLogs.user']);

        return response()->json([
            'status' => 'success',
            'data' => $submission,
        ]);
    }

    /**
     * Update status, priority, cost adjustments, upload receipt, and save admin reply message.
     */
    public function updateStatus(AdminReplyRequest $request, Submission $submission): JsonResponse
    {
        $admin = $request->user();
        $newStatus = $request->input('status');
        $newPriority = $request->input('prioritas');
        $pesan = $request->input('pesan');

        if ($request->filled('harga_satuan')) {
            $submission->harga_satuan = (float) $request->input('harga_satuan');
            $submission->total_biaya = $submission->harga_satuan * $submission->jumlah;
        }

        if ($request->filled('harga_beli_satuan')) {
            $submission->harga_beli_satuan = (float) $request->input('harga_beli_satuan');
            if (! $request->filled('biaya_realisasi')) {
                $submission->biaya_realisasi = $submission->harga_beli_satuan * $submission->jumlah;
            }
        }

        if ($request->filled('biaya_realisasi')) {
            $submission->biaya_realisasi = (float) $request->input('biaya_realisasi');
            if (! $request->filled('harga_beli_satuan') && $submission->jumlah > 0) {
                $submission->harga_beli_satuan = $submission->biaya_realisasi / $submission->jumlah;
            }
        }

        if ($request->filled('tanggal_realisasi')) {
            $submission->tanggal_realisasi = $request->input('tanggal_realisasi');
        } elseif ($newStatus === 'Selesai' && empty($submission->tanggal_realisasi)) {
            $submission->tanggal_realisasi = now()->format('Y-m-d');
        }

        if ($request->filled('target_bulan')) {
            $submission->target_bulan = $request->input('target_bulan');
        }

        // Handle upload of proof of purchase (Nota / Kuitansi)
        if ($request->hasFile('bukti_pembelian')) {
            if ($submission->bukti_pembelian && Storage::disk('public')->exists($submission->bukti_pembelian)) {
                Storage::disk('public')->delete($submission->bukti_pembelian);
            }
            $submission->bukti_pembelian = $request->file('bukti_pembelian')->store('receipts', 'public');
        }

        $submission->status = $newStatus;
        $submission->prioritas = $newPriority;
        $submission->save();

        if (! empty($pesan)) {
            SubmissionReply::create([
                'submission_id' => $submission->id,
                'admin_id' => $admin->id,
                'pesan' => $pesan,
                'status_setelah_balasan' => $newStatus,
            ]);
        }

        $logType = match ($newStatus) {
            'Disetujui' => 'Persetujuan Anggaran',
            'Diproses' => 'Proses Pengadaan',
            'Selesai' => 'Realisasi Pengeluaran',
            'Ditolak' => 'Penolakan Pengajuan',
            default => 'Pembaruan Status',
        };

        $nominalRecorded = $submission->biaya_realisasi !== null ? $submission->biaya_realisasi : $submission->total_biaya;
        $logNote = "Admin {$admin->name} mengubah status menjadi [{$newStatus}].";

        if ($submission->biaya_realisasi !== null) {
            $logNote .= " Realisasi: {$submission->formatted_biaya_realisasi} ({$submission->formatted_selisih_biaya}).";
        }
        if ($submission->bukti_pembelian) {
            $logNote .= ' Bukti sah nota/kuitansi terlampir.';
        }
        if (! empty($pesan)) {
            $logNote .= " Catatan: {$pesan}";
        }

        ExpenseLog::create([
            'submission_id' => $submission->id,
            'user_id' => $admin->id,
            'division_id' => $submission->division_id,
            'tipe' => $logType,
            'nominal' => $nominalRecorded,
            'bulan_periode' => $submission->target_bulan ?? now()->format('Y-m'),
            'keterangan' => $logNote,
        ]);

        if ($submission->user) {
            $submission->user->notify(new SubmissionStatusChangedNotification($submission, $admin, $pesan));
        }

        $submission->load(['user.division', 'division', 'category', 'replies.admin', 'expenseLogs.user']);

        return response()->json([
            'status' => 'success',
            'message' => "Status pengajuan [{$submission->nomor_pengajuan}] berhasil diperbarui menjadi [{$newStatus}].",
            'data' => $submission,
        ]);
    }

    /**
     * Delete a submission (Admin).
     */
    public function destroy(Submission $submission): JsonResponse
    {
        if ($submission->foto_barang && Storage::disk('public')->exists($submission->foto_barang)) {
            Storage::disk('public')->delete($submission->foto_barang);
        }

        if ($submission->bukti_pembelian && Storage::disk('public')->exists($submission->bukti_pembelian)) {
            Storage::disk('public')->delete($submission->bukti_pembelian);
        }

        $nomor = $submission->nomor_pengajuan;
        $submission->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan barang [{$nomor}] berhasil dihapus.",
        ]);
    }
}
