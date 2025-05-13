<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\VoucherDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\ZakatExport;
use Maatwebsite\Excel\Facades\Excel;

class ZakatController extends Controller
{
    public function zakat_page(Request $request = null)
    {
        // Pass default values for year and month
        $year = $request ? $request->input('year', date('Y')) : date('Y');
        $month = $request ? $request->input('month', null) : null;
        $calculation_method = $request ? $request->input('calculation_method', 'cara1') : 'cara1'; // Default to Cara 1

        return view('zakat.zakat_page', compact('year', 'month', 'calculation_method'));
    }

    public function calculateZakat(Request $request)
    {
        try {
            // Ambil input tahun, bulan, dan metode perhitungan
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', null); // Null untuk semua bulan
            $calculation_method = $request->input('calculation_method', 'cara1'); // Default ke cara1

            // Validasi input
            $request->validate([
                'year' => 'nullable|numeric|min:1900|max:' . date('Y'),
                'month' => 'nullable|numeric|min:1|max:12',
                'calculation_method' => 'required|in:cara1,cara2',
            ]);

            // Tentukan rentang tanggal
            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
            }

            // Inisialisasi variabel untuk view
            $totalAktivaLancar = 0;
            $totalHutangLancar = 0;
            $selisih = 0;
            $zakatCara1 = 0;
            $zakatWajibCara1 = false; // Status kewajiban zakat Cara 1
            $labaBersih = 0;
            $zakatCara2 = 0;
            $zakatWajibCara2 = false; // Status kewajiban zakat Cara 2

            // === Cara 1: Perhitungan Zakat Berdasarkan Neraca Keuangan ===
            if ($calculation_method === 'cara1' || $calculation_method === 'both') {
                // Ambil data Aktiva Lancar (Current Assets)
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

                // Ambil data Hutang Lancar (Current Liabilities)
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

                // Hitung Selisih (Aktiva Lancar - Hutang Lancar)
                $selisih = $totalAktivaLancar - $totalHutangLancar;

                // Periksa kewajiban zakat berdasarkan nisab (Rp 85,000,000)
                if ($selisih >= 85000000) {
                    $zakatWajibCara1 = true;
                    $zakatCara1 = $selisih * 0.025; // Hitung zakat jika wajib
                } else {
                    $zakatWajibCara1 = false;
                    $zakatCara1 = 0; // Tidak ada zakat jika tidak wajib
                }
            }

            // === Cara 2: Perhitungan Zakat Berdasarkan Laba Rugi ===
            if ($calculation_method === 'cara2' || $calculation_method === 'both') {
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

                // Tetapkan variabel untuk perhitungan laba rugi
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

                // Periksa kewajiban zakat berdasarkan laba bersih
                if ($labaBersih >= 85000000) {
                    $zakatWajibCara2 = true;
                    $zakatCara2 = $labaBersih * 0.025; // Hitung zakat jika wajib
                } else {
                    $zakatWajibCara2 = false;
                    $zakatCara2 = 0; // Tidak ada zakat jika tidak wajib
                }
            }

            // Kirim data ke view (kembali ke halaman zakat_page)
            return view('zakat.zakat_page', compact(
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
            ));
        } catch (\Exception $e) {
            Log::error('Zakat Calculation Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghitung zakat']);
        }
    }

    public function export(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', null);
        return Excel::download(new ZakatExport($year, $month), 'zakat_report_' . $year . ($month ? '_' . $month : '') . '.xlsx');
    }
}
