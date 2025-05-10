<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class AccountSeeder extends Seeder
{
    public function run()
    {
        $hierarkiAkun = [
            'ASET' => [
                'Aset Lancar' => [
                    'Kas dan Setara Kas' => [
                        ['Kas Tunai', '1.1.01.01'],
                        ['Kas di Bank BSI', '1.1.01.02'],
                        ['Kas di Bank Mandiri', '1.1.01.03'],
                        ['Deposito <= 3', '1.1.01.04'],
                        ['Setara Kas Lainnya', '1.1.01.99'],
                    ],
                    'Piutang' => [
                        ['Piutang Usaha', '1.1.03.01'],
                        ['Piutang kepada Pegawai', '1.1.03.02'],
                        ['Piutang Lainnya', '1.1.03.99'],
                    ],
                    'Penyisihan Piutang' => [
                        ['Penyisihan Piutang Usaha Tak Tertagih', '1.1.04.01'],
                    ],
                    'Persediaan' => [
                        ['Persediaan Barang Dagangan', '1.1.05.01'],
                        ['Persediaan Bahan Baku', '1.1.05.02'],
                        ['Persediaan Barang Dalam Proses', '1.1.05.03'],
                        ['Persediaan Barang Jadi', '1.1.05.04'],
                    ],
                    'Pembayaran Dimuka' => [
                        ['Sewa Dibayar Dimuka', '1.1.07.01'],
                        ['Asuransi Dibayar Dimuka', '1.1.07.02'],
                        ['PPh 25', '1.1.07.03'],
                        ['PPN Masukan', '1.1.07.04'],
                    ],
                    'Aset Lancar Lainnya' => [
                        ['Aset Lancar Lainnya', '1.1.99.99'],
                    ],
                ],
                'Investasi' => [
                    'Investasi' => [
                        ['Deposito > 3 bulan', '1.2.01.01'],
                        ['Investasi Lainnya', '1.2.01.99'],
                    ],
                ],
                'Aset Tetap' => [
                    'Aset Tetap' => [
                        ['Tanah', '1.3.01.01'],
                        ['Kendaraan', '1.3.01.02'],
                        ['Peralatan dan Mesin', '1.3.01.03'],
                        ['Meubelair', '1.3.01.04'],
                        ['Gedung dan Bangunan', '1.3.01.05'],
                        ['Konstruksi Dalam Pengerjaan', '1.3.01.06'],
                    ],
                    'Akumulasi Penyusutan Aset Tetap' => [
                        ['Akumulasi Penyusutan Kendaraan', '1.3.02.01'],
                        ['Akumulasi Penyusutan Peralatan dan Mesin', '1.3.02.02'],
                        ['Akumulasi Penyusutan Meubelair', '1.3.02.03'],
                        ['Akumulasi Penyusutan Gedung dan Bangunan', '1.3.02.04'],
                    ],
                ],
            ],
            'KEWAJIBAN' => [
                'Kewajiban Jangka Pendek' => [
                    'Utang Usaha' => [
                        ['Utang Usaha', '2.1.01.01'],
                    ],
                    'Utang Pajak' => [
                        ['PPN Keluaran', '2.1.02.01'],
                        ['PPh 21', '2.1.02.02'],
                        ['PPh 23', '2.1.02.03'],
                        ['PPh 29', '2.1.02.04'],
                    ],
                    'Utang Gaji/Upah dan Tunjangan' => [
                        ['Utang Gaji/Upah dan Tunjangan', '2.1.03.01'],
                    ],
                    'Utang Utilitas' => [
                        ['Utang Listrik', '2.1.04.01'],
                        ['Utang Telepon/Internet', '2.1.04.02'],
                    ],
                    'Utang Jangka Pendek Lainnya' => [
                        ['Utang Jangka Pendek Lainnya', '2.1.99.99'],
                    ],
                ],
                'Kewajiban Jangka Panjang' => [
                    'Utang Ke Bank' => [
                        ['Utang Ke Bank', '2.2.01.01'],
                    ],
                    'Utang Jangka Panjang Lainnya' => [
                        ['Utang Jangka Panjang Lainnya', '2.2.99.99'],
                    ],
                ],
            ],
            'EKUITAS' => [
                'Modal Pemilik' => [
                    'Modal Pemilik' => [
                        ['Modal Pemilik', '3.1.00.00'],
                    ],
                ],
                'Pengambilan oleh Pemilik' => [
                    'Pengambilan oleh Pemilik' => [
                        ['Pengambilan oleh Pemilik', '3.2.00.00'],
                    ],
                ],
                'Saldo Laba' => [
                    'Saldo Laba' => [
                        ['Saldo Laba', '3.2.00.00'],
                    ],
                ],
                'Saldo Laba' => [
                    'Saldo Laba' => [
                        ['Saldo Laba Tidak Dicadangkan', '3.3.01.01'],
                    ],
                ],
                'Ikhtisar Laba Rugi' => [
                    'Ikhtisar Laba Rugi' => [
                        ['Ikhtisar Laba Rugi', '3.4.00.01'],
                    ],
                ],
                'Laba Rugi Ditahan' => [
                    'Laba Rugi Ditahan' => [
                        ['Laba Rugi Ditahan', '3.5.00.01'],
                    ],
                ],
            ],
            'PENDAPATAN_USAHA' => [
                'Pendapatan Penjualan Barang Dagangan' => [
                    'Harga Pokok Penjualan Barang Dagangan' => [
                        ['Pendapatan Penjualan Barang Dagangan', '4.1.01.01'],
                        ['Diskon Penjualan Barang Dagangan', '4.1.01.02'],
                    ],
                ],
                'Pendapatan Penjualan Barang Jadi' => [
                    'Pendapatan Penjualan Barang Jadi' =>[
                        ['Pendapatan Penjualan Barang Jadi', '4.2.01.01'],
                        ['Diskon Penjualan Barang Jadi', '4.2.01.02'],
                    ],
                ],
            ],
            'HARGA_POKOK_PRODUKSI_DAN_PENJUALAN' => [
                'Harga Pokok Pembelian Barang Dagangan' => [
                    'Harga Pokok Pembelian Barang Dagangan' => [
                        ['Harga Pokok Pembelian Barang Dagangan', '5.1.01.01'],
                    ],
                ],
                'Harga Pokok Pembelian Barang Jadi' => [
                    'Harga Pokok Pembelian Barang Jadi' => [
                        ['Harga Pokok Pembelian Barang Jadi', '5.2.01.01'],
                    ],
                ],
                'Harga Pokok Produksi' => [
                    'Biaya Bahan Baku' => [
                        ['Biaya Bahan Baku', '5.3.01.01'],
                    ],
                    'Beban Upah dan Tunjangan Bag. Produksi' => [
                        ['Beban Upah dan Tunjangan Bag. Produksi', '5.3.02.01'],
                    ],
                    'Beban Lembur, Insentif (Bonus) Bag. Produksi' => [
                        ['Beban Lembur, Insentif (Bonus) Bag. Produksi', '5.3.02.02'],
                    ],
                    'Biaya Overhead' => [
                        ['Beban Pemeliharaan dan Perbaikan Peralatan Kantor', '5.3.03.01'],
                        ['Beban Pemeliharaan dan Perbaikan Mesin', '5.3.03.02'],
                        ['Beban Perlengkapan Produksi', '5.3.03.03'],
                        ['Beban Listrik Pabrik', '5.3.03.04'],
                    ],
                ],
            ],
            'BEBAN_BEBAN_USAHA' => [
                'Beban Administrasi dan Umum' => [
                    'Beban Pegawai Bagian Administrasi Umum' => [
                        ['Beban Gaji dan Tunjangan Bag. Adum', '6.1.01.01'],
                        ['Beban Insentif (Bonus) Bag. Adum', '6.1.01.02'],
                        ['Beban Seragam Pegawai Bag. Adum', '6.1.01.03'],
                        ['Beban Pegawai Bag. Adum Lainnya', '6.1.01.99'],
                    ],
                    'Beban Perlengkapan' => [
                        ['Beban Alat Tulis Kantor (ATK)', '6.1.02.01'],
                        ['Beban Foto Copy dan Cetak', '6.1.02.02'],
                        ['Beban Konsumsi', '6.1.02.03'],
                        ['Beban Perlengkapan Lainnya', '6.1.02.99'],
                    ],
                    'Beban Pemeliharaan dan Perbaikan Peralatan Kantor' => [
                        ['Beban Pemeliharaan dan Perbaikan Peralatan Kantor', '6.1.03.01'],
                    ],
                    'Beban Utilitas' => [
                        ['Beban Listrik Kantor', '6.1.04.01'],
                        ['Beban Telepon/Internet', '6.1.04.02'],
                        ['Beban Utilitas Lainnya', '6.1.04.99'],
                    ],
                    'Beban Sewa dan Asuransi' => [
                        ['Beban Sewa', '6.1.05.01'],
                        ['Beban Asuransi', '6.1.05.02'],
                    ],
                    'Beban Kebersihan dan Keamanan' => [
                        ['Beban Kebersihan', '6.1.06.01'],
                        ['Beban Keamanan', '6.1.06.02'],
                    ],
                    'Beban Penyisihan dan Penyusutan/Amortisasi' => [
                        ['Beban Penyisihan Piutang Tak Tertagih', '6.1.07.01'],
                        ['Beban Penyusutan Kendaraan', '6.1.07.02'],
                        ['Beban Penyusutan Peralatan dan Mesin', '6.1.07.03'],
                        ['Beban Penyusutan Meubelair', '6.1.07.04'],
                        ['Beban Penyusutan Gedung dan Bangunan', '6.1.07.05'],
                        ['Beban Amortisasi Aset tak berwujud', '6.1.07.06'],
                    ],
                    'Beban Administrasi dan Umum Lainnya' => [
                        ['Beban BBM, Parkir, Toll', '6.1.99.01'],
                        ['Beban Audit', '6.1.99.02'],
                        ['Beban Perjalanan Dinas', '6.1.99.03'],
                        ['Beban Transportasi', '6.1.99.04'],
                        ['Beban Jamuan Tamu', '6.1.99.05'],
                        ['Beban Administrasi dan Umum Lainnya', '6.1.99.99'],
                    ],
                ],
                'Beban Operasional' => [
                    'Beban Pegawai Bagian Operasional' => [
                        ['Beban Gaji/Upah Bag. Operasional', '6.2.01.01'],
                        ['Beban Uang Makan Bag. Operasional', '6.2.01.02'],
                    ],
                    'Beban Pemeliharaan dan Perbaikan' => [
                        ['Beban Perbaikan dan Renovasi', '6.2.02.02'],
                    ],
                    'Beban Operasional Lainnya' => [
                        ['Beban Operasional Lainnya', '6.2.99.99'],
                    ],
                ],
                'Beban Pemasaran' => [
                    'Beban Pegawai Bagian Pemasaran' => [
                        ['Beban Gaji/Upah Bag. Pemasaran', '6.3.01.01'],
                        ['Beban Insentif (Bonus) Bag. Pemasaran', '6.3.01.02'],
                        ['Beban Seragam Pegawai Bag. Pemasaran', '6.3.01.03'],
                    ],
                    'Beban Pemasaran Lainnya' => [
                        ['Beban Pemasaran Lainnya', '6.3.99.99'],
                    ],
                ],
            ],
            'PENDAPATAN_DAN_BEBAN_LAIN_LAIN' => [
                'Pendapatan Lain-lain' => [
                    'Pendapatan dari Bank' => [
                        ['Pendapatan Bunga Bank', '7.1.01.01'],
                    ],
                    'Pendapatan Penjualan Aset Tetap' => [
                        ['Keuntungan Penjualan Aset Tetap', '7.1.02.01'],
                    ],
                    'Pendapatan Lain-lain lainnya' => [
                        ['Pendapatan Lain-lain lainnya', '7.1.99.99'],
                    ],
                ],
                'Beban Lain-lain' => [
                    'Beban Bank' => [
                        ['Beban Administrasi Bank', '7.2.01.01'],
                        ['Beban Bunga', '7.2.01.02'],
                    ],
                    'Beban Bunga' => [
                        ['Beban Bunga', '7.2.02.01'],
                    ],
                    'Beban Penjualan Aset Tetap' => [
                        ['Kerugian Penjualan Aset Tetap', '7.2.03.01'],
                    ],
                    'Beban Lain-lain lainnya' => [
                        ['Beban Lain-lain lainnya', '7.2.99.99'],
                    ],
                ],
                'Beban Pajak' => [
                    'Beban Pajak' => [
                        ['Beban PPh 21', '7.3.01.01'],
                        ['Beban PPh 23', '7.3.01.02'],
                        ['Beban PPh 25', '7.3.01.03'],
                        ['Beban PPh 29', '7.3.01.04'],
                        ['Beban PPh Final', '7.3.01.05'],
                        ['Beban Pajak Lainnya', '7.3.01.99'],
                    ],
                ],
            ],
        ];

        foreach ($hierarkiAkun as $accountType => $accountSections) {
            foreach ($accountSections as $accountSection => $accountSubsections) {
                $this->createAccountCodes($accountType, $accountSection, $accountSubsections);
            }
        }
    }

    private function createAccountCodes($accountType, $accountSection, $accountSubsections, $parentSubsection = null)
    {
        if (is_array($accountSubsections) && !empty($accountSubsections)) {
            foreach ($accountSubsections as $key => $value) {
                if (is_array($value) && is_numeric($key)) {
                    ChartOfAccount::create([
                        'account_type' => $accountType,
                        'account_section' => $accountSection,
                        'account_subsection' => $parentSubsection,
                        'account_name' => $value[0],
                        'account_code' => $value[1],
                    ]);
                } elseif (is_array($value)) {
                    $this->createAccountCodes($accountType, $accountSection, $value, $key);
                } else {
                    ChartOfAccount::create([
                        'account_type' => $accountType,
                        'account_section' => $accountSection,
                        'account_subsection' => $parentSubsection,
                        'account_name' => $value,
                    ]);
                }
            }
        } else {
            ChartOfAccount::create([
                'account_type' => $accountType,
                'account_section' => $accountSection,
                'account_name' => $accountSubsections,
            ]);
        }
    }
}
