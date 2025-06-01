<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lokasi')->unique(); // Contoh: Gudang Utama, Lantai 2 - Rak A1
            $table->string('kode_lokasi')->unique()->nullable(); // Kode unik untuk lokasi, misal: GDU-01, L2-RA1
            $table->text('deskripsi_lokasi')->nullable(); // Deskripsi tambahan tentang lokasi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasis');
    }
};