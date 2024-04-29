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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->morphs('purchasable');
            $table->string('title');
            $table->string('short_description')->nullable();
            $table->string('option')->nullable();
            $table->string('sku')->index();
            $table->unsignedBigInteger('price')->index();
            $table->unsignedMediumInteger('quantity');
            $table->unsignedBigInteger('sub_total')->index();
            $table->unsignedBigInteger('discount_total')->default(0)->index();
            $table->unsignedBigInteger('total')->index();
            $table->longText('notes')->nullable();
            $table->unsignedBigInteger('sort')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
