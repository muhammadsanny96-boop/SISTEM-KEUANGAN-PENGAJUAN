<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'ATK',
                'deskripsi' => 'Alat Tulis Kantor seperti kertas, pulpen, map, stapler, dan amplop.',
            ],
            [
                'nama_kategori' => 'Elektronik',
                'deskripsi' => 'Perangkat komputer, laptop, monitor, keyboard, mouse, kabel, dan adaptor.',
            ],
            [
                'nama_kategori' => 'Furniture',
                'deskripsi' => 'Meja kerja, kursi ergonomis, lemari berkas, dan rak dokumen.',
            ],
            [
                'nama_kategori' => 'Peralatan Kebersihan',
                'deskripsi' => 'Sapu, kain pel, cairan pembersih, tempat sampah, dan tisu toilet/tangan.',
            ],
            [
                'nama_kategori' => 'Peralatan Kantor',
                'deskripsi' => 'Dispenser air, printer multifungsi, proyektor ruang rapat, papan tulis.',
            ],
            [
                'nama_kategori' => 'Lainnya',
                'deskripsi' => 'Kebutuhan umum dan barang penunjang operasional lainnya.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['nama_kategori' => $category['nama_kategori']],
                ['deskripsi' => $category['deskripsi']]
            );
        }
    }
}
