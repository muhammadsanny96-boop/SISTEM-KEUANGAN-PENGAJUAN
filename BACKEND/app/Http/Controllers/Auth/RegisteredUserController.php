<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view with available divisions.
     */
    public function create(): View
    {
        $allDivisions = Division::with('headUser')->orderBy('nama_divisi')->get();
        $availableDivisions = $allDivisions->filter(fn ($div) => $div->headUser === null);

        return view('auth.register', compact('allDivisions', 'availableDivisions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'division_id' => [
                'required',
                'exists:divisions,id',
                Rule::unique('users', 'division_id')->where('role', 'user'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'division_id.required' => 'Divisi wajib dipilih untuk akun Kepala Divisi.',
            'division_id.unique' => 'Divisi yang Anda pilih sudah memiliki akun Kepala Divisi terdaftar (1 akun per divisi).',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'user',
            'division_id' => $request->division_id,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
