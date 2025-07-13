<?php

namespace App\Services;

use App\Models\VoucherDetails;
use App\Models\ChartOfAccount;
use App\Services\GeneralLedgerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    protected $generalLedgerService;

    public function __construct(GeneralLedgerService $generalLedgerService)
    {
        $this->generalLedgerService = $generalLedgerService;
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
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Set default period (current month)
            $startDate = isset($params['start_date']) ? Carbon::parse($params['start_date']) : Carbon::now()->startOfMonth();
            $endDate = isset($params['end_date']) ? Carbon::parse($params['end_date']) : Carbon::now()->endOfMonth();

            // Limit range to 90 days to avoid performance issues
            if ($startDate->diffInDays($endDate) > 90) {
                $endDate = $startDate->copy()->addDays(90);
            }

            // Get income statement data
            $incomeStatementData = $this->generalLedgerService->prepareIncomeStatementData([
                'year' => $startDate->year,
                'month' => $startDate->month,
            ]);

            // Get balance sheet data (for summary cards only)
            $balanceSheetData = $this->generalLedgerService->prepareBalanceSheetData([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Get trial balance data
            $trialBalanceData = $this->generalLedgerService->prepareTrialBalanceData([
                'year' => $startDate->year,
                'month' => $startDate->month,
            ]);

            // Log trial balance data for debugging
            Log::debug('Trial Balance Data', [
                'accountBalances' => $trialBalanceData['accountBalances'] ?? [],
                'accountNames' => $trialBalanceData['accountNames'] ?? [],
            ]);

            // Get recent transactions (top 5)
            $generalLedgerData = $this->generalLedgerService->prepareGeneralLedgerData([
                'year' => $startDate->year,
                'month' => $startDate->month,
            ]);
            $recentTransactions = $generalLedgerData['voucherDetails']
                ->take(5)
                ->map(function ($detail) {
                    return [
                        'voucher_date' => $detail->voucher ? $detail->voucher->voucher_date : null,
                        'account_name' => $detail->account_name ?? 'Unknown',
                        'debit' => $detail->debit ?? 0,
                        'credit' => $detail->credit ?? 0,
                    ];
                });

            // Get monthly profit trend (last 6 months)
            $profitTrend = [];
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::create($startDate->year, $startDate->month, 1)->subMonths($i);
                $profitData = Cache::remember("profit_{$date->year}_{$date->month}", 3600, function () use ($date) {
                    return $this->generalLedgerService->prepareIncomeStatementData([
                        'year' => $date->year,
                        'month' => $date->month,
                    ]);
                });
                $profitTrend[] = $profitData['labaBersih'] ?? 0;
                $labels[] = $date->format('M Y');
            }

            // Get daily profit
            $dailyProfit = [];
            $dailyLabels = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $profitData = Cache::remember("daily_profit_{$currentDate->format('Y-m-d')}", 3600, function () use ($currentDate) {
                    return $this->generalLedgerService->calculateNetProfit(
                        $currentDate->copy()->startOfDay(),
                        $currentDate->copy()->endOfDay()
                    );
                });
                $dailyProfit[] = $profitData['labaBersih'] ?? 0;
                $dailyLabels[] = $currentDate->format('d M Y');
                $currentDate->addDay();
            }

            // Operating Expenses
            $operatingExpenses = Cache::remember("operating_expenses_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}", 3600, function () use ($startDate, $endDate) {
                $result = VoucherDetails::with('voucher')
                    ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('voucher_date', [$startDate, $endDate]);
                    })
                    ->whereIn('voucher_details.account_code', function ($query) {
                        $query->select('account_code')
                            ->from('chart_of_accounts')
                            ->where('account_code', 'like', '6.%');
                    })
                    ->join('chart_of_accounts', 'voucher_details.account_code', '=', 'chart_of_accounts.account_code')
                    ->select('chart_of_accounts.account_name', DB::raw('SUM(voucher_details.debit - voucher_details.credit) as total'))
                    ->groupBy('chart_of_accounts.account_name')
                    ->get();
                $mapped = $result->isEmpty() ? collect([]) : $result->mapWithKeys(function ($item) {
                    return [$item->account_name => abs($item->total)]; // Use abs() to ensure positive values for pie chart
                });
                // Log for debugging
                Log::debug('Operating Expenses Data', [
                    'result' => $result->toArray(),
                    'mapped' => $mapped->toArray(),
                ]);
                return $mapped->isEmpty() ? collect(['No Data' => 0]) : $mapped; // Fallback for empty data
            });

            // Pendapatan vs Beban
            $pendapatanVsBeban = [
                'labels' => ['Pendapatan Penjualan', 'Pendapatan Lain', 'HPP', 'Beban Operasional', 'Beban Lain', 'Pajak'],
                'data' => [
                    $incomeStatementData['pendapatanPenjualan'] ?? 0,
                    $incomeStatementData['totalPendapatanLain'] ?? 0,
                    $incomeStatementData['hpp'] ?? 0,
                    $incomeStatementData['totalBebanOperasional'] ?? 0,
                    $incomeStatementData['totalBebanLain'] ?? 0,
                    $incomeStatementData['totalBebanPajak'] ?? 0,
                ],
            ];

            // Saldo Akun Utama
            $keyAccounts = [
                '1.1.03.01' => 'Piutang Usaha',
                '2.1.01.01' => 'Utang Usaha',
                '1.1.01' => 'Kas',
                '1.1.05' => 'Persediaan',
            ];
            $saldoAkunUtama = collect($trialBalanceData['accountBalances'] ?? [])
                ->filter(function ($value, $key) use ($keyAccounts) {
                    foreach ($keyAccounts as $accountCode => $accountName) {
                        if (strpos($key, $accountCode) === 0) {
                            return true;
                        }
                    }
                    return false;
                })
                ->mapWithKeys(function ($value, $key) use ($keyAccounts) {
                    foreach ($keyAccounts as $accountCode => $accountName) {
                        if (strpos($key, $accountCode) === 0) {
                            return [$accountName => $value];
                        }
                    }
                    return [];
                })->filter();
            // Log for debugging
            Log::debug('Saldo Akun Utama Data', [
                'saldoAkunUtama' => $saldoAkunUtama->toArray(),
            ]);
            // Fallback for empty data
            if ($saldoAkunUtama->isEmpty()) {
                $saldoAkunUtama = collect(['No Data' => 0]);
            }

            // Transactions per Category
            $transactionCategories = Cache::remember("transactions_per_category_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}", 3600, function () use ($startDate, $endDate) {
                $result = VoucherDetails::with('voucher')
                    ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('voucher_date', [$startDate, $endDate]);
                    })
                    ->join('chart_of_accounts', 'voucher_details.account_code', '=', 'chart_of_accounts.account_code')
                    ->select('chart_of_accounts.account_type', DB::raw('SUM(voucher_details.debit + voucher_details.credit) as total'))
                    ->groupBy('chart_of_accounts.account_type')
                    ->get();
                $mapped = $result->isEmpty() ? collect([]) : $result->mapWithKeys(function ($item) {
                    return [$item->account_type ?? 'Unknown' => $item->total];
                });
                return $mapped->isEmpty() ? collect(['No Data' => 0]) : $mapped;
            });
            $cashFlow = Cache::remember("cash_flow_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}", 3600, function () use ($startDate, $endDate) {
                $result = VoucherDetails::with('voucher')
                    ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('voucher_date', [$startDate, $endDate]);
                    })
                    ->where('voucher_details.account_code', 'like', '1.1.01.%')
                    ->join('chart_of_accounts', 'voucher_details.account_code', '=', 'chart_of_accounts.account_code')
                    ->select('chart_of_accounts.account_name', DB::raw('SUM(voucher_details.debit - voucher_details.credit) as total'))
                    ->groupBy('chart_of_accounts.account_name')
                    ->get();
                return $result->isEmpty() ? collect(['No Cash Flow Data' => 0]) : $result->mapWithKeys(function ($item) {
                    return [$item->account_name => $item->total];
                });
            });

            // Format data for visualization
            return [
                'income_statement' => [
                    'pendapatan_penjualan' => $incomeStatementData['pendapatanPenjualan'] ?? 0,
                    'laba_kotor' => $incomeStatementData['labaKotor'] ?? 0,
                    'laba_bersih' => $incomeStatementData['labaBersih'] ?? 0,
                    'total_beban_operasional' => $incomeStatementData['totalBebanOperasional'] ?? 0,
                    'total_pendapatan_lain' => $incomeStatementData['totalPendapatanLain'] ?? 0,
                    'total_beban_lain' => $incomeStatementData['totalBebanLain'] ?? 0,
                    'total_beban_pajak' => $incomeStatementData['totalBebanPajak'] ?? 0,
                    'year' => $startDate->year,
                    'month' => $startDate->month,
                ],
                'balance_sheet' => [
                    'aset_lancar' => $balanceSheetData['asetLancarData']->sum('saldo') ?? 0,
                    'aset_tetap' => $balanceSheetData['asetTetapData']->sum('saldo') ?? 0,
                    'kewajiban' => $balanceSheetData['kewajibanData']->sum('saldo') ?? 0,
                    'ekuitas' => $balanceSheetData['ekuitasData']->sum('saldo') ?? 0,
                    'laba_bersih' => $balanceSheetData['labaBersih'] ?? 0,
                ],
                'trial_balance' => [
                    'account_balances' => $trialBalanceData['accountBalances'] ?? [],
                    'account_names' => $trialBalanceData['accountNames'] ?? [],
                    'key_accounts' => [
                        'piutang_usaha' => ($trialBalanceData['accountBalances'] ?? [])['1.1.03.01'] ?? 0,
                        'utang_usaha' => ($trialBalanceData['accountBalances'] ?? [])['2.1.01.01'] ?? 0,
                    ],
                ],
                'recent_transactions' => $recentTransactions,
                'profit_trend' => [
                    'data' => $profitTrend,
                    'labels' => $labels,
                ],
                'daily_profit' => [
                    'data' => $dailyProfit,
                    'labels' => $dailyLabels,
                ],
                'operating_expenses' => [
                    'labels' => $operatingExpenses->keys()->toArray(),
                    'data' => $operatingExpenses->values()->toArray(),
                ],
                'pendapatan_vs_beban' => $pendapatanVsBeban,
                'saldo_akun_utama' => [
                    'labels' => $saldoAkunUtama->keys()->toArray(),
                    'data' => $saldoAkunUtama->values()->toArray(),
                ],
                'transactions_per_category' => [
                    'labels' => $transactionCategories->keys()->toArray(),
                    'data' => $transactionCategories->values()->toArray(),
                ],
                'cash_flow' => [
                    'labels' => $cashFlow->keys()->toArray(),
                    'data' => $cashFlow->values()->toArray(),
                ],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching dashboard data: ' . $e->getMessage());
            throw $e;
        }
    }
}
