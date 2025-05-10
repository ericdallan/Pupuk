<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class StockExport implements FromCollection, WithHeadings, WithStyles
{
    protected $filterDate;

    public function __construct($filterDate)
    {
        $this->filterDate = Carbon::parse($filterDate)->startOfDay();
    }

    public function collection()
    {
        // Fetch stock data with transactions and voucher types
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'vouchers.voucher_type')
            ->distinct('stocks.item')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->get();

        // Group and calculate incoming, outgoing, and final stock for each item
        $stockMap = [];
        foreach ($stockData as $stock) {
            $stockKey = $stock->item;
            if (!isset($stockMap[$stockKey])) {
                $stockMap[$stockKey] = (object) [
                    'id' => $stock->id,
                    'item' => $stock->item,
                    'unit' => $stock->unit,
                    'quantity' => $stock->quantity,
                    'incoming' => 0,
                    'outgoing' => 0,
                    'final_stock' => 0,
                    'transactions' => collect()
                ];
            }

            // Incoming and outgoing stock on the filter date
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->isSameDay($this->filterDate)) {
                if ($stock->voucher_type === 'PB') {
                    $stockMap[$stockKey]->incoming += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing += $stock->transaction_quantity;
                }
            }

            // Collect transactions (though not used for export, keeping logic consistent)
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->gte(Carbon::now()->subDays(7))) {
                $stockMap[$stockKey]->transactions->push((object) [
                    'description' => $stock->description,
                    'voucher_type' => $stock->voucher_type,
                    'quantity' => $stock->transaction_quantity,
                    'created_at' => $stock->created_at
                ]);
            }
        }

        // Calculate final stock
        foreach ($stockMap as $stock) {
            $stock->final_stock = ($stock->incoming ?? 0) - ($stock->outgoing ?? 0);
        }

        // Prepare the collection for export, ensuring nulls are 0
        return collect(array_values($stockMap))->map(function ($stock, $index) {
            return [
                'No' => $index + 1,
                'Nama Barang' => $stock->item,
                'Satuan' => $stock->unit,
                'Stok Tersedia' => $stock->quantity,
                'Masuk Barang' => $stock->incoming ?? 0,
                'Keluar Barang' => $stock->outgoing ?? 0,
                'Akhir' => $stock->final_stock ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Satuan',
            'Stok Tersedia',
            'Masuk Barang',
            'Keluar Barang',
            'Akhir',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply styles to the entire sheet
        $sheet->getStyle('A1:G' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => 'Arial',
                'size' => 12,
            ],
        ]);

        // Bold and larger font for headers
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'CCCCCC'],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
