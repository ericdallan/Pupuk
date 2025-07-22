<?php

namespace App\Services;

use App\Models\VoucherDetails;
use App\Services\GeneralLedgerService;
use App\Services\StockService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardService
{
    protected $generalLedgerService;
    protected $stockService;

    public function __construct(GeneralLedgerService $generalLedgerService, StockService $stockService)
    {
        $this->generalLedgerService = $generalLedgerService;
        $this->stockService = $stockService;
    }

    /**
     * Get the admin dashboard data
     *
     * @param array $params
     * @return array
     */
    public function getDashboardData(array $params = [])
    {
        try {
            // Validate parameters
            $validator = Validator::make($params, [
                'stock_qty_month' => 'nullable|integer|between:1,12',
                'stock_qty_year' => 'nullable|integer|min:1900',
                'stock_amount_month' => 'nullable|integer|between:1,12',
                'stock_amount_year' => 'nullable|integer|min:1900',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Set default period for stock charts (current month)
            $stockQtyMonth = $params['stock_qty_month'] ?? Carbon::now()->month;
            $stockQtyYear = $params['stock_qty_year'] ?? Carbon::now()->year;
            $stockAmountMonth = $params['stock_amount_month'] ?? Carbon::now()->month;
            $stockAmountYear = $params['stock_amount_year'] ?? Carbon::now()->year;

            $stockQtyStartDate = Carbon::create($stockQtyYear, $stockQtyMonth, 1)->startOfMonth();
            $stockQtyEndDate = $stockQtyStartDate->copy()->endOfMonth();
            $stockAmountStartDate = Carbon::create($stockAmountYear, $stockAmountMonth, 1)->startOfMonth();
            $stockAmountEndDate = $stockAmountStartDate->copy()->endOfMonth();

            // Get monthly profit trend (last 12 months)
            $profitTrend = [];
            $labels = [];
            $currentDate = Carbon::now()->subMonths(11)->startOfMonth(); // Start from 12 months ago
            for ($i = 0; $i < 12; $i++) {
                $date = $currentDate->copy()->addMonths($i);
                // Log::debug('Processing profit month', ['date' => $date->toDateString()]);
                $profitData = Cache::remember("profit_{$date->year}_{$date->month}", 60, function () use ($date) {
                    return $this->generalLedgerService->prepareIncomeStatementData([
                        'year' => $date->year,
                        'month' => $date->month,
                    ]) ?: ['labaBersih' => 0]; // Fallback to 0 if no data
                });
                // Log::debug('Profit data', ['date' => $date->toDateString(), 'data' => $profitData]);
                $profitTrend[] = $profitData['labaBersih'] ?? 0;
                $labels[] = $date->format('M Y');
            }
            // Get monthly sales trend (last 12 months)
            $salesTrend = [
                'sales_dagangan' => [],
                'sales_jadi' => [],
            ];
            $currentDate = Carbon::now()->subMonths(11)->startOfMonth(); // Start from 12 months ago
            for ($i = 0; $i < 12; $i++) {
                $date = $currentDate->copy()->addMonths($i);
                Log::debug('Processing sales month', ['date' => $date->toDateString()]);
                $salesData = Cache::remember("sales_{$date->year}_{$date->month}", 60, function () use ($date) {
                    return $this->generalLedgerService->calculateNetProfit(
                        Carbon::create($date->year, $date->month, 1)->startOfMonth(),
                        Carbon::create($date->year, $date->month, 1)->endOfMonth()
                    ) ?: [
                        'pendapatanPenjualanDagangan' => 0,
                        'pendapatanPenjualanJadi' => 0
                    ];
                });
                Log::debug('Sales data', ['date' => $date->toDateString(), 'data' => $salesData]);
                $salesTrend['sales_dagangan'][] = $salesData['pendapatanPenjualanDagangan'] ?? 0;
                $salesTrend['sales_jadi'][] = $salesData['pendapatanPenjualanJadi'] ?? 0;
            }

            // Stock Composition by Quantity
            $stockCompositionQty = Cache::remember("stock_composition_qty_{$stockQtyStartDate->format('Y-m-d')}_{$stockQtyEndDate->format('Y-m-d')}", 60, function () use ($stockQtyStartDate, $stockQtyEndDate) {
                $stockData = $this->stockService->prepareStockData([
                    'start_date' => $stockQtyStartDate->toDateString(),
                    'end_date' => $stockQtyEndDate->toDateString(),
                    'table_filter' => 'stocks',
                ])['stockData'];

                $items = collect();
                foreach ($stockData as $itemEntries) {
                    foreach ($itemEntries as $entry) {
                        if ($entry->final_stock_qty > 0) {
                            $items->push([
                                'label' => $entry->item . ($entry->size ? ' (' . $entry->size . ')' : ''),
                                'value' => $entry->final_stock_qty,
                            ]);
                        }
                    }
                }
                $topItems = $items->sortByDesc('value')->take(10);
                $result = $topItems->isEmpty() ? collect(['No Stock Data' => 0]) : $topItems->mapWithKeys(function ($item) {
                    return [$item['label'] => $item['value']];
                });
                Log::debug('Stock Composition by Quantity Data', ['result' => $result->toArray()]);
                return $result;
            });

            // Stock Composition by Amount
            $stockCompositionAmount = Cache::remember("stock_composition_amount_{$stockAmountStartDate->format('Y-m-d')}_{$stockAmountEndDate->format('Y-m-d')}", 60, function () use ($stockAmountStartDate, $stockAmountEndDate) {
                $stockData = $this->stockService->prepareStockData([
                    'start_date' => $stockAmountStartDate->toDateString(),
                    'end_date' => $stockAmountEndDate->toDateString(),
                    'table_filter' => 'stocks',
                ])['stockData'];

                $items = collect();
                foreach ($stockData as $itemEntries) {
                    foreach ($itemEntries as $entry) {
                        if ($entry->final_stock_qty > 0) {
                            $amount = $entry->final_stock_qty * $entry->final_hpp;
                            $items->push([
                                'label' => $entry->item . ($entry->size ? ' (' . $entry->size . ')' : ''),
                                'value' => $amount,
                            ]);
                        }
                    }
                }
                $topItems = $items->sortByDesc('value')->take(10);
                $result = $topItems->isEmpty() ? collect(['No Stock Data' => 0]) : $topItems->mapWithKeys(function ($item) {
                    return [$item['label'] => $item['value']];
                });
                Log::debug('Stock Composition by Amount Data', ['result' => $result->toArray()]);
                return $result;
            });

            // Format data for visualization
            return [
                'profit_trend' => [
                    'data' => $profitTrend,
                    'labels' => $labels,
                ],
                'sales_trend' => [
                    'data' => $salesTrend,
                    'labels' => $labels,
                ],
                'stock_composition_qty' => [
                    'labels' => $stockCompositionQty->keys()->toArray(),
                    'data' => $stockCompositionQty->values()->toArray(),
                ],
                'stock_composition_amount' => [
                    'labels' => $stockCompositionAmount->keys()->toArray(),
                    'data' => $stockCompositionAmount->values()->toArray(),
                ],
                'stock_qty_month' => $stockQtyMonth,
                'stock_qty_year' => $stockQtyYear,
                'stock_amount_month' => $stockAmountMonth,
                'stock_amount_year' => $stockAmountYear,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard data: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Clear cache for specific period
     *
     * @param int $year
     * @param int $month
     * @return void
     */
    public function clearCacheForPeriod($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        Cache::forget("profit_{$year}_{$month}");
        Cache::forget("sales_{$year}_{$month}");
        Cache::forget("stock_composition_qty_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}");
        Cache::forget("stock_composition_amount_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}");
    }
}
