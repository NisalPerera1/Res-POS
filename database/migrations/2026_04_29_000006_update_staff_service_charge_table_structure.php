<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('staff_service_charges');
        
        Schema::create('staff_service_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->foreignId('monthly_service_charge_id')->constrained('monthly_service_charges')->onDelete('cascade');
            $table->tinyInteger('month');
            $table->year('year');
            $table->decimal('service_charge_pct', 5, 2);
            $table->decimal('share_amount', 10, 2);
            $table->timestamps();
            
            $table->unique(['staff_id', 'monthly_service_charge_id']);
            $table->index(['staff_id', 'month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_service_charges');
    }
};
