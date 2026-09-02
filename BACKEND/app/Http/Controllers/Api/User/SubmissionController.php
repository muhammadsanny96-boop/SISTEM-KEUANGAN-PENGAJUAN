<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Models\ExpenseLog;
use App\Models\Submission;
use App\Models\SubmissionReply;
use App\Models\User;
use App\Notifications\NewSubmissionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Display a paginated listing of submissions for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $submissions = Submission::where('user_id', $user->id)
            ->with(['category', 'division'])
            ->withCount('replies')
            ->search($request->query('search'))
            ->filterStatus($request->query('status'))
            ->filterCategory($request->query('category_id'))
            ->filterPriority($request->query('prioritas'))
            ->filterTargetMonth($request->query('target_bulan'))
            ->latest()
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $submissions,
        ]);
    }

    /**
     * Store a newly created submission.
     */
    public function store(StoreSubmissionRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->division_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun Anda belum terhubung dengan Divisi mana pun. Silakan hubungi Administrator.',
            ], 422);
        }

        $validated = $request->validated();
        $validated['user_id'] = $user->id;
        $validated['division_id'] = $user->division_id;
        $validated['status'] = 'Menunggu';
        $validated['harga_satuan'] = (float) ($validated['harga_satuan'] ?? 0);
        $validated['total_biaya'] = $validated['harga_satuan'] * (int) $validated['jumlah'];
        $validated['target_bulan'] = $validated['target_bulan'] ?? now()->format('Y-m');
        $validated['jenis_pengajuan'] = $validated['jenis_pengajuan'] ?? 'Barang Baru';
        $validated['prioritas'] = (isset($validated['prioritas']) && $validated['prioritas'] === 'Darurat') ? 'Mendesak' : ($validated['prioritas'] ?? 'Sedang');

        if ($request->hasFile('foto_barang')) {
            $path = $request->file('foto_barang')->store('submissions', 'public');
            $validated['foto_barang'] = $path;
        }

        $submission = Submission::create($validated);

        // Record Initial Expense Log
        ExpenseLog::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'division_id' => $user->division_id,
            'tipe' => 'Pengajuan Baru',
            'nominal' => $submission->total_biaya,
            'bulan_periode' => $submission->target_bulan,
            'keterangan' => "Pengajuan baru barang [{$submission->nama_barang}] sejumlah {$submission->jumlah} {$submission->satuan} dengan estimasi biaya {$submission->formatted_total_biaya} oleh {$user->name}.",
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewSubmissionNotification($submission));
        }

        $submission->load(['category', 'division', 'user']);

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan barang [{$submission->nomor_pengajuan}] berhasil dibuat.",
            'data' => $submission,
        ], 201);
    }

    /**
     * Display the specified submission with full timeline and details.
     */
    public function show(Request $request, Submission $submission): JsonResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke pengajuan ini.',
            ], 403);
        }

        $submission->load(['user', 'division', 'category', 'replies.admin', 'expenseLogs.user']);

        return response()->json([
            'status' => 'success',
            'data' => $submission,
        ]);
    }

    /**
     * Update the specified submission (only if pending).
     */
    public function update(UpdateSubmissionRequest $request, Submission $submission): JsonResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak diizinkan mengubah pengajuan ini.',
            ], 403);
        }

        if (! $submission->isPending()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak dapat diedit karena sudah diproses atau ditinjau oleh Admin.',
            ], 422);
        }

        $validated = $request->validated();
        $validated['harga_satuan'] = (float) ($validated['harga_satuan'] ?? 0);
        $validated['total_biaya'] = $validated['harga_satuan'] * (int) $validated['jumlah'];
        $validated['target_bulan'] = $validated['target_bulan'] ?? now()->format('Y-m');
        if (isset($validated['prioritas']) && $validated['prioritas'] === 'Darurat') {
            $validated['prioritas'] = 'Mendesak';
        }

        if ($request->hasFile('foto_barang')) {
            if ($submission->foto_barang && Storage::disk('public')->exists($submission->foto_barang)) {
                Storage::disk('public')->delete($submission->foto_barang);
            }

            $path = $request->file('foto_barang')->store('submissions', 'public');
            $validated['foto_barang'] = $path;
        }

        $submission->update($validated);

        // Record Update Expense Log
        ExpenseLog::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'division_id' => $user->division_id,
            'tipe' => 'Penyesuaian Biaya',
            'nominal' => $submission->total_biaya,
            'bulan_periode' => $submission->target_bulan,
            'keterangan' => "User {$user->name} memperbarui data pengajuan & estimasi biaya menjadi {$submission->formatted_total_biaya}.",
        ]);

        $submission->load(['category', 'division', 'user']);

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan barang [{$submission->nomor_pengajuan}] berhasil diperbarui.",
            'data' => $submission,
        ]);
    }

    /**
     * Delete the specified submission (only if pending).
     */
    public function destroy(Request $request, Submission $submission): JsonResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak diizinkan menghapus pengajuan ini.',
            ], 403);
        }

        if (! $submission->isPending()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak dapat dihapus karena sudah dalam proses atau selesai.',
            ], 422);
        }

        if ($submission->foto_barang && Storage::disk('public')->exists($submission->foto_barang)) {
            Storage::disk('public')->delete($submission->foto_barang);
        }

        $nomor = $submission->nomor_pengajuan;

        // Record Cancellation Log
        ExpenseLog::create([
            'submission_id' => null,
            'user_id' => $user->id,
            'division_id' => $user->division_id,
            'tipe' => 'Pembatalan',
            'nominal' => $submission->total_biaya,
            'bulan_periode' => $submission->target_bulan ?? now()->format('Y-m'),
            'keterangan' => "User {$user->name} membatalkan dan menghapus pengajuan [{$nomor}] senilai {$submission->formatted_total_biaya}.",
        ]);

        $submission->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Pengajuan barang [{$nomor}] berhasil dibatalkan dan dihapus.",
        ]);
    }

    /**
     * Store a comment/reply from the employee on their submission.
     */
    public function reply(Request $request, Submission $submission): JsonResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke pengajuan ini.',
            ], 403);
        }

        $request->validate([
            'pesan' => 'required|string|max:2000',
        ]);

        $reply = SubmissionReply::create([
            'submission_id' => $submission->id,
            'admin_id' => $user->id,
            'pesan' => $request->input('pesan'),
            'status_setelah_balasan' => null,
        ]);

        $reply->load('admin');

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan balasan berhasil dikirim.',
            'data' => $reply,
        ], 201);
    }
}
