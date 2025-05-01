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
</style>

<div class="mt-4">
    <!-- Form Filter Tahun -->
    <div class="d-flex align-items-end">
        <form action="{{ route('incomeStatement_page') }}" method="GET" class="row g-3 align-items-end flex-grow-1">
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan:</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua Bulan</option>
                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                        </option>
                        @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun:</label>
                <select name="year" id="year" class="form-select">
                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('export_income_statement', ['month' => $month, 'year' => $year]) }}" class="btn btn-secondary">
                    Export as Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Laporan Laba Rugi -->
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Keterangan</th>
                <th class="text-end">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pendapatan Penjualan</td>
                <td class="text-end">{{ number_format($pendapatanPenjualan, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Harga Pokok Penjualan</td>
                <td class="text-end">({{ number_format($hpp, 2, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="fw-bolder">Laba Kotor</td>
                <td class="text-end">{{ number_format($labaKotor, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="fw-bolder">Beban Operasional</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td class="indent">Total Beban Operasional</td>
                <td class="text-end">({{ number_format($totalBebanOperasional, 2, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="fw-bolder">Laba Operasi</td>
                <td class="text-end">{{ number_format($labaOperasi, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="fw-bolder">Pendapatan Lain-lain</td>
                <td class="text-end">{{ number_format($totalPendapatanLain, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="fw-bolder">Beban Lain-lain</td>
                <td class="text-end">({{ number_format($totalBebanLain, 2, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="fw-bolder">Laba Sebelum Pajak</td>
                <td class="text-end">{{ number_format($labaSebelumPajak, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="fw-bolder">Pajak Penghasilan</td>
                <td class="text-end">({{ number_format($totalBebanPajak, 2, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="fw-bolder total">Laba Bersih</td>
                <td class="text-end total">{{ number_format($labaBersih, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection