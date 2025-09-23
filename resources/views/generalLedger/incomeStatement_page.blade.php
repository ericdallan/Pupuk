@extends('layouts.app')

@section('content')
@section('title', 'Laporan Laba Rugi')

<style>
    .highlight {
        background-color: #fefcbf;
        padding: 2px 4px;
        border-radius: 4px;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 0.75rem;
    }

    .expandable {
        cursor: pointer;
        transition: background-color 0.2s;
        position: relative;
    }

    .expandable:hover {
        background-color: #e9ecef;
    }

    .expandable td:first-child::before {
        content: '\25B6';
        display: inline-block;
        width: 20px;
        text-align: center;
        font-size: 0.9em;
        margin-right: 8px;
        transition: transform 0.2s;
    }

    .expandable.expanded td:first-child::before {
        content: '\25BC';
        transform: rotate(0deg);
    }

    .sub-row {
        display: none;
        background-color: #f8f9fa;
    }

    .sub-row.visible {
        display: table-row;
    }

    .sub-row td:first-child {
        width: 40px;
    }

    .sub-row .indent {
        padding-left: 2rem;
    }

    #taxRow {
        display: none !important;
    }

    #taxRow.visible {
        display: table-row !important;
    }

    .summary-card {
        border-left: 4px solid #007bff;
        transition: transform 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-3px);
    }

    .btn-primary,
    .btn-success,
    .btn-warning {
        transition: transform 0.2s;
    }

    .btn-primary:hover,
    .btn-success:hover,
    .btn-warning:hover {
        transform: translateY(-2px);
    }

    .table-responsive {
        overflow-x: auto;
    }

    /* Ensure consistent column widths */
    .table th:first-child,
    .table td:first-child {
        width: 40px;
        text-align: center;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        width: auto;
        min-width: 300px;
    }

    .table th:last-child,
    .table td:last-child {
        width: 200px;
        text-align: right;
    }

    /* Prevent text wrapping in amount column */
    .text-end {
        white-space: nowrap;
    }

    /* Make sure all main rows are visible */
    .main-row {
        display: table-row !important;
    }
</style>

