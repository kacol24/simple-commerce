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
            $table->string('order_no')->unique()->index();
            $table->unsignedBigInteger('sub_total')->index();
            $table->unsignedBigInteger('shipping_total')->default(0)->index();
            $table->longText('shipping_breakdown')->nullable();
            $table->unsignedBigInteger('discount_total')->default(0)->index();
            $table->unsignedBigInteger('fees_total')->default(0)->index();
            $table->unsignedBigInteger('grand_total')->default(0)->index();
            $table->unsignedBigInteger('paid_total')->default(0)->index();
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
