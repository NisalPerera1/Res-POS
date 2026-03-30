<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Update image column to be properly nullable and add comment
            $table->string('image')->nullable()->change()->comment('Menu item image filename');
        });
    }

    public function down(): void
    {
        // No rollback needed since we're just updating column definition
    }
};
