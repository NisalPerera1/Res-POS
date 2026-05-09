<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->string('addon_name'); // e.g., "Extra Cheese", "Bullseye Egg"
            $table->decimal('quantity', 10, 2); // amount of the add-on
            $table->string('unit'); // grams, kg, pieces, units
            $table->decimal('unit_price', 10, 2); // price per unit
            $table->decimal('total_price', 10, 2); // quantity × unit_price
            $table->text('notes')->nullable(); // additional specifications
            $table->boolean('is_custom_price')->default(false); // for market-price items
            $table->timestamps();
            
            // Indexes for performance
            $table->index('order_item_id');
            $table->index('addon_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_addons');
    }
};
