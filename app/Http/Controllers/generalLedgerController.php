<?php

namespace App\Http\Controllers;

use App\Models\voucherDetails;
use App\Models\ChartOfAccount;
use App\Models\Subsidiary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class generalLedgerController extends Controller
{
    public function generalledger_page(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $selectedAccountName = $request->input('account_name', []); // Default to empty array

        if ($request->has('account_name_hidden') && !empty($request->input('account_name_hidden'))) {
            $selectedAccountName = explode(',', $request->input('account_name_hidden'));
        } elseif (in_array('', $selectedAccountName)) {
            $selectedAccountName = []; // Treat empty string in array as "All Accounts"
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $excludedPrefixes = ['1.1.03.01.', '2.1.01.01.'];
        $excludedAccountCodes = ChartOfAccount::where(function ($query) use ($excludedPrefixes) {
            foreach ($excludedPrefixes as $prefix) {
                $query->orWhere('account_code', 'like', $prefix . '%');
            }
        })
            ->whereRaw('LENGTH(account_code) > ?', [strlen($excludedPrefixes[0])]) // Ensure code is longer than the prefix
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

        // Fetch subsidiaries data to map subsidiaries_code to account_code
        $subsidiariesMap = Subsidiary::pluck('account_code', 'subsidiary_code')->toArray();

        // Iterate through voucher details and update account_code if a match is found in subsidiaries
        $voucherDetails = $voucherDetails->map(function ($detail) use ($subsidiariesMap) {
            if (isset($subsidiariesMap[$detail->account_code])) {
                $detail->account_code = $subsidiariesMap[$detail->account_code];
            }
            return $detail;
        });

        $accountCodesInVoucherDetails = $voucherDetails->pluck('account_code')->unique()->toArray();

        $accountNames = ChartOfAccount::whereIn('account_code', $accountCodesInVoucherDetails)
            ->pluck('account_name', 'account_code') // Fetch as key-value pair for easier mapping
            ->toArray();

        // Update account_name in voucherDetails based on the fetched accountNames
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

        return view('generalLedger.generalLedger_page', compact('voucherDetails', 'year', 'month', 'availableAccountNames', 'selectedAccountName'));
    }

    public function trialBalance_page(Request $request)
    {
        try {
            // Ambil input tahun dan bulan, default ke tahun dan bulan saat ini
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            // Validasi input tahun dan bulan
            if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
                return redirect()->back()->withErrors(['error' => 'Invalid year or month']);
            }

            // Tentukan rentang tanggal untuk bulan yang dipilih
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Definisikan pemetaan kode akun untuk subsidiary codes
            $accountCodesMap = [
                'Piutang Usaha' => '1.1.03.01',
                'Utang Usaha' => '2.1.01.01',
            ];

            // Ambil data VoucherDetails untuk periode yang dipilih
            $voucherDetails = VoucherDetails::with('voucher')
                ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('voucher_date', [$startDate, $endDate]);
                })
                ->get();

            // Inisialisasi array untuk menyimpan saldo akun
            $accountBalances = [];

            // Proses setiap detail voucher
            foreach ($voucherDetails as $detail) {
                $accountCode = $detail->account_code;

                // Cek apakah account_code adalah subsidiary code
                $isSubsidiary = false;
                foreach ($accountCodesMap as $accountName => $prefix) {
                    if (strpos($accountCode, $prefix . '.') === 0) {
                        // Jika kode adalah subsidiary (misal 1.1.03.01.01), gunakan kode parent
                        $accountCode = $prefix;
                        $isSubsidiary = true;
                        break;
                    }
                }

                // Inisialisasi saldo akun jika belum ada
                if (!isset($accountBalances[$accountCode])) {
                    $accountBalances[$accountCode] = 0;
                }

                // Tambahkan saldo (debit - credit)
                $accountBalances[$accountCode] += $detail->debit - $detail->credit;
            }

            // Ambil nama akun dari ChartOfAccount untuk kode akun yang ada di $accountBalances
            $accountNames = ChartOfAccount::whereIn('account_code', array_keys($accountBalances))
                ->pluck('account_name', 'account_code')
                ->toArray();

            // Pastikan nama akun untuk kode parent dari $accountCodesMap benar
            foreach ($accountCodesMap as $accountName => $accountCode) {
                if (isset($accountBalances[$accountCode])) {
                    $accountNames[$accountCode] = $accountName;
                }
            }

            // Urutkan berdasarkan kode akun
            ksort($accountBalances);

            // Konversi $accountBalances ke koleksi untuk mendukung isEmpty() di view
            $accountBalances = collect($accountBalances);

            return view('generalLedger.trialBalance_page', compact('accountBalances', 'accountNames', 'year', 'month'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred while generating the trial balance']);
        }
    }

    public function incomeStatement_page(Request $request)
    {
        try {
            // Ambil input tahun dan bulan, default ke tahun dan bulan saat ini
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', null); // Null untuk semua bulan

            // Validasi input
            $request->validate([
                'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
                'month' => 'nullable|numeric|min:1|max:12',
            ]);

            // Tentukan rentang tanggal
            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }

            // Definisikan kategori akun berdasarkan kode akun
            $accountCategories = [
                'Pendapatan Penjualan' => ['4.'],
                'Harga Pokok Penjualan' => ['5.1.', '5.2.', '5.3.'],
                'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
                'Pendapatan Lain-lain' => ['7.1.'],
                'Beban Lain-lain' => ['7.2.'],
                'Pajak Penghasilan' => ['7.3.'],
            ];

            // Inisialisasi total
            $totals = array_fill_keys(array_keys($accountCategories), 0);

            // Ambil data VoucherDetails dengan agregasi
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

            // Proses data untuk menghitung total per kategori
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

            // Tetapkan variabel untuk view
            $pendapatanPenjualan = $totals['Pendapatan Penjualan'];
            $hpp = $totals['Harga Pokok Penjualan'];
            $totalBebanOperasional = $totals['Beban Operasional'];
            $totalPendapatanLain = $totals['Pendapatan Lain-lain'];
            $totalBebanLain = $totals['Beban Lain-lain'];
            $totalBebanPajak = $totals['Pajak Penghasilan'];

            // Hitung metrik laba rugi
            $labaKotor = $pendapatanPenjualan - $hpp;
            $labaOperasi = $labaKotor - $totalBebanOperasional;
            $labaSebelumPajak = $labaOperasi + $totalPendapatanLain - $totalBebanLain;
            $labaBersih = $labaSebelumPajak - $totalBebanPajak;

            return view('generalLedger.incomeStatement_page', compact(
                'pendapatanPenjualan',
                'hpp',
                'labaKotor',
                'totalBebanOperasional',
                'labaOperasi',
                'totalPendapatanLain',
                'totalBebanLain',
                'labaSebelumPajak',
                'totalBebanPajak',
                'labaBersih',
                'year',
                'month'
            ));
        } catch (\Exception $e) {
            Log::error('Income Statement Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghasilkan laporan laba rugi']);
        }
    }
    public function balanceSheet_page(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Parse dates or set default to current year
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfYear();

        // Ambil data Aset Lancar per account_subsection
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

        // Ambil data Aset Tetap per account_subsection
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

        // Ambil data Kewajiban per account_subsection
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

        // Ambil data Ekuitas per account_subsection
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

        // Pastikan 'Laba Rugi Ditahan' ada di $ekuitasData
        if (isset($ekuitasData['Laba Rugi Ditahan'])) {
            $labaDitahanSaldo = $ekuitasData['Laba Rugi Ditahan']->saldo ?? 0;
            $labaDitahanSaldo += $labaBersih;
            $ekuitasData['Laba Rugi Ditahan']->saldo = $labaDitahanSaldo;
        } else {
            // Handle jika 'Laba Rugi Ditahan' tidak ada, mungkin tambahkan sebagai item baru
            $ekuitasData['Laba Rugi Ditahan'] = (object) ['account_name' => 'Laba Rugi Ditahan', 'saldo' => $labaBersih];
        }

        // Ambil daftar unik account_subsection untuk Aset
        $allAset = DB::table('chart_of_accounts')
            ->where('account_type', 'ASET')
            ->select('account_subsection as account_name', 'account_section')
            ->distinct()
            ->get()
            ->groupBy('account_section');

        // Ambil daftar unik account_subsection untuk Kewajiban
        $allKewajiban = DB::table('chart_of_accounts')
            ->where('account_type', 'KEWAJIBAN')
            ->select('account_subsection as account_name')
            ->distinct()
            ->get();

        // Ambil daftar unik account_subsection untuk Ekuitas dan filter
        $allEkuitas = DB::table('chart_of_accounts')
            ->where('account_type', 'EKUITAS')
            ->whereNotIn('account_subsection', ['Pengambilan Oleh Pemilik', 'Saldo laba'])
            ->select('account_subsection as account_name')
            ->distinct()
            ->get();

        // Kirim data ke view
        return view('generalLedger.balanceSheet_page', [
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
        ]);
    }
}
