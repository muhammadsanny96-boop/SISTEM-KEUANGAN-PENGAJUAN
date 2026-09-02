<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Submission;
use App\Models\SubmissionReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $users = User::where('role', 'user')->with('division')->get();
        $categories = Category::all();

        if ($users->isEmpty() || $categories->isEmpty() || ! $admin) {
            return;
        }

        $items = [
            [
                'nama' => 'Kertas HVS A4 80gr PaperOne',
                'cat' => 'ATK',
                'satuan' => 'Rim',
                'jumlah' => 10,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Stok kertas di lemari penyimpanan lantai 2 sudah habis terpakai untuk pencetakan laporan bulanan.',
                'status' => 'Disetujui',
                'reply' => 'Pengajuan disetujui. Tim logistik telah membelikan 10 rim dan barang sudah siap diambil di ruang admin.',
            ],
            [
                'nama' => 'Monitor 24 Inch IPS 75Hz HDMI',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Rusak',
                'prioritas' => 'Tinggi',
                'alasan' => 'Layar monitor workstation mengalami flickering garis vertikal tebal dan sering mati mendadak saat bekerja.',
                'status' => 'Diproses',
                'reply' => 'Sedang dicek oleh vendor servis. Jika tidak bisa diperbaiki dalam 2 hari, akan diajukan penggantian unit baru.',
            ],
            [
                'nama' => 'Kursi Ergonomis Mesh Jaring',
                'cat' => 'Furniture',
                'satuan' => 'Unit',
                'jumlah' => 2,
                'jenis' => 'Barang Perlu Diganti',
                'prioritas' => 'Sedang',
                'alasan' => 'Hidrolik kursi sudah rusak dan tidak bisa dinaikkan, menyebabkan posisi duduk tidak ergonomis.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
            [
                'nama' => 'Refill Cairan Pembersih Lantai 5L',
                'cat' => 'Peralatan Kebersihan',
                'satuan' => 'Jerigen',
                'jumlah' => 3,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Tinggi',
                'alasan' => 'Persediaan cairan pembersih lantai habis untuk kebersihan harian ruang lobby dan toilet.',
                'status' => 'Selesai',
                'reply' => 'Barang sudah tiba dan telah diserahkan langsung ke staf kebersihan.',
            ],
            [
                'nama' => 'Printer Barcode Thermal Bluetooth',
                'cat' => 'Peralatan Kantor',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Baru',
                'prioritas' => 'Mendesak',
                'alasan' => 'Diperlukan untuk mempercepat tagging resi dan nomor rak keluar masuk gudang baru.',
                'status' => 'Disetujui',
                'reply' => 'Disetujui. Pembelian diproses melalui procurement dan perkiraan tiba 3 hari kerja.',
            ],
            [
                'nama' => 'Keyboard Mekanikal Custom Gaming',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Rendah',
                'alasan' => 'Ingin upgrade keyboard dengan switch custom untuk keperluan mengetik harian.',
                'status' => 'Ditolak',
                'reply' => 'Pengajuan ditolak karena spesifikasi barang di luar standar inventaris kantor. Silakan ajukan keyboard standar perkantoran.',
            ],
            [
                'nama' => 'Toner Printer HP LaserJet Pro M404',
                'cat' => 'ATK',
                'satuan' => 'Cartridge',
                'jumlah' => 2,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Mendesak',
                'alasan' => 'Toner printer utama departemen keuangan sudah bergaris dan lampu indikator low toner menyala.',
                'status' => 'Disetujui',
                'reply' => 'Toner telah disiapkan di ruang logistik, mohon tanda tangan form serah terima barang.',
            ],
            [
                'nama' => 'Kabel LAN Cat6 UTP 10 Meter',
                'cat' => 'Elektronik',
                'satuan' => 'Pcs',
                'jumlah' => 4,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Sedang',
                'alasan' => 'Penambahan kabel jaringan untuk meja kerja karyawan baru divisi HRD.',
                'status' => 'Selesai',
                'reply' => 'Kabel sudah dipasang dan diterminasi oleh tim IT support.',
            ],
            [
                'nama' => 'Whiteboard Magnetic 120 x 80 cm',
                'cat' => 'Peralatan Kantor',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Baru',
                'prioritas' => 'Sedang',
                'alasan' => 'Papan tulis dibutuhkan untuk ruang meeting marketing brainstorming mingguan.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
            [
                'nama' => 'Hand Sanitizer Gel 500ml Pump',
                'cat' => 'Peralatan Kebersihan',
                'satuan' => 'Botol',
                'jumlah' => 6,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Pengisian ulang hand sanitizer pada dispenser meja resepsionis dan koridor.',
                'status' => 'Disetujui',
                'reply' => 'Stok tersedia di gudang perlengkapan, langsung didistribusikan hari ini.',
            ],
            [
                'nama' => 'SSD NVMe 1TB PCIe 4.0',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 2,
                'jenis' => 'Barang Perlu Diganti',
                'prioritas' => 'Tinggi',
                'alasan' => 'Server development lokal kehabisan storage caching dan performa database menurun drastis.',
                'status' => 'Diproses',
                'reply' => 'Sedang dalam proses purchase order (PO) ke vendor terdaftar.',
            ],
            [
                'nama' => 'Stopkontak Kabel 5 Lubang 3 Meter',
                'cat' => 'Elektronik',
                'satuan' => 'Pcs',
                'jumlah' => 3,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Sedang',
                'alasan' => 'Kekurangan colokan listrik untuk perangkat kerja di kubikel divisi operasional.',
                'status' => 'Selesai',
                'reply' => 'Stopkontak SNI telah diserahkan dan dipasang aman.',
            ],
            [
                'nama' => 'Map Ordner Bantex F4 7cm',
                'cat' => 'ATK',
                'satuan' => 'Pcs',
                'jumlah' => 12,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Pengarsipan dokumen bukti potong pajak dan invoice tahun berjalan.',
                'status' => 'Disetujui',
                'reply' => 'Ordner telah diambil oleh bagian keuangan.',
            ],
            [
                'nama' => 'Lampu LED Tube T8 18 Watt',
                'cat' => 'Lainnya',
                'satuan' => 'Pcs',
                'jumlah' => 5,
                'jenis' => 'Barang Rusak',
                'prioritas' => 'Tinggi',
                'alasan' => 'Beberapa titik lampu di lorong gudang mati sehingga area inspeksi barang menjadi gelap.',
                'status' => 'Selesai',
                'reply' => 'Penggantian lampu telah selesai dikerjakan oleh teknisi gedung.',
            ],
            [
                'nama' => 'Headset USB dengan Noise Cancelling Mic',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 2,
                'jenis' => 'Barang Baru',
                'prioritas' => 'Sedang',
                'alasan' => 'Dibutuhkan untuk staf customer care saat meeting online dengan klien internasional.',
                'status' => 'Diproses',
                'reply' => 'Sedang membandingkan penawaran harga dari 2 vendor e-katalog.',
            ],
            [
                'nama' => 'Kopi Bubuk & Gula Sachet Ruang Tamu',
                'cat' => 'Lainnya',
                'satuan' => 'Pack',
                'jumlah' => 4,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Persediaan pantry untuk hidangan tamu perusahaan sudah menipis.',
                'status' => 'Disetujui',
                'reply' => 'Sudah dibelikan saat belanja rutin pantry mingguan.',
            ],
            [
                'nama' => 'Mesin Penghancur Kertas (Paper Shredder)',
                'cat' => 'Peralatan Kantor',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Rusak',
                'prioritas' => 'Sedang',
                'alasan' => 'Mata pisau mesin shredder macet dan motor penggerak mengeluarkan asap saat dipakai.',
                'status' => 'Diproses',
                'reply' => 'Mesin lama sedang dibawa ke bengkel resmi untuk estimasi biaya perbaikan.',
            ],
            [
                'nama' => 'Stapler Besar Jilid HD-50 & Isi Staples',
                'cat' => 'ATK',
                'satuan' => 'Set',
                'jumlah' => 2,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Rendah',
                'alasan' => 'Untuk kebutuhan jilid dokumen penawaran tender tebal di divisi marketing.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
            [
                'nama' => 'Tempat Sampah Injak Stainless 12L',
                'cat' => 'Peralatan Kebersihan',
                'satuan' => 'Unit',
                'jumlah' => 3,
                'jenis' => 'Barang Perlu Diganti',
                'prioritas' => 'Sedang',
                'alasan' => 'Tempat sampah plastik lama sudah pecah di bagian pedal injaknya.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
            [
                'nama' => 'Mouse Wireless Logitech M330 Silent',
                'cat' => 'Elektronik',
                'satuan' => 'Pcs',
                'jumlah' => 3,
                'jenis' => 'Barang Rusak',
                'prioritas' => 'Sedang',
                'alasan' => 'Sensor optik sering macet dan klik kiri double click tidak sengaja.',
                'status' => 'Disetujui',
                'reply' => 'Unit pengganti sudah tersedia di IT inventory, silakan bawa mouse lama untuk ditukar.',
            ],
            [
                'nama' => 'Lakban Bening Daimaru 2 Inch',
                'cat' => 'ATK',
                'satuan' => 'Roll',
                'jumlah' => 20,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Tinggi',
                'alasan' => 'Kebutuhan packing pengiriman paket sampel barang ke pelanggan di gudang.',
                'status' => 'Selesai',
                'reply' => '1 dus lakban (24 roll) telah diserahkan langsung ke supervisor gudang.',
            ],
            [
                'nama' => 'Webcam Full HD 1080p Ruang Rapat Kecil',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Baru',
                'prioritas' => 'Sedang',
                'alasan' => 'Ruang rapat 2 belum memiliki kamera video conference yang memadai.',
                'status' => 'Diproses',
                'reply' => 'Menunggu persetujuan alokasi anggaran bulan depan dari Finance Director.',
            ],
            [
                'nama' => 'Baterai AA & AAA Alkaline Pack',
                'cat' => 'Lainnya',
                'satuan' => 'Pack',
                'jumlah' => 8,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Untuk remote AC ruang kerja dan wireless presenter ruang rapat.',
                'status' => 'Disetujui',
                'reply' => 'Stok baterai telah disimpan di laci operasional.',
            ],
            [
                'nama' => 'Kipas Angin Dinding Tornado 18 Inch',
                'cat' => 'Lainnya',
                'satuan' => 'Unit',
                'jumlah' => 2,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Sedang',
                'alasan' => 'Sirkulasi udara di area loading dock gudang kurang baik saat siang hari.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
            [
                'nama' => 'Timbangan Digital Presisi 30kg',
                'cat' => 'Peralatan Kantor',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Rusak',
                'prioritas' => 'Mendesak',
                'alasan' => 'Timbangan akurasi tidak stabil dan sering mati di tengah proses penimbangan kargo.',
                'status' => 'Diproses',
                'reply' => 'Sudah dipanggilkan teknisi kalibrasi untuk uji kelayakan.',
            ],
            [
                'nama' => 'Dispenser Sabun Cuci Tangan Otomatis Sensor',
                'cat' => 'Peralatan Kebersihan',
                'satuan' => 'Unit',
                'jumlah' => 2,
                'jenis' => 'Barang Baru',
                'prioritas' => 'Rendah',
                'alasan' => 'Untuk modernisasi wastafel toilet lantai 1.',
                'status' => 'Ditolak',
                'reply' => 'Dispenser manual yang ada saat ini masih berfungsi dengan sangat baik dan terawat.',
            ],
            [
                'nama' => 'Pulpen Gel Pilot G2 0.5mm Hitam',
                'cat' => 'ATK',
                'satuan' => 'Lusin',
                'jumlah' => 5,
                'jenis' => 'Barang Habis',
                'prioritas' => 'Sedang',
                'alasan' => 'Persediaan alat tulis untuk staf administrasi dan tanda tangan berkas kontrak.',
                'status' => 'Disetujui',
                'reply' => 'Barang sudah masuk dan dapat diambil di meja sekretariat.',
            ],
            [
                'nama' => 'Router WiFi 6 Dual Band Gigabit',
                'cat' => 'Elektronik',
                'satuan' => 'Unit',
                'jumlah' => 1,
                'jenis' => 'Barang Perlu Diganti',
                'prioritas' => 'Tinggi',
                'alasan' => 'Access Point lama di divisi IT sering putus koneksi saat traffic upload tinggi.',
                'status' => 'Diproses',
                'reply' => 'Pengadaan barang sedang disesuaikan dengan konfigurasi mikrotik pusat.',
            ],
            [
                'nama' => 'Kotak P3K Lengkap Isi Standar K3',
                'cat' => 'Lainnya',
                'satuan' => 'Set',
                'jumlah' => 2,
                'jenis' => 'Barang Perlu Diganti',
                'prioritas' => 'Tinggi',
                'alasan' => 'Obat-obatan dan plester di kotak P3K gudang sudah banyak yang expired.',
                'status' => 'Selesai',
                'reply' => 'Kotak P3K telah diperbarui lengkap dengan masa kadaluarsa 2028.',
            ],
            [
                'nama' => 'Flashdisk 64GB USB 3.2 Metal Case',
                'cat' => 'Elektronik',
                'satuan' => 'Pcs',
                'jumlah' => 4,
                'jenis' => 'Barang Perlu Dibeli',
                'prioritas' => 'Sedang',
                'alasan' => 'Untuk transfer file presentasi video promosi ke booth pameran luar kota.',
                'status' => 'Menunggu',
                'reply' => null,
            ],
        ];

        $counter = 1;
        $now = now();

        $prices = [
            'ATK' => 55000,
            'Elektronik' => 1750000,
            'Furniture' => 850000,
            'Peralatan Kebersihan' => 125000,
            'Peralatan Kantor' => 1200000,
            'Lainnya' => 180000,
        ];

        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');

        foreach ($items as $idx => $item) {
            $user = $users[$idx % $users->count()];
            $category = $categories->firstWhere('nama_kategori', $item['cat']) ?? $categories->first();
            $date = $now->copy()->subDays(30 - $counter);
            $padded = str_pad($counter, 4, '0', STR_PAD_LEFT);
            $noPengajuan = "PB-{$date->format('Ymd')}-{$padded}";

            $basePrice = $prices[$item['cat']] ?? 150000;
            $hargaSatuan = $basePrice + (($idx % 5) * 15000);
            $totalBiaya = $hargaSatuan * $item['jumlah'];
            $targetBulan = ($idx % 3 === 0) ? $nextMonth : $currentMonth;

            $hargaBeliSatuan = null;
            $biayaRealisasi = null;
            $tanggalRealisasi = null;

            if (in_array($item['status'], ['Disetujui', 'Selesai'])) {
                // Berikan variasi harga beli (ada yang hemat 10%, ada yang sama, ada yang over budget sedikit)
                $discountFactor = match ($idx % 4) {
                    0 => 0.90, // Hemat 10%
                    1 => 0.95, // Hemat 5%
                    2 => 1.00, // Tepat sesuai estimasi
                    3 => 1.05, // Sedikit over budget 5%
                };
                $hargaBeliSatuan = round($hargaSatuan * $discountFactor);
                $biayaRealisasi = $hargaBeliSatuan * $item['jumlah'];
                $tanggalRealisasi = $date->copy()->addDays(2)->format('Y-m-d');
            }

            $submission = Submission::create([
                'nomor_pengajuan' => $noPengajuan,
                'user_id' => $user->id,
                'division_id' => $user->division_id,
                'category_id' => $category->id,
                'nama_barang' => $item['nama'],
                'jumlah' => $item['jumlah'],
                'satuan' => $item['satuan'],
                'jenis_pengajuan' => $item['jenis'],
                'prioritas' => $item['prioritas'],
                'alasan' => $item['alasan'],
                'harga_satuan' => $hargaSatuan,
                'total_biaya' => $totalBiaya,
                'target_bulan' => $targetBulan,
                'harga_beli_satuan' => $hargaBeliSatuan,
                'biaya_realisasi' => $biayaRealisasi,
                'tanggal_realisasi' => $tanggalRealisasi,
                'bukti_pembelian' => null,
                'foto_barang' => null,
                'status' => $item['status'],
                'created_at' => $date,
                'updated_at' => $date->copy()->addHours(3),
            ]);

            if (! empty($item['reply'])) {
                SubmissionReply::create([
                    'submission_id' => $submission->id,
                    'admin_id' => $admin->id,
                    'pesan' => $item['reply'],
                    'status_setelah_balasan' => $item['status'],
                    'created_at' => $date->copy()->addHours(4),
                    'updated_at' => $date->copy()->addHours(4),
                ]);
            }

            $counter++;
        }
    }
}
