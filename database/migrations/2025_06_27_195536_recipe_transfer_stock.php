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
        Schema::create('recipe_transfer_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->foreignId('transfer_stock_id')->constrained('transfer_stocks')->onDelete('cascade');
            $table->integer('quantity')->default(1); // Quantity of transfer_stock item required
            $table->timestamps();

            // Unique constraint to prevent duplicate recipe-transfer_stock combinations
            $table->unique(['recipe_id', 'transfer_stock_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_transfer_stock');
    }
};
