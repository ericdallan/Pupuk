<?php

namespace App\Services;

use App\Models\VoucherDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

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
    public function calculateNetProfit(Carbon $startDate, Carbon $endDate): array
    {
        $accountCategories = [
            'Pendapatan Penjualan Bahan Baku' => ['4.1.'],
            'Pendapatan Penjualan Barang Jadi' => ['4.2.'],
            'Harga Pokok Penjualan' => ['5.1.', '5.2.', '5.3.'],
            'Beban Operasional' => ['6.1.', '6.2.', '6.3.', '7.3'],
            'Pendapatan Lain-lain' => ['7.1.', '7.2.'],
        ];

        $totals = array_fill_keys(array_keys($accountCategories), 0);
        $details = array_fill_keys(array_keys($accountCategories), []);

        // Ambil data transaksi dengan JOIN langsung ke chart_of_accounts
        $voucherDetails = VoucherDetails::with(['voucher', 'chartOfAccount'])
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('voucher_date', [$startDate, $endDate]);
            })
            ->whereHas('chartOfAccount', function ($query) use ($accountCategories) {
                $query->where(function ($q) use ($accountCategories) {
                    foreach ($accountCategories as $prefixes) {
                        foreach ($prefixes as $prefix) {
                            $q->orWhere('account_code', 'like', $prefix . '%');
                        }
                    }
                });
            })
            ->select('voucher_details.account_code')
            ->selectRaw('SUM(credit - debit) as pendapatan_balance')
            ->selectRaw('SUM(debit - credit) as beban_balance')
            ->groupBy('account_code')
            ->get();

        // Proses data transaksi
        foreach ($voucherDetails as $detail) {
            $accountCode = $detail->account_code;
            $chartAccount = $detail->chartOfAccount;
            $subsection = $chartAccount ? $chartAccount->account_subsection : 'Unknown';

            // Cari kategori yang sesuai
            foreach ($accountCategories as $category => $prefixes) {
                $matched = false;
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        // Tentukan balance berdasarkan kategori
                        $balance = in_array($category, ['Pendapatan Penjualan Bahan Baku', 'Pendapatan Penjualan Barang Jadi', 'Pendapatan Lain-lain'])
                            ? $detail->pendapatan_balance
                            : $detail->beban_balance;

                        // Tambahkan ke total
                        $totals[$category] += $balance;

                        // Tambahkan ke detail per subsection (hindari duplikasi)
                        if (!isset($details[$category][$subsection])) {
                            $details[$category][$subsection] = 0;
                        }
                        $details[$category][$subsection] += $balance;

                        $matched = true;
                        break;
                    }
                }
                if ($matched) break; // Keluar dari loop kategori jika sudah cocok
            }
        }

        // Tambahkan subsection dengan nilai 0 untuk kategori yang tidak memiliki transaksi
        $allAccounts = DB::table('chart_of_accounts')
            ->where(function ($query) use ($accountCategories) {
                foreach ($accountCategories as $prefixes) {
                    foreach ($prefixes as $prefix) {
                        $query->orWhere('account_code', 'like', $prefix . '%');
                    }
                }
            })
            ->select('account_code', 'account_subsection')
            ->get();

        foreach ($allAccounts as $account) {
            $accountCode = $account->account_code;
            $subsection = $account->account_subsection ?? 'Unknown';

            foreach ($accountCategories as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (strpos($accountCode, $prefix) === 0) {
                        if (!isset($details[$category][$subsection])) {
                            $details[$category][$subsection] = 0;
                        }
                        break 2; // Keluar dari kedua loop
                    }
                }
            }
        }

        // Hitung komponen laba rugi
        $pendapatanPenjualanDagangan = $totals['Pendapatan Penjualan Bahan Baku'] ?? 0;
        $pendapatanPenjualanJadi = $totals['Pendapatan Penjualan Barang Jadi'] ?? 0;
        $pendapatanPenjualan = $pendapatanPenjualanDagangan + $pendapatanPenjualanJadi;
        $hpp = $totals['Harga Pokok Penjualan'] ?? 0;
        $totalBebanOperasional = $totals['Beban Operasional'] ?? 0;
        $totalPendapatanLain = $totals['Pendapatan Lain-lain'] ?? 0;

        // Hitung Beban Pajak Penghasilan
        $pphFinalRate = 0.005;
        $bebanPajakPenghasilan = $pendapatanPenjualan * $pphFinalRate;

        // Hitung laba rugi
        $labaKotor = $pendapatanPenjualan - $hpp;
        $labaOperasi = $labaKotor - $totalBebanOperasional;
        $labaSebelumPajak = $labaOperasi + $totalPendapatanLain;
        $labaBersih = $labaSebelumPajak - $bebanPajakPenghasilan;

        return [
            'pendapatanPenjualan' => $pendapatanPenjualan,
            'pendapatanPenjualanDagangan' => $pendapatanPenjualanDagangan,
            'pendapatanPenjualanJadi' => $pendapatanPenjualanJadi,
            'hpp' => $hpp,
            'labaKotor' => $labaKotor,
            'totalBebanOperasional' => $totalBebanOperasional,
            'labaOperasi' => $labaOperasi,
            'totalPendapatanLain' => $totalPendapatanLain,
            'labaSebelumPajak' => $labaSebelumPajak,
            'bebanPajakPenghasilan' => $bebanPajakPenghasilan,
            'labaBersih' => $labaBersih,
            'details' => $details,
        ];
    }

    /**
     * Prepare data for income statement
     *
     * @param array $data
     * @return array
     * @throws \Exception
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

            // Tentukan periode untuk data periodik
            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }

            // Tentukan periode untuk data kumulatif
            $cumulativeStartDate = Carbon::create($year, 1, 1)->startOfYear();
            $cumulativeEndDate = $month ? Carbon::create($year, $month, 1)->endOfMonth() : Carbon::create($year, 12, 31)->endOfYear();

            // Hitung data periodik
            $periodData = $this->calculateNetProfit($startDate, $endDate);

            // Hitung data kumulatif
            $cumulativeData = $this->calculateNetProfit($cumulativeStartDate, $cumulativeEndDate);

            // Format periodData untuk output konsisten
            $formattedPeriodData = collect([
                ['Keterangan' => 'Pendapatan Penjualan Bahan Baku', 'Jumlah (Rp)' => $periodData['pendapatanPenjualanDagangan']],
                ['Keterangan' => 'Pendapatan Penjualan Barang Jadi', 'Jumlah (Rp)' => $periodData['pendapatanPenjualanJadi']],
                ['Keterangan' => 'Total Pendapatan Penjualan', 'Jumlah (Rp)' => $periodData['pendapatanPenjualan']],
                ['Keterangan' => 'Harga Pokok Penjualan', 'Jumlah (Rp)' => -$periodData['hpp']],
                ['Keterangan' => 'Laba Kotor', 'Jumlah (Rp)' => $periodData['labaKotor']],
                ['Keterangan' => 'Beban Operasional', 'Jumlah (Rp)' => -$periodData['totalBebanOperasional']],
                ['Keterangan' => 'Laba Operasi', 'Jumlah (Rp)' => $periodData['labaOperasi']],
                ['Keterangan' => 'Pendapatan Lain-lain', 'Jumlah (Rp)' => $periodData['totalPendapatanLain']],
                ['Keterangan' => 'Laba Bersih', 'Jumlah (Rp)' => $periodData['labaSebelumPajak']], // Laba Bersih = Laba Sebelum Pajak
            ]);

            // Format cumulativeData untuk output konsisten
            $formattedCumulativeData = collect([
                ['Keterangan' => 'Pendapatan Penjualan Bahan Baku', 'Jumlah (Rp)' => $cumulativeData['pendapatanPenjualanDagangan']],
                ['Keterangan' => 'Pendapatan Penjualan Barang Jadi', 'Jumlah (Rp)' => $cumulativeData['pendapatanPenjualanJadi']],
                ['Keterangan' => 'Total Pendapatan Penjualan', 'Jumlah (Rp)' => $cumulativeData['pendapatanPenjualan']],
                ['Keterangan' => 'Harga Pokok Penjualan', 'Jumlah (Rp)' => -$cumulativeData['hpp']],
                ['Keterangan' => 'Laba Kotor', 'Jumlah (Rp)' => $cumulativeData['labaKotor']],
                ['Keterangan' => 'Beban Operasional', 'Jumlah (Rp)' => -$cumulativeData['totalBebanOperasional']],
                ['Keterangan' => 'Laba Operasi', 'Jumlah (Rp)' => $cumulativeData['labaOperasi']],
                ['Keterangan' => 'Pendapatan Lain-lain', 'Jumlah (Rp)' => $cumulativeData['totalPendapatanLain']],
                ['Keterangan' => 'Laba Bersih', 'Jumlah (Rp)' => $cumulativeData['labaSebelumPajak']], // Laba Bersih = Laba Sebelum Pajak
            ]);

            // Generate nama file
            $fileName = $month
                ? 'laporan_laba_rugi_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . $year . '.xlsx'
                : 'laporan_laba_rugi_' . $year . '.xlsx';

            return [
                'periodData' => $formattedPeriodData,
                'cumulativeData' => $formattedCumulativeData,
                'year' => $year,
                'month' => $month,
                'filename' => $fileName,
                'details' => [
                    'period' => $periodData['details'],
                    'cumulative' => $cumulativeData['details'],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error preparing Income Statement data: ' . $e->getMessage());
            throw $e;
        }
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
