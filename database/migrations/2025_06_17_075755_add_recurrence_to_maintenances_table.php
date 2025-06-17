<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('lampiran')->comment('Apakah jadwal ini berulang?');
            $table->integer('recurrence_interval')->nullable()->after('is_recurring')->comment('Interval pengulangan (contoh: 3)');
            $table->enum('recurrence_unit', ['hari', 'minggu', 'bulan', 'tahun'])->nullable()->after('recurrence_interval')->comment('Satuan interval (hari/minggu/bulan/tahun)');
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurrence_interval', 'recurrence_unit']);
        });
    }
};