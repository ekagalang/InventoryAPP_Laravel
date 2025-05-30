<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            // Tambahkan kolom kategori_id setelah kolom 'deskripsi' (atau sesuaikan posisinya)
            // Pastikan kolom ini bisa null jika barang tidak wajib memiliki kategori,
            // atau definisikan onDelete behavior (misal cascade, set null)
            $table->foreignId('kategori_id')
                  ->nullable() // Boleh null jika barang bisa tidak punya kategori
                  ->after('deskripsi') // Opsional: menentukan posisi kolom
                  ->constrained('kategoris') // Merujuk ke tabel 'kategoris' kolom 'id'
                  ->onDelete('set null'); // Jika kategori dihapus, set kategori_id di barang menjadi NULL
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['kategori_id']);
            // Kemudian hapus kolomnya
            $table->dropColumn('kategori_id');
        });
    }
};