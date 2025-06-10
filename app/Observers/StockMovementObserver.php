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
        Log::info('[OBSERVER] Dijalankan untuk StockMovement ID: ' . $stockMovement->id);

        $barang = Barang::find($stockMovement->barang_id); // Gunakan find() agar lebih aman
        if (!$barang) {
            Log::error('[OBSERVER] Gagal: Barang dengan ID ' . $stockMovement->barang_id . ' tidak ditemukan.');
            return;
        }

        $stokSebelumnya = $barang->stok;
        Log::info('[OBSERVER] Barang: ' . $barang->nama_barang . ' | Stok Awal: ' . $stokSebelumnya);

        if (in_array($stockMovement->tipe_pergerakan, ['masuk', 'koreksi-tambah', 'pengembalian'])) {
            $barang->stok += $stockMovement->kuantitas;
        } elseif (in_array($stockMovement->tipe_pergerakan, ['keluar', 'koreksi-kurang'])) {
            $barang->stok -= $stockMovement->kuantitas;
        }

        $barang->save(); // Simpan perubahan stok pada barang
        Log::info('[OBSERVER] Barang: ' . $barang->nama_barang . ' | Stok Baru: ' . $barang->stok);

        // Update record StockMovement dengan data stok yang akurat
        $stockMovement->stok_sebelumnya = $stokSebelumnya;
        $stockMovement->stok_setelahnya = $barang->stok;
        $stockMovement->saveQuietly(); // Simpan tanpa memicu observer lagi

        // ... (logika pengiriman notifikasi stok minimum tetap di sini) ...
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
