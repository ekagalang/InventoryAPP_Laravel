<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Barang;
use App\Models\User; // Untuk notifikasi
use App\Notifications\LowStockNotification; // Untuk notifikasi
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Untuk notifikasi
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        Log::info('StockMovementObserver: created event triggered for StockMovement ID: ' . $stockMovement->id); // LOG AWAL

        $barang = $stockMovement->barang;

        if ($barang) { // Kurung kurawal pembuka untuk if ($barang)
            Log::info('StockMovementObserver: Barang found: ' . $barang->nama_barang . ' (ID: ' . $barang->id . ')');
            
            $stokBarangSebelumPergerakanIni = $barang->stok;
            Log::info('StockMovementObserver: Stok barang SEBELUM update: ' . $stokBarangSebelumPergerakanIni);

            if ($stockMovement->tipe_pergerakan == 'masuk' || $stockMovement->tipe_pergerakan == 'koreksi-tambah') {
                $barang->stok += $stockMovement->kuantitas;
                Log::info('StockMovementObserver: Tipe MASUK/KOREKSI-TAMBAH. Kuantitas: ' . $stockMovement->kuantitas . '. Stok barang akan menjadi: ' . $barang->stok);
            } elseif ($stockMovement->tipe_pergerakan == 'keluar' || $stockMovement->tipe_pergerakan == 'koreksi-kurang') {
                $barang->stok -= $stockMovement->kuantitas;
                Log::info('StockMovementObserver: Tipe KELUAR/KOREKSI-KURANG. Kuantitas: ' . $stockMovement->kuantitas . '. Stok barang akan menjadi: ' . $barang->stok);
            } else {
                Log::warning('StockMovementObserver: Tipe pergerakan tidak dikenal: ' . $stockMovement->tipe_pergerakan);
            }
            
            $barang->save(); // Simpan perubahan stok barang
            Log::info('StockMovementObserver: Stok barang SETELAH update dan disimpan: ' . $barang->fresh()->stok); // Ambil stok terbaru dari DB

            // Update stok_sebelumnya dan stok_setelahnya di StockMovement
            // $stockMovement->stok_sebelumnya diisi dengan nilai stok barang SEBELUM observer ini mengubahnya
            // $stockMovement->stok_setelahnya diisi dengan nilai stok barang SETELAH observer ini mengubahnya
            $stockMovement->stok_sebelumnya = $stokBarangSebelumPergerakanIni;
            $stockMovement->stok_setelahnya = $barang->stok; // Ini sudah $barang->stok yang terupdate
            $stockMovement->saveQuietly();
            Log::info('StockMovementObserver: StockMovement record updated with stok_sebelumnya: ' . $stockMovement->stok_sebelumnya . ' dan stok_setelahnya: ' . $stockMovement->stok_setelahnya);


            // PENGECEKAN STOK MINIMUM & PENGIRIMAN NOTIFIKASI
            if (($stockMovement->tipe_pergerakan == 'keluar' || $stockMovement->tipe_pergerakan == 'koreksi-kurang') &&
                $barang->stok_minimum > 0 && 
                $barang->stok <= $barang->stok_minimum) 
            { 
                Log::info('STOK MINIMUM TERCAPAI untuk: ' . $barang->nama_barang . '. Mengirim notifikasi...');
                $usersToNotify = User::role(['Admin', 'StafGudang'])->get(); 
                if ($usersToNotify->isNotEmpty()) {
                    Notification::send($usersToNotify, new LowStockNotification($barang));
                    Log::info('Notifikasi LowStockNotification dikirim ke ' . $usersToNotify->count() . ' pengguna.');
                } else {
                    Log::warning('Tidak ada user Admin atau StafGudang yang ditemukan untuk notifikasi stok minimum.');
                }
            } 
        
        } else { // Kurung kurawal penutup untuk if ($barang) 
            Log::error('StockMovementObserver: Barang tidak ditemukan untuk StockMovement ID: ' . $stockMovement->id);
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
