<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class AccountCodeExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $hierarkiAkun;

    public function __construct($hierarkiAkun)
    {
        $this->hierarkiAkun = $hierarkiAkun;
    }

    public function collection(): Collection
    {
        $data = [];

        foreach ($this->hierarkiAkun as $accountType => $accountSections) {
            foreach ($accountSections as $accountSection => $accountSubsections) {
                if (is_array($accountSubsections)) {
                    foreach ($accountSubsections as $accountSubsection => $accountNames) {
                        foreach ($accountNames as $accountName) {
                            $data[] = [
                                'Account Type' => str_replace('_', ' ', $accountType),
                                'Account Section' => $accountSection,
                                'Account Subsection' => $accountSubsection,
                                'Account Name' => is_array($accountName) ? $accountName[0] : $accountName,
                                'Account Code' => is_array($accountName) ? (isset($accountName[1]) ? $accountName[1] : '') : '',
                            ];
                        }
                    }
                } else {
                    foreach ($accountSubsections as $accountName) {
                        $data[] = [
                            'Account Type' => str_replace('_', ' ', $accountType),
                            'Account Section' => $accountSection,
                            'Account Subsection' => '',
                            'Account Name' => is_array($accountName) ? $accountName[0] : $accountName,
                            'Account Code' => is_array($accountName) ? (isset($accountName[1]) ? $accountName[1] : '') : '',
                        ];
                    }
                }
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Account Type',
            'Account Section',
            'Account Subsection',
            'Account Name',
            'Account Code',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $headerRow = 1;

        return [
            // Style the header row
            $headerRow => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '4F81BD'], // Blue color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style the body rows
            'A2:' . $highestColumn . $highestRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Style specific columns (optional - example for Account Code)
            'E' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],
            // You can add more specific styling based on your needs
        ];
    }
}
