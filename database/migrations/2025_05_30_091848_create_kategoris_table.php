<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id(); // Primary key (auto-increment)
            $table->string('nama_kategori')->unique(); // Nama kategori, harus unik
            $table->text('deskripsi_kategori')->nullable(); // Deskripsi opsional
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};