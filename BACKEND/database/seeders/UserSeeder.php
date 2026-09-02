<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all();
        $itDivision = $divisions->firstWhere('nama_divisi', 'IT') ?? $divisions->first();

        // 1. Akun Admin Utama
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'division_id' => $itDivision?->id,
                'phone' => '081234567890',
                'email_verified_at' => now(),
            ]
        );

        // 2. Tepat 1 Akun Kepala Divisi Per Divisi (Sesuai Aturan 1 User Per Divisi)
        $headOfDivisions = [
            ['name' => 'Budi Pratama', 'email' => 'kadiv.it@example.com', 'divisi' => 'IT', 'phone' => '081234567801'],
            ['name' => 'Siti Nurhaliza', 'email' => 'kadiv.hrd@example.com', 'divisi' => 'HRD', 'phone' => '081234567802'],
            ['name' => 'Rian Hidayat', 'email' => 'kadiv.keuangan@example.com', 'divisi' => 'Keuangan', 'phone' => '081234567803'],
            ['name' => 'Dewi Lestari', 'email' => 'kadiv.operasional@example.com', 'divisi' => 'Operasional', 'phone' => '081234567804'],
            ['name' => 'Fajar Santoso', 'email' => 'kadiv.marketing@example.com', 'divisi' => 'Marketing', 'phone' => '081234567805'],
            ['name' => 'Agus Wibowo', 'email' => 'kadiv.gudang@example.com', 'divisi' => 'Gudang', 'phone' => '081234567806'],
        ];

        foreach ($headOfDivisions as $data) {
            $division = $divisions->firstWhere('nama_divisi', $data['divisi']);

            if ($division) {
                User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'division_id' => $division->id,
                        'phone' => $data['phone'],
                        'email_verified_at' => now(),
                    ]
                );
            }
        }
    }
}
