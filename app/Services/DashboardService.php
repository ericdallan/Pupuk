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
                'profit_trend_period' => 'nullable|in:last_12_months,yearly',
                'profit_trend_year' => 'nullable|integer|min:1900',
                'sales_trend_period' => 'nullable|in:last_12_months,yearly',
                'sales_trend_year' => 'nullable|integer|min:1900',
                'stock_qty_month' => 'nullable|integer|between:1,12',
                'stock_qty_year' => 'nullable|integer|min:1900',
                'stock_amount_month' => 'nullable|integer|between:1,12',
                'stock_amount_year' => 'nullable|integer|min:1900',
                'stock_qty_limit' => 'nullable|integer|in:5,10',
                'stock_qty_chart_type' => 'nullable|in:bar,pie',
                'stock_amount_limit' => 'nullable|integer|in:5,10',
                'stock_amount_chart_type' => 'nullable|in:bar,pie',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Set default period for stock charts (current month)
            $stockQtyMonth = $params['stock_qty_month'] ?? Carbon::now()->month;
            $stockQtyYear = $params['stock_qty_year'] ?? Carbon::now()->year;
            $stockAmountMonth = $params['stock_amount_month'] ?? Carbon::now()->month;
            $stockAmountYear = $params['stock_amount_year'] ?? Carbon::now()->year;
            $stockQtyLimit = $params['stock_qty_limit'] ?? 5;
            $stockQtyChartType = $params['stock_qty_chart_type'] ?? 'bar';
            $stockAmountLimit = $params['stock_amount_limit'] ?? 5;
            $stockAmountChartType = $params['stock_amount_chart_type'] ?? 'bar';

            $stockQtyStartDate = Carbon::create($stockQtyYear, $stockQtyMonth, 1)->startOfMonth();
            $stockQtyEndDate = $stockQtyStartDate->copy()->endOfMonth();
            $stockAmountStartDate = Carbon::create($stockAmountYear, $stockAmountMonth, 1)->startOfMonth();
            $stockAmountEndDate = $stockAmountStartDate->copy()->endOfMonth();

            // Get monthly profit trend
            $profitTrend = [];
            $profitLabels = [];
            $profitTrendPeriod = $params['profit_trend_period'] ?? 'last_12_months';
            $profitTrendYear = $params['profit_trend_year'] ?? Carbon::now()->year;

            if ($profitTrendPeriod === 'yearly') {
                // Full year (January to December of selected year)
                $startDate = Carbon::create($profitTrendYear, 1, 1)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = $startDate->copy()->addMonths($i);
                    Log::debug('Processing profit month', ['date' => $date->toDateString()]);
                    $profitData = Cache::remember("profit_{$date->year}_{$date->month}", 60, function () use ($date) {
                        return $this->generalLedgerService->prepareIncomeStatementData([
                            'year' => $date->year,
                            'month' => $date->month,
                        ]) ?: ['labaBersih' => 0];
                    });
                    $profitTrend[] = $profitData['labaBersih'] ?? 0;
                    $profitLabels[] = $date->translatedFormat('M Y');
                }
            } else {
                // Last 12 months
                $currentDate = Carbon::now()->subMonths(11)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = $currentDate->copy()->addMonths($i);
                    Log::debug('Processing profit month', ['date' => $date->toDateString()]);
                    $profitData = Cache::remember("profit_{$date->year}_{$date->month}", 60, function () use ($date) {
                        return $this->generalLedgerService->prepareIncomeStatementData([
                            'year' => $date->year,
                            'month' => $date->month,
                        ]) ?: ['labaBersih' => 0];
                    });
                    $profitTrend[] = $profitData['labaBersih'] ?? 0;
                    $profitLabels[] = $date->translatedFormat('M Y');
                }
            }

            // Get monthly sales trend
            $salesTrend = [
                'sales_dagangan' => [],
                'sales_jadi' => [],
            ];
            $salesLabels = [];
            $salesTrendPeriod = $params['sales_trend_period'] ?? 'last_12_months';
            $salesTrendYear = $params['sales_trend_year'] ?? Carbon::now()->year;

            if ($salesTrendPeriod === 'yearly') {
                // Full year (January to December of selected year)
                $startDate = Carbon::create($salesTrendYear, 1, 1)->startOfMonth();
                for ($i = 0; $i < 12; $i++) {
                    $date = $startDate->copy()->addMonths($i);
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
                    $salesTrend['sales_dagangan'][] = $salesData['pendapatanPenjualanDagangan'] ?? 0;
                    $salesTrend['sales_jadi'][] = $salesData['pendapatanPenjualanJadi'] ?? 0;
                    $salesLabels[] = $date->translatedFormat('M Y');
                }
            } else {
                // Last 12 months
                $currentDate = Carbon::now()->subMonths(11)->startOfMonth();
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
                    $salesTrend['sales_dagangan'][] = $salesData['pendapatanPenjualanDagangan'] ?? 0;
                    $salesTrend['sales_jadi'][] = $salesData['pendapatanPenjualanJadi'] ?? 0;
                    $salesLabels[] = $date->translatedFormat('M Y');
                }
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
                    'labels' => $profitLabels,
                ],
                'sales_trend' => [
                    'data' => $salesTrend,
                    'labels' => $salesLabels,
                ],
                'stock_composition_qty' => [
                    'labels' => $stockCompositionQty->keys()->toArray(),
                    'data' => $stockCompositionQty->values()->toArray(),
                ],
                'stock_composition_amount' => [
                    'labels' => $stockCompositionAmount->keys()->toArray(),
                    'data' => $stockCompositionAmount->values()->toArray(),
                ],
                'profit_trend_period' => $profitTrendPeriod,
                'profit_trend_year' => $profitTrendYear,
                'sales_trend_period' => $salesTrendPeriod,
                'sales_trend_year' => $salesTrendYear,
                'stock_qty_month' => $stockQtyMonth,
                'stock_qty_year' => $stockQtyYear,
                'stock_amount_month' => $stockAmountMonth,
                'stock_amount_year' => $stockAmountYear,
                'stock_qty_limit' => $stockQtyLimit,
                'stock_qty_chart_type' => $stockQtyChartType,
                'stock_amount_limit' => $stockAmountLimit,
                'stock_amount_chart_type' => $stockAmountChartType,
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
