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

        // Ambil saldo awal dari transaksi pertama berdasarkan voucher_id
        $openingBalances = $this->getOpeningBalances($stockKeys, $startDate);

        // Fetch HPP averages berdasarkan rata-rata nominal per transaksi
        $hppAverages = $this->getHppAverages($stockKeys, $startDate, $endDate);

        // Ambil transaksi stok
        if ($tableFilter === 'all' || $tableFilter === 'stocks') {
            $stockData = $this->getStockTransactions('stocks', $stockKeys, $startDate, $endDate);
        }
        if ($tableFilter === 'all' || $tableFilter === 'transfer_stocks') {
            $transferStockData = $this->getStockTransactions('transfer_stocks', $stockKeys, $startDate, $endDate);
        }
        if ($tableFilter === 'all' || $tableFilter === 'used_stocks') {
            $usedStockData = $this->getStockTransactions('used_stocks', $stockKeys, $startDate, $endDate);
        }

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
     * Store a new recipe without reducing transfer_stocks
     *
     * @param string $productName
     * @param array $transferStockIds
     * @param array $quantities
     * @return void
     * @throws \Exception
     */
    public function storeRecipe(string $productName, array $transferStockIds, array $quantities, string $productSize): void
    {
        // Periksa ketersediaan stok
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
            // Buat entri UsedStock untuk barang jadi
            $usedStock = UsedStock::create([
                'item' => $productName,
                'size' => $productSize,
                'quantity' => 0,
            ]);

            // Buat entri Recipe dan tautkan ke UsedStock
            $recipe = Recipes::create(['product_name' => $productName, 'used_stock_id' => $usedStock->id]);

            // Kaitkan transfer stocks ke resep via pivot table with item and size
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

    private function getOpeningBalances(array $stockKeys, Carbon $startDate): Collection
    {
        Log::debug("=== DETAILED DEBUG ===");
        // Log::debug("Stock Keys:", $stockKeys);
        // Log::debug("Start Date:", [$startDate->format('Y-m-d H:i:s')]);

        // Adjust startDate to include all transactions (e.g., current date)
        $startDate = Carbon::now(); // Or set to '2025-06-30' to include all logged transactions

        $vouchers = DB::table('vouchers')->get(['id', 'voucher_type']);
        Log::debug("All Vouchers:", $vouchers->toArray());

        $allTransactions = DB::table('transactions')
            ->select('id', 'voucher_id', 'description', 'size', 'quantity', 'nominal', 'created_at')
            ->get();
        Log::debug("All Transactions:", $allTransactions->toArray());

        $openingBalances = collect();

        foreach ($stockKeys as $key) {
            $itemSize = explode('|', $key);
            $item = $itemSize[0];
            $size = $itemSize[1] ?? '';

            // Get the first transaction for the item and size
            $transaction = DB::table('transactions as t1')
                ->select('t1.quantity as total_quantity', 't1.nominal as avg_nominal')
                ->join('vouchers', 't1.voucher_id', '=', 'vouchers.id')
                ->where('t1.description', $item)
                ->where('t1.size', $size)
                ->where('t1.description', 'NOT LIKE', 'HPP %')
                ->where('vouchers.voucher_type', 'PB')
                ->where('t1.created_at', '<=', $startDate)
                ->orderBy('t1.created_at', 'asc') // Order by earliest transaction
                ->first();

            // Log::debug("Query for $key:", $transaction ? (array)$transaction : ['not found']);

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
     * Ambil rata-rata HPP berdasarkan nominal per transaksi
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
     * Ambil transaksi stok dari tabel tertentu
     */
    private function getStockTransactions(string $tableName, array $stockKeys, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = DB::table($tableName)
            ->select(
                $tableName . '.id',
                $tableName . '.item',
                $tableName . '.size',
                'transactions.created_at',
                'transactions.description',
                'transactions.quantity as transaction_quantity',
                'transactions.nominal',
                'vouchers.id',
                'vouchers.voucher_type'
            )
            ->leftJoin('transactions', function ($join) use ($tableName) {
                $join->on($tableName . '.item', '=', 'transactions.description')
                    ->on($tableName . '.size', '=', 'transactions.size');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->where('transactions.description', 'NOT LIKE', 'HPP %')
            ->whereIn(DB::raw('CONCAT(' . $tableName . '.item, \'|\', COALESCE(' . $tableName . '.size, ""))'), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $results = $query->get();
        Log::debug("Stock Transactions for $tableName:", $results->toArray());

        return $results->groupBy(function ($item) {
            return $item->item . '|' . ($item->size ?? '');
        });
    }

    /**
     * Proses data stok yang dikelompokkan
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
            $openingBalance = $openingBalances->get($stockKey, (object) ['opening_qty' => 0, 'opening_hpp' => 0]);
            $hppEntry = $hppAverages[$stockKey] ?? ['average_hpp' => 0];

            $incomingTransactions = $records->where('voucher_type', 'PB');
            $outgoingTransactions = $records->whereIn('voucher_type', ['PJ', 'PK', 'PH']);

            $incomingQty = $incomingTransactions->sum('transaction_quantity') ?? 0;
            $outgoingQty = $outgoingTransactions->sum('transaction_quantity') ?? 0;

            // Hitung final_hpp berdasarkan rata-rata tertimbang
            $totalQty = ($openingBalance->opening_qty ?? 0) + $incomingQty;
            $totalValue = ($openingBalance->opening_qty ?? 0) * $openingBalance->opening_hpp + $incomingQty * $hppEntry['average_hpp'];
            $final_hpp = $totalQty > 0 ? $totalValue / $totalQty : ($hppEntry['average_hpp'] ?? 0);

            // Hitung final_stock_qty berdasarkan opening, incoming, dan outgoing
            $finalStockQty = ($openingBalance->opening_qty ?? 0) + $incomingQty - $outgoingQty;

            $entry = (object) [
                'id' => $records->first()->id ?? null,
                'item' => $item,
                'size' => $size,
                'quantity' => $this->getCurrentStockQuantity($tableName, $item, $size),
                'opening_qty' => $openingBalance->opening_qty,
                'opening_hpp' => $openingBalance->opening_hpp,
                'incoming_qty' => $incomingQty,
                'incoming_hpp' => $hppEntry['average_hpp'],
                'outgoing_qty' => $outgoingQty,
                'outgoing_hpp' => $hppEntry['average_hpp'],
                'final_stock_qty' => $finalStockQty,
                'final_hpp' => $final_hpp,
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

            $stockMap[$item][] = $entry;
        }

        return $stockMap;
    }

    /**
     * Ambil kuantitas stok saat ini
     */
    private function getCurrentStockQuantity(string $tableName, string $item, string $size): int
    {
        return DB::table($tableName)
            ->where('item', $item)
            ->where('size', $size)
            ->sum('quantity') ?? 0;
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
    public function getTransactions(int $stockId, string $filter): Collection
    {
        $dateRange = $filter === '7_days' ? Carbon::now()->subDays(7) : Carbon::now()->subMonth();

        $stock = DB::table('stocks')->where('id', $stockId)->first();
        if (!$stock) {
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

        return $transactions->map(function ($transaction) {
            $transaction->created_at = Carbon::parse($transaction->created_at)->format('d-m-Y');
            return (object) $transaction;
        });
    }

    /**
     * Prepare data for transfer form PDF
     */
    public function prepareTransferFormData(string $tableFilter): array
    {
        $query = DB::table('transfer_stocks')
            ->select('id', 'item', 'size', 'quantity');

        if ($tableFilter !== 'all' && $tableFilter !== 'transfer_stocks') {
            $query->whereRaw('1 = 0'); // No results if not 'all' or 'transfer_stocks'
        }

        $transferStockData = $query->get();

        return [
            'transferStockData' => $transferStockData,
            'date' => Carbon::today()->format('d-m-Y'),
        ];
    }
}
