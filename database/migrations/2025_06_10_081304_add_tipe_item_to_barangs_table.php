<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            // Tambahkan kolom baru setelah 'nama_barang'
            $table->enum('tipe_item', ['habis_pakai', 'aset'])->default('habis_pakai')->after('nama_barang')->comment('Tipe item: habis pakai atau aset');
        });
    }
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn('tipe_item');
        });
    }
};