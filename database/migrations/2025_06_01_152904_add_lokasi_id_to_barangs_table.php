<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->foreignId('lokasi_id')
                  ->nullable()
                  ->after('unit_id') // Atau sesuaikan posisinya, setelah unit_id
                  ->constrained('lokasis') // Merujuk ke tabel 'lokasis'
                  ->onDelete('set null'); // Jika lokasi dihapus, set lokasi_id di barang menjadi NULL
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });
    }
};