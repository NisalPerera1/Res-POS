<?php

// ─────────────────────────────────────────────────────────────
// 2024_01_01_000002_create_staff_leaves_table.php
// ─────────────────────────────────────────────────────────────
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['full_day', 'half_day']);
            // For half-day: whether morning or afternoon
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();
            $table->string('reason')->nullable();
            $table->boolean('is_paid')->default(false); // Paid vs unpaid leave
            $table->timestamps();

            // One record per staff per date (prevent duplicates)
            $table->unique(['staff_id', 'date', 'type']);
            $table->index(['staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leaves');
    }
};
