<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('monthly_service_charges');
        
        Schema::create('monthly_service_charges', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
            
            $table->unique(['month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_service_charges');
    }
};
