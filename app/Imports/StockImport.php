<?php

namespace App\Imports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockImport implements ToModel, WithHeadingRow
{
    /**
     * Map Excel row to Stock model
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Stock([
            'stock_name' => $row['stock_name'],
            'quantity' => $row['quantity'],
            'size' => $row['size'],
            'date' => \Carbon\Carbon::parse($row['date'])->toDateString(),
        ]);
    }
}
