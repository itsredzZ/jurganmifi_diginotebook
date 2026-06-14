<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['mifi', 'router', 'battery', 'simcard', 'accessory']);
            $table->unsignedBigInteger('purchase_price');
            $table->unsignedBigInteger('sell_price');
            $table->integer('stock_rumah')->default(0);
            $table->integer('stock_toko')->default(0);
            $table->date('date_added');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};