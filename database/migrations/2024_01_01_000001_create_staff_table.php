<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone');
            $table->string('role');
            $table->enum('status', ['Active', 'On Leave', 'Resigned'])->default('Active');
            $table->decimal('salary', 10, 2);
            $table->date('hire_date');
            $table->string('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff');
    }
};
