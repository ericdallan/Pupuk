<?php

namespace App\Exports;

use App\Models\VoucherDetails;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class GeneralLedgerMultiSheetExport implements WithMultipleSheets
{
    protected $month;
    protected $year;
    protected $selectedAccountNames;

    public function __construct($month, $year, $selectedAccountNames = [])
    {
        $this->month = $month;
        $this->year = $year;
        $this->selectedAccountNames = $selectedAccountNames;
    }

    public function sheets(): array
    {
        // Fetch data for monthly ledger (specified month)
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        $monthlyVoucherDetailsQuery = VoucherDetails::with('voucher')
            ->whereHas('voucher', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('voucher_date', [$startDate, $endDate]);
            });

        if (!empty($this->selectedAccountNames)) {
            $monthlyVoucherDetailsQuery->whereIn('account_name', $this->selectedAccountNames);
        }

        $monthlyVoucherDetails = $monthlyVoucherDetailsQuery->orderBy('account_code')
            ->orderBy('voucher_id')
            ->get();

        // Fetch data for cumulative ledger (January to specified month)
        $cumulativeStartDate = Carbon::create($this->year, 1, 1)->startOfYear();
        $cumulativeEndDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();
        $cumulativeVoucherDetailsQuery = VoucherDetails::with('voucher')
            ->whereHas('voucher', function ($query) use ($cumulativeStartDate, $cumulativeEndDate) {
                $query->whereBetween('voucher_date', [$cumulativeStartDate, $cumulativeEndDate]);
            });

        if (!empty($this->selectedAccountNames)) {
            $cumulativeVoucherDetailsQuery->whereIn('account_name', $this->selectedAccountNames);
        }

        $cumulativeVoucherDetails = $cumulativeVoucherDetailsQuery->orderBy('account_code')
            ->orderBy('voucher_id')
            ->get();

        // Define sheet titles
        $monthlySheetTitle = "Laporan Buku Besar";
        $cumulativeSheetTitle = "Akumulasi Laporan Buku Besar";

        return [
            new GeneralLedgerExport($monthlyVoucherDetails, $this->month, $this->year, $monthlySheetTitle),
            new CumulativeGeneralLedgerExport($cumulativeVoucherDetails, $this->month, $this->year, $cumulativeSheetTitle),
        ];
    }
}
