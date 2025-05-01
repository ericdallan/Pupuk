<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IncomeStatementExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $year;
    protected $month;

    public function __construct(Collection $data, $year, $month)
    {
        $this->data = $data;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            $row['Jumlah (Rp)'] = $row['Jumlah (Rp)'] === null ? 0 : $row['Jumlah (Rp)'];
            return $row;
        });
    }

    public function headings(): array
    {
        $period = $this->month
            ? date('F', mktime(0, 0, 0, $this->month, 10)) . ' ' . $this->year
            : 'Tahun ' . $this->year;

        return [
            ['Laporan Laba Rugi'],
            ['Periode: ' . $period],
            [],
            ['Keterangan', 'Jumlah (Rp)'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = ($this->data->isEmpty() ? 1 : $this->data->count()) + 4; // +4 karena ada 4 baris heading
        $lastColumn = 'B';

        $styleArray = [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['italic' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                ],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'B' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'numberFormat' => ['formatCode' => '_(* #,##0.00_);_(* (#,##0.00);_(* 0.00_);_(@_)'],
            ],
            'A4:' . $lastColumn . $lastRow => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                ],
            ],
        ];

        if (!$this->data->isEmpty()) {
            $boldRows = [
                'Laba Kotor',
                'Laba Operasi',
                'Laba Sebelum Pajak',
                'Laba Bersih',
                'Beban Operasional',
                'Pendapatan Lain-lain',
                'Beban Lain-lain',
            ];

            foreach ($boldRows as $keterangan) {
                $rowNumber = $this->findRow($keterangan);
                if ($rowNumber !== null) {
                    $styleArray['A' . $rowNumber]['font'] = ['bold' => true];
                    $styleArray['A' . $rowNumber]['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE0']];
                    $styleArray['B' . $rowNumber]['font'] = ['bold' => true];
                }
            }

            $italicRows = [
                'Harga Pokok Penjualan',
                'Pajak Penghasilan',
                'Total Beban Operasional',
            ];

            foreach ($italicRows as $keterangan) {
                $rowNumber = $this->findRow($keterangan);
                if ($rowNumber !== null) {
                    $styleArray['A' . $rowNumber]['font'] = ['italic' => true];
                    $styleArray['B' . $rowNumber]['font'] = ['italic' => true];
                }
            }

            $labaBersihRow = $this->findRow('Laba Bersih');
            if ($labaBersihRow !== null) {
                $styleArray['A' . $labaBersihRow]['font'] = ['bold' => true, 'size' => 14];
                $styleArray['A' . $labaBersihRow]['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'ADD8E6']];
                $styleArray['B' . $labaBersihRow]['font'] = ['bold' => true, 'size' => 14];
                $styleArray['B' . $labaBersihRow]['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'ADD8E6']];
            }

            $totalBebanOperasionalRow = $this->findRow('Total Beban Operasional');
            if ($totalBebanOperasionalRow !== null) {
                $styleArray['A' . $totalBebanOperasionalRow]['alignment'] = ['horizontal' => Alignment::HORIZONTAL_LEFT, 'indent' => 2];
            }
        }

        return $styleArray;
    }

    private function findRow(string $keterangan): ?int
    {
        foreach ($this->data as $index => $row) {
            if (isset($row['Keterangan']) && trim($row['Keterangan']) === $keterangan) {
                return $index + 5; // +5 karena data mulai dari baris 5 (1-based indexing)
            }
        }
        return null;
    }
}
