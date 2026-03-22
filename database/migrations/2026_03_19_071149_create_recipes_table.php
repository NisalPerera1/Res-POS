<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Recipe links menu items to ingredients (for auto-deduction)
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_used', 10, 3); // per 1 serving
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('recipes'); }
};