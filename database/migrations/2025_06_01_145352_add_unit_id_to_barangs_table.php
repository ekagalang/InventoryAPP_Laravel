<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('kategori_id') // Atau sesuaikan posisinya
                  ->constrained('units')
                  ->onDelete('set null'); // Jika unit dihapus, set unit_id di barang menjadi NULL
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};