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
        // Service charges table - monthly tracking
        Schema::create('service_charges', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->tinyInteger('month'); // 1-12
            $table->decimal('total_collected', 10, 2)->default(0);
            $table->decimal('total_distributed', 10, 2)->default(0);
            $table->decimal('remaining_balance', 10, 2)->default(0);
            $table->timestamp('distribution_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->timestamps();
            
            $table->unique(['year', 'month']);
            $table->index(['year', 'month']);
            $table->index('status');
        });

        // Service charge distributions table - staff distribution records
        Schema::create('service_charge_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_charge_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2)->nullable(); // Optional percentage for equal split
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['service_charge_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_charge_distributions');
        Schema::dropIfExists('service_charges');
    }
};
