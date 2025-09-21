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
        Schema::create('applied_cost_details', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('applied_cost_id'); // Foreign key to applied_costs
            $table->decimal('nominal', 15, 2); // Individual beban amount
            $table->foreign('applied_cost_id')->references('id')->on('applied_costs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_cost_details');
    }
};
