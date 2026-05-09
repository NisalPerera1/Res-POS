<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('gross_pay', 10, 2)->default(0)->change();
            $table->decimal('total_deductions', 10, 2)->default(0)->change();
            $table->decimal('net_pay', 10, 2)->default(0)->change();
            $table->integer('days_worked')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('gross_pay', 10, 2)->change();
            $table->decimal('total_deductions', 10, 2)->change();
            $table->decimal('net_pay', 10, 2)->change();
            $table->integer('days_worked')->change();
        });
    }
};
