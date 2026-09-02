<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'nama_divisi' => 'IT',
                'deskripsi' => 'Divisi Teknologi Informasi & Infrastruktur Sistem',
            ],
            [
                'nama_divisi' => 'HRD',
                'deskripsi' => 'Divisi Sumber Daya Manusia & Personalia',
            ],
            [
                'nama_divisi' => 'Keuangan',
                'deskripsi' => 'Divisi Akuntansi, Keuangan, & Pajak',
            ],
            [
                'nama_divisi' => 'Operasional',
                'deskripsi' => 'Divisi Manajemen Operasional Kantor & Fasilitas',
            ],
            [
                'nama_divisi' => 'Marketing',
                'deskripsi' => 'Divisi Pemasaran, Kreatif, & Branding',
            ],
            [
                'nama_divisi' => 'Gudang',
                'deskripsi' => 'Divisi Logistik, Pergudangan, & Distribusi Stok',
            ],
        ];

        foreach ($divisions as $division) {
            Division::updateOrCreate(
                ['nama_divisi' => $division['nama_divisi']],
                ['deskripsi' => $division['deskripsi']]
            );
        }
    }
}
