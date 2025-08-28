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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'skipped'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['maintenance_id', 'scheduled_date']);
            $table->index(['status', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
