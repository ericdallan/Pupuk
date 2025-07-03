<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Imports\StockImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UsedStock;
use App\Models\Recipes;
use App\Models\TransferStock;

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
        $tableFilter = $data['table_filter'] ?? 'all';

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->isFuture()) {
            $endDate = Carbon::now()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $startDate = $endDate->copy()->startOfYear();
        }

        // Fetch all relevant items and sizes from transactions
        $allStockRecords = DB::table('transactions')
            ->select('description as item', 'size')
            ->where('description', 'NOT LIKE', 'HPP %')
            ->distinct()
            ->get();

        $stockKeys = $allStockRecords->map(function ($stock) {
            return $stock->item . '|' . ($stock->size ?? '');
        })->unique()->all();

        // Fetch opening balances
        $openingBalances = $this->getOpeningBalances($stockKeys, $startDate);

        // Fetch HPP averages
        $hppAverages = $this->getHppAverages($stockKeys, $startDate, $endDate);

        // Fetch stock transactions
        $stockData = $tableFilter === 'all' || $tableFilter === 'stocks' ? $this->getStockTransactions('stocks', $stockKeys, $startDate, $endDate) : collect([]);
        $transferStockData = $tableFilter === 'all' || $tableFilter === 'transfer_stocks' ? $this->getStockTransactions('transfer_stocks', $stockKeys, $startDate, $endDate) : collect([]);
        $usedStockData = $tableFilter === 'all' || $tableFilter === 'used_stocks' ? $this->getStockTransactions('used_stocks', $stockKeys, $startDate, $endDate) : collect([]);

        // Process grouped stock data
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
     * Fetch transactions for a specific table
     */
    private function getStockTransactions(string $tableName, array $stockKeys, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = DB::table($tableName)
            ->select(
                "$tableName.id as stock_id", // Use alias to avoid ambiguity
                "$tableName.item",
                "$tableName.size",
                'transactions.created_at',
                'transactions.description',
                'transactions.quantity as transaction_quantity',
                'transactions.nominal',
                'vouchers.voucher_type'
            )
            ->leftJoin('transactions', function ($join) use ($tableName) {
                $join->on("$tableName.item", '=', 'transactions.description')
                    ->on("$tableName.size", '=', 'transactions.size');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw("CONCAT($tableName.item, '|', COALESCE($tableName.size, ''))"), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $results = $query->get();
        Log::debug("Stock Transactions for $tableName:", $results->toArray());

        return $results->groupBy(function ($item) {
            return $item->item . '|' . ($item->size ?? '');
        });
    }

    /**
     * Process grouped stock data
     */
    private function processGroupedStockData(Collection $stockData, Collection $openingBalances, array $hppAverages, string $tableName): array
    {
        $stockMap = [];

        foreach ($stockData as $key => $records) {
            $itemSize = explode('|', $key);
            $item = $itemSize[0];
            $size = $itemSize[1] ?? '';

            if (!isset($stockMap[$item])) {
                $stockMap[$item] = [];
            }

            $stockKey = $item . '|' . $size;

            // Fetch the stock record to get the correct ID
            $stockRecord = DB::table($tableName)
                ->select('id')
                ->where('item', $item)
                ->where('size', $size)
                ->first();

            // Get the first transaction for opening balance
            $firstRecord = $records->sortBy('created_at')->first();
            $openingBalance = (object) [
                'opening_qty' => $firstRecord ? ($firstRecord->transaction_quantity ?? 0) : 0,
                'opening_hpp' => $firstRecord ? ($firstRecord->nominal ?? 0) : 0,
            ];

            // Filter PB transactions after the first record
            $pbRecordsAfterOpening = $records
                ->where('voucher_type', 'PB')
                ->where('created_at', '>', $firstRecord->created_at ?? '1970-01-01 00:00:00')
                ->values();

            $incomingQty = $pbRecordsAfterOpening->sum('transaction_quantity') ?? 0;
            $outgoingQty = $records->whereIn('voucher_type', ['PJ', 'PK', 'PH'])->sum('transaction_quantity') ?? 0;

            // Calculate final stock quantity
            $finalStockQty = ($openingBalance->opening_qty ?? 0) + $incomingQty - $outgoingQty;

            // Calculate HPP
            $totalHppValue = ($openingBalance->opening_hpp ?? 0);
            $transactionCount = 1;

            foreach ($pbRecordsAfterOpening as $pbRecord) {
                $hppValue = $pbRecord->nominal ?? 0;
                $totalHppValue += $hppValue;
                $transactionCount += 1;
            }

            $finalHpp = $transactionCount > 0 ? $totalHppValue / $transactionCount : ($hppAverages[$stockKey]['average_hpp'] ?? 0);

            $entry = (object) [
                'id' => $stockRecord ? $stockRecord->id : null, // Use the stock table's ID
                'item' => $item,
                'size' => $size,
                'quantity' => $this->getCurrentStockQuantity($tableName, $item, $size),
                'opening_qty' => $openingBalance->opening_qty,
                'opening_hpp' => $openingBalance->opening_hpp,
                'incoming_qty' => $incomingQty,
                'incoming_hpp' => $hppAverages[$stockKey]['average_hpp'] ?? 0,
                'outgoing_qty' => $outgoingQty,
                'outgoing_hpp' => $hppAverages[$stockKey]['average_hpp'] ?? 0,
                'final_stock_qty' => $finalStockQty,
                'final_hpp' => $finalHpp,
                'average_hpp' => $hppAverages[$stockKey]['average_hpp'] ?? 0,
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

            $stockMap[$item][] = $entry;
        }

        return $stockMap;
    }

    /**
     * Fetch opening balances
     */
    private function getOpeningBalances(array $stockKeys, Carbon $startDate): Collection
    {
        $openingBalances = collect();

        foreach ($stockKeys as $key) {
            $itemSize = explode('|', $key);
            $item = $itemSize[0];
            $size = $itemSize[1] ?? '';

            $transaction = DB::table('transactions as t1')
                ->select('t1.quantity as total_quantity', 't1.nominal as avg_nominal')
                ->join('vouchers', 't1.voucher_id', '=', 'vouchers.id')
                ->where('t1.description', $item)
                ->where('t1.size', $size)
                ->where('t1.description', 'NOT LIKE', 'HPP %')
                ->where('vouchers.voucher_type', 'PB')
                ->where('t1.created_at', '<=', $startDate)
                ->orderBy('t1.created_at', 'asc')
                ->first();

            $openingBalances->put($key, (object) [
                'item' => $item,
                'size' => $size,
                'opening_qty' => $transaction ? $transaction->total_quantity : 0,
                'opening_hpp' => $transaction ? $transaction->avg_nominal : 0
            ]);
        }

        return $openingBalances;
    }

    /**
     * Fetch HPP averages
     */
    private function getHppAverages(array $stockKeys, Carbon $startDate, Carbon $endDate): array
    {
        $transactions = DB::table('transactions')
            ->select(
                'transactions.description as item',
                'transactions.size',
                DB::raw('COALESCE(AVG(transactions.nominal), 0) as average_hpp')
            )
            ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('vouchers.voucher_type', 'PB')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(transactions.description, \'|\', COALESCE(transactions.size, ""))'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->groupBy('transactions.description', 'transactions.size')
            ->get();

        $hppAverages = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->item . '|' . ($transaction->size ?? '');
            $hppAverages[$key] = ['average_hpp' => $transaction->average_hpp];
        }

        return $hppAverages;
    }

    /**
     * Fetch current stock quantity
     */
    private function getCurrentStockQuantity(string $tableName, string $item, string $size): int
    {
        return DB::table($tableName)
            ->where('item', $item)
            ->where('size', $size)
            ->sum('quantity') ?? 0;
    }

    /**
     * Store a new recipe
     */
    public function storeRecipe(string $productName, array $transferStockIds, array $quantities, string $productSize): void
    {
        foreach ($transferStockIds as $index => $stockId) {
            $transferStock = TransferStock::find($stockId);
            if (!$transferStock) {
                Log::warning('Transfer stock not found:', ['stock_id' => $stockId]);
                throw new \Exception("Bahan baku dengan ID $stockId tidak ditemukan.");
            }
            if ($transferStock->quantity < $quantities[$index]) {
                Log::warning('Insufficient stock for transfer stock:', [
                    'stock_id' => $stockId,
                    'item' => $transferStock->item,
                    'available_quantity' => $transferStock->quantity,
                    'requested_quantity' => $quantities[$index],
                ]);
                throw new \Exception("Stok untuk {$transferStock->item} ({$transferStock->size}) tidak cukup. Tersedia: {$transferStock->quantity}, diminta: {$quantities[$index]}.");
            }
        }

        DB::beginTransaction();

        try {
            $usedStock = UsedStock::create([
                'item' => $productName,
                'size' => $productSize,
                'quantity' => 0,
            ]);

            $recipe = Recipes::create(['product_name' => $productName, 'used_stock_id' => $usedStock->id]);

            for ($i = 0; $i < count($transferStockIds); $i++) {
                $transferStock = TransferStock::find($transferStockIds[$i]);
                Log::debug('Attaching ingredient:', [
                    'recipe_id' => $recipe->id,
                    'transfer_stock_id' => $transferStockIds[$i],
                    'quantity' => $quantities[$i],
                    'item' => $transferStock->item,
                    'size' => $transferStock->size,
                ]);
                $recipe->transferStocks()->attach($transferStockIds[$i], [
                    'quantity' => $quantities[$i],
                    'item' => $transferStock->item,
                    'size' => $transferStock->size,
                ]);
            }

            DB::commit();

            Log::info('Recipe created successfully:', [
                'recipe_id' => $recipe->id,
                'used_stock_id' => $usedStock->id,
                'product_name' => $productName,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Recipe Service Error: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Prepare data for stock export
     */
    public function prepareExportData(string $startDate, string $endDate): array
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->isFuture()) {
            $endDate = Carbon::now()->endOfDay();
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
     */
    public function getTransactions(int $stockId, string $filter, string $table)
    {
        Log::info('Fetching transactions', ['stockId' => $stockId, 'table' => $table, 'filter' => $filter]);

        try {
            $modelClass = match ($table) {
                'stocks' => \App\Models\Stock::class,
                'transfer_stocks' => \App\Models\TransferStock::class,
                'used_stocks' => \App\Models\UsedStock::class,
                default => null,
            };

            if (!$modelClass) {
                Log::warning("Invalid table name: $table");
                return ['transactions' => [], 'error' => 'Invalid table name'];
            }

            $stock = $modelClass::find($stockId);
            if (!$stock) {
                Log::warning("No record found for stockId: $stockId in table: $table");
                return ['transactions' => []];
            }

            $query = \App\Models\Transactions::select([
                'transactions.id',
                'transactions.description',
                'transactions.quantity',
                'transactions.nominal',
                'transactions.created_at',
                'vouchers.voucher_type'
            ])
                ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
                ->where('transactions.description', $stock->item)
                ->where('transactions.size', $stock->size);

            if ($filter === '7_days') {
                $query->where('transactions.created_at', '>=', now()->subDays(7));
            } elseif ($filter === '1_month') {
                $query->where('transactions.created_at', '>=', now()->subMonth());
            }

            $transactions = $query->get()->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description ?? 'No Description',
                    'voucher_type' => $transaction->voucher_type ?? 'Unknown',
                    'quantity' => $transaction->quantity ?? 0,
                    'nominal' => $transaction->nominal ?? 0,
                    'created_at' => $transaction->created_at ? $transaction->created_at->format('Y-m-d') : null,
                ];
            })->toArray();

            Log::info('Transactions fetched successfully', ['stockId' => $stockId, 'table' => $table, 'count' => count($transactions)]);
            return ['transactions' => $transactions];
        } catch (\Exception $e) {
            Log::error('Error fetching transactions', [
                'stockId' => $stockId,
                'table' => $table,
                'filter' => $filter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['transactions' => [], 'error' => 'Failed to fetch transactions'];
        }
    }

    /**
     * Prepare data for transfer form PDF
     */
    public function prepareTransferFormData(string $tableFilter): array
    {
        $query = DB::table('transfer_stocks')
            ->select('id', 'item', 'size', 'quantity');

        if ($tableFilter !== 'all' && $tableFilter !== 'transfer_stocks') {
            $query->whereRaw('1 = 0');
        }

        $transferStockData = $query->get();

        return [
            'transferStockData' => $transferStockData,
            'date' => Carbon::today()->format('d-m-Y'),
        ];
    }
}
