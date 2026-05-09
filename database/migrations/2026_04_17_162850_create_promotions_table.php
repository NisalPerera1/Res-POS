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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'buy_one_get_one'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->nullable(); // for percentage or fixed amount
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->date('active_from');
            $table->date('active_to');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['active_from', 'active_to']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
