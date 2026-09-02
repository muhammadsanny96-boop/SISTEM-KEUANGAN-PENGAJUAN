<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user') instanceof User ? $this->route('user')->id : $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($userId)],
            'role' => ['required', 'in:admin,user'],
            'division_id' => [
                Rule::requiredIf(fn () => $this->input('role') === 'user'),
                'nullable',
                'exists:divisions,id',
                Rule::unique('users', 'division_id')->where('role', 'user')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::defaults()],
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
