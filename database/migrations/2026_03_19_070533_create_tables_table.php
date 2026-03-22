<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // e.g. "T-01", "Bar 3"
            $table->string('section')->nullable(); // e.g. "Main Hall", "Outdoor"
            $table->integer('capacity')->default(4);
            $table->enum('status', ['free', 'occupied', 'reserved', 'cleaning'])
                  ->default('free');
            $table->unsignedBigInteger('current_order_id')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};