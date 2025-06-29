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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id'); 
            $table->string('description');
            $table->string('size')->nullable(); // Kolom Size, string, nullable
            $table->integer('quantity')->unsigned()->default(1); // Kolom Quantity, integer positif, default 1
            $table->decimal('nominal', 15, 2)->default(0.00); // Kolom Nominal, decimal (15 digit total, 2 desimal), default 0.00
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rincian_transaksis');
    }
};
