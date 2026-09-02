<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Division;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Division $divisionIT;

    protected Division $divisionHRD;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->divisionIT = Division::create([
            'nama_divisi' => 'IT & Engineering',
            'deskripsi' => 'Pengembangan & Pemeliharaan Sistem',
        ]);

        $this->divisionHRD = Division::create([
            'nama_divisi' => 'Human Resource',
            'deskripsi' => 'Pengembangan SDM',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Hardware & IT Supplies',
            'deskripsi' => 'Perangkat keras komputer',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'division_id' => $this->divisionIT->id,
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
            'division_id' => $this->divisionIT->id,
        ]);
    }

    public function test_user_can_create_submission_with_cost_and_generates_expense_log(): void
    {
        $currentMonth = now()->format('Y-m');

        $response = $this->actingAs($this->user)->post(route('user.submissions.store'), [
            'nama_barang' => 'Monitor LED 24 Inch',
            'category_id' => $this->category->id,
            'jumlah' => 2,
            'satuan' => 'Unit',
            'harga_satuan' => 1500000,
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Tinggi',
            'alasan' => 'Penambahan unit untuk kebutuhan developer baru di divisi IT.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('submissions', [
            'nama_barang' => 'Monitor LED 24 Inch',
            'jumlah' => 2,
            'harga_satuan' => 1500000,
            'total_biaya' => 3000000,
            'target_bulan' => $currentMonth,
        ]);

        $submission = Submission::where('nama_barang', 'Monitor LED 24 Inch')->first();
        $this->assertNotNull($submission);

        $this->assertDatabaseHas('expense_logs', [
            'submission_id' => $submission->id,
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'tipe' => 'Pengajuan Baru',
            'nominal' => 3000000,
            'bulan_periode' => $currentMonth,
        ]);
    }

    public function test_user_can_update_submission_cost_and_generates_expense_log(): void
    {
        $currentMonth = now()->format('Y-m');

        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Switch Hub 16 Port',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'harga_satuan' => 800000,
            'total_biaya' => 800000,
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Sedang',
            'alasan' => 'Penambahan port switch untuk ruang rapat baru.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->user)->put(route('user.submissions.update', $submission->id), [
            'nama_barang' => 'Switch Hub 24 Port Gigabit',
            'category_id' => $this->category->id,
            'jumlah' => 2,
            'satuan' => 'Unit',
            'harga_satuan' => 1200000,
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Tinggi',
            'alasan' => 'Ditingkatkan ke 24 port gigabit karena kebutuhan bertambah.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'jumlah' => 2,
            'harga_satuan' => 1200000,
            'total_biaya' => 2400000,
        ]);

        $this->assertDatabaseHas('expense_logs', [
            'submission_id' => $submission->id,
            'tipe' => 'Penyesuaian Biaya',
            'nominal' => 2400000,
        ]);
    }

    public function test_admin_reply_and_approval_creates_approval_expense_log(): void
    {
        $currentMonth = now()->format('Y-m');

        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Keyboard Mechanical',
            'jumlah' => 3,
            'satuan' => 'Pcs',
            'harga_satuan' => 500000,
            'total_biaya' => 1500000,
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Sedang',
            'alasan' => 'Penggantian keyboard yang rusak untuk tim.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.submissions.reply', $submission->id), [
            'status' => 'Disetujui',
            'prioritas' => 'Tinggi',
            'pesan' => 'Disetujui untuk pengadaan minggu ini oleh vendor resmi.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'Disetujui',
        ]);

        $this->assertDatabaseHas('expense_logs', [
            'submission_id' => $submission->id,
            'user_id' => $this->admin->id,
            'division_id' => $this->divisionIT->id,
            'tipe' => 'Persetujuan Anggaran',
            'nominal' => 1500000,
            'bulan_periode' => $currentMonth,
        ]);
    }

    public function test_admin_can_view_expense_report_and_monthly_division_breakdown(): void
    {
        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');

        // Submission Bulan Ini
        Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'RAM DDR4 16GB',
            'jumlah' => 2,
            'satuan' => 'Pcs',
            'harga_satuan' => 700000,
            'total_biaya' => 1400000,
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Sedang',
            'alasan' => 'Upgrade memory PC server.',
            'status' => 'Disetujui',
        ]);

        // Submission Bulan Depan
        Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Server Rack 42U',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'harga_satuan' => 8500000,
            'total_biaya' => 8500000,
            'target_bulan' => $nextMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Tinggi',
            'alasan' => 'Persiapan server rack untuk migrasi data center bulan depan.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.expenses.index'));

        $response->assertOk();
        $response->assertViewIs('admin.expenses.index');
        $response->assertViewHas('totalExpenseThisMonth', 1400000.0);
        $response->assertViewHas('projectedExpenseNextMonth', 8500000.0);
        $response->assertSee('RAM DDR4 16GB');
        $response->assertSee('Server Rack 42U');
        $response->assertSee('IT & Engineering');
    }

    public function test_non_admin_cannot_access_admin_expense_report(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.expenses.index'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_expense_report_computes_realized_costs_and_net_savings_accurately(): void
    {
        $currentMonth = now()->format('Y-m');

        // Item 1: Estimasi 2.000.000, Realisasi 1.800.000 (Hemat 200.000)
        Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->divisionIT->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'SSD 1TB',
            'jumlah' => 2,
            'satuan' => 'Pcs',
            'harga_satuan' => 1000000,
            'total_biaya' => 2000000,
            'harga_beli_satuan' => 900000,
            'biaya_realisasi' => 1800000,
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'bukti_pembelian' => 'receipts/ssd.pdf',
            'target_bulan' => $currentMonth,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Tinggi',
            'alasan' => 'Upgrade server.',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.expenses.index'));

        $response->assertOk();
        $response->assertViewHas('realizedExpenseThisMonth', 1800000.0);
        $response->assertViewHas('savingsThisMonth', 200000.0);
        $response->assertSee('SSD 1TB');
        $response->assertSee('+Rp 200.000');
    }
}
