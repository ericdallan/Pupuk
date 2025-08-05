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
        // Merge stock data, transfer stock data, and used stock data
        $allStockData = collect();
        foreach ([$this->stockData, $this->transferStockData, $this->usedStockData] as $data) {
            foreach ($data as $item => $entries) {
                foreach ($entries as $stock) {
                    $allStockData->push($stock);
                }
            }
        }

        // Remove duplicates by item and size, keeping the most relevant entry
        $allStockData = $allStockData->unique(function ($stock) {
            return $stock->item . '|' . ($stock->size ?? '');
        })->map(function ($stock, $index) {
            return [
                'No' => $index + 1,
                'Nama Barang' => $stock->item,
                'Satuan' => $stock->size ?? '', // Using 'size' instead of 'unit'
                'Saldo Awal Qty' => $stock->opening_qty ?? 0,
                'Saldo Awal HPP' => $stock->opening_hpp ?? 0,
                'Masuk Barang Qty' => $stock->incoming_qty ?? 0,
                'Masuk Barang HPP' => $stock->average_pb_hpp ?? 0,
                'Keluar Barang Qty' => $stock->outgoing_qty ?? 0,
                'Keluar Barang HPP' => $stock->outgoing_hpp ?? 0,
                'Akhir Qty' => $stock->final_stock_qty ?? 0,
                'Akhir HPP' => $stock->final_hpp ?? 0,
            ];
        });

        return $allStockData;
    }

    public function headings(): array
    {
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
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '008000'], // Green
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:M1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A2:M' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(20);

        // Add title with date range
        $title = 'Laporan Stock Tanggal ' . Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y');
        $sheet->insertNewRowBefore(1);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(25);
        $sheet->getRowDimension('2')->setRowHeight(20);
        $sheet->getStyle('A2:M2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '008000'], // Green
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A2:M2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    public function title(): string
    {
        return 'Stock Report';
    }
}
