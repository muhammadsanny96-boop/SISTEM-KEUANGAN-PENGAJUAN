<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index(): JsonResponse
    {
        $divisions = Division::with(['headUser'])
            ->withCount(['users', 'submissions'])
            ->orderBy('nama_divisi')
            ->get();

        $availableHeads = User::where('role', 'user')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $divisions,
            'available_heads' => $availableHeads,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255', 'unique:divisions,nama_divisi'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kepala_divisi_id' => ['nullable', 'exists:users,id'],
        ]);

        $division = Division::create($validated);

        if (! empty($validated['kepala_divisi_id'])) {
            User::where('id', $validated['kepala_divisi_id'])->update(['division_id' => $division->id]);
        }

        $division->load('headUser');

        return response()->json([
            'status' => 'success',
            'message' => 'Divisi berhasil ditambahkan.',
            'data' => $division,
        ], 201);
    }

    public function update(Request $request, Division $division): JsonResponse
    {
        $validated = $request->validate([
            'nama_divisi' => ['required', 'string', 'max:255', 'unique:divisions,nama_divisi,'.$division->id],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'kepala_divisi_id' => ['nullable', 'exists:users,id'],
        ]);

        $division->update($validated);

        if (! empty($validated['kepala_divisi_id'])) {
            User::where('id', $validated['kepala_divisi_id'])->update(['division_id' => $division->id]);
        }

        $division->load('headUser');

        return response()->json([
            'status' => 'success',
            'message' => 'Divisi berhasil diperbarui.',
            'data' => $division,
        ]);
    }

    public function destroy(Division $division): JsonResponse
    {
        if ($division->users()->exists() || $division->submissions()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Divisi tidak dapat dihapus karena masih memiliki relasi dengan user atau data pengajuan.',
            ], 422);
        }

        $division->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Divisi berhasil dihapus.',
        ]);
    }
}
