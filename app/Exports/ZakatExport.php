<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ZakatExport implements FromCollection, WithHeadings, WithStyles
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        // Tentukan rentang tanggal
        if ($this->month) {
            $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
            $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        } else {
            $startDate = Carbon::create($this->year, 1, 1)->startOfYear();
            $endDate = Carbon::create($this->year, 12, 31)->endOfYear();
        }

        $data = [];

        // === Cara 1: Perhitungan Zakat Berdasarkan Neraca Keuangan ===
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
        $zakatCara1 = $selisih * 0.025;

        // === Cara 2: Perhitungan Zakat Berdasarkan Laba Rugi ===
        $accountCategories = [
            'Pendapatan Penjualan' => ['4.'],
            'Harga Pokok Penjualan' => ['5.1.', '5.2.', '5.3.'],
            'Beban Operasional' => ['6.1.', '6.2.', '6.3.'],
            'Pendapatan Lain-lain' => ['7.1.'],
            'Beban Lain-lain' => ['7.2.'],
            'Pajak Penghasilan' => ['7.3.'],
        ];

        $totals = array_fill_keys(array_keys($accountCategories), 0);

        $voucherDetails = DB::table('voucher_details')
            ->join('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
            ->whereBetween('vouchers.voucher_date', [$startDate, $endDate])
            ->where(function ($query) use ($accountCategories) {
                foreach ($accountCategories as $prefixes) {
                    foreach ($prefixes as $prefix) {
                        $query->orWhere('voucher_details.account_code', 'like', $prefix . '%');
                    }
                }
            })
            ->select('voucher_details.account_code', DB::raw('SUM(voucher_details.credit - voucher_details.debit) as pendapatan_balance'), DB::raw('SUM(voucher_details.debit - voucher_details.credit) as beban_balance'))
            ->groupBy('voucher_details.account_code')
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
        $zakatCara2 = $labaBersih * 0.025;

        // Prepare data for export based on selected method
        $data[] = [
            'Tahun' => $this->year,
            'Bulan' => $this->month ? Carbon::create()->month($this->month)->format('F') : 'Semua',
            'Metode' => 'Cara 1 (Neraca Keuangan)',
            'Aktiva Lancar' => $totalAktivaLancar ?? 0,
            'Hutang Lancar' => $totalHutangLancar ?? 0,
            'Selisih' => $selisih ?? 0,
            'Zakat (2.5% x Selisih)' => $zakatCara1 ?? 0,
        ];

        $data[] = [
            'Tahun' => $this->year,
            'Bulan' => $this->month ? Carbon::create()->month($this->month)->format('F') : 'Semua',
            'Metode' => 'Cara 2 (Laba Rugi)',
            'Laba Bersih' => $labaBersih ?? 0,
            'Zakat (2.5% x Laba Bersih)' => $zakatCara2 ?? 0,
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Bulan',
            'Metode',
            'Aktiva Lancar',
            'Hutang Lancar',
            'Selisih',
            'Zakat (2.5% x Selisih)',
            'Laba Bersih',
            'Zakat (2.5% x Laba Bersih)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply styles to the entire sheet
        $sheet->getStyle('A1:I' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => 'Arial',
                'size' => 12,
            ],
            'numberFormat' => [
                'formatCode' => '#,##0.00', // Format angka dengan 2 desimal
            ],
        ]);

        // Bold and larger font for headers
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'CCCCCC'],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
