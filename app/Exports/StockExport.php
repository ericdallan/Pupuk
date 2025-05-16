<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Http\Request;

class StockExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        // Fetch all transactions with voucher type PB to calculate average HPP per stock item
        $allTransactions = DB::table('transactions')
            ->select('transactions.description', 'transactions.nominal')
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get();

        // Calculate average HPP for each stock item based on PB transactions only
        $hppAverages = [];
        foreach ($allTransactions as $transaction) {
            $item = $transaction->description;
            if (!isset($hppAverages[$item])) {
                $hppAverages[$item] = ['total_nominal' => 0, 'count' => 0];
            }
            $hppAverages[$item]['total_nominal'] += $transaction->nominal ?? 0;
            $hppAverages[$item]['count'] += 1;
        }

        // Compute the average HPP
        foreach ($hppAverages as $item => $data) {
            $hppAverages[$item]['average_hpp'] = $data['count'] > 0 ? $data['total_nominal'] / $data['count'] : 0;
        }

        // Fetch the earliest transaction for each stock item to determine opening balance
        $openingBalances = DB::table('transactions')
            ->select('t1.description as item', 't1.quantity as opening_qty', 't1.nominal as opening_hpp', 't1.created_at')
            ->from('transactions as t1')
            ->join('vouchers', 't1.voucher_id', '=', 'vouchers.id')
            ->join(DB::raw('(
                SELECT description, MIN(created_at) as min_created_at
                FROM transactions
                WHERE description NOT LIKE "HPP %"
                GROUP BY description
            ) as t2'), function ($join) {
                $join->on('t1.description', '=', 't2.description')
                    ->whereColumn('t1.created_at', 't2.min_created_at');
            })
            ->where('t1.description', 'NOT LIKE', 'HPP %')
            ->where('t1.created_at', '<=', $endDate)
            ->get()
            ->keyBy('item');

        // Fetch stock data with transactions and voucher types for the specified date range
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->distinct('stocks.item')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get();

        // Group and calculate incoming, outgoing, opening, and final stock for each item
        $stockMap = [];
        foreach ($stockData as $stock) {
            $stockKey = $stock->item;
            if (!isset($stockMap[$stockKey])) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $stockMap[$stockKey] = (object) [
                    'id' => $stock->id,
                    'item' => $stock->item,
                    'unit' => $stock->unit,
                    'quantity' => $stock->quantity,
                    'opening_qty' => $openingBalance ? $openingBalance->opening_qty : 0,
                    'opening_hpp' => $openingBalance ? ($openingBalance->opening_hpp ?? 0) : 0,
                    'incoming_qty' => 0,
                    'outgoing_qty' => 0,
                    'final_stock_qty' => 0,
                    'average_hpp' => $hppAverages[$stock->item]['average_hpp'] ?? 0,
                    'transactions' => collect()
                ];
            }

            // Incoming and outgoing stock within the date range, excluding the earliest transaction
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->between($startDate, $endDate)) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $isEarliestTransaction = $openingBalance && Carbon::parse($stock->created_at)->eq(Carbon::parse($openingBalance->created_at));

                if ($stock->voucher_type === 'PB' && !$isEarliestTransaction) {
                    $stockMap[$stockKey]->incoming_qty += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing_qty += $stock->transaction_quantity;
                }
            }
        }

        // Calculate final stock
        foreach ($stockMap as $stock) {
            $stock->final_stock_qty = ($stock->opening_qty ?? 0) + ($stock->incoming_qty ?? 0) - ($stock->outgoing_qty ?? 0);
        }

        return collect(array_values($stockMap))->map(function ($stock, $index) {
            return [
                'No' => $index + 1,
                'Nama Barang' => $stock->item,
                'Satuan' => $stock->unit,
                'Stok Tersedia Qty' => $stock->quantity,
                'Stok Tersedia HPP' => $stock->average_hpp ?? 0, // Ensure 0 if null
                'Saldo Awal Qty' => $stock->opening_qty ?? 0,     // Ensure 0 if null
                'Saldo Awal HPP' => $stock->opening_hpp ?? 0,     // Ensure 0 if null
                'Masuk Barang Qty' => $stock->incoming_qty ?? 0,
                'Masuk Barang HPP' => $stock->average_hpp ?? 0, // Ensure 0 if null
                'Keluar Barang Qty' => $stock->outgoing_qty ?? 0,
                'Keluar Barang HPP' => $stock->average_hpp ?? 0, // Ensure 0 if null
                'Akhir Qty' => $stock->final_stock_qty ?? 0,
                'Akhir HPP' => $stock->average_hpp ?? 0, // Ensure 0 if null
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Satuan',
            'Stok Tersedia Qty',
            'Stok Tersedia HPP',
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
                'horizontal' => Alignment::HORIZONTAL_CENTER, // Center align the content
            ],
        ]);
        $sheet->getRowDimension('1')->setRowHeight(20);

        // Add title with date range
        $title = 'Laporan Stock Tanggal ' . Carbon::parse($this->startDate)->format('d-m-Y') . ' s/d ' . Carbon::parse($this->endDate)->format('d-m-Y');
        $sheet->insertNewRowBefore(1);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:M1'); // Merge all columns for the title
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
        $sheet->getStyle('A2:M2')->applyFromArray([ // Apply style to the header row
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
