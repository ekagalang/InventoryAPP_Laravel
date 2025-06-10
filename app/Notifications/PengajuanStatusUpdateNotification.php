<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ItemRequest $itemRequest;
    private string $pesanNotifikasi;
    private string $judulEmail;
    private string $status;

    public function __construct(ItemRequest $itemRequest)
    {
        $this->itemRequest = $itemRequest;
        $this->status = $this->itemRequest->status;
        $this->setNotificationDetails();
    }

    // Method helper untuk mengatur detail pesan notifikasi
    private function setNotificationDetails(): void
    {
        $id = $this->itemRequest->id;
        $namaBarang = $this->itemRequest->barang->nama_barang ?? 'N/A';

        switch ($this->status) {
            case 'Disetujui':
                $this->judulEmail = "Pengajuan Barang Anda Disetujui (ID: #{$id})";
                $this->pesanNotifikasi = "Kabar baik! Pengajuan Anda untuk <strong>{$namaBarang}</strong> telah <strong>Disetujui</strong>.";
                if ($this->itemRequest->catatan_approval) {
                    $this->pesanNotifikasi .= " Catatan: " . $this->itemRequest->catatan_approval;
                }
                break;
            case 'Ditolak':
                $this->judulEmail = "Pengajuan Barang Anda Ditolak (ID: #{$id})";
                $this->pesanNotifikasi = "Mohon maaf, pengajuan Anda untuk <strong>{$namaBarang}</strong> telah <strong>Ditolak</strong>.";
                if ($this->itemRequest->catatan_approval) {
                    $this->pesanNotifikasi .= " Alasan: " . $this->itemRequest->catatan_approval;
                }
                break;
            case 'Diproses':
                $this->judulEmail = "Pengajuan Barang Anda Telah Diproses (ID: #{$id})";
                $this->pesanNotifikasi = "Pengajuan Anda untuk <strong>{$namaBarang}</strong> telah <strong>Diproses</strong>. Barang akan segera/sudah dapat diambil.";
                break;
            case 'Dibatalkan':
                $this->judulEmail = "Pengajuan Barang Dibatalkan (ID: #{$id})";
                $this->pesanNotifikasi = "Pengajuan Anda untuk <strong>{$namaBarang}</strong> telah berhasil Anda <strong>Batalkan</strong>.";
                break;
            default:
                $this->judulEmail = "Update Status Pengajuan Barang (ID: #{$id})";
                $this->pesanNotifikasi = "Status pengajuan Anda untuk <strong>{$namaBarang}</strong> telah diupdate menjadi <strong>{$this->status}</strong>.";
                break;
        }
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Link ke halaman daftar pengajuan si pengguna
        $url = route('pengajuan.barang.index');

        return (new MailMessage)
                    ->subject($this->judulEmail)
                    ->greeting('Halo ' . ($notifiable->name ?? 'Pengguna') . ',')
                    ->line($this->pesanNotifikasi)
                    ->action('Lihat Pengajuan Saya', $url)
                    ->line('Terima kasih telah menggunakan aplikasi kami.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'item_request_id' => $this->itemRequest->id,
            'pesan' => $this->pesanNotifikasi,
            'url' => route('pengajuan.barang.index'),
            'tipe_notifikasi' => 'update_pengajuan_' . strtolower($this->status),
        ];
    }
}