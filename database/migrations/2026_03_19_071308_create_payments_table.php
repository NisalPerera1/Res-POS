<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('method', ['cash', 'card', 'mobile', 'voucher', 'complimentary']);
            $table->decimal('amount', 10, 2);
            $table->decimal('tendered', 10, 2)->default(0); // cash given
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('reference')->nullable(); // card ref, etc.
            $table->string('receipt_number')->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};