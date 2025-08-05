<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        // Calculate total debit and credit based on transactions
        $totalDebitCredit =
            // Kayu Jenjeng
            (1 * 35000.00) + (97 * 29000.00) + (223 * 22500.00) + (163 * 13000.00) + (145 * 8000.00) +
            (764 * 7000.00) + (340 * 9500.00) + (180 * 12500.00) + (47 * 18000.00) + (6 * 27000.00) +
            (49 * 5500.00) + (29 * 4000.00) + (0 * 18000.00) + (6 * 21000.00) + (108 * 16000.00) +
            // Kayu Merahan
            (71 * 19000.00) + (55 * 29000.00) + (13 * 40000.00) + (36 * 2000.00) + (21 * 3600.00) +
            (0 * 2500.00) + (0 * 45000.00) + (6 * 12000.00) +
            // Kayu Mahoni
            (0 * 22500.00) + (107 * 34000.00) + (7 * 55000.00) + (0 * 19500.00) + (28 * 55000.00) +
            (2 * 66000.00) + (0 * 30000.00) + (0 * 40000.00) + (0 * 12000.00) + (59 * 12500.00) +
            (14 * 18000.00) + (35 * 40000.00) + (2 * 54000.00) + (10 * 21500.00) + (0 * 20000.00) +
            (0 * 53000.00) + (48 * 32000.00) + (3 * 38000.00) +
            // Kayu Bayur
            (6 * 37000.00) + (37 * 27000.00) + (40 * 31000.00) + (32 * 15000.00) + (0 * 14000.00) +
            (58 * 43000.00) + (0 * 25000.00) + (0 * 17000.00) + (0 * 31000.00) + (0 * 25000.00);

        // Insert into vouchers table
        DB::table('vouchers')->insert([
            'id' => 1,
            'voucher_number' => 'PB-00000001',
            'voucher_type' => 'PB',
            'voucher_date' => Carbon::create(2025, 1, 1)->toDateString(),
            'voucher_day' => Carbon::create(2025, 1, 1)->locale('id')->dayName,
            'prepared_by' => 'Eric',
            'given_to' => 'Heri',
            'transaction' => 'Stok Awal',
            'approved_by' => 'Akang',
            'store' => 'Mebel',
            'invoice' => NULL,
            'total_debit' => $totalDebitCredit,
            'total_credit' => $totalDebitCredit,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Insert into voucher_details table
        DB::table('voucher_details')->insert([
            [
                'id' => 1,
                'voucher_id' => 1,
                'account_code' => '3.1.00.00',
                'account_name' => 'Modal Pemilik',
                'debit' => 0.00,
                'credit' => $totalDebitCredit,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'voucher_id' => 1,
                'account_code' => '1.1.05.02',
                'account_name' => 'Persediaan Bahan Baku',
                'debit' => $totalDebitCredit,
                'credit' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert into transactions table
        DB::table('transactions')->insert([
            // Kayu Jenjeng
            ['id' => 1, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'nominal' => 35000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 97, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 223, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 163, 'nominal' => 13000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 145, 'nominal' => 8000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 764, 'nominal' => 7000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 340, 'nominal' => 9500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 180, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 47, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 49, 'nominal' => 5500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 29, 'nominal' => 4000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 0, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'nominal' => 21000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 108, 'nominal' => 16000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 16, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 71, 'nominal' => 19000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 55, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 13, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 36, 'nominal' => 2000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 21, 'nominal' => 3600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 0, 'nominal' => 2500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 0, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 24, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 0, 'nominal' => 19500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'nominal' => 66000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 0, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 0, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 35, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'nominal' => 54000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 10, 'nominal' => 21500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 0, 'nominal' => 53000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'nominal' => 32000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'nominal' => 38000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 42, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 6, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 37, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 40, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 32, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'nominal' => 43000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 0, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'nominal' => 17000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into stocks table
        DB::table('stocks')->insert([
            // Kayu Jenjeng
            ['id' => 1, 'item' => 'Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 97, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 97, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 223, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 223, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 163, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 163, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 145, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 145, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 764, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 764, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 340, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 340, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 180, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 180, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 47, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 47, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 49, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 49, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 29, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 29, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'HPP Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 108, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 108, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 31, 'item' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 71, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 71, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 55, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'HPP Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 55, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'HPP Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 36, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'HPP Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 36, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'item' => 'Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 21, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'item' => 'HPP Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 21, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'item' => 'HPP Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'item' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'HPP Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'item' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 47, 'item' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'HPP Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'item' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 54, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'item' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'item' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'item' => 'HPP Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'item' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'item' => 'HPP Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'item' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'item' => 'HPP Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'item' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 69, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 35, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 35, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 71, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'item' => 'HPP Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 75, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 76, 'item' => 'HPP Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'item' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'item' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'item' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 82, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 83, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'item' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 86, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 87, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 40, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 88, 'item' => 'HPP Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 40, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 89, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 90, 'item' => 'HPP Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 91, 'item' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'item' => 'HPP Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'item' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 94, 'item' => 'HPP Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 97, 'item' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 98, 'item' => 'HPP Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 99, 'item' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 100, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 101, 'item' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
