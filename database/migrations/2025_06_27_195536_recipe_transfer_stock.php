<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_transfer_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->foreignId('transfer_stock_id')->constrained('transfer_stocks')->onDelete('cascade');
            $table->string('item');
            $table->string('size');
            $table->integer('quantity')->default(1); // Jumlah bahan baku yang dibutuhkan
            $table->decimal('nominal', 15, 2)->default(0.00); // Kolom Nominal, decimal (15 digit total, 2 desimal), default 0.00
            $table->timestamps();

            // $table->unique(['recipe_id', 'transfer_stock_id']); // Hindari duplikat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_transfer_stock');
    }
};
