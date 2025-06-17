<?php

namespace App\Notifications;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Maintenance $maintenance)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Kirim ke database (untuk UI) dan email
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.maintenances.show', $this->maintenance->id);
        return (new MailMessage)
                    ->subject('Pengingat Jadwal Maintenance: ' . $this->maintenance->nama_perbaikan)
                    ->greeting('Halo ' . ($notifiable->name ?? 'Pengguna') . ',')
                    ->line('Ini adalah pengingat untuk jadwal maintenance yang akan datang.')
                    ->line('**Nama Perbaikan:** ' . $this->maintenance->nama_perbaikan)
                    ->line('**Tanggal Maintenance:** ' . \Carbon\Carbon::parse($this->maintenance->tanggal_maintenance)->isoFormat('dddd, DD MMMM YYYY'))
                    ->line('**Barang Terkait:** ' . ($this->maintenance->barang->nama_barang ?? 'Umum/Lainnya'))
                    ->action('Lihat Detail Jadwal', $url)
                    ->line('Mohon persiapkan segala sesuatunya.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pesan' => 'PENGINGAT: Jadwal maintenance untuk <strong>' . $this->maintenance->nama_perbaikan . '</strong> akan dilaksanakan pada tanggal ' . \Carbon\Carbon::parse($this->maintenance->tanggal_maintenance)->isoFormat('DD MMM YYYY') . '.',
            'url' => route('admin.maintenances.show', $this->maintenance->id),
            'tipe_notifikasi' => 'maintenance_reminder',
        ];
    }
}