<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('staff_leaves');
        
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['full_day', 'half_day'])->default('full_day');
            $table->enum('half_day_period', ['morning', 'afternoon'])->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
            
            $table->index(['staff_id', 'date']);
            $table->index(['type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_leaves');
    }
};
