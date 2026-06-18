<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales_volumes', function (Blueprint $table) {
            $table->id();
            $table->integer('year'); // Tahun (2018, 2026, dst)
            $table->integer('month')->nullable(); // Bulan (1-12). Bisa NULL jika data cuma total per tahun
            $table->integer('quantity'); // Total unit terjual
            $table->boolean('is_forecast')->default(false); // False = Aktual, True = Prediksi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_volumes');
    }
};
