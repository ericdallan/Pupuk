<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Imports\StockImport;
use App\Models\Company;
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

        // Fetch stock transactions
        $stockData = $tableFilter === 'all' || $tableFilter === 'stocks' ? $this->getStockTransactions('stocks', $stockKeys, $startDate, $endDate) : collect([]);

        // Process grouped stock data
        $stockMap = $this->processGroupedStockData($stockData, $openingBalances, 'stocks', $startDate, $endDate);

        return [
            'stockData' => $stockMap,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        return [
            'stockData' => $stockMap,
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
                'vouchers.voucher_type',
                'vouchers.voucher_number',
                'vouchers.id as voucher_id'
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

        return $results->groupBy(function ($item) {
            return $item->item . '|' . ($item->size ?? '');
        })->map(function ($records) {
            // Hilangkan duplikat berdasarkan voucher_id, created_at, dan transaction_quantity
            return $records->unique(function ($record) {
                return ($record->voucher_id ?? 'null') . '|' . ($record->created_at ?? 'null') . '|' . ($record->transaction_quantity ?? 'null');
            })->map(function ($record) {
                return (object) [
                    'stock_id' => $record->stock_id,
                    'item' => $record->item,
                    'size' => $record->size,
                    'description' => $record->description ?? 'No Description',
                    'voucher_type' => $record->voucher_type ?? 'Unknown',
                    'voucher_number' => $record->voucher_number ?? 'N/A',
                    'voucher_id' => $record->voucher_id ?? null,
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
                'stocks' => ['PB', 'PYB', 'RPJ'],
                default => [],
            };

            $outgoingVoucherTypes = match ($tableName) {
                'stocks' => ['PJ', 'PYK', 'RPB'],
                default => [],
            };

            // Filter incoming records with strict voucher_type check and deduplicate
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
            })->unique(function ($record) {
                return $record->voucher_id . '|' . $record->created_at . '|' . $record->transaction_quantity; // Deduplicate based on unique combo
            });

            $incomingQty = $incomingRecords->sum('transaction_quantity') ?? 0;
            $incomingHpp = $incomingRecords->isNotEmpty() ? ($incomingRecords->avg('nominal') ?? 0) : 0;

            // Initialize outgoing records as an empty collection
            $outgoingRecords = collect([]);

            // Calculate outgoing quantities
            $outgoingQty = 0;
            $outgoingHpp = 0;
            // Filter outgoing records with strict voucher_type check and deduplicate
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
            })->unique(function ($record) {
                return $record->voucher_id . '|' . $record->created_at; // Deduplicate based on voucher_id and created_at
            });

            $outgoingQty = $outgoingRecords->sum('transaction_quantity') ?? 0;
            $outgoingHpp = $outgoingRecords->isNotEmpty() ? ($outgoingRecords->avg('nominal') ?? $incomingHpp) : $incomingHpp;


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

            $entry = (object) [
                'id' => $stockRecord ? $stockRecord->id : null,
                'item' => $item,
                'size' => $size,
                'quantity' => $stockRecord ? $stockRecord->quantity : 0,
                'opening_qty' => $openingBalance->opening_qty,
                'opening_hpp' => round(floatval($openingBalance->opening_hpp ?? 0), 2),
                'incoming_qty' => $incomingQty,
                'incoming_hpp' => round(floatval($incomingHpp ?? 0), 2),
                'outgoing_qty' => $outgoingQty,
                'outgoing_hpp' => round(floatval($outgoingHpp ?? 0), 2),
                'final_stock_qty' => $finalQty,
                'final_hpp' => round(floatval($finalHpp ?? 0), 2),
                'average_pb_hpp' => round($incomingHpp),
                'transactions' => $records->map(function ($record) {
                    return (object) [
                        'description' => $record->description ?? 'No Description',
                        'voucher_type' => $record->voucher_type ?? 'Unknown',
                        'voucher_number' => $record->voucher_number ?? 'N/A',
                        'voucher_id' => $record->voucher_id ?? null,
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
     * Calculate the average HPP for a given item and size within a date range
     *
     * @param string $item
     * @param string|null $size
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float|null
     */
    public function getAverageHPP(string $item, ?string $size, Carbon $startDate, Carbon $endDate): ?float
    {
        try {
            // Ensure dates are properly formatted
            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();

            // Query transactions to calculate average HPP
            $averageHpp = DB::table('transactions')
                ->join('vouchers', 'transactions.voucher_id', '=', 'vouchers.id')
                ->where('transactions.description', $item)
                ->where('transactions.size', $size ?? '')
                ->whereIn('vouchers.voucher_type', ['PB']) // Relevant voucher types for HPP
                ->whereBetween('transactions.created_at', [$startDate, $endDate])
                ->where('transactions.description', 'NOT LIKE', 'HPP %')
                ->avg('transactions.nominal');

            // Return the average HPP, or null if no data is found
            return $averageHpp ? round(floatval($averageHpp), 2) : null;
        } catch (\Exception $e) {
            Log::error('Error calculating average HPP: ' . $e->getMessage(), [
                'item' => $item,
                'size' => $size,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);
            return null;
        }
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
                ->whereIn('vouchers.voucher_type', ['PB'])
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
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
        ];
    }

    /**
     * Apply selected applied cost to stock data
     *
     * @param array $stockData
     * @param int|null $appliedCostId
     * @return array
     */
    public function applyAppliedCostToStockData(array $stockData, ?int $appliedCostId = null): array
    {
        if (!$appliedCostId) {
            return $stockData;
        }

        try {
            $appliedCost = \App\Models\AppliedCost::find($appliedCostId);
            if (!$appliedCost) {
                return $stockData;
            }

            // Calculate total number of stock items across all categories
            $totalStockItems = $this->countTotalStockItems($stockData);

            if ($totalStockItems === 0) {
                return $stockData;
            }

            // Distribute applied cost evenly across all items
            $appliedCostPerItem = $appliedCost->total_nominal / $totalStockItems;

            // Apply to each stock category
            foreach ($stockData as $category => &$categoryData) {
                if (is_array($categoryData)) {
                    foreach ($categoryData as $itemName => &$itemSizes) {
                        if (is_array($itemSizes)) {
                            foreach ($itemSizes as &$stockItem) {
                                if (is_object($stockItem)) {
                                    // Add applied cost to HPP calculations
                                    $stockItem->opening_hpp += $appliedCostPerItem;
                                    $stockItem->incoming_hpp += $appliedCostPerItem;
                                    $stockItem->outgoing_hpp += $appliedCostPerItem;
                                    $stockItem->final_hpp += $appliedCostPerItem;

                                    // Store applied cost info for display
                                    $stockItem->applied_cost_per_item = $appliedCostPerItem;
                                    $stockItem->applied_cost_id = $appliedCostId;
                                    $stockItem->applied_cost_total = $appliedCost->total_nominal;
                                }
                            }
                        }
                    }
                }
            }

            return $stockData;
        } catch (\Exception $e) {
            Log::error('Error applying applied cost to stock data: ' . $e->getMessage());
            return $stockData;
        }
    }

    /**
     * Count total stock items across all categories
     *
     * @param array $stockData
     * @return int
     */
    private function countTotalStockItems(array $stockData): int
    {
        $count = 0;

        foreach ($stockData as $categoryData) {
            if (is_array($categoryData)) {
                foreach ($categoryData as $itemSizes) {
                    if (is_array($itemSizes)) {
                        $count += count($itemSizes);
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Get applied cost summary for display
     *
     * @param int $appliedCostId
     * @return array|null
     */
    public function getAppliedCostSummary(int $appliedCostId): ?array
    {
        try {
            $appliedCost = \App\Models\AppliedCost::with('details')->find($appliedCostId);

            if (!$appliedCost) {
                return null;
            }

            return [
                'id' => $appliedCost->id,
                'total_nominal' => $appliedCost->total_nominal,
                'created_at' => $appliedCost->created_at->format('d/m/Y H:i'),
                'details_count' => $appliedCost->details->count(),
                'details' => $appliedCost->details->map(function ($detail) {
                    return [
                        'description' => $detail->description,
                        'nominal' => $detail->nominal
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting applied cost summary: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Prepare stock data with applied cost support
     * Modified version of existing prepareStockData method
     */
    public function prepareStockDataWithAppliedCost(array $data): array
    {
        // Get base stock data using existing method
        $stockData = $this->prepareStockData($data);

        // Apply selected applied cost if in management mode and cost is selected
        $appliedCostId = $data['applied_cost_id'] ?? null;
        $isManagementMode = ($data['mode'] ?? 'accounting') === 'management';

        if ($isManagementMode && $appliedCostId) {
            $stockData['stockData'] = $this->applyAppliedCostToStockData($stockData['stockData'], $appliedCostId);

            // Add applied cost summary to return data
            $stockData['appliedCostSummary'] = $this->getAppliedCostSummary($appliedCostId);
        }

        return $stockData;
    }
}
