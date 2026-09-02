<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:admin,user'],
            'division_id' => [
                Rule::requiredIf(fn () => $this->input('role') === 'user'),
                'nullable',
                'exists:divisions,id',
                Rule::unique('users', 'division_id')->where('role', 'user'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::defaults()],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'division_id.required' => 'Divisi wajib dipilih untuk akun Kepala Divisi.',
            'division_id.unique' => 'Divisi ini sudah memiliki akun Kepala Divisi terdaftar (1 akun per divisi).',
        ];
    }
}
