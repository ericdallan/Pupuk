<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BalanceSheetExport implements FromCollection, WithStyles
{
    protected $asetLancarData;
    protected $asetTetapData;
    protected $kewajibanData;
    protected $ekuitasData;
    protected $startDate;
    protected $endDate;

    public function __construct($asetLancarData, $asetTetapData, $kewajibanData, $ekuitasData, $startDate, $endDate)
    {
        $this->asetLancarData = $asetLancarData;
        $this->asetTetapData = $asetTetapData;
        $this->kewajibanData = $kewajibanData;
        $this->ekuitasData = $ekuitasData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $data = [];
        $formattedStartDate = '';
        if ($this->startDate instanceof Carbon) {
            $formattedStartDate = $this->startDate->format('d F Y'); // Contoh: April 2025
        } else {
            // Jika $this->startDate bukan objek Carbon, coba interpretasikan sebagai tanggal
            try {
                $formattedStartDate = Carbon::parse($this->startDate)->format('d F Y');
            } catch (\Exception $e) {
                $formattedStartDate = $this->startDate; // Jika gagal diinterpretasikan, gunakan nilai aslinya
            }
        }

        $formattedEndDate = '';
        if ($this->endDate instanceof Carbon) {
            $formattedEndDate = $this->endDate->format('d F Y'); // Contoh: April 2025
        } else {
            // Jika $this->endDate bukan objek Carbon, coba interpretasikan sebagai tanggal
            try {
                $formattedEndDate = Carbon::parse($this->endDate)->format('d F Y');
            } catch (\Exception $e) {
                $formattedEndDate = $this->endDate; // Jika gagal diinterpretasikan, gunakan nilai aslinya
            }
        }

        // Header Utama
        $data[] = ['LAPORAN NERACA KEUANGAN', ''];
        $data[] = ['Periode: ' . $formattedStartDate . ' - ' . $formattedEndDate, ''];
        $data[] = ['', '']; // Baris kosong sebagai pemisah

        // ASET Section
        $data[] = ['ASET', ''];
        $data[] = ['ASET LANCAR', ''];
        $totalAsetLancar = 0;
        foreach ($this->asetLancarData as $akun) {
            $saldo = $akun->saldo ?? 0;
            $data[] = [$akun->account_name, number_format($saldo, 2, ',', '.')];
            $totalAsetLancar += $saldo;
        }
        $data[] = ['Total Aset Lancar', number_format($totalAsetLancar, 2, ',', '.')];

        $data[] = ['ASET TETAP', ''];
        $totalAsetTetap = 0;
        foreach ($this->asetTetapData as $akun) {
            $saldo = $akun->saldo ?? 0;
            $data[] = [$akun->account_name, number_format($saldo, 2, ',', '.')];
            $totalAsetTetap += $saldo;
        }
        $data[] = ['Total Aset Tetap', number_format($totalAsetTetap, 2, ',', '.')];

        $totalAset = $totalAsetLancar + $totalAsetTetap;
        $data[] = ['TOTAL ASET', number_format($totalAset, 2, ',', '.')];

        // KEWAJIBAN DAN EKUITAS Section
        $data[] = ['KEWAJIBAN DAN EKUITAS', ''];
        $data[] = ['KEWAJIBAN', ''];
        $totalKewajiban = 0;
        foreach ($this->kewajibanData as $akun) {
            $saldo = $akun->saldo ?? 0;
            $data[] = [$akun->account_name, number_format($saldo, 2, ',', '.')];
            $totalKewajiban += $saldo;
        }
        $data[] = ['Total Kewajiban', number_format($totalKewajiban, 2, ',', '.')];

        $data[] = ['EKUITAS', ''];
        $totalEkuitas = 0;
        // Filter data ekuitas di sini
        foreach ($this->ekuitasData as $akun) {
            if (!in_array($akun->account_name, ['Pengambilan Pemilik', 'Saldo laba'])) {
                $saldo = $akun->saldo ?? 0;
                $data[] = [$akun->account_name, number_format($saldo, 2, ',', '.')];
                $totalEkuitas += $saldo;
            }
        }
        $data[] = ['Total Ekuitas', number_format($totalEkuitas, 2, ',', '.')];

        $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;
        $data[] = ['TOTAL KEWAJIBAN + EKUITAS', number_format($totalKewajibanEkuitas, 2, ',', '.')];

        return new Collection($data);
    }

    public function styles(Worksheet $sheet)
    {
        // Calculate counts for dynamic row numbers
        $asetLancarCount = $this->asetLancarData->count();
        $asetTetapCount = $this->asetTetapData->count();
        $kewajibanCount = $this->kewajibanData->count();
        $ekuitasCount = $this->ekuitasData->count();

        // Styling Header Utama
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Styling Main Headers
        $sheet->mergeCells('A4:B4'); // ASET
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal('center');

        $kewajibanEkuitasRow = 10 + $asetLancarCount + $asetTetapCount;
        $sheet->mergeCells("A{$kewajibanEkuitasRow}:B{$kewajibanEkuitasRow}"); // KEWAJIBAN DAN EKUITAS
        $sheet->getStyle("A{$kewajibanEkuitasRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$kewajibanEkuitasRow}")->getAlignment()->setHorizontal('center');

        // Styling Subheaders
        $subHeaderRows = [
            5, // ASET LANCAR
            7 + $asetLancarCount, // ASET TETAP
            11 + $asetLancarCount + $asetTetapCount, // KEWAJIBAN
            13 + $asetLancarCount + $asetTetapCount + $kewajibanCount, // EKUITAS
        ];
        foreach ($subHeaderRows as $row) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('E9ECEF');
        }

        // Styling Total Rows
        $totalRows = [
            6 + $asetLancarCount, // Total Aset Lancar
            8 + $asetLancarCount + $asetTetapCount, // Total Aset Tetap
            9 + $asetLancarCount + $asetTetapCount, // TOTAL ASET
            12 + $asetLancarCount + $asetTetapCount + $kewajibanCount, // Total Kewajiban
            14 + $asetLancarCount + $asetTetapCount + $kewajibanCount + $ekuitasCount, // Total Ekuitas
            15 + $asetLancarCount + $asetTetapCount + $kewajibanCount + $ekuitasCount, // TOTAL KEWAJIBAN + EKUITAS
        ];
        foreach ($totalRows as $row) {
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:B{$row}")->getBorders()
                ->getTop()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        }

        // Set Column Widths and Alignments
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getStyle('B1:B' . $sheet->getHighestRow())->getAlignment()->setHorizontal('right');

        return [];
    }
}
