<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->string('group_name'); // e.g. "Spice Level", "Size", "Add-ons"
            $table->string('name');       // e.g. "Mild", "Large", "Extra Cheese"
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot: menu_item_modifier
        Schema::create('menu_item_modifier', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_item_id', 'modifier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifier');
        Schema::dropIfExists('modifiers');
    }
};