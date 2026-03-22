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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('kot_round')->nullable()->after('status')->comment('KOT round number');
            $table->timestamp('kot_sent_at')->nullable()->after('kot_round')->comment('When item was sent to kitchen');
            
            // Add index for performance
            $table->index(['order_id', 'kot_round']);
            $table->index(['order_id', 'is_void', 'kot_round']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'kot_round']);
            $table->dropIndex(['order_id', 'is_void', 'kot_round']);
            $table->dropColumn(['kot_round', 'kot_sent_at']);
        });
    }
};
