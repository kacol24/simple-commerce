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
        Schema::create('order_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string("amountable_type")->index();
            $table->string('name');
            $table->unsignedBigInteger('amount')->index();
            $table->longText('description')->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_amounts');
    }
};
