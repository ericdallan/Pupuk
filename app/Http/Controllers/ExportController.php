<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\voucherDetails;
use App\Exports\BalanceSheetExport;
use App\Exports\GeneralLedgerMultiSheetExport;
use App\Exports\IncomeStatementMultiSheetExport;
use Illuminate\Support\Facades\Log;
use App\Exports\TrialBalanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function generalledger_print(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $selectedAccountName = $request->input('account_name', []);

        if ($request->has('account_name_hidden') && !empty($request->input('account_name_hidden'))) {
            $selectedAccountName = explode(',', $request->input('account_name_hidden'));
        } elseif (in_array('', $selectedAccountName)) {
            $selectedAccountName = []; // Treat empty string as "All Accounts"
        }

        // Format month for filename
        $formattedMonth = sprintf("%02d", $month);
        $filename = 'General_Ledger_' . $formattedMonth . '-' . $year . '.xlsx';

        // Use the multi-sheet export
        return Excel::download(new GeneralLedgerMultiSheetExport($month, $year, $selectedAccountName), $filename);
    }

    public function exportIncomeStatement(Request $request)
    {
        try {
            // Ambil input tahun dan bulan, default ke tahun saat ini
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', null); // Null untuk semua bulan

            // Validasi input
            $request->validate([
                'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
                'month' => 'nullable|numeric|min:1|max:12',
            ]);

            // Tentukan rentang tanggal untuk periode
            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }

            // Tentukan rentang tanggal untuk akumulasi (dari awal tahun hingga akhir bulan yang dipilih)
            $cumulativeStartDate = Carbon::create($year, 1, 1)->startOfYear();
            $cumulativeEndDate = $month ? Carbon::create($year, $month, 1)->endOfMonth() : Carbon::create($year, 12, 31)->endOfYear();

            // Definisikan kategori akun berdasarkan kode akun
            $accountCategories = [
                'Pendapatan Penjualan' => ['4.'],
                'Harga Pokok Penjualan' => ['5.1.','5.2','5.3'],
                'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
                'Pendapatan Lain-lain' => ['7.1.'],
                'Beban Lain-lain' => ['7.2.'],
                'Pajak Penghasilan' => ['7.3.'],
            ];

            // Inisialisasi total untuk periode dan akumulasi
            $periodTotals = array_fill_keys(array_keys($accountCategories), 0);
            $cumulativeTotals = array_fill_keys(array_keys($accountCategories), 0);

            // Ambil data VoucherDetails untuk periode
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

            // Proses data untuk menghitung total per kategori (periode)
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

            // Ambil data VoucherDetails untuk akumulasi
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

            // Proses data untuk menghitung total per kategori (akumulasi)
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

            // Tetapkan variabel untuk periode
            $pendapatanPenjualan = $periodTotals['Pendapatan Penjualan'] ?? 0;
            $hpp = $periodTotals['Harga Pokok Penjualan'] ?? 0;
            $totalBebanOperasional = $periodTotals['Beban Operasional'] ?? 0;
            $totalPendapatanLain = $periodTotals['Pendapatan Lain-lain'] ?? 0;
            $totalBebanLain = $periodTotals['Beban Lain-lain'] ?? 0;
            $totalBebanPajak = $periodTotals['Pajak Penghasilan'] ?? 0;

            // Hitung metrik laba rugi untuk periode
            $labaKotor = $pendapatanPenjualan - $hpp;
            $labaOperasi = $labaKotor - $totalBebanOperasional;
            $labaSebelumPajak = $labaOperasi + $totalPendapatanLain - $totalBebanLain;
            $labaBersih = $labaSebelumPajak - $totalBebanPajak;

            // Siapkan data untuk ekspor periode
            $periodData = collect([
                [
                    'Keterangan' => 'Pendapatan Penjualan',
                    'Jumlah (Rp)' => $pendapatanPenjualan,
                ],
                [
                    'Keterangan' => 'Harga Pokok Penjualan',
                    'Jumlah (Rp)' => -$hpp,
                ],
                [
                    'Keterangan' => 'Laba Kotor',
                    'Jumlah (Rp)' => $labaKotor,
                ],
                [
                    'Keterangan' => 'Beban Operasional',
                    'Jumlah (Rp)' => -$totalBebanOperasional,
                ],
                [
                    'Keterangan' => 'Total Beban Operasional',
                    'Jumlah (Rp)' => -$totalBebanOperasional,
                ],
                [
                    'Keterangan' => 'Laba Operasi',
                    'Jumlah (Rp)' => $labaOperasi,
                ],
                [
                    'Keterangan' => 'Pendapatan Lain-lain',
                    'Jumlah (Rp)' => $totalPendapatanLain,
                ],
                [
                    'Keterangan' => 'Beban Lain-lain',
                    'Jumlah (Rp)' => -$totalBebanLain,
                ],
                [
                    'Keterangan' => 'Laba Sebelum Pajak',
                    'Jumlah (Rp)' => $labaSebelumPajak,
                ],
                [
                    'Keterangan' => 'Pajak Penghasilan',
                    'Jumlah (Rp)' => -$totalBebanPajak,
                ],
                [
                    'Keterangan' => 'Laba Bersih',
                    'Jumlah (Rp)' => $labaBersih,
                ],
            ]);

            // Tetapkan variabel untuk akumulasi
            $cumulativePendapatanPenjualan = $cumulativeTotals['Pendapatan Penjualan'] ?? 0;
            $cumulativeHpp = $cumulativeTotals['Harga Pokok Penjualan'] ?? 0;
            $cumulativeTotalBebanOperasional = $cumulativeTotals['Beban Operasional'] ?? 0;
            $cumulativeTotalPendapatanLain = $cumulativeTotals['Pendapatan Lain-lain'] ?? 0;
            $cumulativeTotalBebanLain = $cumulativeTotals['Beban Lain-lain'] ?? 0;
            $cumulativeTotalBebanPajak = $cumulativeTotals['Pajak Penghasilan'] ?? 0;

            // Hitung metrik laba rugi untuk akumulasi
            $cumulativeLabaKotor = $cumulativePendapatanPenjualan - $cumulativeHpp;
            $cumulativeLabaOperasi = $cumulativeLabaKotor - $cumulativeTotalBebanOperasional;
            $cumulativeLabaSebelumPajak = $cumulativeLabaOperasi + $cumulativeTotalPendapatanLain - $cumulativeTotalBebanLain;
            $cumulativeLabaBersih = $cumulativeLabaSebelumPajak - $cumulativeTotalBebanPajak;

            // Siapkan data untuk ekspor akumulasi
            $cumulativeData = collect([
                [
                    'Keterangan' => 'Pendapatan Penjualan',
                    'Jumlah (Rp)' => $cumulativePendapatanPenjualan,
                ],
                [
                    'Keterangan' => 'Harga Pokok Penjualan',
                    'Jumlah (Rp)' => -$cumulativeHpp,
                ],
                [
                    'Keterangan' => 'Laba Kotor',
                    'Jumlah (Rp)' => $cumulativeLabaKotor,
                ],
                [
                    'Keterangan' => 'Beban Operasional',
                    'Jumlah (Rp)' => -$cumulativeTotalBebanOperasional,
                ],
                [
                    'Keterangan' => 'Total Beban Operasional',
                    'Jumlah (Rp)' => -$cumulativeTotalBebanOperasional,
                ],
                [
                    'Keterangan' => 'Laba Operasi',
                    'Jumlah (Rp)' => $cumulativeLabaOperasi,
                ],
                [
                    'Keterangan' => 'Pendapatan Lain-lain',
                    'Jumlah (Rp)' => $cumulativeTotalPendapatanLain,
                ],
                [
                    'Keterangan' => 'Beban Lain-lain',
                    'Jumlah (Rp)' => -$cumulativeTotalBebanLain,
                ],
                [
                    'Keterangan' => 'Laba Sebelum Pajak',
                    'Jumlah (Rp)' => $cumulativeLabaSebelumPajak,
                ],
                [
                    'Keterangan' => 'Pajak Penghasilan',
                    'Jumlah (Rp)' => -$cumulativeTotalBebanPajak,
                ],
                [
                    'Keterangan' => 'Laba Bersih',
                    'Jumlah (Rp)' => $cumulativeLabaBersih,
                ],
            ]);

            // Tentukan nama file
            $fileName = $month
                ? 'laporan_laba_rugi_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '_' . $year . '.xlsx'
                : 'laporan_laba_rugi_' . $year . '.xlsx';

            // Ekspor dengan multiple sheets
            return Excel::download(new IncomeStatementMultiSheetExport($periodData, $cumulativeData, $year, $month), $fileName);
        } catch (\Exception $e) {
            Log::error('Export Income Statement Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat mengekspor laporan laba rugi']);
        }
    }
    public function exportNeracaSaldo(Request $request)
    {

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Assuming you have logic to fetch account balances for the given month and year
        $accountBalances = $this->getAccountBalances($month, $year);

        // Assuming you have logic to fetch account names
        $accountNames = $this->getAccountNames();

        // Correct way to instantiate TrialBalanceExport with the required arguments
        $export = new TrialBalanceExport($month, $year, $accountBalances, $accountNames);

        return Excel::download($export, 'neraca_saldo_' . date('Y-m') . '.xlsx');
    }

    public function exportBalanceSheet(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Parse dates or set default to current year
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfYear();

        // Ambil semua akun Aset Lancar per account_subsection
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

        // Ambil semua akun Aset Tetap per account_subsection
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

        // Ambil semua akun Kewajiban per account_subsection
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

        // Ambil semua akun Ekuitas per account_subsection
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

        // Calculate Net Profit from Income Statement
        $accountCategories = [
            'Pendapatan Penjualan' => ['4.'],
            'Harga Pokok Penjualan' => ['5.1.','5.2.','5.3.'],
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

        // Pastikan 'Laba Rugi Ditahan' ada, jika tidak, buat dengan saldo awal 0
        if (!isset($ekuitasData[$labaRugiDitahanKey])) {
            $ekuitasData[$labaRugiDitahanKey] = (object)['account_name' => $labaRugiDitahanKey, 'saldo' => 0];
        }

        // Tambahkan saldo 'Laba Ditahan' ke 'Laba Rugi Ditahan'
        $ekuitasData[$labaRugiDitahanKey]->saldo += $saldoLabaDitahan;

        // Tambahkan Laba Bersih ke 'Laba Rugi Ditahan'
        $ekuitasData[$labaRugiDitahanKey]->saldo += $labaBersih;

        // Hapus 'Laba Ditahan' dari array ekuitas
        unset($ekuitasData[$labaDitahanKey]);

        // Filter $ekuitasData to exclude "Pengambilan Pemilik" and "Saldo laba"
        $filteredEkuitasData = $ekuitasData->reject(function ($item, $key) {
            return in_array($key, ['Pengambilan Oleh Pemilik', 'Saldo laba']);
        });

        // Pass the filtered $ekuitasData to the export class
        return Excel::download(new BalanceSheetExport(
            $asetLancarData,
            $asetTetapData,
            $kewajibanData,
            $filteredEkuitasData,
            $startDate,
            $endDate
        ), 'balance_sheet.xlsx');
    }
    protected function getAccountBalances(int $month, int $year): array
    {
        return DB::table('voucher_details')
            ->selectRaw('account_code, SUM(debit - credit) as balance')
            ->whereMonth('created_at', $month) // Adjust 'created_at' to your actual date column
            ->whereYear('created_at', $year)   // Adjust 'created_at' to your actual date column
            ->groupBy('account_code')
            ->pluck('balance', 'account_code')
            ->toArray();
    }

    /**
     * Fetches account names.
     * Adjust the query to match your database schema.
     *
     * @return array
     */
    protected function getAccountNames(): array
    {
        return DB::table('chart_of_accounts')->pluck('account_name', 'account_code')->toArray();
    }
}
