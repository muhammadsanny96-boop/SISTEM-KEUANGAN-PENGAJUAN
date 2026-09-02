<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Models\Category;
use App\Models\ExpenseLog;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewSubmissionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * Display a listing of user submissions with search and filter capabilities.
     */
    public function index(Request $request): View
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
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('nama_kategori')->get();

        return view('user.submissions.index', compact('submissions', 'categories'));
    }

    /**
     * Show the form for creating a new submission.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $categories = Category::orderBy('nama_kategori')->get();
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        return view('user.submissions.create', compact('user', 'categories', 'currentMonth', 'nextMonth', 'currentMonthName', 'nextMonthName'));
    }

    /**
     * Store a newly created submission in storage and record an expense log.
     */
    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->division_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda belum terhubung dengan Divisi mana pun. Silakan hubungi Administrator.');
        }

        $validated = $request->validated();
        $validated['user_id'] = $user->id;
        $validated['division_id'] = $user->division_id;
        $validated['status'] = 'Menunggu';
        $validated['harga_satuan'] = (float) ($validated['harga_satuan'] ?? 0);
        $validated['total_biaya'] = $validated['harga_satuan'] * (int) $validated['jumlah'];
        $validated['target_bulan'] = $validated['target_bulan'] ?? now()->format('Y-m');

        // Handle optional photo upload
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

        // Notify all admins about the new submission
        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewSubmissionNotification($submission));
        }

        return redirect()->route('user.submissions.show', $submission->id)
            ->with('success', "Pengajuan barang [{$submission->nomor_pengajuan}] berhasil dibuat dengan estimasi biaya {$submission->formatted_total_biaya}.");
    }

    /**
     * Display the specified submission, cost details, and its conversation history.
     */
    public function show(Request $request, Submission $submission): View
    {
        $user = $request->user();

        // Authorization check: User can only view their own submissions
        if ($submission->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $submission->load(['user', 'division', 'category', 'replies.admin', 'expenseLogs.user']);

        return view('user.submissions.show', compact('submission'));
    }

    /**
     * Show the form for editing the specified submission.
     */
    public function edit(Request $request, Submission $submission): View|RedirectResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            abort(403, 'Anda tidak diizinkan mengubah pengajuan ini.');
        }

        if (! $submission->isPending()) {
            return redirect()->route('user.submissions.show', $submission->id)
                ->with('error', 'Pengajuan tidak dapat diedit karena sudah diproses atau ditinjau oleh Admin.');
        }

        $categories = Category::orderBy('nama_kategori')->get();
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');
        $currentMonthName = now()->translatedFormat('F Y');
        $nextMonthName = now()->addMonth()->translatedFormat('F Y');

        return view('user.submissions.edit', compact('submission', 'categories', 'user', 'currentMonth', 'nextMonth', 'currentMonthName', 'nextMonthName'));
    }

    /**
     * Update the specified submission in storage.
     */
    public function update(UpdateSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            abort(403, 'Anda tidak diizinkan mengubah pengajuan ini.');
        }

        if (! $submission->isPending()) {
            return redirect()->route('user.submissions.show', $submission->id)
                ->with('error', 'Pengajuan tidak dapat diedit karena sudah diproses oleh Admin.');
        }

        $validated = $request->validated();
        $validated['harga_satuan'] = (float) ($validated['harga_satuan'] ?? 0);
        $validated['total_biaya'] = $validated['harga_satuan'] * (int) $validated['jumlah'];
        $validated['target_bulan'] = $validated['target_bulan'] ?? now()->format('Y-m');

        if ($request->hasFile('foto_barang')) {
            // Delete old photo if exists
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

        return redirect()->route('user.submissions.show', $submission->id)
            ->with('success', "Pengajuan barang [{$submission->nomor_pengajuan}] berhasil diperbarui.");
    }

    /**
     * Remove the specified submission from storage.
     */
    public function destroy(Request $request, Submission $submission): RedirectResponse
    {
        $user = $request->user();

        if ($submission->user_id !== $user->id) {
            abort(403, 'Anda tidak diizinkan menghapus pengajuan ini.');
        }

        if (! $submission->isPending()) {
            return redirect()->route('user.submissions.show', $submission->id)
                ->with('error', 'Pengajuan tidak dapat dihapus karena sudah dalam proses atau selesai.');
        }

        if ($submission->foto_barang && Storage::disk('public')->exists($submission->foto_barang)) {
            Storage::disk('public')->delete($submission->foto_barang);
        }

        $nomor = $submission->nomor_pengajuan;

        // Record Cancellation Log before delete
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

        return redirect()->route('user.submissions.index')
            ->with('success', "Pengajuan barang [{$nomor}] berhasil dibatalkan dan dihapus.");
    }
}
