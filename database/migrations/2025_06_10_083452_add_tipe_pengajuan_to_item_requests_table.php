<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->enum('tipe_pengajuan', ['permintaan', 'peminjaman'])->default('permintaan')->after('barang_id')->comment('Tipe pengajuan: permintaan (habis pakai) atau peminjaman (aset)');
        });
    }
    public function down(): void {
        Schema::table('item_requests', function (Blueprint $table) {
            $table->dropColumn('tipe_pengajuan');
        });
    }
};