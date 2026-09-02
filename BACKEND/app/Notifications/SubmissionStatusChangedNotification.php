<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmissionStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Submission $submission,
        public User $admin,
        public ?string $pesan = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'nomor_pengajuan' => $this->submission->nomor_pengajuan,
            'status' => $this->submission->status,
            'admin_name' => $this->admin->name,
            'pesan' => $this->pesan,
            'title' => 'Pembaruan Status Pengajuan',
            'message' => "Pengajuan {$this->submission->nomor_pengajuan} ({$this->submission->nama_barang}) telah diperbarui statusnya menjadi [{$this->submission->status}] oleh Admin {$this->admin->name}.".($this->pesan ? " Catatan: \"{$this->pesan}\"" : ''),
            'url' => route('user.submissions.show', $this->submission->id),
            'type' => 'status_changed',
        ];
    }
}
