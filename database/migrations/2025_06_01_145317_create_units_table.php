<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit')->unique(); // Contoh: Pieces, Box, Kilogram
            $table->string('singkatan_unit')->unique()->nullable(); // Contoh: Pcs, Box, Kg
            $table->text('deskripsi_unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};