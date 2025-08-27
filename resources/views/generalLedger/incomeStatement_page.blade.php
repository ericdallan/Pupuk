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

    #taxRow {
        display: none;
    }

    #taxRow.visible {
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
                <button type="button" class="btn btn-warning me-2" id="toggleTax">Perhitungan Pajak</button>
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
                <td class="text-end">
                    {{ $pendapatanPenjualan < 0 ? '(' . number_format(abs($pendapatanPenjualan), 2, ',', '.') . ')' : number_format($pendapatanPenjualan, 2, ',', '.') }}
                </td>
            </tr>
            @foreach ($details['Pendapatan Penjualan Bahan Baku'] ?? [] as $subsection => $balance)
                @if ($balance != 0)
                    <tr class="sub-row" data-parent="pendapatanPenjualan">
                        <td></td>
                        <td class="indent">{{ $subsection }}</td>
                        <td class="text-end">
                            {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            @foreach ($details['Pendapatan Penjualan Barang Jadi'] ?? [] as $subsection => $balance)
                @if ($balance != 0)
                    <tr class="sub-row" data-parent="pendapatanPenjualan">
                        <td></td>
                        <td class="indent">{{ $subsection }}</td>
                        <td class="text-end">
                            {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            @foreach ($details['Pendapatan Sewa'] ?? [] as $subsection => $balance)
                @if ($balance != 0)
                    <tr class="sub-row" data-parent="pendapatanPenjualan">
                        <td></td>
                        <td class="indent">{{ $subsection }}</td>
                        <td class="text-end">
                            {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
            <!-- Harga Pokok Penjualan -->
            <tr class="expandable" data-category="hpp">
                <td>Harga Pokok Penjualan</td>
                <td class="text-end">({{ number_format(abs($hpp), 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Harga Pokok Penjualan'] ?? [] as $code => $balance)
                <tr class="sub-row" data-parent="hpp">
                    <td></td>
                    <td class="indent">
                        {{ \App\Models\ChartOfAccount::where('account_code', $code)->first()->name ?? $code }}
                    </td>
                    <td class="text-end">({{ number_format(abs($balance), 2, ',', '.') }})</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="fw-bolder">Laba Kotor</td>
                <td class="text-end">
                    {{ $labaKotor < 0 ? '(' . number_format(abs($labaKotor), 2, ',', '.') . ')' : number_format($labaKotor, 2, ',', '.') }}
                </td>
            </tr>
            <!-- Beban Operasional -->
            <tr class="expandable" data-category="bebanOperasional">
                <td class="fw-bolder">Beban Operasional</td>
                <td class="text-end">({{ number_format(abs($totalBebanOperasional), 2, ',', '.') }})</td>
            </tr>
            @foreach ($details['Beban Operasional'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="bebanOperasional">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">({{ number_format(abs($balance), 2, ',', '.') }})</td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="fw-bolder">Laba Operasi</td>
                <td class="text-end">
                    {{ $labaOperasi < 0 ? '(' . number_format(abs($labaOperasi), 2, ',', '.') . ')' : number_format($labaOperasi, 2, ',', '.') }}
                </td>
            </tr>

            <!-- Pendapatan Lain-lain -->
            <tr class="expandable" data-category="pendapatanLain">
                <td class="fw-bolder">Pendapatan Lain-lain</td>
                <td class="text-end">
                    {{ $totalPendapatanLain < 0 ? '(' . number_format(abs($totalPendapatanLain), 2, ',', '.') . ')' : number_format($totalPendapatanLain, 2, ',', '.') }}
                </td>
            </tr>
            @foreach ($details['Pendapatan Lain-lain'] ?? [] as $subsection => $balance)
                <tr class="sub-row" data-parent="pendapatanLain">
                    <td></td>
                    <td class="indent">{{ $subsection }}</td>
                    <td class="text-end">
                        {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <td></td>
                <td class="fw-bolder">Laba Sebelum Pajak</td>
                <td class="text-end" id="labaSebelumPajak">
                    {{ $labaSebelumPajak < 0 ? '(' . number_format(abs($labaSebelumPajak), 2, ',', '.') . ')' : number_format($labaSebelumPajak, 2, ',', '.') }}
                </td>
            </tr>

            <!-- Beban Pajak Penghasilan -->
            <tr id="taxRow">
                <td></td>
                <td class="fw-bolder">Pajak Penghasilan Final</td>
                <td class="text-end" id="taxAmount">
                    {{ $bebanPajakPenghasilan < 0 ? '(' . number_format(abs($bebanPajakPenghasilan), 2, ',', '.') . ')' : number_format($bebanPajakPenghasilan, 2, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td></td>
                <td class="fw-bolder total">Laba Bersih/Rugi</td>
                <td class="text-end total" id="labaBersih">
                    {{ $labaSebelumPajak < 0 ? '(' . number_format(abs($labaSebelumPajak), 2, ',', '.') . ')' : number_format($labaSebelumPajak, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    // Store initial values from Blade
    const labaSebelumPajak = {{ $labaSebelumPajak }};
    const bebanPajakPenghasilan = {{ $bebanPajakPenghasilan }};
    const labaBersih = {{ $labaBersih }};

    // Format number to Indonesian Rupiah with parentheses for negative numbers
    function formatRupiah(number) {
        if (number < 0) {
            return '(' + new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Math.abs(number)) + ')';
        }
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number);
    }

    // Toggle tax calculation
    document.getElementById('toggleTax').addEventListener('click', function() {
        const taxRow = document.getElementById('taxRow');
        const labaBersihCell = document.getElementById('labaBersih');
        const isTaxVisible = taxRow.classList.contains('visible');

        if (isTaxVisible) {
            // Hide tax row and show Laba Sebelum Pajak as Laba Bersih
            taxRow.classList.remove('visible');
            labaBersihCell.textContent = formatRupiah(labaSebelumPajak);
            this.textContent = 'Perhitungan Pajak';
        } else {
            // Show tax row and show Laba Bersih with tax
            taxRow.classList.add('visible');
            labaBersihCell.textContent = formatRupiah(labaBersih);
            this.textContent = 'Sembunyikan Pajak';
        }
    });

    // Expandable rows logic
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
