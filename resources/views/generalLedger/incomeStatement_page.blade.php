@extends('layouts.app')
@section('content')
@section('title', 'Laporan Laba Rugi')

<style>
    table {
        width: 80%;
        border-collapse: collapse;
        margin: 20px auto;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    .total {
        font-weight: bold;
    }

    .indent {
        padding-left: 20px;
    }

    .filter-form {
        width: 80%;
        margin: 20px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .filter-form select {
        width: 150px;
    }

    .expandable {
        cursor: pointer;
    }

    .expandable::before {
        content: '+ ';
        font-weight: bold;
        display: block;
        text-align: center;
    }

    .expanded::before {
        content: '- ';
        font-weight: bold;
        display: block;
        text-align: center;
    }

    .sub-row {
        display: none;
    }

    .sub-row.visible {
        display: table-row;
    }
</style>

<div class="mt-4">
    <!-- Form Filter Tahun -->
    <div class="d-flex align-items-end">
        <form action="{{ route('incomeStatement_page') }}" method="GET" class="row g-3 align-items-end flex-grow-1">
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan:</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua Bulan</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun:</label>
                <select name="year" id="year" class="form-select">
                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('export_income_statement', ['month' => $month, 'year' => $year]) }}"
                    class="btn btn-success">
                    Export as Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Laporan Laba Rugi -->
    <table class="table table-bordered table-striped table-hover" id="incomeTable">
        <thead class="table-dark">
            <tr>
                <th></th>
                <th>Keterangan</th>
                <th class="text-end">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Pendapatan Penjualan -->
            <tr class="expandable" data-category="pendapatanPenjualan">
                <td>Pendapatan Penjualan</td>
                <td class="text-end">{{ number_format($pendapatanPenjualan, 2, ',', '.') }}</td>
            </tr>
            <tr class="sub-row" data-parent="pendapatanPenjualan">
                <td></td>
                <td class="indent">Pendapatan Penjualan Bahan Baku</td>
                <td class="text-end">{{ number_format($pendapatanPenjualanDagangan, 2, ',', '.') }}</td>
            </tr>
            @foreach ($details['Pendapatan Penjualan Bahan Baku'] ?? [] as $code => $balance)
                <tr class="sub-row" data-parent="pendapatanPenjualan" style="display: none;">
                    <td></td>
                    <td class="indent indent">
                        {{ \App\Models\ChartOfAccount::where('account_code', $code)->first()->name ?? $code }}</td>
                    <td class="text-end">{{ number_format($balance, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="sub-row" data-parent="pendapatanPenjualan">
                <td></td>
                <td class="indent">Pendapatan Penjualan Barang Jadi</td>
                <td class="text-end">{{ number_format($pendapatanPenjualanJadi, 2, ',', '.') }}</td>
            </tr>
            @foreach ($details['Pendapatan Penjualan Barang Jadi'] ?? [] as $code => $balance)
                <tr class="sub-row" data-parent="pendapatanPenjualan" style="display: none;">
                    <td></td>
                    <td class="indent indent">
                        {{ \App\Models\ChartOfAccount::where('account_code', $code)->first()->name ?? $code }}</td>
                    <td class="text-end">{{ number_format($balance, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Harga Pokok Penjualan -->
            <tr class="expandable" data-category="hpp">
                <td>Harga Pokok Penjualan</td>
                <td class="text-end">({{ number_format($hpp, 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Harga Pokok Penjualan'] ?? [] as $code => $balance)
                <tr class="sub-row" data-parent="hpp">
                    <td></td>
                    <td class="indent">
                        {{ \App\Models\ChartOfAccount::where('account_code', $code)->first()->name ?? $code }}</td>
                    <td class="text-end">({{ number_format($balance, 2, ',', '.') }})</td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td class="fw-bolder">Laba Kotor</td>
                <td class="text-end">{{ number_format($labaKotor, 2, ',', '.') }}</td>
            </tr>

            <!-- Beban Operasional -->
            <tr class="expandable" data-category="bebanOperasional">
                <td class="fw-bolder">Beban Operasional</td>
                <td class="text-end">({{ number_format($totalBebanOperasional, 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Beban Operasional'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="bebanOperasional">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">({{ number_format($balance, 2, ',', '.') }})</td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td class="fw-bolder">Laba Operasi</td>
                <td class="text-end">{{ number_format($labaOperasi, 2, ',', '.') }}</td>
            </tr>

            <!-- Pendapatan Lain-lain -->
            <tr class="expandable" data-category="pendapatanLain">
                <td class="fw-bolder">Pendapatan Lain-lain</td>
                <td class="text-end">{{ number_format($totalPendapatanLain, 2, ',', '.') }}</td>
            </tr>
            @foreach ($details['Pendapatan Lain-lain'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="pendapatanLain">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">{{ number_format($balance, 2, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Beban Lain-lain -->
            <tr class="expandable" data-category="bebanLain">
                <td class="fw-bolder">Beban Lain-lain</td>
                <td class="text-end">({{ number_format($totalBebanLain, 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Beban Lain-lain'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="bebanLain">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">({{ number_format($balance, 2, ',', '.') }})</td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td class="fw-bolder">Laba Sebelum Pajak</td>
                <td class="text-end">{{ number_format($labaSebelumPajak, 2, ',', '.') }}</td>
            </tr>

            <!-- Pajak Penghasilan -->
            <tr class="expandable" data-category="pajak">
                <td class="fw-bolder">Pajak Penghasilan</td>
                <td class="text-end">({{ number_format($totalBebanPajak, 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Pajak Penghasilan'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="pajak">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">({{ number_format($balance, 2, ',', '.') }})</td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td class="fw-bolder total">Laba Bersih/Rugi</td>
                <td class="text-end total">{{ number_format($labaBersih, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    document.querySelectorAll('.expandable').forEach(function(row) {
        if (row) {
            row.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                const subRows = document.querySelectorAll('.sub-row[data-parent="' + category + '"]');
                subRows.forEach(function(subRow) {
                    subRow.classList.toggle('visible');
                });
                this.classList.toggle('expanded');
            });
        }
    });
</script>

@endsection
