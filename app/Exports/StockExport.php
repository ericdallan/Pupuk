<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $stockData;
    protected $transferStockData;
    protected $usedStockData;

    public function __construct($startDate, $endDate, array $data)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->stockData = collect($data['stockData'] ?? []);
        $this->transferStockData = collect($data['transferStockData'] ?? []);
        $this->usedStockData = collect($data['usedStockData'] ?? []);
    }

    public function collection()
    {
        $allStockData = collect();
        $index = 1;

        // Helper function to map stock data
        $mapStockData = function ($stock, $index) {
            return [
                'No' => $index,
                'Nama Barang' => $stock->item,
                'Satuan' => $stock->size ?? '',
                'Saldo Awal Qty' => $stock->opening_qty ?? 0,
                'Saldo Awal HPP' => 'Rp. ' . number_format(round($stock->opening_hpp ?? 0, 2), 2, ',', '.'),
                'Masuk Barang Qty' => $stock->incoming_qty ?? 0,
                'Masuk Barang HPP' => 'Rp. ' . number_format(round($stock->average_pb_hpp ?? 0, 2), 2, ',', '.'),
                'Keluar Barang Qty' => $stock->outgoing_qty ?? 0,
                'Keluar Barang HPP' => 'Rp. ' . number_format(round($stock->outgoing_hpp ?? 0, 2), 2, ',', '.'),
                'Akhir Qty' => $stock->final_stock_qty ?? 0,
                'Akhir HPP' => 'Rp. ' . number_format(round($stock->final_hpp ?? 0, 2), 2, ',', '.'),
            ];
        };

        // Define category-specific headers
        $bahanBakuHeaders = [
            'No' => 'No',
            'Nama Barang' => 'Nama Bahan Baku',
            'Satuan' => 'Satuan',
            'Saldo Awal Qty' => 'Stok Awal Qty',
            'Saldo Awal HPP' => 'Stok Awal HPP',
            'Masuk Barang Qty' => 'Pembelian Qty',
            'Masuk Barang HPP' => 'Pembelian HPP',
            'Keluar Barang Qty' => 'Pengeluaran Qty',
            'Keluar Barang HPP' => 'Pengeluaran HPP',
            'Akhir Qty' => 'Stok Akhir Qty',
            'Akhir HPP' => 'Stok Akhir HPP',
        ];

        $barangPemindahanHeaders = [
            'No' => 'No',
            'Nama Barang' => 'Nama Barang Pemindahan',
            'Satuan' => 'Satuan',
            'Saldo Awal Qty' => 'Stok Awal Qty',
            'Saldo Awal HPP' => 'Stok Awal HPP',
            'Masuk Barang Qty' => 'Pemindahan Masuk Qty',
            'Masuk Barang HPP' => 'Pemindahan Masuk HPP',
            'Keluar Barang Qty' => 'Pemindahan Keluar Qty',
            'Keluar Barang HPP' => 'Pemindahan Keluar HPP',
            'Akhir Qty' => 'Stok Akhir Qty',
            'Akhir HPP' => 'Stok Akhir HPP',
        ];

        $barangJadiHeaders = [
            'No' => 'No',
            'Nama Barang' => 'Nama Barang Jadi',
            'Satuan' => 'Satuan',
            'Saldo Awal Qty' => 'Stok Awal Qty',
            'Saldo Awal HPP' => 'Stok Awal HPP',
            'Masuk Barang Qty' => 'Produksi Qty',
            'Masuk Barang HPP' => 'Produksi HPP',
            'Keluar Barang Qty' => 'Penjualan Qty',
            'Keluar Barang HPP' => 'Penjualan HPP',
            'Akhir Qty' => 'Stok Akhir Qty',
            'Akhir HPP' => 'Stok Akhir HPP',
        ];

        // Add spacer row
        $allStockData->push(['No' => '', 'Nama Barang' => '', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);

        // Add Bahan Baku (stocks)
        if ($this->stockData->isNotEmpty()) {
            $allStockData->push(['No' => '', 'Nama Barang' => 'Bahan Baku', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);
            $allStockData->push($bahanBakuHeaders);
            foreach ($this->stockData as $item => $entries) {
                foreach ($entries as $stock) {
                    $allStockData->push($mapStockData($stock, $index++));
                }
            }
            $allStockData->push(['No' => '', 'Nama Barang' => '', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);
        }

        // Add Barang Pemindahan (transfer_stocks)
        if ($this->transferStockData->isNotEmpty()) {
            $allStockData->push(['No' => '', 'Nama Barang' => 'Barang Pemindahan', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);
            $allStockData->push($barangPemindahanHeaders);
            foreach ($this->transferStockData as $item => $entries) {
                foreach ($entries as $stock) { // Fixed: Changed $goods to $entries
                    $allStockData->push($mapStockData($stock, $index++));
                }
            }
            $allStockData->push(['No' => '', 'Nama Barang' => '', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);
        }

        // Add Barang Jadi (used_stocks)
        if ($this->usedStockData->isNotEmpty()) {
            $allStockData->push(['No' => '', 'Nama Barang' => 'Barang Jadi', 'Satuan' => '', 'Saldo Awal Qty' => '', 'Saldo Awal HPP' => '', 'Masuk Barang Qty' => '', 'Masuk Barang HPP' => '', 'Keluar Barang Qty' => '', 'Keluar Barang HPP' => '', 'Akhir Qty' => '', 'Akhir HPP' => '']);
            $allStockData->push($barangJadiHeaders);
            foreach ($this->usedStockData as $item => $entries) {
                foreach ($entries as $stock) {
                    $allStockData->push($mapStockData($stock, $index++));
                }
            }
        }

        return $allStockData;
    }

    public function headings(): array
    {
        // Generic header as placeholder
        return [
            'No',
            'Nama Barang',
            'Satuan',
            'Saldo Awal Qty',
            'Saldo Awal HPP',
            'Masuk Barang Qty',
            'Masuk Barang HPP',
            'Keluar Barang Qty',
            'Keluar Barang HPP',
            'Akhir Qty',
            'Akhir HPP',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Title with date range
        $title = 'Laporan Stock Tanggal ' . Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y');
        $sheet->insertNewRowBefore(1);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Header styling (placeholder, less prominent)
        $sheet->getStyle('A2:K2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '696969'], // Dim gray
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        $sheet->getRowDimension('2')->setRowHeight(25);

        // Styling for category headers, category-specific headers, and data rows
        $rowCount = $sheet->getHighestRow();
        $categoryRows = [];
        $categoryHeaderRows = [];
        $dataRows = [];
        $currentRow = 3;
        foreach ($this->collection() as $row) {
            if (empty($row['No']) && !empty($row['Nama Barang']) && in_array($row['Nama Barang'], ['Bahan Baku', 'Barang Pemindahan', 'Barang Jadi'])) {
                $categoryRows[] = $currentRow;
            } elseif (!empty($row['No']) && in_array($row['No'], ['No'])) {
                $categoryHeaderRows[] = $currentRow;
            } elseif (!empty($row['No']) && is_numeric($row['No'])) {
                $dataRows[] = $currentRow;
            }
            $currentRow++;
        }

        // Style category headers (Bahan Baku, Barang Pemindahan, Barang Jadi)
        foreach ($categoryRows as $row) {
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Calibri',
                    'color' => ['rgb' => '000000'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6FA'], // Light purple
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'indent' => 1,
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(25);
        }

        // Style category-specific headers
        foreach ($categoryHeaderRows as $row) {
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Calibri',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '008000'], // Green
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(25);
        }

        // Style data rows with right alignment for numeric columns
        foreach ($dataRows as $row) {
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Nama Barang
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Satuan
            $sheet->getStyle('D' . $row . ':K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Qty and HPP
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'font' => [
                    'name' => 'Calibri',
                    'size' => 11,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // Style spacer rows
        $spacerRows = array_diff(range(3, $rowCount), array_merge($categoryRows, $categoryHeaderRows, $dataRows));
        foreach ($spacerRows as $row) {
            $sheet->getRowDimension($row)->setRowHeight(10);
        }
    }

    public function title(): string
    {
        return 'Stock Report';
    }
}
