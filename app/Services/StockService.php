<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

        // Fetch all relevant items and sizes
        $stocks = DB::table('stocks')->select('item', 'size')->distinct()->get();
        $transferStocks = DB::table('transfer_stocks')->select('item', 'size')->distinct()->get();
        $usedStocks = DB::table('used_stocks')->select('item', 'size')->distinct()->get();

        $allStockRecords = $stocks->merge($transferStocks)->merge($usedStocks);
        $stockKeys = $allStockRecords->map(function ($stock) {
            return $stock->item . '|' . $stock->size;
        })->unique()->all();

        // Fetch all transactions with count of transactions per group
        $allTransactions = DB::table('transactions')
            ->select(
                'transactions.description as item',
                'transactions.size',
                DB::raw('COALESCE(SUM(transactions.nominal), 0) as total_nominal'),
                DB::raw('COUNT(*) as transaction_count') // Add count of transactions per group
            )
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(transactions.description, \'|\', transactions.size)'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->groupBy('transactions.description', 'transactions.size')
            ->get();

        Log::info('All Transactions Fetched', ['count' => $allTransactions->count(), 'sample' => $allTransactions->take(5)->toArray()]);

        $hppAverages = [];
        foreach ($allTransactions as $transaction) {
            $description = $transaction->item ?? 'Unknown';
            $size = $transaction->size ?? 'Unknown';
            $totalNominal = $transaction->total_nominal ?? 0;
            $transactionCount = $transaction->transaction_count ?? 1; // Use the count from the query

            $key = $description . '|' . $size;
            if (!isset($hppAverages[$key])) {
                $hppAverages[$key] = ['total_nominal' => 0, 'count' => 0];
            }
            $hppAverages[$key]['total_nominal'] += $totalNominal;
            $hppAverages[$key]['count'] += $transactionCount; // Add the count of transactions
        }

        foreach ($hppAverages as $key => $data) {
            $hppAverages[$key]['average_hpp'] = $data['count'] > 0 ? $data['total_nominal'] / $data['count'] : 0;
            Log::debug('Calculated HPP Average', ['key' => $key, 'total_nominal' => $data['total_nominal'], 'count' => $data['count'], 'average_hpp' => $hppAverages[$key]['average_hpp']]);
        }

        // Rest of the method remains unchanged...
        $openingBalances = DB::table('transactions as t1')
            ->select(
                't1.description as item',
                't1.size as size',
                DB::raw('COALESCE(SUM(CASE WHEN t1.quantity > 0 THEN t1.quantity ELSE 0 END), 0) as opening_qty'),
                DB::raw('COALESCE(SUM(t1.nominal), 0) as opening_hpp')
            )
            ->join('vouchers', 't1.voucher_id', '=', 'vouchers.id')
            ->where('t1.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(t1.description, \'|\', t1.size)'), $stockKeys)
            ->where('t1.created_at', '<', $startDate)
            ->groupBy('t1.description', 't1.size')
            ->get()
            ->keyBy(function ($item) {
                return $item->item . '|' . $item->size;
            });

        $stockData = DB::table('stocks')
            ->select(
                'stocks.id',
                'stocks.item',
                'stocks.size',
                'stocks.quantity',
                'transactions.created_at',
                'transactions.description',
                'transactions.quantity as transaction_quantity',
                'transactions.nominal',
                'vouchers.voucher_type'
            )
            ->leftJoin('transactions', function ($join) {
                $join->on('stocks.item', '=', 'transactions.description')
                    ->on('stocks.size', '=', 'transactions.size');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(stocks.item, \'|\', stocks.size)'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->item . '|' . $item->size;
            });

        $transferStockData = DB::table('transfer_stocks')
            ->select(
                'transfer_stocks.id',
                'transfer_stocks.item',
                'transfer_stocks.size',
                'transfer_stocks.quantity',
                'transactions.created_at',
                'transactions.description',
                'transactions.quantity as transaction_quantity',
                'transactions.nominal',
                'vouchers.voucher_type'
            )
            ->leftJoin('transactions', function ($join) {
                $join->on('transfer_stocks.item', '=', 'transactions.description')
                    ->on('transfer_stocks.size', '=', 'transactions.size');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(transfer_stocks.item, \'|\', transfer_stocks.size)'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->item . '|' . $item->size;
            });

        $usedStockData = DB::table('used_stocks')
            ->select(
                'used_stocks.id',
                'used_stocks.item',
                'used_stocks.size',
                'used_stocks.quantity',
                'transactions.created_at',
                'transactions.description',
                'transactions.quantity as transaction_quantity',
                'transactions.nominal',
                'vouchers.voucher_type'
            )
            ->leftJoin('transactions', function ($join) {
                $join->on('used_stocks.item', '=', 'transactions.description')
                    ->on('used_stocks.size', '=', 'transactions.size');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(used_stocks.item, \'|\', used_stocks.size)'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($item) {
                return $item->item . '|' . $item->size;
            });

        $stockMap = $this->processGroupedStockData($stockData, $openingBalances, $hppAverages, 'stocks');
        $transferStockMap = $this->processGroupedStockData($transferStockData, $openingBalances, $hppAverages, 'transfer_stocks');
        $usedStockMap = $this->processGroupedStockData($usedStockData, $openingBalances, $hppAverages, 'used_stocks');

        return [
            'stockData' => $stockMap,
            'transferStockData' => $transferStockMap,
            'usedStockData' => $usedStockMap,
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
    private function processGroupedStockData(Collection $stockData, Collection $openingBalances, array $hppAverages, string $tableName): array
    {
        $stockMap = [];
        foreach ($stockData as $key => $records) {
            $itemSize = explode('|', $key);
            $item = $itemSize[0];
            $size = $itemSize[1];

            if (!isset($stockMap[$item])) {
                $stockMap[$item] = [];
            }

            $stockKey = $item . '|' . $size;
            $openingBalance = $openingBalances->get($stockKey, (object) ['opening_qty' => 0, 'opening_hpp' => 0]);
            $hppEntry = $hppAverages[$stockKey] ?? ['average_hpp' => 0];

            // Log::debug('Processing Stock Entry', ['key' => $key, 'item' => $item, 'size' => $size, 'record_count' => $records->count()]);

            // Calculate specific HPP and quantities for each category
            $incomingTransactions = $records->where('voucher_type', 'PB');
            $outgoingTransactions = $records->whereIn('voucher_type', ['PJ', 'PK', 'PH']);

            // Log::debug('Transaction Breakdown', [
            //     'incoming_count' => $incomingTransactions->count(),
            //     'outgoing_count' => $outgoingTransactions->count(),
            //     'sample_incoming' => $incomingTransactions->take(2)->toArray(),
            //     'sample_outgoing' => $outgoingTransactions->take(2)->toArray(),
            // ]);

            $incomingQty = $incomingTransactions->sum(function ($transaction) {
                return $transaction->transaction_quantity > 0 ? $transaction->transaction_quantity : 0;
            }) ?? 0;
            $incomingNominal = $incomingTransactions->sum(function ($transaction) {
                return $transaction->nominal ?? 0;
            }) ?? 0;
            $outgoingQty = $outgoingTransactions->sum(function ($transaction) {
                return $transaction->transaction_quantity > 0 ? $transaction->transaction_quantity : 0;
            }) ?? 0;
            $outgoingNominal = $outgoingTransactions->sum(function ($transaction) {
                return $transaction->nominal ?? 0;
            }) ?? 0;

            // Log::debug('Calculated Quantities and Nominals', [
            //     'incoming_qty' => $incomingQty,
            //     'incoming_nominal' => $incomingNominal,
            //     'outgoing_qty' => $outgoingQty,
            //     'outgoing_nominal' => $outgoingNominal,
            // ]);

            // HPP calculated as total nominal without division by quantity
            $opening_hpp = $openingBalance->opening_hpp; // Total nominal from opening balances
            $incoming_hpp = $incomingNominal; // Total nominal for incoming transactions
            $outgoing_hpp = $outgoingNominal; // Total nominal for outgoing transactions
            // Final HPP can be a cumulative total or based on a specific logic (e.g., last nominal); here using total nominal approach
            $final_hpp = $opening_hpp + $incoming_hpp - $outgoing_hpp; // Simplified cumulative HPP

            // Log::info('Calculated HPP Values', [
            //     'opening_hpp' => $opening_hpp,
            //     'incoming_hpp' => $incoming_hpp,
            //     'outgoing_hpp' => $outgoing_hpp,
            //     'final_hpp' => $final_hpp,
            //     'fallback_average_hpp' => $hppEntry['average_hpp'],
            // ]);

            $entry = (object) [
                'id' => $records->first()->id ?? null,
                'item' => $item,
                'size' => $size,
                'quantity' => $records->sum('quantity') ?? 0,
                'opening_qty' => $openingBalance->opening_qty ?? 0,
                'opening_hpp' => $opening_hpp,
                'incoming_qty' => $incomingQty,
                'incoming_hpp' => $incoming_hpp,
                'outgoing_qty' => $outgoingQty,
                'outgoing_hpp' => $outgoing_hpp,
                'final_stock_qty' => 0,
                'final_hpp' => $final_hpp, // Added final HPP
                'average_hpp' => $hppEntry['average_hpp'],
                'transactions' => $records->map(function ($record) {
                    return (object) [
                        'description' => $record->description ?? 'No description',
                        'voucher_type' => $record->voucher_type ?? null,
                        'quantity' => $record->transaction_quantity ?? 0,
                        'nominal' => $record->nominal ?? 0,
                        'created_at' => $record->created_at ?? null,
                    ];
                })->values(),
                'table_name' => $tableName
            ];

            $entry->final_stock_qty = ($entry->opening_qty ?? 0) + ($entry->incoming_qty ?? 0) - ($entry->outgoing_qty ?? 0);
            $stockMap[$item][] = $entry;
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
            // Log::warning('Stock not found', ['stockId' => $stockId]);
            return collect([]);
        }

        $transactions = DB::table('transactions')
            ->where('description', $stock->item)
            ->where('size', $stock->size)
            ->where('description', 'NOT LIKE', 'HPP %')
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.created_at', '>=', $dateRange)
            ->select('transactions.description', 'transactions.size', 'vouchers.voucher_type', 'transactions.quantity', 'transactions.nominal', 'transactions.created_at')
            ->get();

        // Log::info('Transactions Fetched for Stock', ['stockId' => $stockId, 'count' => $transactions->count(), 'sample' => $transactions->take(5)->toArray()]);

        return $transactions->map(function ($transaction) {
            $transaction->created_at = Carbon::parse($transaction->created_at)->format('d-m-Y');
            return (object) $transaction;
        });
    }

    /**
     * Prepare data for transfer form PDF
     *
     * @param string $tableFilter
     * @return array
     */
    public function prepareTransferFormData(string $tableFilter): array
    {
        $transferStockData = DB::table('transfer_stocks')
            ->select('id', 'item', 'size', 'quantity')
            ->when($tableFilter !== 'all', function ($query) use ($tableFilter) {
                if ($tableFilter === 'transfer_stocks') {
                    return $query;
                }
                return $query->whereRaw('1 = 0'); // Return empty if filter is not 'all' or 'transfer_stocks'
            })
            ->get();

        // Log::info('Transfer Form Data Prepared', ['tableFilter' => $tableFilter, 'count' => $transferStockData->count()]);

        return [
            'transferStockData' => $transferStockData,
            'date' => Carbon::today()->format('d-m-Y'),
        ];
    }
}
