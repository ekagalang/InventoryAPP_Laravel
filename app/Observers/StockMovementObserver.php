<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Barang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Notifications\LowStockNotification; // TAMBAHKAN INI
use App\Models\User;                        // TAMBAHKAN INI
use Illuminate\Support\Facades\Notification; // TAMBAHKAN INI

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        $barang = $stockMovement->barang;

        if ($barang) {
            $stokBarangSebelumPergerakanIni = $barang->stok;

            if ($stockMovement->tipe_pergerakan == 'masuk' || $stockMovement->tipe_pergerakan == 'koreksi-tambah') {
                $barang->stok += $stockMovement->kuantitas;
            } elseif ($stockMovement->tipe_pergerakan == 'keluar' || $stockMovement->tipe_pergerakan == 'koreksi-kurang') {
                $barang->stok -= $stockMovement->kuantitas;
            }
            $barang->save(); // Simpan perubahan stok barang

            // Update stok_sebelumnya dan stok_setelahnya di StockMovement
            $stockMovement->stok_sebelumnya = $stokBarangSebelumPergerakanIni;
            $stockMovement->stok_setelahnya = $barang->stok;
            $stockMovement->saveQuietly();

            // === PENGECEKAN STOK MINIMUM ===
            // Hanya cek jika ini adalah transaksi keluar atau koreksi kurang,
            // dan jika barang tersebut memiliki pengaturan stok_minimum > 0
            if (($stockMovement->tipe_pergerakan == 'keluar' || $stockMovement->tipe_pergerakan == 'koreksi-kurang') &&
                $barang->stok_minimum > 0 && 
                $barang->stok <= $barang->stok_minimum) 
            {
                Log::info('STOK MINIMUM TERCAPAI untuk barang: ' . $barang->nama_barang . '. Notifikasi akan dikirim.'); // Biarkan Log untuk debugging awal

                // Ambil user yang akan dinotifikasi (misalnya Admin dan StafGudang)
                $usersToNotify = User::role(['Admin', 'StafGudang'])->get(); 

                if ($usersToNotify->isNotEmpty()) {
                    Notification::send($usersToNotify, new LowStockNotification($barang));
                    Log::info('Notifikasi LowStockNotification dikirim ke ' . $usersToNotify->count() . ' pengguna.');
                } else {
                    Log::warning('Tidak ada user Admin atau StafGudang yang ditemukan untuk dikirimi notifikasi stok minimum.');
                }
            }

                $usersToNotify = User::role(['Admin', 'StafGudang'])->get(); // Contoh mengambil user Admin & StafGudang
                if ($usersToNotify->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($usersToNotify, new LowStockNotification($barang));
                }
            }
        }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        // TODO (Untuk Pengembangan Lanjutan):
        // Jika Anda mengizinkan data pergerakan stok untuk diedit (misalnya, kuantitas atau tipe diubah),
        // logika untuk mengkalkulasi ulang stok barang perlu diimplementasikan di sini.
        // Ini akan lebih kompleks karena Anda perlu:
        // 1. Mengembalikan efek dari nilai pergerakan LAMA ke stok barang.
        // 2. Menerapkan efek dari nilai pergerakan BARU ke stok barang.
        // 3. Mengupdate stok_sebelumnya dan stok_setelahnya pada $stockMovement.
    }

    /**
     * Handle the StockMovement "deleted" event.
     *
     * @param  \App\Models\StockMovement  $stockMovement
     * @return void
     */

    public function deleted(StockMovement $stockMovement): void
    {
        // TODO (Untuk Pengembangan Lanjutan):
        // Jika Anda mengizinkan data pergerakan stok untuk dihapus,
        // stok barang harus dikembalikan/disesuaikan.
        // Misalnya:
        // - Jika pergerakan 'masuk' dihapus, maka stok barang harus dikurangi sejumlah kuantitas pergerakan tsb.
        // - Jika pergerakan 'keluar' dihapus, maka stok barang harus ditambah sejumlah kuantitas pergerakan tsb.
        //
        // $barang = $stockMovement->barang;
        // if ($barang) {
        //     if ($stockMovement->tipe_pergerakan == 'masuk') {
        //         $barang->stok -= $stockMovement->kuantitas;
        //     } elseif ($stockMovement->tipe_pergerakan == 'keluar') {
        //         $barang->stok += $stockMovement->kuantitas;
        //     }
        //     $barang->save();
        // }
    }


    /**
     * Handle the StockMovement "restored" event.
     */
    public function restored(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "force deleted" event.
     */
    public function forceDeleted(StockMovement $stockMovement): void
    {
        //
    }
}
