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
            (1 * 35000.00) + (63 * 30000.00) + (104 * 23500.00) + (63 * 14000.00) + (451 * 8500.00) +
            (303 * 7500.00) + (225 * 9500.00) + (162 * 13000.00) + (13 * 18500.00) + (64 * 48000.00) +
            (28 * 7500.00) + (111 * 6000.00) + (7 * 4500.00) + (5 * 18000.00) + (6 * 21000.00) + (10 * 16000.00) +
            // Kayu Merahan
            (149 * 20000.00) + (116 * 29000.00) + (42 * 41000.00) + (400 * 2000.00) + (44 * 3600.00) +
            (75 * 2500.00) + (6 * 45000.00) + (46 * 12000.00) + (121 * 25000.00) +
            // Kayu Mahoni
            (9 * 22500.00) + (54 * 35000.00) + (2 * 55000.00) + (2 * 21000.00) + (31 * 55000.00) +
            (2 * 66000.00) + (85 * 32500.00) + (2 * 40000.00) + (18 * 14000.00) + (4 * 12500.00) +
            (14 * 18000.00) + (48 * 43000.00) + (4 * 53000.00) + (110 * 24000.00) + (14 * 20000.00) +
            (2 * 53000.00) + (39 * 32000.00) + (3 * 38000.00) +
            // Kayu Bayur
            (4 * 37000.00) + (32 * 27000.00) + (48 * 31000.00) + (49 * 15000.00) + (14 * 14000.00) +
            (65 * 43000.00) + (51 * 25000.00) + (9 * 17000.00) + (6 * 31000.00) + (2 * 25000.00) +
            // Kusen Jendela Mahoni
            (9 * 105000.00) + (12 * 105000.00) +
            // Pintu Kayu (Panel)
            (8 * 400000.00) + (11 * 400000.00) + (7 * 400000.00) + (28 * 400000.00) +
            // Pintu (Full Triplek)
            (5 * 260000.00) +
            // Loster (motif bali)
            (18 * 55000.00) + (25 * 45000.00) + (3 * 45000.00) + (27 * 45000.00) + (10 * 20000.00) + (56 * 35000.00) +
            // Jati
            (2 * 150000.00) + (1 * 110000.00) + (2 * 130000.00) + (3 * 30000.00) +
            // Pintu Tikblok
            (1 * 290000.00) +
            // Kusen Jendela Bayur
            (4 * 150000.00) +
            // Ram Kaca Bayur
            (12 * 125000.00);

        // Insert into vouchers table
        DB::table('vouchers')->insert([
            'id' => 1,
            'voucher_number' => 'PB-00000001',
            'voucher_type' => 'PB',
            'voucher_date' => Carbon::now()->toDateString(),
            'voucher_day' => Carbon::now()->locale('id')->dayName,
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
            ['id' => 2, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 63, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 104, 'nominal' => 23500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 63, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 451, 'nominal' => 8500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x6x3m (A)', 'quantity' => 303, 'nominal' => 7500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 225, 'nominal' => 9500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '1,5x18x3m', 'quantity' => 162, 'nominal' => 13000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 13, 'nominal' => 18500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 64, 'nominal' => 48000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x6x3m bc', 'quantity' => 28, 'nominal' => 7500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 111, 'nominal' => 6000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 7, 'nominal' => 4500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 5, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'nominal' => 21000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m', 'quantity' => 10, 'nominal' => 16000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 17, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 149, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 116, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 42, 'nominal' => 41000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 400, 'nominal' => 2000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 44, 'nominal' => 3600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 75, 'nominal' => 2500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 6, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 46, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x3m', 'quantity' => 121, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 26, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 9, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 54, 'nominal' => 35000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 2, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 2, 'nominal' => 21000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 31, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'nominal' => 66000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 85, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 18, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 4, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 48, 'nominal' => 43000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 4, 'nominal' => 53000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 110, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 14, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'nominal' => 53000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 39, 'nominal' => 32000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'nominal' => 38000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 44, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 4, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 32, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 48, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 49, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 14, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 65, 'nominal' => 43000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 51, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 9, 'nominal' => 17000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 6, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 2, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Jendela Mahoni
            ['id' => 54, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '40x120', 'quantity' => 9, 'nominal' => 105000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '40x140', 'quantity' => 12, 'nominal' => 105000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Kayu (Panel)
            ['id' => 56, 'voucher_id' => 1, 'description' => 'Pintu Kayu (Panel)', 'size' => '80x2m', 'quantity' => 8, 'nominal' => 400000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'voucher_id' => 1, 'description' => 'Pintu Kayu (Panel)', 'size' => '75x2m', 'quantity' => 11, 'nominal' => 400000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'voucher_id' => 1, 'description' => 'Pintu Kayu (Panel)', 'size' => '70x2m', 'quantity' => 7, 'nominal' => 400000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'voucher_id' => 1, 'description' => 'Pintu Kayu (Panel)', 'size' => '60x2m', 'quantity' => 28, 'nominal' => 400000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu (Full Triplek)
            ['id' => 60, 'voucher_id' => 1, 'description' => 'Pintu (Full Triplek)', 'size' => '80x2m', 'quantity' => 5, 'nominal' => 260000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster (motif bali)
            ['id' => 61, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x128m', 'quantity' => 18, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x88m', 'quantity' => 25, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x83m', 'quantity' => 3, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x78m', 'quantity' => 27, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x30m', 'quantity' => 10, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'voucher_id' => 1, 'description' => 'Loster (motif bali)', 'size' => '15x49m', 'quantity' => 56, 'nominal' => 35000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Jati
            ['id' => 67, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '4x20x2m', 'quantity' => 2, 'nominal' => 150000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'nominal' => 110000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 69, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'nominal' => 130000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '5x10x2m', 'quantity' => 3, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Tikblok
            ['id' => 71, 'voucher_id' => 1, 'description' => 'Pintu Tikblok', 'size' => '80x2m', 'quantity' => 1, 'nominal' => 290000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Jendela Bayur
            ['id' => 72, 'voucher_id' => 1, 'description' => 'Kusen Jendela Bayur', 'size' => '140x40', 'quantity' => 4, 'nominal' => 150000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca Bayur
            ['id' => 73, 'voucher_id' => 1, 'description' => 'Ram Kaca Bayur', 'size' => '120x40', 'quantity' => 12, 'nominal' => 125000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into stocks table
        DB::table('stocks')->insert([
            // Kayu Jenjeng
            ['id' => 1, 'item' => 'Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 104, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 104, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 451, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 451, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Jenjeng', 'size' => '4x6x3m (A)', 'quantity' => 303, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x6x3m (A)', 'quantity' => 303, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 225, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Kayu Jenjeng', 'size' => '1,5x15x3m', 'quantity' => 225, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Jenjeng', 'size' => '1,5x18x3m', 'quantity' => 162, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Kayu Jenjeng', 'size' => '1,5x18x3m', 'quantity' => 162, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Jenjeng', 'size' => '4x6x3m bc', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x6x3m bc', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 111, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 111, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'item' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'HPP Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 33, 'item' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 149, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 149, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 116, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'HPP Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 116, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'HPP Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'item' => 'Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 400, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'item' => 'HPP Kayu Merahan', 'size' => '2x3x2m', 'quantity' => 400, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 44, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'item' => 'HPP Kayu Merahan', 'size' => '3x4x2m', 'quantity' => 44, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'item' => 'Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'HPP Kayu Merahan', 'size' => '2x3x2,5m', 'quantity' => 75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'item' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'HPP Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'item' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 46, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 46, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Kayu Merahan', 'size' => '5x10x3m', 'quantity' => 121, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'HPP Kayu Merahan', 'size' => '5x10x3m', 'quantity' => 121, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 51, 'item' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'item' => 'HPP Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 54, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 54, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 54, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'item' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'item' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 31, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 31, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'item' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 85, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'item' => 'HPP Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 85, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'item' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'item' => 'HPP Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 69, 'item' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'item' => 'HPP Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 71, 'item' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 75, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 76, 'item' => 'HPP Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 110, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 110, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'item' => 'HPP Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'item' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 82, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 83, 'item' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 39, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 39, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'item' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 86, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 87, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 88, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 89, 'item' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 90, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 91, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'item' => 'HPP Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 49, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 94, 'item' => 'HPP Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 49, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'item' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'item' => 'HPP Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 97, 'item' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 65, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 98, 'item' => 'HPP Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 65, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 99, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 51, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 100, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 51, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 101, 'item' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'item' => 'HPP Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 103, 'item' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 104, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 105, 'item' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 106, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        DB::table('used_stocks')->insert([
            // Kusen Jendela Mahoni
            ['id' => 1, 'item' => 'Kusen Jendela Mahoni', 'size' => '40x120', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'Kusen Jendela Mahoni', 'size' => '40x140', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Kayu (Panel)
            ['id' => 3, 'item' => 'Pintu Kayu (Panel)', 'size' => '80x2m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'Pintu Kayu (Panel)', 'size' => '75x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Pintu Kayu (Panel)', 'size' => '70x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'Pintu Kayu (Panel)', 'size' => '60x2m', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu (Full Triplek)
            ['id' => 7, 'item' => 'Pintu (Full Triplek)', 'size' => '80x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster (motif bali)
            ['id' => 8, 'item' => 'Loster (motif bali)', 'size' => '15x128m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Loster (motif bali)', 'size' => '15x88m', 'quantity' => 25, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'Loster (motif bali)', 'size' => '15x83m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Loster (motif bali)', 'size' => '15x78m', 'quantity' => 27, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'Loster (motif bali)', 'size' => '15x30m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Loster (motif bali)', 'size' => '15x49m', 'quantity' => 56, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Jati
            ['id' => 14, 'item' => 'Jati', 'size' => '4x20x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Jati', 'size' => '5x10x2m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Tikblok
            ['id' => 18, 'item' => 'Pintu Tikblok', 'size' => '80x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Jendela Bayur
            ['id' => 19, 'item' => 'Kusen Jendela Bayur', 'size' => '140x40', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca Bayur
            ['id' => 20, 'item' => 'Ram Kaca Bayur', 'size' => '120x40', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
