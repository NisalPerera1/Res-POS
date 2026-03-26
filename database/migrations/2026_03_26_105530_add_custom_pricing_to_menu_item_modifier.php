<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_item_modifier', function (Blueprint $table) {
            // Add custom price column to allow different prices for different menu items
            $table->decimal('custom_price', 10, 2)->nullable()->after('modifier_id');
            
            // Add index for better performance
            $table->index(['menu_item_id', 'modifier_id']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_item_modifier', function (Blueprint $table) {
            $table->dropIndex(['menu_item_id', 'modifier_id']);
            $table->dropColumn('custom_price');
        });
    }
};
