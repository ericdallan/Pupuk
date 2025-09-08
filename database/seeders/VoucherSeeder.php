<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run()
    {
        // Calculate total debit and credit for Bahan Baku (Raw Materials)
        $totalDebitCreditBahanBaku =
            (64240822.00);

        // Calculate total debit and credit for Barang Setengah Jadi
        $totalDebitCreditSetengahJadi =
            // Kayu Mahoni
            (18 * 45000.00) + (22 * 34000.00) + (11 * 19500.00) + (46 * 30000.00) + (48 * 12000.00) + (8 * 40000.00) +
            (2 * 54000.00) + (112 * 21500.00) + (27 * 20000.00) + (63 * 14680.00) + (4 * 21600.00) + (4 * 7290.00) + (2 * 42300.00) +
            // Kayu Bayur
            (2 * 12000.00) + (110 * 14680.00) + (115 * 37000.00) + (85 * 30000.00) + (43 * 15000.00) + (18 * 25000.00);

        // Calculate total debit and credit for Barang Jadi
        $totalDebitCreditBarangJadi = (17387500.00);

        // Insert into vouchers table for Bahan Baku and Barang Jadi (PB)
        DB::table('vouchers')->insert([
            [
                'id' => 1,
                'voucher_number' => 'PB-00000001',
                'voucher_type' => 'PB',
                'voucher_date' => Carbon::create(2025, 9, 6)->toDateString(),
                'voucher_day' => Carbon::create(2025, 9, 6)->locale('id')->dayName,
                'prepared_by' => 'Eric',
                'given_to' => 'Heri',
                'transaction' => 'Stok Awal Bahan Baku & Barang Jadi',
                'approved_by' => 'Akang',
                'store' => 'Mebel',
                'invoice' => null,
                'total_debit' => $totalDebitCreditBahanBaku + $totalDebitCreditBarangJadi,
                'total_credit' => $totalDebitCreditBahanBaku + $totalDebitCreditBarangJadi,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Insert for Barang Setengah Jadi (PH)
            [
                'id' => 2,
                'voucher_number' => 'PH-00000001',
                'voucher_type' => 'PH',
                'voucher_date' => Carbon::create(2025, 9, 6)->toDateString(),
                'voucher_day' => Carbon::create(2025, 9, 6)->locale('id')->dayName,
                'prepared_by' => 'Eric',
                'given_to' => 'Heri',
                'transaction' => 'Stok Awal Barang Setengah Jadi',
                'approved_by' => 'Akang',
                'store' => 'Mebel',
                'invoice' => null,
                'total_debit' => $totalDebitCreditSetengahJadi,
                'total_credit' => $totalDebitCreditSetengahJadi,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert into voucher_details table for Bahan Baku and Barang Jadi (PB)
        DB::table('voucher_details')->insert([
            [
                'id' => 1,
                'voucher_id' => 1,
                'account_code' => '3.1.00.00',
                'account_name' => 'Modal Pemilik',
                'debit' => 0.00,
                'credit' => $totalDebitCreditBahanBaku + $totalDebitCreditBarangJadi,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'voucher_id' => 1,
                'account_code' => '1.1.05.02',
                'account_name' => 'Persediaan Bahan Baku',
                'debit' => $totalDebitCreditBahanBaku,
                'credit' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'voucher_id' => 1,
                'account_code' => '1.1.05.04',
                'account_name' => 'Persediaan Barang Jadi',
                'debit' => $totalDebitCreditBarangJadi,
                'credit' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Insert for Barang Setengah Jadi (PH)
            [
                'id' => 4,
                'voucher_id' => 2,
                'account_code' => '3.1.00.00',
                'account_name' => 'Modal Pemilik',
                'debit' => 0.00,
                'credit' => $totalDebitCreditSetengahJadi,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'voucher_id' => 2,
                'account_code' => '1.1.05.03',
                'account_name' => 'Persediaan Barang Dalam Proses',
                'debit' => $totalDebitCreditSetengahJadi,
                'credit' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert into transactions table for Bahan Baku and Barang Jadi (PB)
        DB::table('transactions')->insert([
            //Bahan Baku
            // Jati
            ['id' => 1, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'nominal' => 96000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'nominal' => 115200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '5x10x2m', 'quantity' => 2, 'nominal' => 80000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '4x8x2m', 'quantity' => 1, 'nominal' => 51200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '4x20x2m', 'quantity' => 1, 'nominal' => 128000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '3x9x2m', 'quantity' => 1, 'nominal' => 43200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 7, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 130, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 27, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 127, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 69, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 16, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 29, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x9x1,5m', 'quantity' => 110, 'nominal' => 14680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Jenjeng
            ['id' => 14, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 50, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 29, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 123, 'nominal' => 12625.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 312, 'nominal' => 8000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 726, 'nominal' => 6923.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 305, 'nominal' => 9125.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 235, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 102, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x6x3m', 'quantity' => 39, 'nominal' => 11000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x2,5m', 'quantity' => 1, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 34, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 94, 'nominal' => 5500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 11, 'nominal' => 4000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'nominal' => 21000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 290, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x2m', 'quantity' => 4, 'nominal' => 6000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 30, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 5, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 18, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'nominal' => 66000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 91, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 1, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 48, 'nominal' => 18750.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 44, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x28x2m', 'quantity' => 1, 'nominal' => 58240.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x28x2m', 'quantity' => 1, 'nominal' => 43680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'nominal' => 53000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 42, 'nominal' => 32000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 1, 'nominal' => 38000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 18, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 22, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 11, 'nominal' => 19500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 48, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'nominal' => 54000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 112, 'nominal' => 21500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 27, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 63, 'nominal' => 14680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x1,5m', 'quantity' => 4, 'nominal' => 21600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x1,5m', 'quantity' => 4, 'nominal' => 7290.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 2, 'nominal' => 42300.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 54, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '10x10x3m (A)', 'quantity' => 6, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 130, 'nominal' => 18800.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 57, 'nominal' => 28667.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x15x3m', 'quantity' => 2, 'nominal' => 21600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 10, 'nominal' => 11750.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '6x12x4m', 'quantity' => 35, 'nominal' => 57600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x3m', 'quantity' => 2, 'nominal' => 16800.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x20x2m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x2,5m', 'quantity' => 2, 'nominal' => 13125.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x18x3m', 'quantity' => 3, 'nominal' => 25920.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x3m', 'quantity' => 102, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x2,5m', 'quantity' => 3, 'nominal' => 9000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Reng
            ['id' => 66, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '2x3x2m', 'quantity' => 605, 'nominal' => 1575.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '3x4x2m', 'quantity' => 29, 'nominal' => 3500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '2x3x4m', 'quantity' => 15, 'nominal' => 2400.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            //Bahan Setengah Jadi
            // Kayu Mahoni
            ['id' => 69, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 18, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 22, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 71, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 11, 'nominal' => 19500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 46, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 48, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 8, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 75, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'nominal' => 54000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 76, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 112, 'nominal' => 21500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 27, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 63, 'nominal' => 14680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x20x1,5m', 'quantity' => 4, 'nominal' => 21600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x9x1,5m', 'quantity' => 4, 'nominal' => 7290.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 2, 'nominal' => 42300.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 82, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 2, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 83, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x9x1,5m', 'quantity' => 110, 'nominal' => 14680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 115, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 85, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 86, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 43, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 87, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 18, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            //Barang Jadi
            // Bupenlih Kaca Polos
            ['id' => 88, 'voucher_id' => 1, 'description' => 'Bupenlih Kaca Polos', 'size' => '40x60', 'quantity' => 2, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bupenlih Mahoni
            ['id' => 89, 'voucher_id' => 1, 'description' => 'Bupenlih Mahoni', 'size' => '40x60', 'quantity' => 1, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Jendela
            ['id' => 90, 'voucher_id' => 1, 'description' => 'Kusen Jendela', 'size' => 'Jalosi', 'quantity' => 2, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 91, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '150x50', 'quantity' => 1, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '120x50', 'quantity' => 1, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '140x40', 'quantity' => 2, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Pintu Bayur
            ['id' => 94, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '80x200', 'quantity' => 3, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '75x200', 'quantity' => 15, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '70x200', 'quantity' => 4, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Pintu Mahoni
            ['id' => 97, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '120x200', 'quantity' => 1, 'nominal' => 87500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 98, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '70x200', 'quantity' => 4, 'nominal' => 87500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 99, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '80x200', 'quantity' => 2, 'nominal' => 87500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Siku Mahoni
            ['id' => 100, 'voucher_id' => 1, 'description' => 'Kusen Siku Mahoni', 'size' => '150x50', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster
            ['id' => 101, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '128x15', 'quantity' => 9, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '88x15', 'quantity' => 6, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 103, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '78x15', 'quantity' => 24, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 104, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '84x15', 'quantity' => 5, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 105, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '48x15', 'quantity' => 1, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 106, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '123x15', 'quantity' => 3, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 107, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '55x15', 'quantity' => 5, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 108, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '23x23', 'quantity' => 7, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 110, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '140x15', 'quantity' => 1, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Kaca
            ['id' => 111, 'voucher_id' => 1, 'description' => 'Loster Kaca', 'size' => '83x15', 'quantity' => 1, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Kembang
            ['id' => 112, 'voucher_id' => 1, 'description' => 'Loster Kembang', 'size' => '30x15', 'quantity' => 3, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 113, 'voucher_id' => 1, 'description' => 'Loster Kembang', 'size' => '89x15', 'quantity' => 15, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 114, 'voucher_id' => 1, 'description' => 'Loster Kembang', 'size' => '79x15', 'quantity' => 4, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 115, 'voucher_id' => 1, 'description' => 'Loster Kembang', 'size' => '49x15', 'quantity' => 6, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Polos
            ['id' => 116, 'voucher_id' => 1, 'description' => 'Loster Polos', 'size' => '30x15', 'quantity' => 4, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 117, 'voucher_id' => 1, 'description' => 'Loster Polos', 'size' => '88x15', 'quantity' => 3, 'nominal' => 24000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Bayur
            ['id' => 118, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '60x200', 'quantity' => 4, 'nominal' => 142500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 119, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '80x200', 'quantity' => 4, 'nominal' => 172500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 120, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '75x200', 'quantity' => 2, 'nominal' => 208000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Mahoni
            ['id' => 121, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '60x200', 'quantity' => 44, 'nominal' => 139500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 122, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '70x200', 'quantity' => 5, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 123, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '75x200', 'quantity' => 6, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 124, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '80x200', 'quantity' => 2, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Mahoni Tiblok
            ['id' => 125, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni Tiblok', 'size' => '80x200', 'quantity' => 1, 'nominal' => 194500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Panel Bayur
            ['id' => 126, 'voucher_id' => 1, 'description' => 'Pintu Panel Bayur', 'size' => '70x200', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca
            ['id' => 127, 'voucher_id' => 1, 'description' => 'Ram Kaca 3R Mahoni', 'size' => '140x40', 'quantity' => 13, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 128, 'voucher_id' => 1, 'description' => 'Ram Kaca 3R Mahoni', 'size' => '120x40', 'quantity' => 4, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 129, 'voucher_id' => 1, 'description' => 'Ram Kaca Flat Mahoni', 'size' => '140x40', 'quantity' => 23, 'nominal' => 60000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 130, 'voucher_id' => 1, 'description' => 'Ram Kaca Polos Mahoni', 'size' => '140x40', 'quantity' => 2, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 131, 'voucher_id' => 1, 'description' => 'Ram Kaca R Bayur', 'size' => '140x40', 'quantity' => 11, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 132, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '120x50', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 133, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '153x42', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 134, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '120x40', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 135, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '140x40', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 136, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '140x32', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 137, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '150x49', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 138, 'voucher_id' => 1, 'description' => 'Ram Kaca R Mahoni', 'size' => '57x57', 'quantity' => 2, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into stocks table (Bahan Baku)
        DB::table('stocks')->insert([
            // Jati
            ['id' => 1, 'item' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Jati', 'size' => '5x10x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'Jati', 'size' => '4x8x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Jati', 'size' => '4x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'Jati', 'size' => '3x9x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 7, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 27, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 26, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Jenjeng
            ['id' => 13, 'item' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 50, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 29, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 123, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 312, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 726, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 305, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 235, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 102, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Jenjeng', 'size' => '6x6x3m', 'quantity' => 39, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'Kayu Jenjeng', 'size' => '9x9x2,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 34, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 94, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 290, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'Kayu Jenjeng', 'size' => '4x9x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 29, 'item' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'item' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 45, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 36, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Kayu Mahoni', 'size' => '4x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'Kayu Mahoni', 'size' => '3x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'item' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'item' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 42, 'item' => 'Kayu Merahan', 'size' => '10x10x3m (A)', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'item' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 130, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 57, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'item' => 'Kayu Merahan', 'size' => '3x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'item' => 'Kayu Merahan', 'size' => '6x12x4m', 'quantity' => 35, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'Kayu Merahan', 'size' => '5x7x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Kayu Merahan', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'Kayu Merahan', 'size' => '5x7x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'item' => 'Kayu Merahan', 'size' => '3x18x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'item' => 'Kayu Merahan', 'size' => '5x10x3m', 'quantity' => 102, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'item' => 'Kayu Merahan', 'size' => '4x6x2,5m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Reng
            ['id' => 54, 'item' => 'Reng', 'size' => '2x3x2m', 'quantity' => 605, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'item' => 'Reng', 'size' => '3x4x2m', 'quantity' => 29, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'item' => 'Reng', 'size' => '2x3x4m', 'quantity' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Item Baru dari Barang Setengah Jadi (quantity => 0)
            // Kayu Mahoni
            ['id' => 57, 'item' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'item' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'item' => 'Kayu Mahoni', 'size' => '4x20x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'item' => 'Kayu Mahoni', 'size' => '3x9x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'item' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 68, 'item' => 'Kayu Bayur', 'size' => '3x9x1,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into transfer_stocks table (Barang Setengah Jadi)
        DB::table('transfer_stocks')->insert([
            // Kayu Mahoni
            ['id' => 1, 'item' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 22, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 46, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 112, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 27, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Mahoni', 'size' => '4x20x1,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'Kayu Mahoni', 'size' => '3x9x1,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 14, 'item' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Bayur', 'size' => '3x9x1,5m', 'quantity' => 110, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 115, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 85, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 43, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 18, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        // Insert into used_stocks table (Barang Jadi)
        DB::table('used_stocks')->insert([
            // Bupenlih Kaca Polos
            ['id' => 1, 'item' => 'Bupenlih Kaca Polos', 'size' => '40x60', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bupenlih Mahoni
            ['id' => 2, 'item' => 'Bupenlih Mahoni', 'size' => '40x60', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Jendela
            ['id' => 3, 'item' => 'Kusen Jendela', 'size' => 'Jalosi', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'Kusen Jendela Mahoni', 'size' => '150x50', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kusen Jendela Mahoni', 'size' => '120x50', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'Kusen Jendela Mahoni', 'size' => '140x40', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Pintu Bayur
            ['id' => 7, 'item' => 'Kusen Pintu Bayur', 'size' => '80x200', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'Kusen Pintu Bayur', 'size' => '75x200', 'quantity' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kusen Pintu Bayur', 'size' => '70x200', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Pintu Mahoni
            ['id' => 10, 'item' => 'Kusen Pintu Mahoni', 'size' => '120x200', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kusen Pintu Mahoni', 'size' => '70x200', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'Kusen Pintu Mahoni', 'size' => '80x200', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Siku Mahoni
            ['id' => 13, 'item' => 'Kusen Siku Mahoni', 'size' => '150x50', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster
            ['id' => 14, 'item' => 'Loster', 'size' => '128x15', 'quantity' => 9, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Loster', 'size' => '88x15', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'Loster', 'size' => '78x15', 'quantity' => 24, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Loster', 'size' => '84x15', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'Loster', 'size' => '48x15', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Loster', 'size' => '123x15', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'Loster', 'size' => '55x15', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Loster', 'size' => '23x23', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Loster', 'size' => '140x15', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Kaca
            ['id' => 24, 'item' => 'Loster Kaca', 'size' => '83x15', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Kembang
            ['id' => 25, 'item' => 'Loster Kembang', 'size' => '30x15', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'Loster Kembang', 'size' => '89x15', 'quantity' => 15, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Loster Kembang', 'size' => '79x15', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'Loster Kembang', 'size' => '49x15', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster Polos
            ['id' => 29, 'item' => 'Loster Polos', 'size' => '30x15', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'Loster Polos', 'size' => '88x15', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Bayur
            ['id' => 31, 'item' => 'Panel Pintu Bayur', 'size' => '60x200', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'Panel Pintu Bayur', 'size' => '80x200', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Panel Pintu Bayur', 'size' => '75x200', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Mahoni
            ['id' => 34, 'item' => 'Panel Pintu Mahoni', 'size' => '60x200', 'quantity' => 44, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Panel Pintu Mahoni', 'size' => '70x200', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'Panel Pintu Mahoni', 'size' => '75x200', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Panel Pintu Mahoni', 'size' => '80x200', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Panel Pintu Mahoni Tiblok
            ['id' => 38, 'item' => 'Panel Pintu Mahoni Tiblok', 'size' => '80x200', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Pintu Panel Bayur
            ['id' => 39, 'item' => 'Pintu Panel Bayur', 'size' => '70x200', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca
            ['id' => 40, 'item' => 'Ram Kaca 3R Mahoni', 'size' => '140x40', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Ram Kaca 3R Mahoni', 'size' => '120x40', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'item' => 'Ram Kaca Flat Mahoni', 'size' => '140x40', 'quantity' => 23, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'item' => 'Ram Kaca Polos Mahoni', 'size' => '140x40', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'Ram Kaca R Bayur', 'size' => '140x40', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'item' => 'Ram Kaca R Mahoni', 'size' => '120x50', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'Ram Kaca R Mahoni', 'size' => '153x42', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'item' => 'Ram Kaca R Mahoni', 'size' => '120x40', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'Ram Kaca R Mahoni', 'size' => '140x40', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Ram Kaca R Mahoni', 'size' => '140x32', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'Ram Kaca R Mahoni', 'size' => '150x49', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'item' => 'Ram Kaca R Mahoni', 'size' => '57x57', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
