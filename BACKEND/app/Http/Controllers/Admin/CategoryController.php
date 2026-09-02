<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with related stats.
     */
    public function index(Request $request): View
    {
        $categories = Category::withCount('submissions')
            ->when($request->query('search'), function ($q, $search) {
                $q->where('nama_kategori', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            })
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:categories,nama_kategori'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah terdaftar.',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori [{$validated['nama_kategori']}] berhasil ditambahkan.");
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', Rule::unique('categories', 'nama_kategori')->ignore($category->id)],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah digunakan.',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori [{$category->nama_kategori}] berhasil diperbarui.");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->submissions()->count() > 0) {
            return redirect()->back()->with('error', "Kategori [{$category->nama_kategori}] tidak dapat dihapus karena masih digunakan pada data pengajuan barang.");
        }

        $nama = $category->nama_kategori;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori [{$nama}] berhasil dihapus.");
    }
}
