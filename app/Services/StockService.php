<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Imports\StockImport;
use App\Models\Company;
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
        $recipes = $data['recipe'] ?? null;
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

        // Fetch stock transactions
        $stockData = $tableFilter === 'all' || $tableFilter === 'stocks' ? $this->getStockTransactions('stocks', $stockKeys, $startDate, $endDate) : collect([]);
        $transferStockData = $tableFilter === 'all' || $tableFilter === 'transfer_stocks' ? $this->getStockTransactions('transfer_stocks', $stockKeys, $startDate, $endDate) : collect([]);
        $usedStockData = $tableFilter === 'all' || $tableFilter === 'used_stocks' ? $this->getStockTransactions('used_stocks', $stockKeys, $startDate, $endDate) : collect([]);

        // Process grouped stock data
        $stockMap = $this->processGroupedStockData($stockData, $openingBalances, 'stocks', $startDate, $endDate);
        $transferStockMap = $this->processGroupedStockData($transferStockData, $openingBalances, 'transfer_stocks', $startDate, $endDate);
        $usedStockMap = $this->processGroupedStockData($usedStockData, $openingBalances, 'used_stocks', $startDate, $endDate);

        // Fetch recipes and their transfer stocks
        $recipes = DB::table('recipes')
            ->leftJoin('recipe_transfer_stock', 'recipes.id', '=', 'recipe_transfer_stock.recipe_id')
            ->leftJoin('transfer_stocks', 'recipe_transfer_stock.transfer_stock_id', '=', 'transfer_stocks.id')
            ->select(
                'recipes.id',
                'recipes.product_name',
                'recipes.size',
                'recipes.nominal',
                'transfer_stocks.item',
                'transfer_stocks.size',
                'recipe_transfer_stock.quantity',
                'recipe_transfer_stock.nominal'
            )
            ->get()
            ->groupBy('id')
            ->map(function ($group) {
                $recipe = $group->first();
                $recipe->transferStocks = $group->map(function ($item) {
                    return (object) [
                        'item' => $item->item,
                        'size' => $item->size,
                        'quantity' => $item->quantity,
                        'nominal' => $item->nominal,
                    ];
                })->unique()->values();
                return $recipe;
            })->values();

        return [
            'stockData' => $stockMap,
            'transferStockData' => $transferStockMap,
            'usedStockData' => $usedStockMap,
            'recipes' => $recipes,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Fetch transactions for a specific table
     */
    private function getStockTransactions(string $tableName, array $stockKeys, Carbon $startDate, Carbon $endDate): Collection
    {
        // Adjust date range to include the full day for endDate
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->endOfDay();

        $query = DB::table($tableName)
            ->select(
                "$tableName.id as stock_id",
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
                    ->on("$tableName.size", '=', 'transactions.size')
                    ->where('transactions.description', 'NOT LIKE', 'HPP %');
            })
            ->leftJoin('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
            ->whereIn(DB::raw("CONCAT($tableName.item, '|', COALESCE($tableName.size, ''))"), $stockKeys)
            ->whereBetween('transactions.created_at', [$startDate, $endDate]);

        $results = $query->get();

        // Debug log to verify data
        // Log::debug('Raw Stock Transactions', $results->toArray());

        return $results->groupBy(function ($item) {
            return $item->item . '|' . ($item->size ?? '');
        })->map(function ($records) {
            return $records->map(function ($record) {
                return (object) [
                    'stock_id' => $record->stock_id,
                    'item' => $record->item,
                    'size' => $record->size,
                    'description' => $record->description ?? 'No Description',
                    'voucher_type' => $record->voucher_type ?? 'Unknown',
                    'transaction_quantity' => $record->transaction_quantity ?? 0,
                    'nominal' => $record->nominal ?? 0,
                    'created_at' => $record->created_at ? Carbon::parse($record->created_at)->toDateTimeString() : null,
                ];
            });
        });
    }

    /**
     * Process grouped stock data
     */
    private function processGroupedStockData(Collection $stockData, Collection $openingBalances, string $tableName, $startDate, $endDate): array
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

            // Fetch the current stock record to get the correct quantity
            $stockRecord = DB::table($tableName)
                ->select('id', 'quantity')
                ->where('item', $item)
                ->where('size', $size)
                ->first();

            // Define opening voucher types based on table
            $openingVoucherType = match ($tableName) {
                'stocks' => 'PB',
                'transfer_stocks' => 'PH',
                'used_stocks' => ['PK', 'PB'],
                default => null,
            };

            // Sort records by created_at
            $sortedRecords = $records->sortBy('created_at')->values();

            // Determine opening balance based on the specific voucher type
            $openingBalance = (object) ['opening_qty' => 0, 'opening_hpp' => 0];

            if ($openingVoucherType) {
                if (is_array($openingVoucherType)) {
                    $firstOpeningRecord = $sortedRecords->whereIn('voucher_type', $openingVoucherType)
                        ->sortBy('created_at')
                        ->first();
                } else {
                    $firstOpeningRecord = $sortedRecords->firstWhere('voucher_type', $openingVoucherType);
                }
                if ($firstOpeningRecord) {
                    $openingBalance->opening_qty = $firstOpeningRecord->transaction_quantity ?? 0;
                    $openingBalance->opening_hpp = $firstOpeningRecord->nominal ?? 0;
                }
            }

            // Fallback to openingBalances if no transaction-based opening
            if ($openingBalance->opening_qty == 0 && $openingBalance->opening_hpp == 0) {
                $defaultBalance = $openingBalances->get($stockKey, (object) ['opening_qty' => 0, 'opening_hpp' => 0]);
                $openingBalance->opening_qty = $defaultBalance->opening_qty;
                $openingBalance->opening_hpp = $defaultBalance->opening_hpp;
            }

            // Define incoming and outgoing voucher types based on table
            $incomingVoucherTypes = match ($tableName) {
                'stocks' => ['PB'],
                'transfer_stocks' => ['PH'],
                'used_stocks' => ['PK', 'PB'],
                default => [],
            };

            $outgoingVoucherTypes = match ($tableName) {
                'stocks' => ['PH'],
                'transfer_stocks' => ['PK', 'PJ'],
                'used_stocks' => ['PJ'],
                default => [],
            };

            // Filter incoming records with strict voucher_type check
            $incomingRecords = $sortedRecords->filter(function ($record) use ($openingBalance, $incomingVoucherTypes, $sortedRecords, $openingVoucherType) {
                if (is_array($openingVoucherType)) {
                    $firstOpeningRecord = $sortedRecords->whereIn('voucher_type', $openingVoucherType)
                        ->sortBy('created_at')
                        ->first();
                } else {
                    $firstOpeningRecord = $sortedRecords->firstWhere('voucher_type', $openingVoucherType);
                }
                $isOpening = $firstOpeningRecord &&
                    $record->transaction_quantity == $firstOpeningRecord->transaction_quantity &&
                    $record->nominal == $firstOpeningRecord->nominal &&
                    $record->created_at == $firstOpeningRecord->created_at &&
                    (is_array($openingVoucherType) ? in_array($record->voucher_type, $openingVoucherType) : $record->voucher_type == $openingVoucherType);
                return !$isOpening && in_array($record->voucher_type, $incomingVoucherTypes);
            });

            $incomingQty = $incomingRecords->sum('transaction_quantity') ?? 0;
            $incomingHpp = $incomingRecords->isNotEmpty() ? ($incomingRecords->avg('nominal') ?? 0) : 0;

            // Initialize outgoing records as an empty collection
            $outgoingRecords = collect([]);

            // Calculate outgoing quantities
            $outgoingQty = 0;
            $outgoingHpp = 0;

            if ($tableName === 'transfer_stocks' && $stockRecord) {
                // Fetch all PK transactions within the dynamic date range
                $pkTransactions = DB::table('transactions as t')
                    ->join('vouchers as v', 't.voucher_id', '=', 'v.id')
                    ->where('v.voucher_type', 'PK')
                    ->whereBetween('t.created_at', [$startDate, $endDate])
                    ->select('t.id', 't.description', 't.quantity as transaction_quantity', 't.size as transaction_size', 't.nominal')
                    ->get();

                // Get all recipes that use this transfer_stocks item with standard cost
                $relevantRecipes = DB::table('recipe_transfer_stock as rts')
                    ->where('rts.item', $item)
                    ->where('rts.size', $size)
                    ->join('recipes as r', 'rts.recipe_id', '=', 'r.id')
                    ->select('r.id as recipe_id', 'r.product_name', 'r.size as recipe_size', 'rts.quantity as material_quantity', 'rts.nominal as standard_cost')
                    ->get();

                $outgoingQty = 0;
                $totalMaterialCost = 0;
                $totalMaterialQty = 0;

                foreach ($pkTransactions as $transaction) {
                    foreach ($relevantRecipes as $recipe) {
                        if ($recipe->product_name === $transaction->description && $recipe->recipe_size === $transaction->transaction_size) {
                            $materialQty = $recipe->material_quantity ?? 0;
                            $standardCostTotal = $recipe->standard_cost ?? $incomingHpp; // Total cost for material_quantity
                            $standardCostPerUnit = $materialQty > 0 ? ($standardCostTotal / $materialQty) : $incomingHpp; // HPP per unit
                            $outgoingQty += $materialQty * $transaction->transaction_quantity;

                            // Use standard cost per unit as outgoing HPP
                            $materialCost = $standardCostPerUnit * $materialQty * $transaction->transaction_quantity;
                            $totalMaterialCost += $materialCost;
                            $totalMaterialQty += $materialQty * $transaction->transaction_quantity;
                        }
                    }
                }

                // Calculate average outgoing HPP based on standard cost per unit
                $outgoingHpp = $totalMaterialQty > 0 ? ($totalMaterialCost / $totalMaterialQty) : $incomingHpp;

                // Fallback to quantity change if no recipe data
                if ($outgoingQty == 0) {
                    $currentQty = $stockRecord->quantity ?? 0;
                    $expectedQty = $openingBalance->opening_qty + $incomingQty;
                    $outgoingQty = max(0, $expectedQty - $currentQty);
                    $outgoingHpp = $incomingHpp; // Fallback to incoming HPP if no recipe data
                }
            } else {
                // Filter outgoing records with strict voucher_type check
                $outgoingRecords = $sortedRecords->filter(function ($record) use ($openingBalance, $outgoingVoucherTypes, $openingVoucherType, $sortedRecords) {
                    if (is_array($openingVoucherType)) {
                        $firstOpeningRecord = $sortedRecords->whereIn('voucher_type', $openingVoucherType)
                            ->sortBy('created_at')
                            ->first();
                    } else {
                        $firstOpeningRecord = $sortedRecords->firstWhere('voucher_type', $openingVoucherType);
                    }
                    $isOpening = $firstOpeningRecord &&
                        $record->transaction_quantity == $firstOpeningRecord->transaction_quantity &&
                        $record->nominal == $firstOpeningRecord->nominal &&
                        $record->created_at == $firstOpeningRecord->created_at &&
                        (is_array($openingVoucherType) ? in_array($record->voucher_type, $openingVoucherType) : $record->voucher_type == $openingVoucherType);
                    return !$isOpening && in_array($record->voucher_type, $outgoingVoucherTypes);
                });
                $outgoingQty = $outgoingRecords->sum('transaction_quantity') ?? 0;
                $outgoingHpp = $outgoingRecords->isNotEmpty() ? ($outgoingRecords->avg('nominal') ?? $incomingHpp) : $incomingHpp;
            }

            $outgoingHpp = $outgoingQty > 0 ? $outgoingHpp : 0;

            // Calculate final stock quantity and HPP
            $finalQty = $openingBalance->opening_qty + $incomingQty - $outgoingQty;
            $totalHppValue = $openingBalance->opening_hpp;
            $transactionCount = $openingBalance->opening_qty > 0 ? 1 : 0;

            foreach ($incomingRecords as $record) {
                $totalHppValue += $record->nominal ?? 0;
                $transactionCount++;
            }

            $finalHpp = $transactionCount > 0 ? $totalHppValue / $transactionCount : ($incomingHpp > 0 ? $incomingHpp : $openingBalance->opening_hpp);

            // Set nominal for transfer_stocks with improved fallback
            $nominal = $tableName === 'transfer_stocks' ? ($incomingRecords->avg('nominal') ?? 0) : 0;
            if ($tableName === 'transfer_stocks' && $nominal == 0) {
                $pbHpp = DB::table('transactions as t')
                    ->join('vouchers as v', 't.voucher_id', '=', 'v.id')
                    ->where('t.description', $item)
                    ->where('t.size', $size)
                    ->where('v.voucher_type', 'PB')
                    ->where('t.description', 'NOT LIKE', 'HPP %')
                    ->avg('t.nominal') ?? null;
                $nominal = $pbHpp ?: ($openingBalances->get($stockKey, (object) ['opening_hpp' => 0])->opening_hpp ?: 0);
            }

            $entry = (object) [
                'id' => $stockRecord ? $stockRecord->id : null,
                'item' => $item,
                'size' => $size,
                'quantity' => $stockRecord ? $stockRecord->quantity : 0,
                'opening_qty' => $openingBalance->opening_qty,
                'opening_hpp' => $openingBalance->opening_hpp,
                'incoming_qty' => $incomingQty,
                'incoming_hpp' => $incomingHpp,
                'outgoing_qty' => $outgoingQty,
                'outgoing_hpp' => $outgoingHpp,
                'final_stock_qty' => $finalQty,
                'final_hpp' => $finalHpp,
                'average_pb_hpp' => $incomingHpp,
                'average_ph_hpp' => $tableName === 'transfer_stocks' ? ($incomingRecords->avg('nominal') ?? 0) : 0,
                'nominal' => $nominal,
                'transactions' => $records->map(function ($record) {
                    return (object) [
                        'description' => $record->description ?? 'No Description',
                        'voucher_type' => $record->voucher_type ?? 'Unknown',
                        'quantity' => $record->transaction_quantity ?? 0,
                        'nominal' => $record->nominal ?? 0,
                        'created_at' => $record->created_at,
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

            // Get the first transaction for opening balance
            $transaction = DB::table('transactions')
                ->select('quantity as total_quantity', 'nominal as avg_nominal')
                ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
                ->where('transactions.description', $item)
                ->where('transactions.size', $size)
                ->where('transactions.description', 'NOT LIKE', 'HPP %')
                ->whereIn('vouchers.voucher_type', ['PB', 'PH', 'PK'])
                ->where('transactions.created_at', '<=', $startDate)
                ->orderBy('transactions.created_at', 'asc')
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
     * Store a new recipe
     */
    public function storeRecipe(string $productName, array $transferStockIds, array $quantities, string $productSize): void
    {
        // Validate input
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
            // Create first UsedStock entry for the product
            $usedStock = UsedStock::create([
                'item' => $productName,
                'size' => $productSize,
                'quantity' => 0,
            ]);

            // Calculate total nominal for the recipe
            $totalNominal = 0;
            $ingredientData = [];

            for ($i = 0; $i < count($transferStockIds); $i++) {
                $transferStock = TransferStock::find($transferStockIds[$i]);

                // Get the average PH nominal
                $averagePhHpp = DB::table('transactions')
                    ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
                    ->where('transactions.description', $transferStock->item)
                    ->where('transactions.size', $transferStock->size)
                    ->where('vouchers.voucher_type', 'PH')
                    ->where('transactions.description', 'NOT LIKE', 'HPP %')
                    ->avg('transactions.nominal') ?? 0;

                // Fallback to average PB nominal
                $nominal = $averagePhHpp;
                if ($nominal == 0) {
                    $averagePbHpp = DB::table('transactions')
                        ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
                        ->where('transactions.description', $transferStock->item)
                        ->where('transactions.size', $transferStock->size)
                        ->where('vouchers.voucher_type', 'PB')
                        ->where('transactions.description', 'NOT LIKE', 'HPP %')
                        ->avg('transactions.nominal') ?? 0;
                    $nominal = $averagePbHpp;
                }

                // Calculate nominal for this ingredient
                $ingredientNominal = $quantities[$i] * $nominal;
                $totalNominal += $ingredientNominal;

                $ingredientData[] = [
                    'transfer_stock_id' => $transferStockIds[$i],
                    'quantity' => $quantities[$i],
                    'nominal' => $ingredientNominal,
                    'item' => $transferStock->item,
                    'size' => $transferStock->size,
                ];
            }

            // Create Recipe entry with total nominal and size
            $recipe = Recipes::create([
                'product_name' => $productName,
                'used_stock_id' => $usedStock->id,
                'size' => $productSize,
                'nominal' => $totalNominal,
            ]);

            // Attach ingredients to recipe
            foreach ($ingredientData as $data) {
                $recipe->transferStocks()->attach($data['transfer_stock_id'], [
                    'quantity' => $data['quantity'],
                    'nominal' => $data['nominal'],
                    'item' => $data['item'],
                    'size' => $data['size'],
                ]);
            }

            // Create second UsedStock entry for HPP
            $hppUsedStock = UsedStock::create([
                'item' => "HPP {$productName}",
                'size' => $productSize,
                'quantity' => 0,
            ]);

            DB::commit();

            // Log::info('Recipe created successfully:', [
            //     'recipe_id' => $recipe->id,
            //     'used_stock_id' => $usedStock->id,
            //     'hpp_used_stock_id' => $hppUsedStock->id,
            //     'product_name' => $productName,
            //     'size' => $productSize,
            //     'nominal' => $totalNominal,
            // ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Store Recipe Service Error: ' . $e->getMessage(), ['exception' => $e]);
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
     * Prepare data for transfer form PDF
     */
    public function prepareTransferFormData(string $tableFilter): array
    {
        $companyLogo = Company::value('logo');
        $company = Company::select('company_name', 'phone', 'director', 'email', 'address')->firstOrFail();

        return [
            'companyLogo' => $companyLogo,
            'company' => $company,
            'date' => Carbon::today()->format('d-m-Y'),
        ];
    }
}
