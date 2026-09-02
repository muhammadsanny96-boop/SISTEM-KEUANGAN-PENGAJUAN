<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Division;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewSubmissionNotification;
use App\Notifications\SubmissionStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Division $division;

    protected Category $category;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nama_divisi' => 'IT Support']);
        $this->category = Category::create(['nama_kategori' => 'Elektronik']);

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'division_id' => $this->division->id,
        ]);

        $this->user = User::create([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'division_id' => $this->division->id,
        ]);
    }

    public function test_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/dashboard');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Administrator');
    }

    public function test_user_can_create_submission_and_auto_assigns_division(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->user)->post('/submissions', [
            'nama_barang' => 'Mouse Logitech M330',
            'category_id' => $this->category->id,
            'jumlah' => 2,
            'satuan' => 'Pcs',
            'jenis_pengajuan' => 'Barang Rusak',
            'prioritas' => 'Sedang',
            'alasan' => 'Mouse kantor tombol kirinya sudah tidak responsif dan macet saat dipakai.',
        ]);

        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'nama_barang' => 'Mouse Logitech M330',
            'status' => 'Menunggu',
        ]);

        $submission = Submission::first();
        $response->assertRedirect(route('user.submissions.show', $submission->id));

        Notification::assertSentTo(
            [$this->admin],
            NewSubmissionNotification::class
        );
    }

    public function test_user_can_edit_submission_when_status_is_menunggu(): void
    {
        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Monitor Lama',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'jenis_pengajuan' => 'Barang Rusak',
            'prioritas' => 'Sedang',
            'alasan' => 'Monitor lama sering mati dan kedap-kedip bergaris.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->user)->put("/submissions/{$submission->id}", [
            'nama_barang' => 'Monitor 24 Inch Baru',
            'category_id' => $this->category->id,
            'jumlah' => 1,
            'satuan' => 'Unit',
            'jenis_pengajuan' => 'Barang Rusak',
            'prioritas' => 'Tinggi',
            'alasan' => 'Monitor lama sering mati dan kedap-kedip bergaris sehingga butuh segera diganti.',
        ]);

        $response->assertRedirect(route('user.submissions.show', $submission->id));

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'nama_barang' => 'Monitor 24 Inch Baru',
            'prioritas' => 'Tinggi',
        ]);
    }

    public function test_user_cannot_edit_submission_when_status_is_diproses(): void
    {
        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Printer HP LaserJet',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'jenis_pengajuan' => 'Barang Habis',
            'prioritas' => 'Sedang',
            'alasan' => 'Tinta printer sudah habis terpakai kantor.',
            'status' => 'Diproses',
        ]);

        $response = $this->actingAs($this->user)->get("/submissions/{$submission->id}/edit");
        $response->assertRedirect(route('user.submissions.show', $submission->id));
    }

    public function test_user_can_delete_submission_when_status_is_menunggu(): void
    {
        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Flashdisk 32GB',
            'jumlah' => 1,
            'satuan' => 'Pcs',
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Rendah',
            'alasan' => 'Untuk kebutuhan transfer file harian kantor.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->user)->delete("/submissions/{$submission->id}");
        $response->assertRedirect(route('user.submissions.index'));

        $this->assertDatabaseMissing('submissions', [
            'id' => $submission->id,
        ]);
    }

    public function test_admin_can_update_status_and_reply_and_user_receives_notification(): void
    {
        Notification::fake();

        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Kertas A4 10 Rim',
            'jumlah' => 10,
            'satuan' => 'Rim',
            'jenis_pengajuan' => 'Barang Habis',
            'prioritas' => 'Sedang',
            'alasan' => 'Stok kertas di lemari lantai 2 sudah habis total.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/submissions/{$submission->id}/reply", [
            'status' => 'Disetujui',
            'prioritas' => 'Tinggi',
            'pesan' => 'Barang disetujui dan segera diambil di gudang logistik.',
        ]);

        $response->assertRedirect(route('admin.submissions.show', $submission->id));

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'Disetujui',
            'prioritas' => 'Tinggi',
        ]);

        $this->assertDatabaseHas('submission_replies', [
            'submission_id' => $submission->id,
            'admin_id' => $this->admin->id,
            'pesan' => 'Barang disetujui dan segera diambil di gudang logistik.',
            'status_setelah_balasan' => 'Disetujui',
        ]);

        Notification::assertSentTo(
            [$this->user],
            SubmissionStatusChangedNotification::class
        );
    }

    public function test_admin_reply_requires_message_if_status_is_changed_from_menunggu(): void
    {
        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Webcam 1080p',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Sedang',
            'alasan' => 'Untuk meeting online divisi setiap minggu.',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/submissions/{$submission->id}/reply", [
            'status' => 'Ditolak',
            'prioritas' => 'Sedang',
            'pesan' => '',
        ]);

        $response->assertSessionHasErrors('pesan');
    }

    public function test_admin_can_input_procurement_details_and_upload_proof_of_purchase(): void
    {
        Storage::fake('public');

        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Printer Thermal Pos',
            'jumlah' => 2,
            'satuan' => 'Unit',
            'harga_satuan' => 1500000,
            'total_biaya' => 3000000,
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Tinggi',
            'alasan' => 'Untuk cetak resi kasir.',
            'status' => 'Diproses',
        ]);

        $fakeReceipt = UploadedFile::fake()->image('nota_pembelian.jpg');

        $response = $this->actingAs($this->admin)->post("/admin/submissions/{$submission->id}/reply", [
            'status' => 'Selesai',
            'prioritas' => 'Tinggi',
            'harga_beli_satuan' => 1350000,
            'biaya_realisasi' => 2700000,
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'bukti_pembelian' => $fakeReceipt,
            'pesan' => 'Barang sudah dibeli di toko resmi dengan diskon 10%, bukti nota terlampir.',
        ]);

        $response->assertSessionHasNoErrors();
        $submission->refresh();

        $this->assertEquals('Selesai', $submission->status);
        $this->assertEquals(1350000, $submission->harga_beli_satuan);
        $this->assertEquals(2700000, $submission->biaya_realisasi);
        $this->assertEquals(300000, $submission->selisih_biaya);
        $this->assertNotNull($submission->bukti_pembelian);

        Storage::disk('public')->assertExists($submission->bukti_pembelian);
    }

    public function test_user_can_view_procurement_realization_and_proof(): void
    {
        $submission = Submission::create([
            'user_id' => $this->user->id,
            'division_id' => $this->division->id,
            'category_id' => $this->category->id,
            'nama_barang' => 'Kursi Kerja Ergonomis',
            'jumlah' => 1,
            'satuan' => 'Unit',
            'harga_satuan' => 1200000,
            'total_biaya' => 1200000,
            'harga_beli_satuan' => 1100000,
            'biaya_realisasi' => 1100000,
            'tanggal_realisasi' => now()->format('Y-m-d'),
            'bukti_pembelian' => 'receipts/test_receipt.jpg',
            'jenis_pengajuan' => 'Barang Baru',
            'prioritas' => 'Sedang',
            'alasan' => 'Kursi kerja staf.',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->user)->get("/submissions/{$submission->id}");

        $response->assertOk();
        $response->assertSee('Rincian Biaya & Realisasi Pengadaan', false);
        $response->assertSee('Bukti Pembelian Sah');
        $response->assertSee('Rp 1.100.000');
    }
}
