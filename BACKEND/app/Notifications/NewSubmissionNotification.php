<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSubmissionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Submission $submission) {}

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
            'nama_barang' => $this->submission->nama_barang,
            'user_name' => $this->submission->user->name ?? 'User',
            'division_name' => $this->submission->division->nama_divisi ?? '-',
            'title' => 'Pengajuan Baru Masuk',
            'message' => "Pengajuan {$this->submission->nomor_pengajuan} ({$this->submission->nama_barang}) dibuat oleh {$this->submission->user->name} dari Divisi {$this->submission->division->nama_divisi}.",
            'url' => route('admin.submissions.show', $this->submission->id),
            'type' => 'new_submission',
        ];
    }
}
