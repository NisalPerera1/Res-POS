<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['full_day', 'half_day']);
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'date']);
            $table->index(['date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_leaves');
    }
};