<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <i class="bi bi-funnel me-2"></i>Filter Laporan
        </div>
        <div class="card-body">
            <form action="{{ route('incomeStatement_page') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select" data-bs-toggle="tooltip"
                        title="Pilih bulan untuk filter laporan">
                        <option value="">Semua Bulan</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Tahun</label>
                    <select name="year" id="year" class="form-select" data-bs-toggle="tooltip"
                        title="Pilih tahun untuk filter laporan">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary me-2" data-bs-toggle="tooltip"
                        title="Terapkan filter"><i class="bi bi-filter me-1"></i>Filter</button>
                    <button type="button" class="btn btn-warning me-2" id="toggleTax" data-bs-toggle="tooltip"
                        title="Tampilkan atau sembunyikan perhitungan pajak"><i
                            class="bi bi-calculator me-1"></i>Perhitungan Pajak</button>
                    <a href="{{ route('export_income_statement', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-success" data-bs-toggle="tooltip" title="Unduh laporan sebagai file Excel"><i
                            class="fas fa-file-excel me-2"></i>Export Excel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Income Statement Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <i class="bi bi-table me-2"></i>Rincian Laba Rugi
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0" id="incomeTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Keterangan</th>
                            <th class="text-end">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Pendapatan Penjualan -->
                        @php
                            $hasPendapatanDetails =
                                !empty($details['Pendapatan Penjualan Bahan Baku']) ||
                                !empty($details['Pendapatan Penjualan Barang Jadi']) ||
                                !empty($details['Pendapatan Sewa']);
                        @endphp

                        <tr class="main-row fw-bold {{ $hasPendapatanDetails ? 'expandable' : '' }}"
                            data-category="pendapatanPenjualan">
                            <td></td>
                            <td>Pendapatan Penjualan</td>
                            <td class="text-end {{ $pendapatanPenjualan < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $pendapatanPenjualan < 0 ? '(' . number_format(abs($pendapatanPenjualan), 2, ',', '.') . ')' : number_format($pendapatanPenjualan, 2, ',', '.') }}
                            </td>
                        </tr>

                        @if ($hasPendapatanDetails)
                            @foreach ($details['Pendapatan Penjualan Bahan Baku'] ?? [] as $subsection => $balance)
                                @if ($balance != 0)
                                    <tr class="sub-row" data-parent="pendapatanPenjualan">
                                        <td></td>
                                        <td class="indent">{{ $subsection }}</td>
                                        <td class="text-end {{ $balance < 0 ? 'text-danger' : '' }}">
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
                                        <td class="text-end {{ $balance < 0 ? 'text-danger' : '' }}">
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
                                        <td class="text-end {{ $balance < 0 ? 'text-danger' : '' }}">
                                            {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif

                        <!-- Harga Pokok Penjualan -->
                        @php
                            $hasHppDetails = !empty($details['Harga Pokok Penjualan']);
                        @endphp

                        <tr class="main-row fw-bold {{ $hasHppDetails ? 'expandable' : '' }}" data-category="hpp">
                            <td></td>
                            <td>Harga Pokok Penjualan</td>
                            <td class="text-end text-danger">
                                ({{ number_format(abs($hpp), 2, ',', '.') }})
                            </td>
                        </tr>

                        @if ($hasHppDetails)
                            @foreach ($details['Harga Pokok Penjualan'] ?? [] as $code => $balance)
                                <tr class="sub-row" data-parent="hpp">
                                    <td></td>
                                    <td class="indent">
                                        {{ \App\Models\ChartOfAccount::where('account_code', $code)->first()->account_name ?? $code }}
                                    </td>
                                    <td class="text-end text-danger">
                                        ({{ number_format(abs($balance), 2, ',', '.') }})
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <!-- Laba Kotor -->
                        <tr class="main-row fw-bold bg-light">
                            <td></td>
                            <td><strong>Laba Kotor</strong></td>
                            <td class="text-end {{ $labaKotor < 0 ? 'text-danger' : 'text-success' }}">
                                <strong>{{ $labaKotor < 0 ? '(' . number_format(abs($labaKotor), 2, ',', '.') . ')' : number_format($labaKotor, 2, ',', '.') }}</strong>
                            </td>
                        </tr>

                        <!-- Beban Operasional -->
                        @php
                            $hasBebanDetails = !empty($details['Beban Operasional']);
                        @endphp

                        <tr class="main-row fw-bold {{ $hasBebanDetails ? 'expandable' : '' }}"
                            data-category="bebanOperasional">
                            <td></td>
                            <td>Beban Operasional</td>
                            <td class="text-end text-danger">
                                ({{ number_format(abs($totalBebanOperasional), 2, ',', '.') }})
                            </td>
                        </tr>

                        @if ($hasBebanDetails)
                            @foreach ($details['Beban Operasional'] ?? [] as $subsection => $balance)
                                <tr class="sub-row" data-parent="bebanOperasional">
                                    <td></td>
                                    <td class="indent">{{ $subsection }}</td>
                                    <td class="text-end text-danger">
                                        ({{ number_format(abs($balance), 2, ',', '.') }})
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <!-- Laba Operasi -->
                        <tr class="main-row fw-bold bg-light">
                            <td></td>
                            <td><strong>Laba Operasi</strong></td>
                            <td class="text-end {{ $labaOperasi < 0 ? 'text-danger' : 'text-success' }}">
                                <strong>{{ $labaOperasi < 0 ? '(' . number_format(abs($labaOperasi), 2, ',', '.') . ')' : number_format($labaOperasi, 2, ',', '.') }}</strong>
                            </td>
                        </tr>

                        <!-- Pendapatan Lain-lain -->
                        @php
                            $hasPendapatanLainDetails = !empty($details['Pendapatan Lain-lain']);
                        @endphp

                        <tr class="main-row fw-bold {{ $hasPendapatanLainDetails ? 'expandable' : '' }}"
                            data-category="pendapatanLain">
                            <td></td>
                            <td>Pendapatan Lain-lain</td>
                            <td class="text-end {{ $totalPendapatanLain < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $totalPendapatanLain < 0 ? '(' . number_format(abs($totalPendapatanLain), 2, ',', '.') . ')' : number_format($totalPendapatanLain, 2, ',', '.') }}
                            </td>
                        </tr>

                        @if ($hasPendapatanLainDetails)
                            @foreach ($details['Pendapatan Lain-lain'] ?? [] as $subsection => $balance)
                                <tr class="sub-row" data-parent="pendapatanLain">
                                    <td></td>
                                    <td class="indent">{{ $subsection }}</td>
                                    <td class="text-end {{ $balance < 0 ? 'text-danger' : '' }}">
                                        {{ $balance < 0 ? '(' . number_format(abs($balance), 2, ',', '.') . ')' : number_format($balance, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        <!-- Laba Sebelum Pajak -->
                        <tr class="main-row fw-bold bg-warning bg-opacity-25">
                            <td></td>
                            <td><strong>Laba Sebelum Pajak</strong></td>
                            <td class="text-end {{ $labaSebelumPajak < 0 ? 'text-danger' : 'text-success' }}"
                                id="labaSebelumPajak">
                                <strong>{{ $labaSebelumPajak < 0 ? '(' . number_format(abs($labaSebelumPajak), 2, ',', '.') . ')' : number_format($labaSebelumPajak, 2, ',', '.') }}</strong>
                            </td>
                        </tr>

                        <!-- Pajak (Hidden by default) -->
                        <tr id="taxRow" class="main-row fw-bold">
                            <td></td>
                            <td>Pajak Penghasilan Final</td>
                            <td class="text-end text-danger" id="taxAmount">
                                ({{ number_format(abs($bebanPajakPenghasilan), 2, ',', '.') }})
                            </td>
                        </tr>

                        <!-- Laba Bersih -->
                        <tr class="main-row fw-bold table-success">
                            <td></td>
                            <td><strong>Laba Bersih/Rugi</strong></td>
                            <td class="text-end {{ $labaSebelumPajak < 0 ? 'text-danger' : 'text-success' }}"
                                id="labaBersih">
                                <strong>{{ $labaSebelumPajak < 0 ? '(' . number_format(abs($labaSebelumPajak), 2, ',', '.') . ')' : number_format($labaSebelumPajak, 2, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

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

            const taxRow = document.getElementById('taxRow');
            if (taxRow) {
                taxRow.classList.remove('visible');
                taxRow.style.display = 'none';
            }

            // Toggle tax calculation
            document.getElementById('toggleTax').addEventListener('click', function() {
                const taxRow = document.getElementById('taxRow');
                const labaBersihCell = document.getElementById('labaBersih');
                const isTaxVisible = taxRow.classList.contains('visible');

                if (isTaxVisible) {
                    taxRow.classList.remove('visible');
                    const formattedLabaSebelumPajak = formatRupiah(labaSebelumPajak);
                    labaBersihCell.innerHTML = '<strong>' + formattedLabaSebelumPajak + '</strong>';
                    labaBersihCell.className = 'text-end ' + (labaSebelumPajak < 0 ? 'text-danger' :
                        'text-success');
                    this.innerHTML = '<i class="bi bi-calculator me-1"></i>Perhitungan Pajak';
                } else {
                    taxRow.classList.add('visible');
                    const formattedLabaBersih = formatRupiah(labaBersih);
                    labaBersihCell.innerHTML = '<strong>' + formattedLabaBersih + '</strong>';
                    labaBersihCell.className = 'text-end ' + (labaBersih < 0 ? 'text-danger' :
                        'text-success');
                    this.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Sembunyikan Pajak';
                }
            });

            // Expandable rows logic
            document.querySelectorAll('.expandable').forEach(function(row) {
                const category = row.getAttribute('data-category');
                const subRows = document.querySelectorAll('.sub-row[data-parent="' + category + '"]');

                if (subRows.length > 0) {
                    row.addEventListener('click', function() {
                        subRows.forEach(function(subRow) {
                            subRow.classList.toggle('visible');
                        });
                        this.classList.toggle('expanded');
                    });
                }
            });
        });
    </script>
@endpush
@endsection
