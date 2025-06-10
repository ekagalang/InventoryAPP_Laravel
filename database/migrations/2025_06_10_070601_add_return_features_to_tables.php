<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB Facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan status 'Dikembalikan' ke tabel item_requests
        // Menggunakan DB::statement untuk mengubah ENUM di MySQL
        DB::statement("ALTER TABLE item_requests MODIFY COLUMN status ENUM('Diajukan', 'Disetujui', 'Ditolak', 'Diproses', 'Dibatalkan', 'Dikembalikan') NOT NULL DEFAULT 'Diajukan'");

        // 2. Tambahkan kolom-kolom baru untuk data pengembalian di tabel item_requests
        Schema::table('item_requests', function (Blueprint $table) {
            $table->foreignId('returned_by_staff_id')->nullable()->after('catatan_pemroses')->constrained('users')->onDelete('set null')->comment('User staf yang menerima pengembalian');
            $table->timestamp('returned_at')->nullable()->after('returned_by_staff_id')->comment('Waktu pengembalian dicatat');
            $table->text('catatan_pengembalian')->nullable()->after('returned_at')->comment('Catatan saat barang dikembalikan');
        });

        // 3. Tambahkan tipe 'pengembalian' ke tabel stock_movements
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN tipe_pergerakan ENUM('masuk', 'keluar', 'koreksi-tambah', 'koreksi-kurang', 'pengembalian') NOT NULL COMMENT 'Jenis pergerakan stok'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke definisi lama jika perlu rollback
        DB::statement("ALTER TABLE item_requests MODIFY COLUMN status ENUM('Diajukan', 'Disetujui', 'Ditolak', 'Diproses', 'Dibatalkan') NOT NULL DEFAULT 'Diajukan'");

        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropForeign(['returned_by_staff_id']);
            $table->dropColumn(['returned_by_staff_id', 'returned_at', 'catatan_pengembalian']);
        });

        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN tipe_pergerakan ENUM('masuk', 'keluar', 'koreksi-tambah', 'koreksi-kurang') NOT NULL COMMENT 'Jenis pergerakan stok'");
    }
};