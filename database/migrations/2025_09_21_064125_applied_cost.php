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
        Schema::create('applied_costs', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_nominal', 15, 2); // Total beban (e.g., Rp 1,000,000.00)
            $table->unsignedBigInteger('master_id'); // Foreign key to masters table
            $table->foreign('master_id')->references('id')->on('masters')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_costs');
    }
};
