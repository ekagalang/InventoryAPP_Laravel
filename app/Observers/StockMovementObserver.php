<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        $barang = $stockMovement->barang; // Mengambil instance Barang yang berelasi

        if ($barang) {
            // 1. Ambil stok barang SAAT INI (sebelum diubah oleh pergerakan ini)
            // Nilai ini akan menjadi 'stok_sebelumnya' yang sebenarnya untuk pergerakan ini.
            $stokBarangSebelumPergerakanIni = $barang->stok;

            // 2. Update stok barang berdasarkan tipe pergerakan
            if ($stockMovement->tipe_pergerakan == 'masuk') {
                $barang->stok += $stockMovement->kuantitas;
            } elseif ($stockMovement->tipe_pergerakan == 'keluar') {
                // Pertimbangan untuk validasi stok cukup bisa ditambahkan di controller sebelum menyimpan StockMovement,
                // atau di sini jika ada aturan bisnis yang ketat (misalnya, tidak boleh negatif).
                // Untuk sekarang, kita biarkan bisa mengurangi.
                $barang->stok -= $stockMovement->kuantitas;
            }

            // 3. Simpan perubahan stok pada model Barang
            $barang->save();

            // 4. Update record StockMovement yang baru saja dibuat dengan nilai stok_sebelumnya dan stok_setelahnya
            // Kita menggunakan saveQuietly() agar tidak memicu event observer lagi (menghindari infinite loop).
            $stockMovement->stok_sebelumnya = $stokBarangSebelumPergerakanIni;
            $stockMovement->stok_setelahnya = $barang->stok; // Stok barang yang baru setelah diupdate
            
            // Bungkus dalam DB::transaction jika ingin memastikan keduanya (update barang & stockMovement) berhasil atau gagal bersamaan.
            // Namun, untuk kasus ini, jika $barang->save() gagal, kode di bawahnya tidak akan tereksekusi.
            // Jika $stockMovement->saveQuietly() gagal setelah $barang->save() berhasil, akan ada sedikit inkonsistensi
            // pada kolom stok_sebelumnya/stok_setelahnya di stock_movements, tapi stok barang utama tetap akurat.
            // DB::transaction(function () use ($stockMovement, $barang, $stokBarangSebelumPergerakanIni) {
            //     $barang->save(); // Pindahkan $barang->save() ke dalam transaksi jika menggunakannya
            //     $stockMovement->stok_sebelumnya = $stokBarangSebelumPergerakanIni;
            //     $stockMovement->stok_setelahnya = $barang->stok;
            //     $stockMovement->saveQuietly();
            // });
            $stockMovement->saveQuietly();
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
