<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class TrialBalanceExport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $month;
    protected $year;
    protected $accountBalances;
    protected $accountNames;

    public function __construct(int $month, int $year, array $accountBalances, array $accountNames)
    {
        $this->month = $month;
        $this->year = $year;
        $this->accountBalances = $accountBalances;
        $this->accountNames = $accountNames;
    }

    /**
     * Mengembalikan koleksi data untuk ekspor ke Excel dengan logika pengelompokan kode akun.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        // Kelompokkan account balances berdasarkan logika pengelompokan
        $groupedBalances = collect($this->accountBalances)->groupBy(function ($balance, $accountCode) {
            // Cek apakah kode akun cocok dengan pola 2.1.01.01.X atau 1.1.03.01.X
            if (preg_match('/^2\.1\.01\.01\.\d+$/', $accountCode)) {
                return '2.1.01.01'; // Petakan ke Utang Usaha
            } elseif (preg_match('/^1\.1\.03\.01\.\d+$/', $accountCode)) {
                return '1.1.03.01'; // Petakan ke Piutang Usaha
            }

            // Jika tidak cocok dengan pola, gunakan kode aslinya
            return $accountCode;
        });

        // Hitung total saldo untuk setiap kode akun yang telah dikelompokkan
        $aggregatedBalances = $groupedBalances->map(function ($balances) {
            return $balances->sum();
        })->toArray();

        // Buat koleksi data untuk ekspor
        return collect($aggregatedBalances)->map(function ($balance, $accountCode) {
            return [
                'account_code' => $accountCode,
                'account_name' => $this->accountNames[$accountCode] ?? 'Tidak Ada Nama Akun',
                'total' => $balance,
            ];
        })->sortBy('account_code'); // Urutkan berdasarkan kode akun
    }

    /**
     * Menentukan header kolom.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            ['Trial Balance Report'],
            ['Periode: ' . $this->month . '/' . $this->year],
            [], // Baris kosong untuk pemisah
            ['Kode Akun', 'Nama Akun', 'Total (Rp)'],
        ];
    }

    /**
     * Menentukan lebar kolom.
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Kode Akun
            'B' => 50, // Nama Akun
            'C' => 25, // Total (Rp)
        ];
    }

    /**
     * Mengatur gaya untuk lembar kerja Excel.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Mengatur judul laporan
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');

        return [
            // Gaya untuk judul laporan
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6E6'],
                ],
            ],
            // Gaya untuk periode
            2 => [
                'font' => ['italic' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Gaya untuk header kolom
            4 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCCCCC'],
                ],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                ],
            ],
            // Gaya untuk kolom
            'A' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'B' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'C' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'numberFormat' => ['formatCode' => '#,##0.00'], // Format mata uang Rp
            ],
            // Gaya untuk semua data
            "A5:{$lastColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ],
        ];
    }
}
