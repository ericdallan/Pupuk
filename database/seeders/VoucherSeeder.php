<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        // Calculate total debit and credit
        $totalDebitCredit = (5 * 30000) + (3 * 14000) + (7 * 9500) + (4 * 13000) + (6 * 19000) +
            (2 * 28000) + (8 * 23500) + (9 * 20000) + (4 * 29000) + (2 * 41000) +
            (3 * 58500) + (5 * 49000) + (7 * 33000);

        // Insert into vouchers table
        DB::table('vouchers')->insert([
            'id' => 1,
            'voucher_number' => 'PB-00000002',
            'voucher_type' => 'PB',
            'voucher_date' => Carbon::now()->toDateString(),
            'voucher_day' => Carbon::now()->locale('id')->dayName,
            'prepared_by' => 'Eric',
            'given_to' => 'Heri',
            'transaction' => 'Stok Awal',
            'approved_by' => 'Eric Dallan',
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
                'account_code' => '1.1.05.01',
                'account_name' => 'Persediaan Barang Dagangan',
                'debit' => $totalDebitCredit,
                'credit' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert into transactions table
        DB::table('transactions')->insert([
            ['id' => 1, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '10x10x3m', 'quantity' => 5, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 3, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 7, 'nominal' => 9500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '1,5x17x3m', 'quantity' => 4, 'nominal' => 13000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 6, 'nominal' => 19000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 2, 'nominal' => 28000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2,5x20x3m', 'quantity' => 8, 'nominal' => 23500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 9, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 4, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 2, 'nominal' => 41000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '6x12x4m', 'quantity' => 3, 'nominal' => 58500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x20x4m', 'quantity' => 5, 'nominal' => 49000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '2x20x4m', 'quantity' => 7, 'nominal' => 33000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into stocks table
        DB::table('stocks')->insert([
            ['id' => 1, 'item' => 'Kayu Jenjeng', 'size' => '10x10x3m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Kayu Jenjeng', 'size' => '10x10x3m','quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Jenjeng', 'size' => '1,5x17x3m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Kayu Jenjeng', 'size' => '1,5x17x3m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Jenjeng', 'size' => '2,5x20x3m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Kayu Jenjeng', 'size' => '2,5x20x3m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Merahan', 'size' => '6x12x4m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Kayu Merahan', 'size' => '6x12x4m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Merahan', 'size' => '3x20x4m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Kayu Merahan', 'size' => '3x20x4m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Kayu Merahan', 'size' => '2x20x4m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Kayu Merahan', 'size' => '2x20x4m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
