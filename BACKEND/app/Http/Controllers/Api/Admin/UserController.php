<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('division')
            ->withCount('submissions')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->query('role'), function ($q, $role) {
                if ($role !== 'all') {
                    $q->where('role', $role);
                }
            })
            ->when($request->query('division_id'), function ($q, $divisionId) {
                if ($divisionId !== 'all') {
                    $q->where('division_id', $divisionId);
                }
            })
            ->latest()
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:admin,user'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->load('division');

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'in:admin,user'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('division');

        return response()->json([
            'status' => 'success',
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => $user,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus satu-satunya akun Administrator yang tersisa.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
