<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_payments', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pembayaran');
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['platform', 'utilitas', 'asuransi', 'sewa', 'berlangganan', 'lainnya'])->default('lainnya');
            $table->date('tanggal_mulai');
            $table->decimal('nominal', 15, 2);
            $table->enum('status', ['aktif', 'nonaktif', 'selesai'])->default('aktif');
            $table->string('penerima')->nullable(); // Nama vendor/penerima
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            
            // Recurring settings
            $table->boolean('is_recurring')->default(true);
            $table->integer('recurrence_interval')->default(1);
            $table->enum('recurrence_unit', ['hari', 'minggu', 'bulan', 'tahun'])->default('bulan');
            $table->integer('max_occurrences')->nullable();
            $table->date('recurring_end_date')->nullable();
            
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['status', 'tanggal_mulai']);
            $table->index(['kategori', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_payments');
    }
};
