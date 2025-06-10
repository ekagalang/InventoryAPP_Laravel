<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanBaruNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ItemRequest $itemRequest;
    public User $pemohon;

    public function __construct(ItemRequest $itemRequest)
    {
        $this->itemRequest = $itemRequest;
        // Eager load relasi 'pemohon' untuk memastikan datanya ada
        $this->pemohon = $this->itemRequest->pemohon; 
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Kirim ke database (untuk UI) dan email
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.pengajuan.barang.show', $this->itemRequest->id);
        return (new MailMessage)
                    ->subject('Pengajuan Barang Baru Diterima (ID: #' . $this->itemRequest->id . ')')
                    ->greeting('Halo ' . ($notifiable->name ?? 'Admin/Staf') . ',')
                    ->line('Ada pengajuan barang baru yang perlu ditinjau:')
                    ->line('Pemohon: ' . $this->pemohon->name)
                    ->line('Barang: ' . ($this->itemRequest->barang->nama_barang ?? 'N/A'))
                    ->line('Kuantitas Diminta: ' . $this->itemRequest->kuantitas_diminta)
                    ->action('Lihat Detail Pengajuan', $url)
                    ->line('Harap segera ditindaklanjuti.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'item_request_id' => $this->itemRequest->id,
            'pesan' => 'Pengajuan barang baru dari <strong>' . $this->pemohon->name . '</strong> untuk barang <strong>' . ($this->itemRequest->barang->nama_barang ?? 'N/A') . '</strong> perlu ditinjau.',
            'url' => route('admin.pengajuan.barang.show', $this->itemRequest->id),
            'tipe_notifikasi' => 'pengajuan_baru',
        ];
    }
}