<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminReplyRequest extends FormRequest
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
            'status' => ['required', 'in:Menunggu,Diproses,Disetujui,Ditolak,Selesai'],
            'prioritas' => ['required', 'in:Rendah,Sedang,Tinggi,Mendesak'],
            'harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'harga_beli_satuan' => ['nullable', 'numeric', 'min:0'],
            'biaya_realisasi' => ['nullable', 'numeric', 'min:0'],
            'tanggal_realisasi' => ['nullable', 'date'],
            'bukti_pembelian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'target_bulan' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'pesan' => [
                Rule::requiredIf(fn () => $this->input('status') !== 'Menunggu'),
                'nullable',
                'string',
            ],
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
            'status.required' => 'Status pengajuan wajib dipilih.',
            'status.in' => 'Status pengajuan tidak valid.',
            'prioritas.required' => 'Prioritas pengajuan wajib dipilih.',
            'prioritas.in' => 'Prioritas pengajuan tidak valid.',
            'harga_satuan.numeric' => 'Harga satuan estimasi harus berupa nominal angka valid.',
            'harga_beli_satuan.numeric' => 'Harga beli satuan aktual harus berupa nominal angka valid.',
            'biaya_realisasi.numeric' => 'Total biaya realisasi harus berupa nominal angka valid.',
            'tanggal_realisasi.date' => 'Tanggal realisasi pembelian tidak valid.',
            'bukti_pembelian.mimes' => 'Bukti pembelian sah harus berupa file Gambar (JPG, PNG, WEBP) atau Dokumen PDF.',
            'bukti_pembelian.max' => 'Ukuran file bukti pembelian maksimal 5MB.',
            'target_bulan.regex' => 'Target bulan harus dalam format YYYY-MM.',
            'pesan.required' => 'Admin wajib memberikan catatan/balasan ketika status pengajuan diubah.',
        ];
    }
}
