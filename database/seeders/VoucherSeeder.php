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
            // Kayu Jenjeng
            (1 * 35000.00) + (6 * 13000.00) + (104 * 29000.00) + (176 * 22500.00) + (180 * 13000.00) +
            (113 * 8000.00) + (705 * 7000.00) + (388 * 9500.00) + (195 * 12500.00) + (8 * 18000.00) +
            (1 * 20250.00) + (41 * 11000.00) + (4 * 9000.00) + (1 * 25000.00) + (6 * 27000.00) +
            (100 * 5500.00) + (32 * 4000.00) + (1 * 18000.00) + (7 * 21000.00) + (117 * 15000.00) +
            (7 * 6000.00) +
            // Kayu Merahan
            (63 * 19000.00) + (59 * 29000.00) + (12 * 40000.00) + (390 * 1500.00) + (122 * 3500.00) +
            (3 * 27000.00) + (0 * 2500.00) + (2 * 21600.00) + (5 * 45000.00) + (3 * 36000.00) +
            (6 * 12000.00) + (41 * 57600.00) + (6 * 16800.00) +
            // Kayu Mahoni
            (0 * 22500.00) + (107 * 34000.00) + (7 * 55000.00) + (7 * 19500.00) + (8 * 42300.00) +
            (28 * 55000.00) + (11 * 42300.00) + (2 * 66000.00) + (0 * 30000.00) + (1 * 40000.00) +
            (2 * 12000.00) + (59 * 12500.00) + (14 * 18000.00) + (32 * 40000.00) + (2 * 54000.00) +
            (57 * 13200.00) + (3 * 8640.00) + (6 * 21500.00) + (1 * 58240.00) + (1 * 43680.00) +
            (0 * 20000.00) + (2 * 53000.00) + (48 * 32000.00) + (3 * 38000.00) +
            // Kayu Bayur
            (12 * 37000.00) + (30 * 27000.00) + (2 * 30000.00) + (10 * 18000.00) + (31 * 15000.00) +
            (0 * 14000.00) + (58 * 43000.00) + (6 * 25000.00) + (0 * 17000.00) + (0 * 31000.00) +
            (0 * 36000.00) +
            // Jati
            (1 * 96000.00) + (2 * 115200.00) + (5 * 80000.00) + (1 * 51200.00) + (1 * 128000.00) +
            (2 * 43200.00);

        // Calculate total debit and credit for Barang Setengah Jadi
        $totalDebitCreditSetengahJadi =
            // Kayu Mahoni
            (74 * 19500.00) + (13 * 16200.00) + (139 * 30000.00) + (64 * 34000.00) + (8 * 42300.00) +
            (62 * 21500.00) + (37 * 40000.00) + (64 * 12000.00) + (14 * 54000.00) + (2 * 40000.00) +
            (6 * 20000.00) + (5 * 14040.00) +
            // Kayu Bayur
            (63 * 30000.00) + (2 * 43000.00) + (76 * 37000.00) + (1 * 25000.00) + (37 * 15000.00) +
            (1 * 17000.00) + (13 * 27000.00);

        // Calculate total debit and credit for Barang Jadi
        $totalDebitCreditBarangJadi =
            // Panel Pintu
            (42 * 139500.00) + (5 * 169500.00) + (10 * 169500.00) + (7 * 169500.00) + (1 * 172500.00) +
            (4 * 142500.00) + (2 * 172500.00) + (7 * 172500.00) + (10 * 60000.00) + (2 * 60000.00) +
            // Loster
            (12 * 12000.00) + (28 * 12000.00) + (23 * 12000.00) + (12 * 12000.00) + (19 * 12000.00) +
            (14 * 12000.00) + (2 * 12000.00) + (1 * 12000.00) + (1 * 12000.00) + (3 * 12000.00) +
            (7 * 12000.00) + (4 * 12000.00) +
            // Kusen
            (1 * 89000.00) + (1 * 89000.00) + (2 * 68000.00) + (1 * 68000.00) + (1 * 68000.00) +
            (1 * 68000.00) + (4 * 68000.00) +
            // Ram Kaca
            (14 * 32500.00) + (8 * 32500.00) + (30 * 32500.00) + (48 * 32500.00) + (4 * 32500.00) +
            (17 * 31000.00) +
            // Bupelin
            (1 * 34000.00) + (1 * 34000.00) +
            // Ram Kaca Riben & Polos
            (1 * 30000.00) + (1 * 30000.00) + (1 * 30000.00) + (1 * 30000.00) + (1 * 30000.00) +
            (2 * 30000.00) + (1 * 30000.00) +
            // Kusen Pintu
            (11 * 92500.00) + (23 * 92500.00) + (4 * 92500.00) + (11 * 89000.00) + (11 * 89000.00) +
            (6 * 89000.00) + (1 * 89000.00) + (1 * 89000.00) + (1 * 89000.00) +
            // Kusen Pintu Bayur
            (1 * 177500.00) + (2 * 177500.00) + (1 * 177500.00) + (2 * 280000.00) + (1 * 178000.00) +
            (2 * 178000.00);

        // Insert into vouchers table for Bahan Baku and Barang Jadi (PB)
        DB::table('vouchers')->insert([
            [
                'id' => 1,
                'voucher_number' => 'PB-00000001',
                'voucher_type' => 'PB',
                'voucher_date' => Carbon::create(2025, 8, 1)->toDateString(),
                'voucher_day' => Carbon::create(2025, 8, 1)->locale('id')->dayName,
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
                'voucher_date' => Carbon::create(2025, 8, 1)->toDateString(),
                'voucher_day' => Carbon::create(2025, 8, 1)->locale('id')->dayName,
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
            // Bahan Baku (Kayu Jenjeng)
            ['id' => 1, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'nominal' => 35000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x2,5m', 'quantity' => 6, 'nominal' => 13000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 104, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 176, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 180, 'nominal' => 13000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 113, 'nominal' => 8000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 705, 'nominal' => 7000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 388, 'nominal' => 9500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 195, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 8, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x2,5m', 'quantity' => 1, 'nominal' => 20250.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x6x3m', 'quantity' => 41, 'nominal' => 11000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x6x2,5m', 'quantity' => 4, 'nominal' => 9000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '9x9x2,5m', 'quantity' => 1, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 100, 'nominal' => 5500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 32, 'nominal' => 4000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 1, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 7, 'nominal' => 21000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 117, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'voucher_id' => 1, 'description' => 'Kayu Jenjeng', 'size' => '4x9x2m', 'quantity' => 7, 'nominal' => 6000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bahan Baku (Kayu Merahan)
            ['id' => 22, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 63, 'nominal' => 19000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 59, 'nominal' => 29000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 12, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '2x3x2m', 'quantity' => 390, 'nominal' => 1500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '3x4x2m', 'quantity' => 122, 'nominal' => 3500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x20x3m (Full)', 'quantity' => 3, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'voucher_id' => 1, 'description' => 'Reng', 'size' => '2x3x2,5m', 'quantity' => 0, 'nominal' => 2500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x15x3m', 'quantity' => 2, 'nominal' => 21600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 5, 'nominal' => 45000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '3x15x4m', 'quantity' => 3, 'nominal' => 36000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '6x12x4', 'quantity' => 41, 'nominal' => 57600.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'voucher_id' => 1, 'description' => 'Kayu Merahan', 'size' => '5x7x3m', 'quantity' => 6, 'nominal' => 16800.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bahan Baku (Kayu Mahoni)
            ['id' => 35, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'nominal' => 22500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 7, 'nominal' => 19500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'nominal' => 42300.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'nominal' => 55000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 11, 'nominal' => 42300.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'nominal' => 66000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 1, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 2, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'nominal' => 12500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 32, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'nominal' => 54000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x8x2,5m', 'quantity' => 57, 'nominal' => 13200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x8x2m', 'quantity' => 3, 'nominal' => 8640.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 6, 'nominal' => 21500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x28x2m', 'quantity' => 1, 'nominal' => 58240.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 54, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '3x28x2m', 'quantity' => 1, 'nominal' => 43680.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'nominal' => 53000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'nominal' => 32000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'voucher_id' => 1, 'description' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'nominal' => 38000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bahan Baku (Kayu Bayur)
            ['id' => 59, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 12, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 30, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 2, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x9x2m', 'quantity' => 10, 'nominal' => 18000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 31, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'nominal' => 14000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'nominal' => 43000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 6, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'nominal' => 17000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 69, 'voucher_id' => 1, 'description' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'nominal' => 36000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bahan Baku (Jati)
            ['id' => 70, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'nominal' => 96000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 71, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'nominal' => 115200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '5x10x2m', 'quantity' => 5, 'nominal' => 80000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '4x8x2m', 'quantity' => 1, 'nominal' => 51200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '4x20x2m', 'quantity' => 1, 'nominal' => 128000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 75, 'voucher_id' => 1, 'description' => 'Jati', 'size' => '3x9x2m', 'quantity' => 2, 'nominal' => 43200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Panel Pintu)
            ['id' => 76, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '60x2m', 'quantity' => 42, 'nominal' => 139500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '70x2m', 'quantity' => 5, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '75x2m', 'quantity' => 10, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'voucher_id' => 1, 'description' => 'Panel Pintu Mahoni', 'size' => '80x2m', 'quantity' => 7, 'nominal' => 169500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '90x2m', 'quantity' => 1, 'nominal' => 172500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '60x2m', 'quantity' => 4, 'nominal' => 142500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 82, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '75x2m', 'quantity' => 2, 'nominal' => 172500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 83, 'voucher_id' => 1, 'description' => 'Panel Pintu Bayur', 'size' => '80x2m', 'quantity' => 7, 'nominal' => 172500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'voucher_id' => 1, 'description' => 'Panel Pintu Triplek', 'size' => '80x2m', 'quantity' => 10, 'nominal' => 60000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'voucher_id' => 1, 'description' => 'Panel Pintu Triplek', 'size' => '75x2m', 'quantity' => 2, 'nominal' => 60000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Loster)
            ['id' => 86, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '128', 'quantity' => 12, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 87, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '88', 'quantity' => 28, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 88, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '78', 'quantity' => 23, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 89, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '84', 'quantity' => 12, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 90, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '48', 'quantity' => 19, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 91, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '30', 'quantity' => 14, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '118', 'quantity' => 2, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'voucher_id' => 1, 'description' => 'Loster', 'size' => '124', 'quantity' => 1, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 94, 'voucher_id' => 1, 'description' => 'Loster Kosong', 'size' => '88', 'quantity' => 1, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'voucher_id' => 1, 'description' => 'Loster Kosong', 'size' => '48', 'quantity' => 3, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'voucher_id' => 1, 'description' => 'Loster Kotak', 'size' => '', 'quantity' => 7, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 97, 'voucher_id' => 1, 'description' => 'Loster Kosongan', 'size' => '30', 'quantity' => 4, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Kusen)
            ['id' => 99, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '110x2m', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 100, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '120x4m', 'quantity' => 2, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 101, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '140x32m', 'quantity' => 5, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 103, 'voucher_id' => 1, 'description' => 'Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'nominal' => 68000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Ram Kaca)
            ['id' => 105, 'voucher_id' => 1, 'description' => 'Ram Kaca Mahoni', 'size' => '140x40m', 'quantity' => 14, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 106, 'voucher_id' => 1, 'description' => 'Ram Kaca Mahoni', 'size' => '140x32m', 'quantity' => 8, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 107, 'voucher_id' => 1, 'description' => 'Ram Kaca Mahoni', 'size' => '120x40m', 'quantity' => 30, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 108, 'voucher_id' => 1, 'description' => 'Ram Kaca Mahoni', 'size' => '91x36m', 'quantity' => 48, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 109, 'voucher_id' => 1, 'description' => 'Ram Kaca Mahoni', 'size' => '44x15m', 'quantity' => 4, 'nominal' => 32500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 110, 'voucher_id' => 1, 'description' => 'Ram Kaca Bayur', 'size' => '140x40m', 'quantity' => 17, 'nominal' => 31000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Bupelin)
            ['id' => 111, 'voucher_id' => 1, 'description' => 'Bupelin', 'size' => '68x48m', 'quantity' => 1, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 112, 'voucher_id' => 1, 'description' => 'Bupelin', 'size' => '60x40m', 'quantity' => 1, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Ram Kaca Riben & Polos)
            ['id' => 113, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '149x49m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 114, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '140x32m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 115, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '152x42m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 116, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '120x38m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 117, 'voucher_id' => 1, 'description' => 'Ram Kaca Polos Mahoni', 'size' => '138x41m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 118, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '140x40m', 'quantity' => 1, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 119, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Bayur', 'size' => '120x40', 'quantity' => 2, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 120, 'voucher_id' => 1, 'description' => 'Ram Kaca Riben Mahoni', 'size' => '57x67', 'quantity' => 2, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Jadi (Kusen Pintu)
            ['id' => 121, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '80x2m', 'quantity' => 11, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 122, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '75x2m', 'quantity' => 23, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 123, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '70x2m', 'quantity' => 4, 'nominal' => 92500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 124, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '75x2m', 'quantity' => 11, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 125, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '70x2m', 'quantity' => 11, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 126, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '80x2m', 'quantity' => 7, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 127, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '110x2m (C) ', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 128, 'voucher_id' => 1, 'description' => 'Kusen Pintu Mahoni', 'size' => '60x1,7m (C) ', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 129, 'voucher_id' => 1, 'description' => 'Kusen Siku', 'size' => '45x1,5m', 'quantity' => 1, 'nominal' => 89000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 130, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '215x2,7m (c)', 'quantity' => 1, 'nominal' => 177500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 131, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '99x2,15m (c)', 'quantity' => 2, 'nominal' => 177500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 132, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '89x2,15m (c)', 'quantity' => 1, 'nominal' => 177500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 133, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '215x1,36m (c)', 'quantity' => 2, 'nominal' => 280000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 134, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '98x1,12m (c)', 'quantity' => 1, 'nominal' => 178000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 135, 'voucher_id' => 1, 'description' => 'Kusen Pintu Bayur', 'size' => '89x1,12m (c)', 'quantity' => 2, 'nominal' => 178000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Barang Setengah Jadi (PH)
            ['id' => 136, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 74, 'nominal' => 19500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 137, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x20x1,5m', 'quantity' => 13, 'nominal' => 16200.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 138, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 139, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 139, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 64, 'nominal' => 34000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 140, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'nominal' => 42300.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 141, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 62, 'nominal' => 21500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 142, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 37, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 143, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 64, 'nominal' => 12000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 144, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 14, 'nominal' => 54000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 145, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'nominal' => 40000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 146, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 6, 'nominal' => 20000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 147, 'voucher_id' => 2, 'description' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 5, 'nominal' => 14040.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 148, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 63, 'nominal' => 30000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 149, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 2, 'nominal' => 43000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 150, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 76, 'nominal' => 37000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 151, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 1, 'nominal' => 25000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 152, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 37, 'nominal' => 15000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 153, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 1, 'nominal' => 17000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 154, 'voucher_id' => 2, 'description' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 13, 'nominal' => 27000.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // Insert into stocks table (Bahan Baku)
        DB::table('stocks')->insert([
            // Kayu Jenjeng
            ['id' => 1, 'item' => 'Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Kayu Jenjeng', 'size' => '10x10x3m (A)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Jenjeng', 'size' => '4x9x2,5m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x2,5m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 104, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Kayu Jenjeng', 'size' => '9x9x3m', 'quantity' => 104, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 176, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m (A)', 'quantity' => 176, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 180, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x3m', 'quantity' => 180, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 113, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x3m', 'quantity' => 113, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 705, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x6x3m', 'quantity' => 705, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 388, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x15x3m', 'quantity' => 388, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 195, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x18x3m', 'quantity' => 195, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Kayu Jenjeng', 'size' => '2x20x3m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Jenjeng', 'size' => '3x20x2,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x2,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Jenjeng', 'size' => '6x6x3m', 'quantity' => 41, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Kayu Jenjeng', 'size' => '6x6x3m', 'quantity' => 41, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Kayu Jenjeng', 'size' => '6x6x2,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Kayu Jenjeng', 'size' => '6x6x2,5m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Kayu Jenjeng', 'size' => '9x9x2,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'HPP Kayu Jenjeng', 'size' => '9x9x2,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'item' => 'Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 100, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2,5m', 'quantity' => 100, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x5x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'HPP Kayu Jenjeng', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'HPP Kayu Jenjeng', 'size' => '6x12x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'item' => 'Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 117, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'item' => 'HPP Kayu Jenjeng', 'size' => '5x10x3m (B)', 'quantity' => 117, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Kayu Jenjeng', 'size' => '4x9x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'item' => 'HPP Kayu Jenjeng', 'size' => '4x9x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Merahan
            ['id' => 43, 'item' => 'Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x4m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 45, 'item' => 'Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'HPP Kayu Merahan', 'size' => '5x7x4m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'item' => 'Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'HPP Kayu Merahan', 'size' => '5x10x4m', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Reng', 'size' => '2x3x2m', 'quantity' => 390, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'HPP Reng', 'size' => '2x3x2m', 'quantity' => 390, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'item' => 'Reng', 'size' => '3x4x2m', 'quantity' => 122, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'item' => 'HPP Reng', 'size' => '3x4x2m', 'quantity' => 122, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 53, 'item' => 'Kayu Merahan', 'size' => '3x20x3m (Full)', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 54, 'item' => 'HPP Kayu Merahan', 'size' => '3x20x3m (Full)', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'item' => 'Reng', 'size' => '2x3x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'item' => 'HPP Reng', 'size' => '2x3x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'item' => 'Kayu Merahan', 'size' => '3x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'item' => 'HPP Kayu Merahan', 'size' => '3x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 59, 'item' => 'Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'item' => 'HPP Kayu Merahan', 'size' => '3x18x4m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'item' => 'Kayu Merahan', 'size' => '3x15x4m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'item' => 'HPP Kayu Merahan', 'size' => '3x15x4m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'item' => 'Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'item' => 'HPP Kayu Merahan', 'size' => '4x6x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'item' => 'Kayu Merahan', 'size' => '6x12x4', 'quantity' => 41, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'item' => 'HPP Kayu Merahan', 'size' => '6x12x4', 'quantity' => 41, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'item' => 'Kayu Merahan', 'size' => '5x7x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'item' => 'HPP Kayu Merahan', 'size' => '5x7x3m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Mahoni
            ['id' => 69, 'item' => 'Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'item' => 'HPP Kayu Mahoni', 'size' => '2x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 71, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 107, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'item' => 'Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x3m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 75, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 76, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'item' => 'Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'item' => 'Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x2,5m', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'item' => 'Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 82, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 83, 'item' => 'Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'item' => 'HPP Kayu Mahoni', 'size' => '6x15x3m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 86, 'item' => 'HPP Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 87, 'item' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 88, 'item' => 'HPP Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 89, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 90, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 91, 'item' => 'Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'item' => 'HPP Kayu Mahoni', 'size' => '3x9x2,5m', 'quantity' => 59, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'item' => 'Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 94, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2,5m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 32, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 97, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 98, 'item' => 'HPP Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 99, 'item' => 'Kayu Mahoni', 'size' => '3x8x2,5m', 'quantity' => 57, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 100, 'item' => 'HPP Kayu Mahoni', 'size' => '3x8x2,5m', 'quantity' => 57, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 101, 'item' => 'Kayu Mahoni', 'size' => '3x8x2m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'item' => 'HPP Kayu Mahoni', 'size' => '3x8x2m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 103, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 104, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 105, 'item' => 'Kayu Mahoni', 'size' => '4x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 106, 'item' => 'HPP Kayu Mahoni', 'size' => '4x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 107, 'item' => 'Kayu Mahoni', 'size' => '3x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 108, 'item' => 'HPP Kayu Mahoni', 'size' => '3x28x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 109, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 110, 'item' => 'HPP Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 111, 'item' => 'Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 112, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2,5m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 113, 'item' => 'Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 114, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2,5m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 115, 'item' => 'Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 116, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x3m', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 117, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 118, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 119, 'item' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 30, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 120, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 30, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 121, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 122, 'item' => 'HPP Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 123, 'item' => 'Kayu Bayur', 'size' => '4x9x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 124, 'item' => 'HPP Kayu Bayur', 'size' => '4x9x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 125, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 31, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 126, 'item' => 'HPP Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 31, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 127, 'item' => 'Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 128, 'item' => 'HPP Kayu Bayur', 'size' => '3x12x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 129, 'item' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 130, 'item' => 'HPP Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 58, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 131, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 132, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 133, 'item' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 134, 'item' => 'HPP Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 135, 'item' => 'Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 136, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2,5m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 137, 'item' => 'Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 138, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x3m', 'quantity' => 0, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Jati
            ['id' => 139, 'item' => 'Jati', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 140, 'item' => 'HPP Jati', 'size' => '3x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 141, 'item' => 'Jati', 'size' => '6x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 142, 'item' => 'HPP Jati', 'size' => '6x12x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 143, 'item' => 'Jati', 'size' => '5x10x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 144, 'item' => 'HPP Jati', 'size' => '5x10x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 145, 'item' => 'Jati', 'size' => '4x8x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 146, 'item' => 'HPP Jati', 'size' => '4x8x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 147, 'item' => 'Jati', 'size' => '4x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 148, 'item' => 'HPP Jati', 'size' => '4x20x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 149, 'item' => 'Jati', 'size' => '3x9x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 150, 'item' => 'HPP Jati', 'size' => '3x9x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        // Insert into transfer_stocks table (Barang Setengah Jadi)
        DB::table('transfer_stocks')->insert([
            // Kayu Mahoni
            ['id' => 1, 'item' => 'Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 74, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x1,5m', 'quantity' => 74, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Kayu Mahoni', 'size' => '3x20x1,5m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Kayu Mahoni', 'size' => '3x20x1,5m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 139, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Kayu Mahoni', 'size' => '3x20x2m', 'quantity' => 139, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Kayu Mahoni', 'size' => '6x12x2,5m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 62, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Kayu Mahoni', 'size' => '4x12x2m', 'quantity' => 62, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Kayu Mahoni', 'size' => '4x20x2m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Kayu Mahoni', 'size' => '3x12x2m', 'quantity' => 64, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Kayu Mahoni', 'size' => '4x25x2m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Kayu Mahoni', 'size' => '3x25x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 21, 'item' => 'Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Kayu Mahoni', 'size' => '4x9x2m', 'quantity' => 6, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Kayu Mahoni', 'size' => '3x9x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kayu Bayur
            ['id' => 25, 'item' => 'Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Kayu Bayur', 'size' => '3x20x2m', 'quantity' => 63, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'HPP Kayu Bayur', 'size' => '4x20x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'item' => 'Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 76, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x2m', 'quantity' => 76, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'item' => 'Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'HPP Kayu Bayur', 'size' => '4x12x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'HPP Kayu Bayur', 'size' => '3x9x2m', 'quantity' => 37, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'HPP Kayu Bayur', 'size' => '4x8x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'HPP Kayu Bayur', 'size' => '6x12x1,5m', 'quantity' => 13, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
        DB::table('used_stocks')->insert([
            // Panel Pintu
            ['id' => 1, 'item' => 'Panel Pintu Mahoni', 'size' => '60x2m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'item' => 'HPP Panel Pintu Mahoni', 'size' => '60x2m', 'quantity' => 42, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'item' => 'Panel Pintu Mahoni', 'size' => '70x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 4, 'item' => 'HPP Panel Pintu Mahoni', 'size' => '70x2m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 5, 'item' => 'Panel Pintu Mahoni', 'size' => '75x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 6, 'item' => 'HPP Panel Pintu Mahoni', 'size' => '75x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 7, 'item' => 'Panel Pintu Mahoni', 'size' => '80x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 8, 'item' => 'HPP Panel Pintu Mahoni', 'size' => '80x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 9, 'item' => 'Panel Pintu Bayur', 'size' => '90x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 10, 'item' => 'HPP Panel Pintu Bayur', 'size' => '90x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 11, 'item' => 'Panel Pintu Bayur', 'size' => '60x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 12, 'item' => 'HPP Panel Pintu Bayur', 'size' => '60x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 13, 'item' => 'Panel Pintu Bayur', 'size' => '75x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 14, 'item' => 'HPP Panel Pintu Bayur', 'size' => '75x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 15, 'item' => 'Panel Pintu Bayur', 'size' => '80x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 16, 'item' => 'HPP Panel Pintu Bayur', 'size' => '80x2m', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 17, 'item' => 'Panel Pintu Triplek', 'size' => '80x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 18, 'item' => 'HPP Panel Pintu Triplek', 'size' => '80x2m', 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 19, 'item' => 'Panel Pintu Triplek', 'size' => '75x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 20, 'item' => 'HPP Panel Pintu Triplek', 'size' => '75x2m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Loster
            ['id' => 21, 'item' => 'Loster', 'size' => '128', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 22, 'item' => 'HPP Loster', 'size' => '128', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 23, 'item' => 'Loster', 'size' => '88', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 24, 'item' => 'HPP Loster', 'size' => '88', 'quantity' => 28, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 25, 'item' => 'Loster', 'size' => '78', 'quantity' => 23, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 26, 'item' => 'HPP Loster', 'size' => '78', 'quantity' => 23, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 27, 'item' => 'Loster', 'size' => '84', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 28, 'item' => 'HPP Loster', 'size' => '84', 'quantity' => 12, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 29, 'item' => 'Loster', 'size' => '48', 'quantity' => 19, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 30, 'item' => 'HPP Loster', 'size' => '48', 'quantity' => 19, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 31, 'item' => 'Loster', 'size' => '30', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 32, 'item' => 'HPP Loster', 'size' => '30', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 33, 'item' => 'Loster', 'size' => '118', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 34, 'item' => 'HPP Loster', 'size' => '118', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 35, 'item' => 'Loster', 'size' => '124', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 36, 'item' => 'HPP Loster', 'size' => '124', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 37, 'item' => 'Loster Kosong', 'size' => '88', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 38, 'item' => 'HPP Loster Kosong', 'size' => '88', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 39, 'item' => 'Loster Kosong', 'size' => '48', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 40, 'item' => 'HPP Loster Kosong', 'size' => '48', 'quantity' => 3, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 41, 'item' => 'Loster Kotak', 'size' => '', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 42, 'item' => 'HPP Loster Kotak', 'size' => '', 'quantity' => 7, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 43, 'item' => 'Loster Kosongan', 'size' => '30', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 44, 'item' => 'HPP Loster Kosongan', 'size' => '30', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen
            ['id' => 45, 'item' => 'Kusen Pintu Mahoni', 'size' => '80x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 46, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '80x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 47, 'item' => 'Kusen Pintu Mahoni', 'size' => '110x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 48, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '110x2m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 49, 'item' => 'Kusen Jendela Mahoni', 'size' => '120x4m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 50, 'item' => 'HPP Kusen Jendela Mahoni', 'size' => '120x4m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 51, 'item' => 'Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 52, 'item' => 'HPP Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 55, 'item' => 'Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 56, 'item' => 'HPP Kusen Jendela Mahoni', 'size' => '122x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 57, 'item' => 'Kusen Jendela Mahoni', 'size' => '140x32m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 58, 'item' => 'HPP Kusen Jendela Mahoni', 'size' => '140x32m', 'quantity' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca
            ['id' => 59, 'item' => 'Ram Kaca Mahoni', 'size' => '140x40m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 60, 'item' => 'HPP Ram Kaca Mahoni', 'size' => '140x40m', 'quantity' => 14, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 61, 'item' => 'Ram Kaca Mahoni', 'size' => '140x32m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 62, 'item' => 'HPP Ram Kaca Mahoni', 'size' => '140x32m', 'quantity' => 8, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 63, 'item' => 'Ram Kaca Mahoni', 'size' => '120x40m', 'quantity' => 30, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 64, 'item' => 'HPP Ram Kaca Mahoni', 'size' => '120x40m', 'quantity' => 30, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 65, 'item' => 'Ram Kaca Mahoni', 'size' => '91x36m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 66, 'item' => 'HPP Ram Kaca Mahoni', 'size' => '91x36m', 'quantity' => 48, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 67, 'item' => 'Ram Kaca Mahoni', 'size' => '44x15m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 68, 'item' => 'HPP Ram Kaca Mahoni', 'size' => '44x15m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 69, 'item' => 'Ram Kaca Bayur', 'size' => '140x40m', 'quantity' => 17, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 70, 'item' => 'HPP Ram Kaca Bayur', 'size' => '140x40m', 'quantity' => 17, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Bupelin
            ['id' => 71, 'item' => 'Bupelin', 'size' => '68x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 72, 'item' => 'HPP Bupelin', 'size' => '68x48m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 73, 'item' => 'Bupelin', 'size' => '60x40m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 74, 'item' => 'HPP Bupelin', 'size' => '60x40m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Ram Kaca Riben & Polos
            ['id' => 75, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '149x49m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 76, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '149x49m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 77, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '140x32m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 78, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '140x32m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 79, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '152x42m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 80, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '152x42m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 81, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '120x38m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 82, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '120x38m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 83, 'item' => 'Ram Kaca Polos Mahoni', 'size' => '138x41m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 84, 'item' => 'HPP Ram Kaca Polos Mahoni', 'size' => '138x41m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 85, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '140x40m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 86, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '140x40m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 87, 'item' => 'Ram Kaca Riben Bayur', 'size' => '120x40m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 88, 'item' => 'HPP Ram Kaca Riben Bayur', 'size' => '120x40m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 89, 'item' => 'Ram Kaca Riben Mahoni', 'size' => '57x67m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 90, 'item' => 'HPP Ram Kaca Riben Mahoni', 'size' => '57x67m', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // Kusen Pintu
            ['id' => 91, 'item' => 'Kusen Pintu Bayur', 'size' => '80x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 92, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '80x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 93, 'item' => 'Kusen Pintu Bayur', 'size' => '75x2m', 'quantity' => 23, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 94, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '75x2m', 'quantity' => 23, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 95, 'item' => 'Kusen Pintu Bayur', 'size' => '70x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 96, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '70x2m', 'quantity' => 4, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 97, 'item' => 'Kusen Pintu Mahoni', 'size' => '75x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 98, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '75x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 99, 'item' => 'Kusen Pintu Mahoni', 'size' => '70x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 100, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '70x2m', 'quantity' => 11, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 101, 'item' => 'Kusen Pintu Mahoni', 'size' => '110x2m (C) ', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 102, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '110x2m (C)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 103, 'item' => 'Kusen Pintu Mahoni', 'size' => '60x1,7m (C) ', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 104, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '60x1,7m (C)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 105, 'item' => 'Kusen Siku', 'size' => '45x1,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 106, 'item' => 'HPP Kusen Siku', 'size' => '45x1,5m', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 107, 'item' => 'Kusen Pintu Bayur', 'size' => '215x2,7m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 108, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '215x2,7m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 109, 'item' => 'Kusen Pintu Bayur', 'size' => '99x2,15m (c)', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 110, 'item' => 'HPP Kusen Pintu Mahoni', 'size' => '99x2,15m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 111, 'item' => 'Kusen Pintu Bayur', 'size' => '89x2,15m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 112, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '89x2,15m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 113, 'item' => 'Kusen Pintu Bayur', 'size' => '215x1,36m (c)', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 114, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '215x1,36m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 115, 'item' => 'Kusen Pintu Bayur', 'size' => '98x1,12m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 116, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '98x1,12m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 117, 'item' => 'Kusen Pintu Bayur', 'size' => '89x1,12m (c)', 'quantity' => 2, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 118, 'item' => 'HPP Kusen Pintu Bayur', 'size' => '89x1,12m (c)', 'quantity' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
