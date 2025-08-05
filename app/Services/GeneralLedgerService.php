<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Subsidiary;
use App\Models\VoucherDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class GeneralLedgerService
{
    /**
     * Calculate net profit (laba bersih) for a given date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function calculateNetProfit(Carbon $startDate, Carbon $endDate): array
    {
        $accountCategories = [
            'Pendapatan Penjualan Bahan Baku' => ['4.1.'],
            'Pendapatan Penjualan Barang Jadi' => ['4.2.'],
            'Harga Pokok Penjualan' => ['5.1.', '5.2.', '5.3.'],
            'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
            'Pendapatan Lain-lain' => ['7.1.'],
            'Beban Lain-lain' => ['7.2.'],
            'Pajak Penghasilan' => ['7.3.'],
        ];

        $totals = array_fill_keys(array_keys($accountCategories), 0);

        $voucherDetails = VoucherDetails::with('voucher')
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('voucher_date', [$startDate, $endDate]);
            })
            ->select('account_code')
            ->selectRaw('SUM(credit - debit) as pendapatan_balance')
            ->selectRaw('SUM(debit - credit) as beban_balance')
            ->where(function ($query) use ($accountCategories) {
                foreach ($accountCategories as $prefixes) {
                    foreach ($prefixes as $prefix) {
                        $query->orWhere('account_code', 'like', $prefix . '%');
                    }
                }
            })
            ->groupBy('account_code')
            ->get();

        // Debug each account code and its balance
        foreach ($voucherDetails as $detail) {
            $accountCode = $detail->account_code;
            Log::debug('Processing account code', ['account_code' => $accountCode, 'pendapatan_balance' => $detail->pendapatan_balance, 'beban_balance' => $detail->beban_balance]);
            foreach ($accountCategories as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        if (in_array($category, ['Pendapatan Penjualan Bahan Baku', 'Pendapatan Penjualan Barang Jadi', 'Pendapatan Lain-lain'])) {
                            $totals[$category] += $detail->pendapatan_balance;
                        } else {
                            $totals[$category] += $detail->beban_balance;
                        }
                        break;
                    }
                }
            }
        }

        $pendapatanPenjualanDagangan = $totals['Pendapatan Penjualan Bahan Baku'] ?? 0;
        $pendapatanPenjualanJadi = $totals['Pendapatan Penjualan Barang Jadi'] ?? 0;
        $pendapatanPenjualan = $pendapatanPenjualanDagangan + $pendapatanPenjualanJadi;
        $hpp = $totals['Harga Pokok Penjualan'] ?? 0;
        $totalBebanOperasional = $totals['Beban Operasional'] ?? 0;
        $totalPendapatanLain = $totals['Pendapatan Lain-lain'] ?? 0;
        $totalBebanLain = $totals['Beban Lain-lain'] ?? 0;
        $totalBebanPajak = $totals['Pajak Penghasilan'] ?? 0;

        $labaKotor = $pendapatanPenjualan - $hpp;
        $labaOperasi = $labaKotor - $totalBebanOperasional;
        $labaSebelumPajak = $labaOperasi + $totalPendapatanLain - $totalBebanLain;
        $labaBersih = $labaSebelumPajak - $totalBebanPajak;

        Log::debug('Calculated Totals', [
            'pendapatanPenjualanDagangan' => $pendapatanPenjualanDagangan,
            'pendapatanPenjualanJadi' => $pendapatanPenjualanJadi,
            'pendapatanPenjualan' => $pendapatanPenjualan,
        ]);

        return [
            'pendapatanPenjualan' => $pendapatanPenjualan,
            'pendapatanPenjualanDagangan' => $pendapatanPenjualanDagangan,
            'pendapatanPenjualanJadi' => $pendapatanPenjualanJadi,
            'hpp' => $hpp,
            'labaKotor' => $labaKotor,
            'totalBebanOperasional' => $totalBebanOperasional,
            'labaOperasi' => $labaOperasi,
            'totalPendapatanLain' => $totalPendapatanLain,
            'totalBebanLain' => $totalBebanLain,
            'labaSebelumPajak' => $labaSebelumPajak,
            'totalBebanPajak' => $totalBebanPajak,
            'labaBersih' => $labaBersih,
        ];
    }

    /**
     * Prepare data for General Ledger page
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function prepareGeneralLedgerData(array $data): array
    {
        try {
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');
            $selectedAccountName = $data['account_name'] ?? [];

            if (isset($data['account_name_hidden']) && !empty($data['account_name_hidden'])) {
                $selectedAccountName = explode(',', $data['account_name_hidden']);
            } elseif (in_array('', $selectedAccountName)) {
                $selectedAccountName = [];
            }

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $excludedPrefixes = ['1.1.03.01.', '2.1.01.01.'];
            $excludedAccountCodes = ChartOfAccount::where(function ($query) use ($excludedPrefixes) {
                foreach ($excludedPrefixes as $prefix) {
                    $query->orWhere('account_code', 'like', $prefix . '%');
                }
            })
                ->whereRaw('LENGTH(account_code) > ?', [strlen($excludedPrefixes[0])])
                ->pluck('account_code')
                ->toArray();

            $voucherDetailsQuery = VoucherDetails::with('voucher')
                ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('voucher_date', [$startDate, $endDate]);
                })
                ->whereNotIn('account_code', $excludedAccountCodes);

            if (!empty($selectedAccountName)) {
                $voucherDetailsQuery->whereIn('account_name', $selectedAccountName);
            }

            $voucherDetails = $voucherDetailsQuery->orderBy('account_code')
                ->orderBy('voucher_id')
                ->get();

            $subsidiariesMap = Subsidiary::pluck('account_code', 'subsidiary_code')->toArray();

            $voucherDetails = $voucherDetails->map(function ($detail) use ($subsidiariesMap) {
                if (isset($subsidiariesMap[$detail->account_code])) {
                    $detail->account_code = $subsidiariesMap[$detail->account_code];
                }
                return $detail;
            });

            $accountCodesInVoucherDetails = $voucherDetails->pluck('account_code')->unique()->toArray();

            $accountNames = ChartOfAccount::whereIn('account_code', $accountCodesInVoucherDetails)
                ->pluck('account_name', 'account_code')
                ->toArray();

            $voucherDetails = $voucherDetails->map(function ($detail) use ($accountNames) {
                if (isset($accountNames[$detail->account_code])) {
                    $detail->account_name = $accountNames[$detail->account_code];
                }
                return $detail;
            });

            $availableAccountNames = ChartOfAccount::whereNotIn('account_code', $excludedAccountCodes)
                ->pluck('account_name')
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            return [
                'voucherDetails' => $voucherDetails,
                'year' => $year,
                'month' => $month,
                'availableAccountNames' => $availableAccountNames,
                'selectedAccountName' => $selectedAccountName,
            ];
        } catch (\Exception $e) {
            Log::error('Error preparing General Ledger data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare data for Trial Balance page
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function prepareTrialBalanceData(array $data): array
    {
        try {
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? date('m');

            if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
                throw new \Exception('Invalid year or month');
            }

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $accountCodesMap = [
                'Piutang Usaha' => '1.1.03.01',
                'Utang Usaha' => '2.1.01.01',
            ];

            $voucherDetails = VoucherDetails::with('voucher')
                ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('voucher_date', [$startDate, $endDate]);
                })
                ->get();

            $accountBalances = [];

            foreach ($voucherDetails as $detail) {
                $accountCode = $detail->account_code;

                foreach ($accountCodesMap as $accountName => $prefix) {
                    if (strpos($accountCode, $prefix . '.') === 0) {
                        $accountCode = $prefix;
                        break;
                    }
                }

                if (!isset($accountBalances[$accountCode])) {
                    $accountBalances[$accountCode] = 0;
                }

                $accountBalances[$accountCode] += $detail->debit - $detail->credit;
            }

            $accountNames = ChartOfAccount::whereIn('account_code', array_keys($accountBalances))
                ->pluck('account_name', 'account_code')
                ->toArray();

            foreach ($accountCodesMap as $accountName => $accountCode) {
                if (isset($accountBalances[$accountCode])) {
                    $accountNames[$accountCode] = $accountName;
                }
            }

            ksort($accountBalances);

            $accountBalances = collect($accountBalances);

            return [
                'accountBalances' => $accountBalances,
                'accountNames' => $accountNames,
                'year' => $year,
                'month' => $month,
            ];
        } catch (\Exception $e) {
            Log::error('Error preparing Trial Balance data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare data for Income Statement page
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public function prepareIncomeStatementData(array $data): array
    {
        try {
            $year = $data['year'] ?? date('Y');
            $month = $data['month'] ?? null;

            $validator = Validator::make($data, [
                'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
                'month' => 'nullable|numeric|min:1|max:12',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }

            $profitData = $this->calculateNetProfit($startDate, $endDate);

            return array_merge($profitData, [
                'year' => $year,
                'month' => $month,
            ]);
        } catch (\Exception $e) {
            Log::error('Error preparing Income Statement data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepare data for Balance Sheet page
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function prepareBalanceSheetData(array $data): array
    {
        try {
            $startDate = $data['start_date'] ?? null;
            $endDate = $data['end_date'] ?? null;

            $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfYear();
            $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfYear();

            $asetLancarData = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'ASET')
                ->where('chart_of_accounts.account_section', 'Aset Lancar')
                ->whereBetween('vouchers.voucher_date', [$start, $end])
                ->select(
                    'chart_of_accounts.account_subsection as account_name',
                    DB::raw('SUM(voucher_details.debit - voucher_details.credit) as saldo')
                )
                ->groupBy('chart_of_accounts.account_subsection')
                ->get()
                ->keyBy('account_name');

            $asetTetapData = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'ASET')
                ->where('chart_of_accounts.account_section', 'Aset Tetap')
                ->whereBetween('vouchers.voucher_date', [$start, $end])
                ->select(
                    'chart_of_accounts.account_subsection as account_name',
                    DB::raw('SUM(voucher_details.debit - voucher_details.credit) as saldo')
                )
                ->groupBy('chart_of_accounts.account_subsection')
                ->get()
                ->keyBy('account_name');

            $kewajibanData = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'KEWAJIBAN')
                ->whereBetween('vouchers.voucher_date', [$start, $end])
                ->select(
                    'chart_of_accounts.account_subsection as account_name',
                    DB::raw('SUM(voucher_details.credit - voucher_details.debit) as saldo')
                )
                ->groupBy('chart_of_accounts.account_subsection')
                ->get()
                ->keyBy('account_name');

            $ekuitasData = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'EKUITAS')
                ->whereBetween('vouchers.voucher_date', [$start, $end])
                ->select(
                    'chart_of_accounts.account_subsection as account_name',
                    DB::raw('SUM(voucher_details.credit - voucher_details.debit) as saldo')
                )
                ->groupBy('chart_of_accounts.account_subsection')
                ->get()
                ->keyBy('account_name');

            $profitData = $this->calculateNetProfit($start, $end);
            $labaBersih = $profitData['labaBersih'];

            if (isset($ekuitasData['Laba Rugi Ditahan'])) {
                $labaDitahanSaldo = $ekuitasData['Laba Rugi Ditahan']->saldo ?? 0;
                $labaDitahanSaldo += $labaBersih;
                $ekuitasData['Laba Rugi Ditahan']->saldo = $labaDitahanSaldo;
            } else {
                $ekuitasData['Laba Rugi Ditahan'] = (object) ['account_name' => 'Laba Rugi Ditahan', 'saldo' => $labaBersih];
            }

            $allAset = DB::table('chart_of_accounts')
                ->where('account_type', 'ASET')
                ->select('account_subsection as account_name', 'account_section')
                ->distinct()
                ->get()
                ->groupBy('account_section');

            $allKewajiban = DB::table('chart_of_accounts')
                ->where('account_type', 'KEWAJIBAN')
                ->select('account_subsection as account_name')
                ->distinct()
                ->get();

            $allEkuitas = DB::table('chart_of_accounts')
                ->where('account_type', 'EKUITAS')
                ->whereNotIn('account_subsection', ['Pengambilan Oleh Pemilik', 'Saldo laba'])
                ->select('account_subsection as account_name')
                ->distinct()
                ->get();

            return [
                'allAset' => $allAset,
                'asetLancarData' => $asetLancarData,
                'asetTetapData' => $asetTetapData,
                'allKewajiban' => $allKewajiban,
                'kewajibanData' => $kewajibanData,
                'allEkuitas' => $allEkuitas,
                'ekuitasData' => $ekuitasData,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'labaBersih' => $labaBersih,
            ];
        } catch (\Exception $e) {
            Log::error('Error preparing Balance Sheet data: ' . $e->getMessage());
            throw $e;
        }
    }
}
