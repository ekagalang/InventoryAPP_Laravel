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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_payment_id')->constrained()->cascadeOnDelete();
            $table->date('due_date'); // Tanggal jatuh tempo
            $table->decimal('expected_amount', 15, 2); // Nominal yang diharapkan
            $table->decimal('actual_amount', 15, 2)->nullable(); // Nominal yang dibayar
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled', 'skipped'])->default('pending');
            $table->date('paid_date')->nullable(); // Tanggal pembayaran
            $table->string('payment_method')->nullable(); // Metode pembayaran (transfer, cash, dll)
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['recurring_payment_id', 'due_date']);
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
