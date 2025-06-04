<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Menggunakan DB::statement karena mengubah enum bisa tricky dan beda antar database
            // Contoh untuk MySQL:
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN tipe_pergerakan ENUM('masuk', 'keluar', 'koreksi-tambah', 'koreksi-kurang') NOT NULL COMMENT 'Jenis pergerakan stok'");
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Kembalikan ke definisi lama jika perlu rollback
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN tipe_pergerakan ENUM('masuk', 'keluar') NOT NULL COMMENT 'Jenis pergerakan stok'");
        });
    }
};
