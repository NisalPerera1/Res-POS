<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('staff_advances');
        
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->tinyInteger('deduct_month')->nullable();
            $table->year('deduct_year')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'status']);
            $table->index(['deduct_month', 'deduct_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_advances');
    }
};
