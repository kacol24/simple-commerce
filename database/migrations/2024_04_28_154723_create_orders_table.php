<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id');
            $table->foreignId('customer_id');
            $table->foreignId('reseller_id')->nullable();
            $table->string('order_no');
            $table->unsignedBigInteger('sub_total');
            $table->unsignedBigInteger('shipping_total');
            $table->unsignedBigInteger('discount_total');
            $table->unsignedBigInteger('fees_total');
            $table->unsignedBigInteger('grand_total');
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
