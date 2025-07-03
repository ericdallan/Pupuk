<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('product_name'); // Nama barang jadi
            $table->unsignedBigInteger('used_stock_id')->nullable(); // Foreign key to used_stocks
            $table->foreign('used_stock_id')->references('id')->on('used_stocks')->onDelete('set null');
            $table->decimal('nominal', 15, 2)->default(0.00); // Kolom Nominal, decimal (15 digit total, 2 desimal), default 0.00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
