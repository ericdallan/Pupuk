<?php

namespace App\Exports;

use App\Models\VoucherDetails;
use App\Models\ChartOfAccount;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;

class GeneralLedgerExport implements FromCollection, WithColumnWidths, WithStyles, WithEvents
{
    protected $voucherDetails;
    protected $year;
    protected $month;
    protected $sheetTitle;

    public function __construct($voucherDetails, $month, $year, $sheetTitle = 'General Ledger')
    {
        $this->voucherDetails = $voucherDetails;
        $this->year = $year;
        $this->month = $month;
        $this->sheetTitle = $sheetTitle;
    }

    public function collection()
    {
        $data = [];

        // Fetch all main account codes and names
        $accountCodes = ChartOfAccount::pluck('account_name', 'account_code')->toArray();

        // Group voucher details by account_code
        $groupedDetails = $this->voucherDetails->groupBy(function ($detail) {
            $subsidiaryCode = $detail->account_code;

            // Map subsidiary accounts to main accounts
            if (preg_match('/^2\.1\.01\.01\.\d+$/', $subsidiaryCode)) {
                return '2.1.01.01'; // Map to Utang Usaha
            } elseif (preg_match('/^1\.1\.03\.01\.\d+$/', $subsidiaryCode)) {
                return '1.1.03.01'; // Map to Piutang Usaha
            }

            // Use original code if no mapping applies
            return $subsidiaryCode;
        });

        foreach ($groupedDetails as $mainAccountCode => $details) {
            $saldo = 0;
            $accountName = $accountCodes[$mainAccountCode] ?? 'Tidak Ada Nama Akun';

            // Add header row for each account group
            $data[] = [
                'Kode Akun',
                'Nama Akun',
                'Tanggal',
                'Transaksi',
                'Ref',
                'Debit (Rp)',
                'Kredit (Rp)',
                'Saldo (Rp)',
            ];

            foreach ($details as $detail) {
                $debit = $detail->debit ?? 0;
                $credit = $detail->credit ?? 0;
                $saldo += $debit - $credit;
                $data[] = [
                    $mainAccountCode,
                    $accountName,
                    Carbon::parse($detail->voucher->voucher_date)->isoFormat('DD MMMM YYYY'),
                    $detail->voucher->transaction,
                    $detail->voucher->voucher_number,
                    $debit,
                    $credit,
                    number_format($saldo, 2, ',', '.'),
                ];
            }

            // Add empty row after each account group
            $data[] = ['', '', '', '', '', '', '', ''];
        }

        // Add report title at the beginning
        $period = $this->month
            ? date('F', mktime(0, 0, 0, $this->month, 10)) . ' ' . $this->year
            : 'Tahun ' . $this->year;
        array_unshift($data, [$this->sheetTitle . ' Bulan ' . $period], ['', '', '', '', '', '', '', '']);

        return collect($data);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Kode Akun
            'B' => 30, // Nama Akun
            'C' => 20, // Tanggal
            'D' => 40, // Transaksi
            'E' => 15, // Ref
            'F' => 18, // Debit (Rp)
            'G' => 18, // Kredit (Rp)
            'H' => 18, // Saldo (Rp)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        return [
            // Style for the report title
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'F' => [
                'numberFormat' => ['formatCode' => '#,##0.00'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
            'G' => [
                'numberFormat' => ['formatCode' => '#,##0.00'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
            'H' => [
                'numberFormat' => ['formatCode' => '#,##0.00'],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
            'A3:' . $lastColumn . $lastRow => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $startRow = 3; // Start after title and empty row

                // Define header style
                $headerStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF808080'], // Dark gray
                    ],
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
                        'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                    ],
                ];

                // Apply styles to header rows and borders
                for ($row = $startRow; $row <= $highestRow; $row++) {
                    if ($sheet->getCell('A' . $row)->getValue() === 'Kode Akun') {
                        $endColumn = 'H';
                        $sheet->getStyle('A' . $row . ':' . $endColumn . $row)->applyFromArray($headerStyle);

                        // Add border below previous account data
                        if ($row > $startRow) {
                            $prevHeaderRow = $row - 1;
                            while ($prevHeaderRow >= $startRow && $sheet->getCell('A' . $prevHeaderRow)->getValue() !== 'Kode Akun') {
                                $sheet->getStyle('A' . $prevHeaderRow . ':' . $endColumn . $prevHeaderRow)
                                    ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                                $prevHeaderRow--;
                            }
                        }
                    }
                }

                // Apply overall borders for data
                $sheet->getStyle('A3:H' . $highestRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Merge cells for title
                $sheet->mergeCells('A1:H1');
            },
        ];
    }
}
