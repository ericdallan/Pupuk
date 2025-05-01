<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class IncomeStatementMultiSheetExport implements WithMultipleSheets
{
    protected $periodData;
    protected $cumulativeData;
    protected $year;
    protected $month;

    public function __construct($periodData, $cumulativeData, $year, $month)
    {
        $this->periodData = $periodData;
        $this->cumulativeData = $cumulativeData;
        $this->year = $year;
        $this->month = $month;
    }

    public function sheets(): array
    {
        return [
            'Laporan Laba Rugi Bulan' => new IncomeStatementExport($this->periodData, $this->year, $this->month),
            'Akumulasi Laporan Laba Rugi' => new CumulativeIncomeStatementExport($this->cumulativeData, $this->year, $this->month),
        ];
    }
}
