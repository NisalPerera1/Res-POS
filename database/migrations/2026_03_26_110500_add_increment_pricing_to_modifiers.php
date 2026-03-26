<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_item_modifier', function (Blueprint $table) {
            // Add increment pricing - how much to ADD to base item price
            $table->decimal('increment_price', 10, 2)->nullable()->after('custom_price');
            
            // Add pricing type column
            $table->enum('pricing_type', ['absolute', 'increment'])->default('absolute')->after('modifier_id');
        });
    }

    public function down(): void
    {
        Schema::table('menu_item_modifier', function (Blueprint $table) {
            $table->dropColumn('increment_price');
            $table->dropColumn('pricing_type');
        });
    }
};
