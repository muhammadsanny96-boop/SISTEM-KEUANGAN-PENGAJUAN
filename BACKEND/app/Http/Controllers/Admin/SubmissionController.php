<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReplyRequest;
use App\Models\Category;
use App\Models\Division;
use App\Models\ExpenseLog;
use App\Models\Submission;
use App\Models\SubmissionReply;
use App\Notifications\SubmissionStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * Display all submissions with filters and search.
     */
    public function index(Request $request): View
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
            ->paginate(12)
            ->withQueryString();

        $divisions = Division::orderBy('nama_divisi')->get();
        $categories = Category::orderBy('nama_kategori')->get();

        return view('admin.submissions.index', compact('submissions', 'divisions', 'categories'));
    }

    /**
     * Display a specific submission along with reply form, cost details, and chat timeline.
     */
    public function show(Request $request, Submission $submission): View
    {
        $submission->load(['user.division', 'division', 'category', 'replies.admin', 'expenseLogs.user']);

        return view('admin.submissions.show', compact('submission'));
    }

    /**
     * Update status, priority, cost adjustments, and store admin reply message + expense audit log.
     */
    public function updateStatus(AdminReplyRequest $request, Submission $submission): RedirectResponse
    {
        $admin = $request->user();
        $oldStatus = $submission->status;
        $newStatus = $request->input('status');
        $newPriority = $request->input('prioritas');
        $pesan = $request->input('pesan');

        // Optional price / procurement adjustments by Admin
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

        // Handle upload of proof of purchase (Nota / Kuitansi / Foto Sah)
        if ($request->hasFile('bukti_pembelian')) {
            if ($submission->bukti_pembelian && Storage::disk('public')->exists($submission->bukti_pembelian)) {
                Storage::disk('public')->delete($submission->bukti_pembelian);
            }
            $submission->bukti_pembelian = $request->file('bukti_pembelian')->store('receipts', 'public');
        }

        // Update submission status & priority
        $submission->status = $newStatus;
        $submission->prioritas = $newPriority;
        $submission->save();

        // Create reply record if message is provided
        if (! empty($pesan)) {
            SubmissionReply::create([
                'submission_id' => $submission->id,
                'admin_id' => $admin->id,
                'pesan' => $pesan,
                'status_setelah_balasan' => $newStatus,
            ]);
        }

        // Determine Expense Log type & description
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

        // Record Expense Audit Log
        ExpenseLog::create([
            'submission_id' => $submission->id,
            'user_id' => $admin->id,
            'division_id' => $submission->division_id,
            'tipe' => $logType,
            'nominal' => $nominalRecorded,
            'bulan_periode' => $submission->target_bulan ?? now()->format('Y-m'),
            'keterangan' => $logNote,
        ]);

        // Send notification to the user who made the submission
        if ($submission->user) {
            $submission->user->notify(new SubmissionStatusChangedNotification($submission, $admin, $pesan));
        }

        return redirect()->route('admin.submissions.show', $submission->id)
            ->with('success', "Status pengajuan [{$submission->nomor_pengajuan}] berhasil diperbarui menjadi [{$newStatus}] dan log pengeluaran tercatat.");
    }

    /**
     * Delete a submission (Admin feature).
     */
    public function destroy(Submission $submission): RedirectResponse
    {
        if ($submission->foto_barang && Storage::disk('public')->exists($submission->foto_barang)) {
            Storage::disk('public')->delete($submission->foto_barang);
        }

        if ($submission->bukti_pembelian && Storage::disk('public')->exists($submission->bukti_pembelian)) {
            Storage::disk('public')->delete($submission->bukti_pembelian);
        }

        $nomor = $submission->nomor_pengajuan;
        $submission->delete();

        return redirect()->route('admin.submissions.index')
            ->with('success', "Pengajuan barang [{$nomor}] berhasil dihapus dari sistem.");
    }
}
