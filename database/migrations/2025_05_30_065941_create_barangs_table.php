<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kode_barang')->unique()->nullable();
            $table->text('deskripsi')->nullable();
            // Untuk kategori, unit, lokasi akan kita tambahkan relasinya nanti
            // $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->onDelete('set null');
            // $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            // $table->foreignId('lokasi_id')->nullable()->constrained('lokasis')->onDelete('set null');
            $table->integer('stok')->default(0);
            $table->decimal('harga_beli', 15, 2)->nullable();
            $table->string('gambar')->nullable(); // Path atau nama file gambar
            $table->enum('status', ['aktif', 'rusak', 'hilang', 'dipinjam'])->default('aktif');
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};