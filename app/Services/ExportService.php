<?php

namespace App\Services;

use App\Models\VoucherDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ExportService
{
    /**
     * Prepare data for General Ledger export
     *
     * @param array $data
     * @return array
     */
    public function prepareGeneralLedgerData(array $data): array
    {
        $year = $data['year'] ?? date('Y');
        $month = $data['month'] ?? date('m');
        $selectedAccountName = $data['account_name'] ?? [];

        if (isset($data['account_name_hidden']) && !empty($data['account_name_hidden'])) {
            $selectedAccountName = explode(',', $data['account_name_hidden']);
        } elseif (in_array('', $selectedAccountName)) {
            $selectedAccountName = [];
        }

        $formattedMonth = sprintf("%02d", $month);
        $filename = 'General_Ledger_' . $formattedMonth . '-' . $year . '.xlsx';

        return [
            'month' => $month,
            'year' => $year,
            'selectedAccountName' => $selectedAccountName,
            'filename' => $filename,
        ];
    }

    /**
     * Prepare data for Income Statement export
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function prepareIncomeStatementData(array $data): array
    {
        $year = $data['year'] ?? date('Y');
        $month = $data['month'] ?? null;

        validator($data, [
            'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
            'month' => 'nullable|numeric|min:1|max:12',
        ])->validate();

        if ($month) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();
        }

        $cumulativeStartDate = Carbon::create($year, 1, 1)->startOfYear();
        $cumulativeEndDate = $month ? Carbon::create($year, $month, 1)->endOfMonth() : Carbon::create($year, 12, 31)->endOfYear();

        $accountCategories = [
            'Pendapatan Penjualan' => ['4.'],
            'Harga Pokok Penjualan' => ['5.1.', '5.2', '5.3'],
            'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
            'Pendapatan Lain-lain' => ['7.1.'],
            'Beban Lain-lain' => ['7.2.'],
            'Pajak Penghasilan' => ['7.3.'],
        ];

        $periodTotals = array_fill_keys(array_keys($accountCategories), 0);
        $cumulativeTotals = array_fill_keys(array_keys($accountCategories), 0);

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

        foreach ($voucherDetails as $detail) {
            $accountCode = $detail->account_code;
            foreach ($accountCategories as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        if (in_array($category, ['Pendapatan Penjualan', 'Pendapatan Lain-lain'])) {
                            if ($category === 'Pendapatan Lain-lain' && strpos($accountCode, '7.1.02.') === 0) {
                                continue;
                            }
                            $periodTotals[$category] += $detail->pendapatan_balance;
                        } else {
                            $periodTotals[$category] += $detail->beban_balance;
                        }
                        break;
                    }
                }
            }
        }

        $cumulativeVoucherDetails = VoucherDetails::with('voucher')
            ->whereHas('voucher', function ($query) use ($cumulativeStartDate, $cumulativeEndDate) {
                $query->whereBetween('voucher_date', [$cumulativeStartDate, $cumulativeEndDate]);
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

        foreach ($cumulativeVoucherDetails as $detail) {
            $accountCode = $detail->account_code;
            foreach ($accountCategories as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        if (in_array($category, ['Pendapatan Penjualan', 'Pendapatan Lain-lain'])) {
                            if ($category === 'Pendapatan Lain-lain' && strpos($accountCode, '7.1.02.') === 0) {
                                continue;
                            }
                            $cumulativeTotals[$category] += $detail->pendapatan_balance;
                        } else {
                            $cumulativeTotals[$category] += $detail->beban_balance;
                        }
                        break;
                    }
                }
            }
        }

        $pendapatanPenjualan = $periodTotals['Pendapatan Penjualan'] ?? 0;
        $hpp = $periodTotals['Harga Pokok Penjualan'] ?? 0;
        $totalBebanOperasional = $periodTotals['Beban Operasional'] ?? 0;
        $totalPendapatanLain = $periodTotals['Pendapatan Lain-lain'] ?? 0;
        $totalBebanLain = $periodTotals['Beban Lain-lain'] ?? 0;
        $totalBebanPajak = $periodTotals['Pajak Penghasilan'] ?? 0;

        $labaKotor = $pendapatanPenjualan - $hpp;
        $labaOperasi = $labaKotor - $totalBebanOperasional;
        $labaSebelumPajak = $labaOperasi + $totalPendapatanLain - $totalBebanLain;
        $labaBersih = $labaSebelumPajak - $totalBebanPajak;

        $periodData = collect([
            ['Keterangan' => 'Pendapatan Penjualan', 'Jumlah (Rp)' => $pendapatanPenjualan],
            ['Keterangan' => 'Harga Pokok Penjualan', 'Jumlah (Rp)' => -$hpp],
            ['Keterangan' => 'Laba Kotor', 'Jumlah (Rp)' => $labaKotor],
            ['Keterangan' => 'Beban Operasional', 'Jumlah (Rp)' => -$totalBebanOperasional],
            ['Keterangan' => 'Total Beban Operasional', 'Jumlah (Rp)' => -$totalBebanOperasional],
            ['Keterangan' => 'Laba Operasi', 'Jumlah (Rp)' => $labaOperasi],
            ['Keterangan' => 'Pendapatan Lain-lain', 'Jumlah (Rp)' => $totalPendapatanLain],
            ['Keterangan' => 'Beban Lain-lain', 'Jumlah (Rp)' => -$totalBebanLain],
            ['Keterangan' => 'Laba Sebelum Pajak', 'Jumlah (Rp)' => $labaSebelumPajak],
            ['Keterangan' => 'Pajak Penghasilan', 'Jumlah (Rp)' => -$totalBebanPajak],
            ['Keterangan' => 'Laba Bersih', 'Jumlah (Rp)' => $labaBersih],
        ]);

        $cumulativePendapatanPenjualan = $cumulativeTotals['Pendapatan Penjualan'] ?? 0;
        $cumulativeHpp = $cumulativeTotals['Harga Pokok Penjualan'] ?? 0;
        $cumulativeTotalBebanOperasional = $cumulativeTotals['Beban Operasional'] ?? 0;
        $cumulativeTotalPendapatanLain = $cumulativeTotals['Pendapatan Lain-lain'] ?? 0;
        $cumulativeTotalBebanLain = $cumulativeTotals['Beban Lain-lain'] ?? 0;
        $cumulativeTotalBebanPajak = $cumulativeTotals['Pajak Penghasilan'] ?? 0;

        $cumulativeLabaKotor = $cumulativePendapatanPenjualan - $cumulativeHpp;
        $cumulativeLabaOperasi = $cumulativeLabaKotor - $cumulativeTotalBebanOperasional;
        $cumulativeLabaSebelumPajak = $cumulativeLabaOperasi + $cumulativeTotalPendapatanLain - $cumulativeTotalBebanLain;
        $cumulativeLabaBersih = $cumulativeLabaSebelumPajak - $cumulativeTotalBebanPajak;

        $cumulativeData = collect([
            ['Keterangan' => 'Pendapatan Penjualan', 'Jumlah (Rp)' => $cumulativePendapatanPenjualan],
            ['Keterangan' => 'Harga Pokok Penjualan', 'Jumlah (Rp)' => -$cumulativeHpp],
            ['Keterangan' => 'Laba Kotor', 'Jumlah (Rp)' => $cumulativeLabaKotor],
            ['Keterangan' => 'Beban Operasional', 'Jumlah (Rp)' => -$cumulativeTotalBebanOperasional],
            ['Keterangan' => 'Total Beban Operasional', 'Jumlah (Rp)' => -$cumulativeTotalBebanOperasional],
            ['Keterangan' => 'Laba Operasi', 'Jumlah (Rp)' => $cumulativeLabaOperasi],
            ['Keterangan' => 'Pendapatan Lain-lain', 'Jumlah (Rp)' => $cumulativeTotalPendapatanLain],
            ['Keterangan' => 'Beban Lain-lain', 'Jumlah (Rp)' => -$cumulativeTotalBebanLain],
            ['Keterangan' => 'Laba Sebelum Pajak', 'Jumlah (Rp)' => $cumulativeLabaSebelumPajak],
            ['Keterangan' => 'Pajak Penghasilan', 'Jumlah (Rp)' => -$cumulativeTotalBebanPajak],
            ['Keterangan' => 'Laba Bersih', 'Jumlah (Rp)' => $cumulativeLabaBersih],
        ]);

        $fileName = $month
            ? 'laporan_laba_rugi_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . $year . '.xlsx'
            : 'laporan_laba_rugi_' . $year . '.xlsx';

        return [
            'periodData' => $periodData,
            'cumulativeData' => $cumulativeData,
            'year' => $year,
            'month' => $month,
            'filename' => $fileName,
        ];
    }

    /**
     * Prepare data for Trial Balance export
     *
     * @param array $data
     * @return array
     */
    public function prepareTrialBalanceData(array $data): array
    {
        $month = $data['month'] ?? now()->month;
        $year = $data['year'] ?? now()->year;

        $accountBalances = $this->getAccountBalances($month, $year);
        $accountNames = $this->getAccountNames();

        $filename = 'neraca_saldo_' . date('Y-m') . '.xlsx';

        return [
            'month' => $month,
            'year' => $year,
            'accountBalances' => $accountBalances,
            'accountNames' => $accountNames,
            'filename' => $filename,
        ];
    }

    /**
     * Prepare data for Balance Sheet export
     *
     * @param array $data
     * @return array
     */
    public function prepareBalanceSheetData(array $data): array
    {
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

        $accountCategories = [
            'Pendapatan Penjualan' => ['4.'],
            'Harga Pokok Penjualan' => ['5.1.', '5.2.', '5.3.'],
            'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
            'Pendapatan Lain-lain' => ['7.1.'],
            'Beban Lain-lain' => ['7.2.'],
            'Pajak Penghasilan' => ['7.3.'],
        ];

        $totals = [
            'Pendapatan Penjualan' => 0,
            'Harga Pokok Penjualan' => 0,
            'Beban Operasional' => 0,
            'Pendapatan Lain-lain' => 0,
            'Beban Lain-lain' => 0,
            'Pajak Penghasilan' => 0,
        ];

        $voucherDetails = VoucherDetails::with('voucher')
            ->whereHas('voucher', function ($query) use ($start, $end) {
                $query->whereBetween('voucher_date', [$start, $end]);
            })
            ->select('account_code')
            ->selectRaw('SUM(credit - debit) as pendapatan_balance')
            ->selectRaw('SUM(debit - credit) as beban_balance')
            ->groupBy('account_code')
            ->get();

        foreach ($voucherDetails as $detail) {
            $accountCode = $detail->account_code;
            foreach ($accountCategories as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        if (in_array($category, ['Pendapatan Penjualan', 'Pendapatan Lain-lain'])) {
                            if ($category === 'Pendapatan Lain-lain' && strpos($accountCode, '7.1.02.') === 0) {
                                continue;
                            }
                            $totals[$category] += $detail->pendapatan_balance;
                        } else {
                            $totals[$category] += $detail->beban_balance;
                        }
                        break;
                    }
                }
            }
        }

        $pendapatanPenjualan = $totals['Pendapatan Penjualan'];
        $hpp = $totals['Harga Pokok Penjualan'];
        $totalBebanOperasional = $totals['Beban Operasional'];
        $totalPendapatanLain = $totals['Pendapatan Lain-lain'];
        $totalBebanLain = $totals['Beban Lain-lain'];
        $totalBebanPajak = $totals['Pajak Penghasilan'];

        $labaKotor = $pendapatanPenjualan - $hpp;
        $labaOperasi = $labaKotor - $totalBebanOperasional;
        $labaSebelumPajak = $labaOperasi + $totalPendapatanLain - $totalBebanLain;
        $labaBersih = $labaSebelumPajak - $totalBebanPajak;

        $labaDitahanKey = 'Laba Ditahan';
        $labaRugiDitahanKey = 'Laba Rugi Ditahan';

        $saldoLabaDitahan = $ekuitasData[$labaDitahanKey]->saldo ?? 0;

        if (!isset($ekuitasData[$labaRugiDitahanKey])) {
            $ekuitasData[$labaRugiDitahanKey] = (object)['account_name' => $labaRugiDitahanKey, 'saldo' => 0];
        }

        $ekuitasData[$labaRugiDitahanKey]->saldo += $saldoLabaDitahan;
        $ekuitasData[$labaRugiDitahanKey]->saldo += $labaBersih;

        unset($ekuitasData[$labaDitahanKey]);

        $filteredEkuitasData = $ekuitasData->reject(function ($item, $key) {
            return in_array($key, ['Pengambilan Oleh Pemilik', 'Saldo laba']);
        });

        return [
            'asetLancarData' => $asetLancarData,
            'asetTetapData' => $asetTetapData,
            'kewajibanData' => $kewajibanData,
            'filteredEkuitasData' => $filteredEkuitasData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filename' => 'balance_sheet.xlsx',
        ];
    }

    /**
     * Fetch account balances for a given month and year
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    protected function getAccountBalances(int $month, int $year): array
    {
        return DB::table('voucher_details')
            ->selectRaw('account_code, SUM(debit - credit) as balance')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('account_code')
            ->pluck('balance', 'account_code')
            ->toArray();
    }

    /**
     * Fetch account names from chart of accounts
     *
     * @return array
     */
    protected function getAccountNames(): array
    {
        return DB::table('chart_of_accounts')->pluck('account_name', 'account_code')->toArray();
    }
}
