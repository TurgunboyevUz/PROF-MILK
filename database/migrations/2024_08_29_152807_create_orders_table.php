<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('tg_users')->onDelete('cascade');
            $table->mediumText('products');
            $table->decimal('shipping_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_method', ['cash', 'payme', 'click'])->default('cash');
            $table->boolean('payment_status')->default(false);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
