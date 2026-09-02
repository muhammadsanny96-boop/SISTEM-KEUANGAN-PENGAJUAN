<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DivisionController extends Controller
{
    /**
     * Display a listing of divisions with related stats.
     */
    public function index(Request $request): View
    {
        $divisions = Division::withCount(['users', 'submissions'])
            ->when($request->query('search'), function ($q, $search) {
                $q->where('nama_divisi', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->orderBy('nama_divisi')
            ->paginate(10)
            ->withQueryString();

        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Store a newly created division in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_divisi' => ['required', 'string', 'max:100', 'unique:divisions,nama_divisi'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique' => 'Nama divisi sudah terdaftar.',
        ]);

        Division::create($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', "Divisi [{$validated['nama_divisi']}] berhasil ditambahkan.");
    }

    /**
     * Update the specified division in storage.
     */
    public function update(Request $request, Division $division): RedirectResponse
    {
        $validated = $request->validate([
            'nama_divisi' => ['required', 'string', 'max:100', Rule::unique('divisions', 'nama_divisi')->ignore($division->id)],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_divisi.unique' => 'Nama divisi sudah digunakan.',
        ]);

        $division->update($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', "Divisi [{$division->nama_divisi}] berhasil diperbarui.");
    }

    /**
     * Remove the specified division from storage.
     */
    public function destroy(Division $division): RedirectResponse
    {
        if ($division->submissions()->count() > 0) {
            return redirect()->back()->with('error', "Divisi [{$division->nama_divisi}] tidak dapat dihapus karena masih memiliki riwayat pengajuan barang.");
        }

        $nama = $division->nama_divisi;
        $division->delete();

        return redirect()->route('admin.divisions.index')
            ->with('success', "Divisi [{$nama}] berhasil dihapus.");
    }
}
