<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Modifier groups (Size, Spice Level, Add-ons)
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // "Size", "Spice Level"
            $table->boolean('is_required')->default(false); // must pick one?
            $table->integer('min_select')->default(0);     // min selections
            $table->integer('max_select')->default(1);     // max selections
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add group_id to modifiers
        Schema::table('modifiers', function (Blueprint $table) {
            $table->foreignId('modifier_group_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('modifier_groups')
                  ->nullOnDelete();
        });

        // Pivot: menu_item to modifier_group
        Schema::create('menu_item_modifier_group', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_item_id', 'modifier_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifier_group');
        Schema::table('modifiers', fn($t) => $t->dropForeign(['modifier_group_id']));
        Schema::table('modifiers', fn($t) => $t->dropColumn('modifier_group_id'));
        Schema::dropIfExists('modifier_groups');
    }
};