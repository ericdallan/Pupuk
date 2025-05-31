<?php

namespace App\Services;

use App\Models\VoucherDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZakatService
{
    /**
     * Prepare data for the zakat page
     *
     * @param Request|null $request
     * @return array
     */
    public function prepareZakatPageData(?Request $request): array
    {
        $year = $request ? $request->input('year', date('Y')) : date('Y');
        $month = $request ? $request->input('month', null) : null;
        $calculation_method = $request ? $request->input('calculation_method', 'cara1') : 'cara1';

        return compact('year', 'month', 'calculation_method');
    }

    /**
     * Calculate zakat based on the provided request
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function calculateZakat(Request $request): array
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', null);
        $calculation_method = $request->input('calculation_method', 'cara1');

        if ($month) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        } else {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();
        }

        $totalAktivaLancar = 0;
        $totalHutangLancar = 0;
        $selisih = 0;
        $zakatCara1 = 0;
        $zakatWajibCara1 = false;
        $labaBersih = 0;
        $zakatCara2 = 0;
        $zakatWajibCara2 = false;

        if ($calculation_method === 'cara1' || $calculation_method === 'both') {
            $aktivaLancar = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'ASET')
                ->where('chart_of_accounts.account_section', 'Aset Lancar')
                ->whereBetween('vouchers.voucher_date', [$startDate, $endDate])
                ->selectRaw('SUM(voucher_details.debit - voucher_details.credit) as saldo')
                ->first();

            $totalAktivaLancar = $aktivaLancar->saldo ?? 0;

            $hutangLancar = DB::table('voucher_details')
                ->leftJoin('subsidiaries', 'voucher_details.account_code', '=', 'subsidiaries.subsidiary_code')
                ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                ->join('chart_of_accounts', function ($join) {
                    $join->on(DB::raw("COALESCE(SUBSTRING_INDEX(subsidiaries.subsidiary_code, '.', 4), voucher_details.account_code)"), '=', 'chart_of_accounts.account_code');
                })
                ->where('chart_of_accounts.account_type', 'KEWAJIBAN')
                ->whereBetween('vouchers.voucher_date', [$startDate, $endDate])
                ->selectRaw('SUM(voucher_details.credit - voucher_details.debit) as saldo')
                ->first();

            $totalHutangLancar = $hutangLancar->saldo ?? 0;

            $selisih = $totalAktivaLancar - $totalHutangLancar;

            if ($selisih >= 85000000) {
                $zakatWajibCara1 = true;
                $zakatCara1 = $selisih * 0.025;
            } else {
                $zakatWajibCara1 = false;
                $zakatCara1 = 0;
            }
        }

        if ($calculation_method === 'cara2' || $calculation_method === 'both') {
            $accountCategories = [
                'Pendapatan Penjualan' => ['4.'],
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

            if ($labaBersih >= 85000000) {
                $zakatWajibCara2 = true;
                $zakatCara2 = $labaBersih * 0.025;
            } else {
                $zakatWajibCara2 = false;
                $zakatCara2 = 0;
            }
        }

        return compact(
            'totalAktivaLancar',
            'totalHutangLancar',
            'selisih',
            'zakatCara1',
            'zakatWajibCara1',
            'labaBersih',
            'zakatCara2',
            'zakatWajibCara2',
            'year',
            'month',
            'calculation_method'
        );
    }

    /**
     * Get export parameters
     *
     * @param Request $request
     * @return array
     */
    public function getExportParameters(Request $request): array
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', null);
        $filename = 'zakat_report_' . $year . ($month ? '_' . $month : '') . '.xlsx';
        return [
            'year' => $year,
            'month' => $month,
            'filename' => $filename,
        ];
    }
}
