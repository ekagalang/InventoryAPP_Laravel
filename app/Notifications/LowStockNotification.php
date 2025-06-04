<?php

namespace App\Notifications;

use App\Models\Barang;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage; // Jika nanti mau pakai email
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification // Opsional: implements ShouldQueue
{
    use Queueable;

    public Barang $barang;

    /**
     * Create a new notification instance.
     */
    public function __construct(Barang $barang)
    {
        $this->barang = $barang;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Kita fokus ke database dulu untuk notifikasi di web
    }

    /**
     * Get the array representation of the notification.
     * (Data yang akan disimpan di kolom 'data' tabel notifications)
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'barang_id' => $this->barang->id,
            'nama_barang' => $this->barang->nama_barang,
            'kode_barang' => $this->barang->kode_barang, // Tambahkan kode barang jika perlu
            'stok_saat_ini' => $this->barang->stok,
            'stok_minimum' => $this->barang->stok_minimum,
            'pesan' => 'Stok untuk barang "' . $this->barang->nama_barang . '" (' . ($this->barang->kode_barang ?? 'N/A') . ') telah mencapai atau di bawah batas minimum!',
            'url' => route('barang.show', $this->barang->id), // URL tujuan saat notifikasi diklik
        ];
    }
    
    // Jika nanti mau pakai email, uncomment dan sesuaikan method toMail()
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->subject('Peringatan Stok Minimum: ' . $this->barang->nama_barang)
    //                 ->greeting('Halo ' . $notifiable->name . ',')
    //                 ->line('Stok untuk barang "' . $this->barang->nama_barang . '" (' . ($this->barang->kode_barang ?? 'N/A') . ') telah mencapai atau di bawah batas minimum.')
    //                 ->line('Stok saat ini: ' . $this->barang->stok)
    //                 ->line('Stok minimum yang ditentukan: ' . $this->barang->stok_minimum)
    //                 ->action('Lihat Detail Barang', route('barang.show', $this->barang->id))
    //                 ->line('Harap segera lakukan pengecekan dan pengadaan ulang jika diperlukan.');
    // }
}