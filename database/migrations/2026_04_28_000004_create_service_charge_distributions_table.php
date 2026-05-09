<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_charge_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->date('distribution_date');
            $table->integer('month');
            $table->integer('year');
            $table->timestamps();
            
            $table->index(['staff_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_charge_distributions');
    }
};
