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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->string('voucher_number');
            $table->string('subsidiary_code');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('voucher_number')->references('voucher_number')->on('vouchers')->onDelete('cascade');
            $table->foreign('subsidiary_code')->references('subsidiary_code')->on('subsidiaries')->onDelete('restrict');

            // Indexes
            $table->index('invoice');
            $table->index('status');
        });

        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->timestamps();

            // Indexes
            $table->index('invoice_id');
            $table->index('voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoices');
    }
};
