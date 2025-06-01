<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade'); // Barang yang bergerak
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Pengguna yang melakukan pencatatan (opsional jika bisa dilakukan sistem)
            $table->enum('tipe_pergerakan', ['masuk', 'keluar']); // Jenis pergerakan: 'masuk' atau 'keluar'
            $table->integer('kuantitas'); // Jumlah barang yang bergerak (selalu positif)
            $table->integer('stok_sebelumnya')->nullable(); // Stok barang sebelum pergerakan ini
            $table->integer('stok_setelahnya')->nullable(); // Stok barang setelah pergerakan ini
            $table->timestamp('tanggal_pergerakan')->useCurrent(); // Waktu pergerakan terjadi
            $table->text('catatan')->nullable(); // Catatan tambahan (misal: No. PO, No. SO, koreksi stok)
            $table->timestamps(); // created_at (waktu pencatatan) dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};