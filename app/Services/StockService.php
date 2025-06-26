<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Imports\StockImport;
use Maatwebsite\Excel\Facades\Excel;
class StockService
{
    /**
     * Prepare data for the Stock page
     *
     * @param array $data
     * @return array
     */
    public function prepareStockData(array $data): array
    {
        $startDate = $data['start_date'] ?? Carbon::today()->startOfYear()->toDateString();
        $endDate = $data['end_date'] ?? Carbon::today()->toDateString();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->isFuture()) {
            $endDate = Carbon::today()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfYear();
        }

        $allTransactions = DB::table('transactions')
            ->select('transactions.description', 'transactions.nominal')
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
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

        // Data untuk tabel stocks
        $stockData = DB::table('stocks')
            ->select('stocks.id', 'stocks.item', 'stocks.unit', 'stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->leftJoin('transactions', 'stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get();

        // Data untuk tabel transfer_stocks
        $transferStockData = DB::table('transfer_stocks')
            ->select('transfer_stocks.id', 'transfer_stocks.item', 'transfer_stocks.unit', 'transfer_stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->leftJoin('transactions', 'transfer_stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get();

        // Data untuk tabel used_stocks
        $usedStockData = DB::table('used_stocks')
            ->select('used_stocks.id', 'used_stocks.item', 'used_stocks.unit', 'used_stocks.quantity', 'transactions.created_at', 'transactions.description', 'transactions.quantity as transaction_quantity', 'transactions.nominal', 'vouchers.voucher_type')
            ->leftJoin('transactions', 'used_stocks.item', '=', 'transactions.description')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get();

        $stockMap = $this->processStockData($stockData, $openingBalances, $hppAverages, 'stocks');
        $transferStockMap = $this->processStockData($transferStockData, $openingBalances, $hppAverages, 'transfer_stocks');
        $usedStockMap = $this->processStockData($usedStockData, $openingBalances, $hppAverages, 'used_stocks');

        return [
            'stockData' => array_values($stockMap),
            'transferStockData' => array_values($transferStockMap),
            'usedStockData' => array_values($usedStockMap),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Process stock data for a specific table
     *
     * @param Collection $stockData
     * @param Collection $openingBalances
     * @param array $hppAverages
     * @param string $tableName
     * @return array
     */
    private function processStockData(Collection $stockData, Collection $openingBalances, array $hppAverages, string $tableName): array
    {
        $startDate = $data['start_date'] ?? Carbon::today()->startOfYear()->toDateString();
        $endDate = $data['end_date'] ?? Carbon::today()->toDateString();

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
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
                    'transactions' => collect(),
                    'table_name' => $tableName
                ];
            }

            if ($stock->voucher_type && Carbon::parse($stock->created_at)->between($startDate, $endDate)) {
                $openingBalance = $openingBalances[$stockKey] ?? null;
                $isEarliestTransaction = $openingBalance && Carbon::parse($stock->created_at)->eq(Carbon::parse($openingBalance->created_at));

                if ($stock->voucher_type === 'PB' && !$isEarliestTransaction) {
                    $stockMap[$stockKey]->incoming_qty += $stock->transaction_quantity;
                } elseif ($stock->voucher_type === 'PJ') {
                    $stockMap[$stockKey]->outgoing_qty += $stock->transaction_quantity;
                }
            }

            if ($stock->voucher_type && Carbon::parse($stock->created_at)->between($startDate, $endDate)) {
                $stockMap[$stockKey]->transactions->push((object) [
                    'description' => $stock->description,
                    'voucher_type' => $stock->voucher_type,
                    'quantity' => $stock->transaction_quantity,
                    'nominal' => $stock->nominal,
                    'created_at' => $stock->created_at
                ]);
            }
        }

        foreach ($stockMap as $stock) {
            $stock->final_stock_qty = ($stock->opening_qty ?? 0) + ($stock->incoming_qty ?? 0) - ($stock->outgoing_qty ?? 0);
        }

        return $stockMap;
    }

    /**
     * Prepare data for stock export
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function prepareExportData(string $startDate, string $endDate): array
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->isFuture()) {
            $endDate = Carbon::today()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfYear();
        }

        $data = $this->prepareStockData(['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]);
        return [
            'stockData' => $data['stockData'],
            'transferStockData' => $data['transferStockData'],
            'usedStockData' => $data['usedStockData'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
        ];
    }

    /**
     * Fetch transactions for a specific stock item
     *
     * @param int $stockId
     * @param string $filter
     * @return Collection
     */
    public function getTransactions(int $stockId, string $filter): Collection
    {
        $dateRange = $filter === '7_days' ? Carbon::now()->subDays(7) : Carbon::now()->subMonth();

        $stock = DB::table('stocks')->where('id', $stockId)->first();
        if (!$stock) {
            return collect([]);
        }

        return DB::table('transactions')
            ->where('description', $stock->item)
            ->where('description', 'NOT LIKE', 'HPP %')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.created_at', '>=', $dateRange)
            ->select('transactions.description', 'vouchers.voucher_type', 'transactions.quantity', 'transactions.nominal', 'transactions.created_at')
            ->get()
            ->map(function ($transaction) {
                $transaction->created_at = Carbon::parse($transaction->created_at)->format('d-m-Y');
                return (object) $transaction;
            });
    }
    /**
     * Import stock data from Excel file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    public function importStockData($file)
    {
        Excel::import(new StockImport(), $file);
    }
}