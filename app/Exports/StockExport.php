<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockExport implements FromCollection, WithHeadings
{
    protected $filterDate;

    public function __construct($filterDate)
    {
        $this->filterDate = $filterDate;
    }

    public function collection()
    {
        $filterDate = Carbon::parse($this->filterDate)->startOfDay();

        // Fetch average HPP for PB transactions
        $allTransactions = DB::table('transactions')
            ->select('transactions.description', 'transactions.nominal')
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->get();

        $hppAverages = [];
        foreach ($allTransactions as $transaction) {
            $item = $transaction->description;
            if (!isset($hppAverages[$item])) {
                $hppAverages[$item] = ['total_nominal' => 0, 'count' => 0];
            }
            $hppAverages[$item]['total_nominal'] += $transaction->nominal ?? 0;
            $hppAverages[$item]['count'] += 1;
        }

        foreach ($hppAverages as $item => $data) {
            $hppAverages[$item]['average_hpp'] = $data['count'] > 0 ? $data['total_nominal'] / $data['count'] : 0;
        }

        // Fetch opening balances (quantity and HPP from earliest transaction)
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
            ->get()
            ->keyBy('item');

        // Fetch stock data
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->distinct('stocks.item')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->get();

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
                    'opening_hpp' => $openingBalance ? ($openingBalance->opening_hpp ?? 0) : 0, // Nominal from earliest transaction
                    'incoming_qty' => 0,
                    'outgoing_qty' => 0,
                    'final_stock_qty' => 0,
                    'average_hpp' => $hppAverages[$stock->item]['average_hpp'] ?? 0, // PB-specific average HPP
                ];
            }

            // Incoming and outgoing stock on the filter date, excluding the earliest transaction
            if ($stock->voucher_type && Carbon::parse($stock->created_at)->isSameDay($filterDate)) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $isEarliestTransaction = $openingBalance && Carbon::parse($stock->created_at)->eq(Carbon::parse($openingBalance->created_at));

                if ($stock->voucher_type === 'PB' && !$isEarliestTransaction) {
                    $stockMap[$stockKey]->incoming_qty += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing_qty += $stock->transaction_quantity;
                }
            }
        }

        foreach ($stockMap as $stock) {
            $stock->final_stock_qty = ($stock->opening_qty ?? 0) + ($stock->incoming_qty ?? 0) - ($stock->outgoing_qty ?? 0);
        }

        return collect(array_values($stockMap))->map(function ($stock, $index) {
            return [
                'No' => $index + 1,
                'Nama Barang' => $stock->item,
                'Satuan' => $stock->unit,
                'Stok Tersedia Qty' => $stock->quantity,
                'Stok Tersedia HPP' => $stock->average_hpp,
                'Saldo Awal Qty' => $stock->opening_qty,
                'Saldo Awal HPP' => $stock->opening_hpp, // Nominal from earliest transaction
                'Masuk Barang Qty' => $stock->incoming_qty,
                'Masuk Barang HPP' => $stock->average_hpp,
                'Keluar Barang Qty' => $stock->outgoing_qty,
                'Keluar Barang HPP' => $stock->average_hpp,
                'Akhir Qty' => $stock->final_stock_qty,
                'Akhir HPP' => $stock->average_hpp,
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
}
