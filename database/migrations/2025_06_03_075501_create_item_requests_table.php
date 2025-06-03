<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('User yang mengajukan');
            $table->foreignId('barang_id')->constrained('barangs')->comment('Barang yang diminta');
            $table->integer('kuantitas_diminta');
            $table->integer('kuantitas_disetujui')->nullable();
            $table->text('keperluan');
            $table->date('tanggal_dibutuhkan')->nullable();
            $table->enum('status', ['Diajukan', 'Disetujui', 'Ditolak', 'Diproses', 'Dibatalkan'])->default('Diajukan');
            $table->foreignId('approved_by')->nullable()->constrained('users')->comment('User yang menyetujui/menolak');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->comment('User yang memproses (mengeluarkan barang)');
            $table->timestamp('processed_at')->nullable();
            $table->text('catatan_approval')->nullable()->comment('Catatan dari approver');
            $table->text('catatan_pemroses')->nullable()->comment('Catatan dari pemroses');
            $table->timestamps(); // tanggal_pengajuan akan menggunakan created_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_requests');
    }
};