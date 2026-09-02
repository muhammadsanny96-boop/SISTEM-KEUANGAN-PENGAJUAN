<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:50'],
            'harga_satuan' => ['nullable', 'numeric', 'min:0'],
            'target_bulan' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'jenis_pengajuan' => ['nullable', 'string', 'in:Barang Habis,Barang Rusak,Barang Perlu Diganti,Barang Baru,Barang Perlu Dibeli'],
            'prioritas' => ['nullable', 'string', 'in:Rendah,Sedang,Tinggi,Mendesak,Darurat'],
            'alasan' => ['required', 'string', 'min:3'],
            'foto_barang' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori barang yang dipilih tidak valid.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'jumlah.required' => 'Jumlah barang wajib diisi.',
            'jumlah.min' => 'Jumlah barang minimal adalah 1.',
            'satuan.required' => 'Satuan barang wajib diisi (misal: PCS, Unit, Box).',
            'harga_satuan.numeric' => 'Estimasi harga satuan harus berupa angka nominal yang valid.',
            'harga_satuan.min' => 'Estimasi harga satuan tidak boleh bernilai negatif.',
            'target_bulan.regex' => 'Format target bulan pengeluaran tidak valid (Gunakan format YYYY-MM).',
            'jenis_pengajuan.required' => 'Jenis pengajuan wajib dipilih.',
            'prioritas.required' => 'Prioritas pengajuan wajib dipilih.',
            'alasan.required' => 'Alasan pengajuan wajib diisi.',
            'alasan.min' => 'Alasan pengajuan minimal harus berisi 10 karakter.',
            'foto_barang.image' => 'File foto harus berupa gambar.',
            'foto_barang.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.',
            'foto_barang.max' => 'Ukuran foto maksimal adalah 2 MB (2048 KB).',
        ];
    }
}
