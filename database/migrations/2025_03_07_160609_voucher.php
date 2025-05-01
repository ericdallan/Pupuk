<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique(); // Made unique for foreign key
            $table->string('voucher_type');
            $table->date('voucher_date');
            $table->string('voucher_day');
            $table->string('prepared_by');
            $table->string('given_to');
            $table->text('transaction')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('store')->nullable();
            $table->string('invoice')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
