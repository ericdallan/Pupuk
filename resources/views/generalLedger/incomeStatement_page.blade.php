@extends('layouts.app')

@section('content')
@section('title', 'Laporan Laba Rugi')

<style>
    /* Enhanced Button Styles */
    .filter-button {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .filter-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        background: linear-gradient(45deg, #0056b3, #003d80);
    }

    .tax-button {
        background: linear-gradient(45deg, #ffc107, #e0a800);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .tax-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        background: linear-gradient(45deg, #e0a800, #c69500);
    }

    .export-button {
        background: linear-gradient(45deg, #28a745, #1e7e34);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .export-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        background: linear-gradient(45deg, #218838, #1a6030);
    }

    /* Table Enhancements */
    .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table tbody tr:hover {
        background-color: #e9ecef;
        transition: background-color 0.2s;
    }

    /* Card Styling */
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    /* Alert Animations */
    .alert {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Expandable Row Styling */
    .expandable {
        cursor: pointer;
        transition: background-color 0.2s;
        position: relative;
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
        white-space: nowrap;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .row.g-3.align-items-end {
            flex-direction: column;
            align-items: stretch;
        }

        .col-md-3,
        .col-md-6 {
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    }

    /* Tooltip Styling */
    .tooltip-inner {
        background-color: #343a40;
        color: white;
        border-radius: 4px;
    }

    .tooltip .tooltip-arrow::before {
        border-top-color: #343a40;
    }
</style>

<div class="container-fluid mt-2">
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
        </div>
        <div class="card-body">
            @php
                // Default to current month and year if not set
                $currentMonth = \Carbon\Carbon::now()->month;
                $currentYear = \Carbon\Carbon::now()->year;
                $month = $month ?? $currentMonth;
                $year = $year ?? $currentYear;
            @endphp
            <form action="{{ route('incomeStatement_page') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Bulan</label>
                    <select name="month" id="month" class="form-select" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Pilih bulan untuk filter laporan">
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
                        data-bs-placement="top" title="Pilih tahun untuk filter laporan">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn filter-button" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Terapkan filter">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <button type="button" class="btn tax-button" id="toggleTax" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Tampilkan atau sembunyikan perhitungan pajak">
                        <i class="fas fa-calculator me-1"></i>Perhitungan Pajak
                    </button>
                    <a href="{{ route('export_income_statement', ['month' => $month, 'year' => $year]) }}"
                        class="btn export-button" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Unduh laporan sebagai file Excel">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Income Statement Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Rincian Laba Rugi</h5>
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
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

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
                    taxRow.style.display = 'none';
                    const formattedLabaSebelumPajak = formatRupiah(labaSebelumPajak);
                    labaBersihCell.innerHTML = '<strong>' + formattedLabaSebelumPajak + '</strong>';
                    labaBersihCell.className = 'text-end ' + (labaSebelumPajak < 0 ? 'text-danger' :
                        'text-success');
                    this.innerHTML = '<i class="fas fa-calculator me-1"></i>Perhitungan Pajak';
                } else {
                    taxRow.classList.add('visible');
                    taxRow.style.display = 'table-row';
                    const formattedLabaBersih = formatRupiah(labaBersih);
                    labaBersihCell.innerHTML = '<strong>' + formattedLabaBersih + '</strong>';
                    labaBersihCell.className = 'text-end ' + (labaBersih < 0 ? 'text-danger' :
                        'text-success');
                    this.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Sembunyikan Pajak';
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