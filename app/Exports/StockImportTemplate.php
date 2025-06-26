<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockImportTemplate implements FromArray, WithHeadings
{
    /**
     * Return an empty array for the template
     *
     * @return array
     */
    public function array(): array
    {
        return []; // No data, just headers
    }

    /**
     * Define headers for the template
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'stock_name',
            'quantity',
            'unit',
            'date',
        ];
    }
}
