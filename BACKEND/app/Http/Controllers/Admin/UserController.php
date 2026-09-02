<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users with search and filter.
     */
    public function index(Request $request): View
    {
        $query = User::with('division')->withCount('submissions');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        if ($divisionId = $request->query('division_id')) {
            if ($divisionId !== 'all') {
                $query->where('division_id', $divisionId);
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $divisions = Division::orderBy('nama_divisi')->get();

        return view('admin.users.index', compact('users', 'divisions'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $divisions = Division::orderBy('nama_divisi')->get();

        return view('admin.users.create', compact('divisions'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna [{$validated['name']}] berhasil ditambahkan ke sistem.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $divisions = Division::orderBy('nama_divisi')->get();

        return view('admin.users.edit', compact('user', 'divisions'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna [{$user->name}] berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna [{$name}] berhasil dihapus.");
    }
}
