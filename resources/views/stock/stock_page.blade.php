@extends('layouts.app')

@section('title', 'Stock Barang Dagangan')

@section('content')
    <style>
        /* Existing Button Styles */
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

        .calculation-button {
            background: linear-gradient(45deg, #ffc107, #e0a800);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .calculation-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
            background: linear-gradient(45deg, #e0a800, #c69500);
        }

        .history-button {
            background: linear-gradient(45deg, #ffca2c, #e0a800);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .history-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 202, 44, 0.3);
            background: linear-gradient(45deg, #e0a800, #c69500);
        }

        .search-button {
            background: linear-gradient(45deg, #17a2b8, #117a8b);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
            background: linear-gradient(45deg, #138496, #0d5d6b);
        }

        .btn-disabled {
            background-color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
            opacity: 0.65;
            transition: opacity 0.2s;
        }

        /* Table Enhancements */
        .table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background: linear-gradient(90deg, #343a40, #212529);
            color: white;
            position: sticky;
            top: 0;
            z-index: 15;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* Subtle shadow for elevation */
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
            font-size: 0.95rem;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.2s;
        }

        /* Ensure table-responsive supports sticky header */
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
            position: relative;
            -webkit-overflow-scrolling: touch;
            /* Smooth scrolling on mobile */
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                max-height: 400px;
                /* Smaller height for mobile */
            }

            .table thead th {
                font-size: 0.85rem;
                padding: 8px;
            }

            .table tbody td {
                font-size: 0.85rem;
            }
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

        /* Badge Styles */
        .status-badge {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 12px;
        }

        /* Table Section */
        .table-section {
            display: block;
        }

        .table-section.hidden {
            display: none;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .no-results-message {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        .btn-group .btn {
            margin-right: 0;
        }

        .btn-group .btn:not(:last-child) {
            border-right: none;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .btn.disabled,
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn-group {
            display: inline-flex;
            vertical-align: middle;
        }

        .btn-group .btn:not(:first-child) {
            margin-left: -1px;
        }

        .btn-group .btn:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-group .btn:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .btn-sm .fas {
            font-size: 0.7rem;
            margin-right: 0.25rem;
        }

        .btn[title] {
            cursor: help;
        }

        .btn:disabled[title] {
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
                width: 100%;
            }

            .btn-group .btn {
                margin-left: 0;
                margin-bottom: 0.25rem;
                border-radius: 0.375rem !important;
            }

            .btn-group .btn:last-child {
                margin-bottom: 0;
            }
        }

        .mode-switch-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-check:checked+.btn-outline-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border-color: #007bff;
            color: white;
        }

        .btn-check:checked+.btn-outline-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
            border-color: #28a745;
            color: white;
        }

        .mode-switch-container .btn {
            transition: all 0.3s ease;
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .mode-switch-container .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #mode-indicator {
            white-space: nowrap;
        }

        .applied-cost-detail {
            max-height: 100px;
            overflow-y: auto;
            font-size: 0.875rem;
        }

        .applied-cost-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
        }

        .applied-cost-item:last-child {
            border-bottom: none;
        }

        .management-mode .hpp-column {
            background-color: rgba(40, 167, 69, 0.1);
            transition: background-color 0.3s ease;
        }

        .hpp-plus-indicator {
            font-size: 0.75rem;
            color: #28a745;
            font-weight: bold;
            margin-top: 0.25rem;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
            display: none;
        }

        .management-mode .hpp-plus-indicator {
            display: block;
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .mode-switch-container {
                width: 100%;
                justify-content: space-between;
            }

            .mode-switch-container .btn {
                flex: 1;
                font-size: 0.8rem;
            }

            .hpp-plus-indicator {
                font-size: 0.65rem;
            }
        }
    </style>

    <!-- Notifikasi -->
    @if (session('success'))
        <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mt-1">
        <!-- Main Filter Form -->
        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('stock_page') }}"
                        class="d-flex flex-wrap align-items-end gap-3">
                        <div class="flex-shrink-0">
                            <label for="start_date" class="form-label small">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date', now()->startOfYear()->toDateString()) }}"
                                min="{{ now()->subYears(5)->startOfYear()->toDateString() }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="flex-shrink-0">
                            <label for="end_date" class="form-label small">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date', now()->toDateString()) }}"
                                min="{{ request('start_date', now()->startOfYear()->toDateString()) }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="flex-shrink-0" hidden>
                            <input type="hidden" name="table_filter" id="table_filter"
                                value="{{ request('table_filter', 'all') }}">
                            <div class="dropdown me-2">
                                <button class="btn btn-light dropdown-toggle" type="button" id="tableFilterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ request('table_filter', 'all') == 'all' ? 'Semua Tabel' : (request('table_filter') == 'stocks' ? 'Tabel Stok' : (request('table_filter') == 'transfer_stocks' ? 'Tabel Transfer Stok' : 'Tabel Stok Terpakai')) }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="tableFilterDropdown">
                                    <li><a class="dropdown-item" href="#" data-filter="all">Semua Tabel</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="stocks">Tabel Stok</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn filter-button" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Terapkan filter berdasarkan tanggal">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}"
                                class="btn export-button" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Ekspor data stok ke Excel">
                                <i class="fas fa-file-excel me-1"></i> Ekspor ke Excel
                            </a>
                            @if (App\Http\Controllers\Auth\AuthController::isMaster())
                                <button type="button" class="btn calculation-button" data-bs-toggle="modal"
                                    data-bs-target="#loadCalculationModal" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Lakukan perhitungan beban">
                                    <i class="fas fa-calculator me-1"></i> Perhitungan Beban
                                </button>
                                <button type="button" class="btn history-button" data-bs-toggle="modal"
                                    data-bs-target="#appliedCostHistoryModal" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Lihat riwayat perhitungan beban">
                                    <i class="fas fa-history me-1"></i> Riwayat Perhitungan
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Global Search Filter -->
        <div class="mb-4">
            <div class="input-group">
                <input type="text" id="globalSearch" class="form-control"
                    placeholder="Cari nama barang atau ukuran...">
                <button class="btn search-button" type="button" id="clearSearch" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Hapus pencarian">Hapus</button>
            </div>
            <small id="searchCount" class="form-text text-muted mt-2"></small>
        </div>

        <!-- Tables Section -->
        <div class="table-container">
            <!-- Stocks Table -->
            <div class="table-section mb-4" data-table="stocks">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Stok Barang Dagangan</h3>
                    @if (App\Http\Controllers\Auth\AuthController::isMaster())
                        <div class="mode-switch-container">
                            <div class="btn-group" role="group" aria-label="Mode Selection">
                                <input type="radio" class="btn-check" name="stockMode" id="accounting-mode"
                                    value="accounting" checked>
                                <label class="btn btn-outline-primary" for="accounting-mode">
                                    <i class="fas fa-calculator me-1"></i> Akuntansi
                                </label>
                                <input type="radio" class="btn-check" name="stockMode" id="management-mode"
                                    value="management">
                                <label class="btn btn-outline-success" for="management-mode">
                                    <i class="fas fa-chart-line me-1"></i> Manajemen
                                </label>
                            </div>
                            <small class="text-muted ms-2" id="mode-indicator">
                                Mode: <span class="fw-bold text-primary">Akuntansi</span>
                            </small>
                        </div>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Ukuran</th>
                                <th colspan="2">Saldo Awal</th>
                                <th colspan="2">Masuk Barang</th>
                                <th colspan="2">Keluar Barang</th>
                                <th colspan="2">Saldo Tersedia (Akhir)</th>
                                <th>Detail</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th>Qty</th>
                                <th class="hpp-column">HPP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $itemCount = 1;
                                $currentItem = '';
                            @endphp
                            @if (is_array($stockData) && !empty($stockData))
                                @foreach ($stockData as $item => $sizes)
                                    @foreach ($sizes as $index => $stock)
                                        <tr data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                            data-size="{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}"
                                            data-opening-hpp="{{ number_format($stock->opening_hpp ?? 0, 2, '.', '') }}"
                                            data-incoming-hpp="{{ number_format($stock->incoming_hpp ?? 0, 2, '.', '') }}"
                                            data-outgoing-hpp="{{ number_format($stock->outgoing_hpp ?? 0, 2, '.', '') }}"
                                            data-final-hpp="{{ number_format($stock->final_hpp ?? 0, 2, '.', '') }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                                                <td rowspan="{{ count($sizes) }}" class="item-name">
                                                    {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                                            @endif
                                            <td class="item-size">{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}
                                            </td>
                                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator"></div>
                                            </td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm btn-info detail-btn" data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="stocks" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Lihat detail stok">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @else
                                                    <span>Tidak Ada Detail</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="12" class="text-center">
                                        <div class="alert alert-info mb-0">Data stok belum ditemukan.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transaction Detail Modals -->
        @php
            $allStocks = [
                'stocks' => collect($stockData)->flatten()->toArray() ?? [],
            ];
        @endphp
        @foreach ($allStocks as $tableName => $stocks)
            @foreach ($stocks as $stock)
                @if ($stock->id)
                    <div class="modal fade" id="detailModal_{{ $tableName }}_{{ $stock->id }}" tabindex="-1"
                        aria-labelledby="detailModalLabel_{{ $tableName }}_{{ $stock->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" data-table-name="{{ $tableName }}">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="detailModalLabel_{{ $tableName }}_{{ $stock->id }}">
                                        Detail Transaksi untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}
                                        ({{ $tableName }})
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Summary Section -->
                                    @if (isset($stock->transactions) && !empty($stock->transactions))
                                        <div class="mb-4 p-3 bg-light rounded shadow-sm">
                                            <h6 class="fw-bold">Ringkasan Transaksi</h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <small class="text-muted">Total Kuantitas</small>
                                                    <p class="mb-0 fw-bold">
                                                        {{ collect($stock->transactions)->sum('quantity') ?? (collect($stock->transactions)->sum('transaction_quantity') ?? 0) }}
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Total Nominal</small>
                                                    <p class="mb-0 fw-bold">
                                                        {{ number_format(collect($stock->transactions)->sum('nominal') ?? 0, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Jumlah Transaksi</small>
                                                    <p class="mb-0 fw-bold">{{ count($stock->transactions) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Filter Section -->
                                    <div class="mb-3">
                                        <label for="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-label required">Tampilkan Transaksi</label>
                                        <select id="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-select modal-filter" data-stock-id="{{ $stock->id }}"
                                            data-bs-toggle="tooltip"
                                            title="Pilih rentang waktu untuk menampilkan transaksi">
                                            <option value="all"
                                                {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>
                                                Semua Transaksi
                                            </option>
                                            <option value="7_days"
                                                {{ request('filter', 'all') == '7_days' ? 'selected' : '' }}>
                                                7 Hari Terakhir
                                            </option>
                                            <option value="1_month"
                                                {{ request('filter', 'all') == '1_month' ? 'selected' : '' }}>
                                                1 Bulan Terakhir
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Transaction Table -->
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover text-center">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th data-bs-toggle="tooltip" title="Nomor urut transaksi">No
                                                    </th>
                                                    <th data-bs-toggle="tooltip" title="Nama item yang ditransaksikan">
                                                        Nama Item</th>
                                                    <th data-bs-toggle="tooltip" title="Nomor voucher terkait">No
                                                        Voucher</th>
                                                    <th data-bs-toggle="tooltip"
                                                        title="Jenis transaksi (Penjualan, Pembelian, dll)">Tipe
                                                        Transaksi</th>
                                                    <th data-bs-toggle="tooltip" title="Jumlah item yang ditransaksikan">
                                                        Kuantitas</th>
                                                    <th data-bs-toggle="tooltip" title="Nilai nominal transaksi">
                                                        Nominal</th>
                                                    <th data-bs-toggle="tooltip" title="Tanggal transaksi">Tanggal
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($stock->transactions) && !empty($stock->transactions))
                                                    @foreach ($stock->transactions as $transaction)
                                                        @if (!str_starts_with($transaction->description ?? '', 'HPP '))
                                                            <tr data-transaction-date="{{ $transaction->created_at }}">
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>{{ htmlspecialchars($transaction->description ?? 'No Description') }}
                                                                </td>
                                                                <td>
                                                                    @if ($transaction->voucher_id && $transaction->voucher_number !== 'N/A')
                                                                        <a href="{{ route('voucher_detail', $transaction->voucher_id) }}"
                                                                            class="text-decoration-none text-primary"
                                                                            data-bs-toggle="tooltip"
                                                                            title="Lihat detail voucher">
                                                                            {{ htmlspecialchars($transaction->voucher_number) }}
                                                                        </a>
                                                                    @else
                                                                        {{ htmlspecialchars($transaction->voucher_number ?? 'No Voucher') }}
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @switch($transaction->voucher_type)
                                                                        @case('PJ')
                                                                            Penjualan
                                                                        @break

                                                                        @case('PB')
                                                                            Pembelian
                                                                        @break

                                                                        @case('PYK')
                                                                            Penyesuaian Berkurang
                                                                        @break

                                                                        @case('PYB')
                                                                            Penyesuaian Bertambah
                                                                        @break

                                                                        @case('RPB')
                                                                            Retur Pembelian
                                                                        @break

                                                                        @case('RPJ')
                                                                            Retur Penjualan
                                                                        @break

                                                                        @default
                                                                            {{ htmlspecialchars($transaction->voucher_type ?? 'Unknown') }}
                                                                    @endswitch
                                                                </td>
                                                                <td>{{ $transaction->quantity ?? ($transaction->transaction_quantity ?? 0) }}
                                                                </td>
                                                                <td>{{ number_format($transaction->nominal ?? 0, 2, ',', '.') }}
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($transaction->created_at ?? now())->format('d-m-Y') }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center">
                                                            <div class="alert alert-info mb-0">Tidak ada transaksi terkait
                                                                untuk barang ini.</div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Loading Indicator -->
                                    <div class="loading-indicator d-none text-center my-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Memuat...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn cancel-button" data-bs-dismiss="modal"
                                        data-bs-toggle="tooltip" title="Tutup jendela">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endforeach

        <!-- Modal Perhitungan Beban -->
        <div class="modal fade" id="loadCalculationModal" tabindex="-1" aria-labelledby="loadCalculationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loadCalculationModalLabel">Perhitungan Beban</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <form id="loadCalculationForm" action="{{ route('applied_cost.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                            <div id="bebanInputs">
                                <div class="input-group mb-2 beban-row">
                                    <span class="input-group-text">Beban 1</span>
                                    <input type="text"
                                        class="form-control @error('beban_description.*') is-invalid @enderror"
                                        name="beban_description[]" placeholder="Deskripsi Beban (e.g., Beban Operasional)"
                                        required>
                                    <input type="number"
                                        class="form-control @error('beban_nominal.*') is-invalid @enderror"
                                        name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0"
                                        required>
                                    <button type="button" class="btn btn-outline-danger remove-row"
                                        style="display: none;">Hapus</button>
                                    @error('beban_description.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('beban_nominal.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary mb-3" id="addBebanRow"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah baris beban baru">Tambah
                                Beban</button>
                            <div class="row">
                                <div class="col-md-8"></div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Akumulasi:</label>
                                    <input type="text" class="form-control bg-light" id="totalAkumulasi" readonly
                                        value="Rp 0">
                                    <input type="hidden" name="total" id="totalHidden" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Hitung & Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Applied Cost History Modal -->
        <div class="modal fade" id="appliedCostHistoryModal" tabindex="-1"
            aria-labelledby="appliedCostHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="appliedCostHistoryModalLabel">
                            <i class="fas fa-history me-2"></i> Riwayat Perhitungan Beban
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($currentMode == 'management')
                            <div class="row mb-3" id="appliedCostModeSelection">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Mode Manajemen Aktif:</strong> Pilih riwayat perhitungan beban untuk
                                        diterapkan
                                        pada perhitungan HPP.
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <label class="form-label mb-0">Pilih Riwayat yang Digunakan:</label>
                                        <select class="form-select w-auto" id="selectedAppliedCostId"
                                            name="applied_cost_id">
                                            <option value="">Pilih Riwayat...</option>
                                            @foreach ($appliedCostHistory as $cost)
                                                <option value="{{ $cost->id }}"
                                                    {{ $selectedAppliedCostId == $cost->id ? 'selected' : '' }}>
                                                    #{{ $cost->id }} - Rp.
                                                    {{ number_format($cost->total_nominal, 2, ',', '.') }}
                                                    ({{ $cost->created_at->format('d M Y H:i') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-sm btn-success" id="applySelectedCost"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Terapkan riwayat beban yang dipilih">
                                            <i class="fas fa-check me-1"></i> Terapkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="clearSelectedCost"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Hapus pilihan riwayat beban">
                                            <i class="fas fa-times me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Search and Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="historySearch"
                                    placeholder="Cari berdasarkan tanggal atau deskripsi...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="historyDateFilter">
                                    <option value="">Semua Tanggal</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="week">Minggu Ini</option>
                                    <option value="month">Bulan Ini</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-primary w-100" id="refreshHistory"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Segarkan daftar riwayat">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh
                                </button>
                            </div>
                        </div>

                        <!-- History Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="appliedCostHistoryTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">
                                            <input type="radio" name="appliedCostSelection" value=""
                                                id="noCostSelection" checked>
                                        </th>
                                        <th width="10%">ID</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="15%">Total Nominal</th>
                                        <th width="40%">Detail Beban</th>
                                        <th width="10%">Status</th>
                                        <th width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    @if ($appliedCostHistory->isEmpty())
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada riwayat perhitungan beban
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($appliedCostHistory as $item)
                                            <tr data-date="{{ $item->created_at->toDateString() }}"
                                                data-description="{{ collect($item->details)->pluck('description')->implode(' ') }}">
                                                <td>
                                                    <input type="radio" name="appliedCostSelection"
                                                        value="{{ $item->id }}"
                                                        {{ $selectedAppliedCostId == $item->id ? 'checked' : '' }}>
                                                </td>
                                                <td><strong>#{{ $item->id }}</strong></td>
                                                <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                                                <td><strong class="text-success">Rp.
                                                        {{ number_format($item->total_nominal, 2, ',', '.') }}</strong>
                                                </td>
                                                <td>
                                                    <div class="applied-cost-detail">
                                                        @foreach ($item->details as $detail)
                                                            <div class="applied-cost-item">
                                                                <span>{{ $detail->description }}</span>
                                                                <span class="text-success">Rp.
                                                                    {{ number_format($detail->nominal, 2, ',', '.') }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge status-badge {{ $selectedAppliedCostId == $item->id ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ $selectedAppliedCostId == $item->id ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary view-detail-btn"
                                                        data-cost-id="{{ $item->id }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Lihat detail beban">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- No Pagination -->
                        <div class="alert alert-info mt-3">
                            Total Riwayat: {{ $appliedCostHistory->count() }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <span class="text-muted" id="historyCount">Total: {{ $appliedCostHistory->count() }}
                                    riwayat</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Data resep dan riwayat beban dari controller
                const appliedCostHistory = @json($appliedCostHistory->items() ?? []);
                let currentSelectedCostId = '{{ $selectedAppliedCostId ?? '' }}';

                // Define variables with null checks
                const form = document.getElementById('filterForm');
                const tableFilterInput = document.getElementById('table_filter');
                const dropdownButton = document.getElementById('tableFilterDropdown');
                const tableSections = document.querySelectorAll('.table-section');
                const globalSearch = document.getElementById('globalSearch');
                const clearSearch = document.getElementById('clearSearch');
                const searchCount = document.getElementById('searchCount');
                const addBebanRow = document.getElementById('addBebanRow');
                const bebanInputs = document.getElementById('bebanInputs');
                const totalAkumulasi = document.getElementById('totalAkumulasi');
                const totalHidden = document.getElementById('totalHidden');
                const loadCalculationForm = document.getElementById('loadCalculationForm');
                const errorMessage = document.getElementById('errorMessage');
                const accountingMode = document.getElementById('accounting-mode');
                const managementMode = document.getElementById('management-mode');
                const modeIndicator = document.getElementById('mode-indicator');
                const tableContainer = document.querySelector('[data-table="stocks"]');
                const appliedCostHistoryModal = document.getElementById('appliedCostHistoryModal');
                const applySelectedCostBtn = document.getElementById('applySelectedCost');
                const clearSelectedCostBtn = document.getElementById('clearSelectedCost');
                const historySearch = document.getElementById('historySearch');
                const historyDateFilter = document.getElementById('historyDateFilter');
                const refreshHistoryBtn = document.getElementById('refreshHistory');
                const historyCount = document.getElementById('historyCount');
                const selectedAppliedCostId = document.getElementById('selectedAppliedCostId');

                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Auto-dismiss alerts
                const successMessage = document.getElementById('success-message');
                if (successMessage) {
                    setTimeout(() => successMessage.classList.add('fade'), 3000);
                }
                const errorMessageAlert = document.getElementById('error-message');
                if (errorMessageAlert) {
                    setTimeout(() => errorMessageAlert.classList.add('fade'), 5000);
                }

                // Function to format currency
                function formatCurrency(value) {
                    return 'Rp. ' + Number(value).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // Function to update total akumulasi
                function updateTotal() {
                    if (!totalAkumulasi || !totalHidden) return;
                    let total = 0;
                    const nominalInputs = bebanInputs.querySelectorAll('input[name="beban_nominal[]"]');
                    nominalInputs.forEach(input => {
                        total += parseFloat(input.value) || 0;
                    });
                    totalAkumulasi.value = formatCurrency(total);
                    totalHidden.value = total;
                }

                // Event listener for adding new beban row
                if (addBebanRow && bebanInputs) {
                    addBebanRow.addEventListener('click', function() {
                        const rowCount = bebanInputs.querySelectorAll('.beban-row').length + 1;
                        const newRow = document.createElement('div');
                        newRow.className = 'input-group mb-2 beban-row';
                        newRow.innerHTML = `
                        <span class="input-group-text">Beban ${rowCount}</span>
                        <input type="text" class="form-control" name="beban_description[]" placeholder="Deskripsi Beban" required>
                        <input type="number" class="form-control" name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0" required>
                        <button type="button" class="btn btn-outline-danger remove-row">Hapus</button>
                    `;
                        bebanInputs.appendChild(newRow);
                        updateRemoveButtons();
                        updateTotal();
                    });
                }

                // Function to update remove buttons visibility
                function updateRemoveButtons() {
                    if (!bebanInputs) return;
                    const rows = bebanInputs.querySelectorAll('.beban-row');
                    const allRemoveBtns = bebanInputs.querySelectorAll('.remove-row');
                    allRemoveBtns.forEach((btn, index) => {
                        btn.style.display = index === 0 ? 'none' : '';
                    });
                    rows.forEach((row, index) => {
                        const span = row.querySelector('.input-group-text');
                        if (span) span.textContent = `Beban ${index + 1}`;
                    });
                }

                // Event delegation for removing rows
                if (bebanInputs) {
                    bebanInputs.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-row')) {
                            e.target.closest('.beban-row').remove();
                            updateRemoveButtons();
                            updateTotal();
                        }
                    });
                }

                // Real-time total update
                if (bebanInputs) {
                    bebanInputs.addEventListener('input', function(e) {
                        if (e.target.name === 'beban_nominal[]') {
                            updateTotal();
                        }
                    });
                }

                updateTotal();

                // Update HPP columns
                function updateHPPColumns(totalNominal = 0) {
                    if (!tableContainer) return;
                    const isManagementMode = managementMode && managementMode.checked;
                    const rows = tableContainer.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const hppCells = row.querySelectorAll('.hpp-column');
                        if (!hppCells.length) return;
                        const openingHpp = parseFloat(row.dataset.openingHpp || 0);
                        const incomingHpp = parseFloat(row.dataset.incomingHpp || 0);
                        const outgoingHpp = parseFloat(row.dataset.outgoingHpp || 0);
                        const finalHpp = parseFloat(row.dataset.finalHpp || 0);

                        const updateCell = (cell, originalHpp) => {
                            const plusIndicator = cell.querySelector('.hpp-plus-indicator');
                            if (isManagementMode && totalNominal > 0) {
                                const newHpp = originalHpp + totalNominal;
                                cell.firstChild.textContent = formatCurrency(newHpp);
                                if (plusIndicator) {
                                    plusIndicator.textContent = `+ ${formatCurrency(totalNominal)}`;
                                }
                            } else {
                                cell.firstChild.textContent = formatCurrency(originalHpp);
                                if (plusIndicator) {
                                    plusIndicator.textContent = '';
                                }
                            }
                        };

                        updateCell(hppCells[0], openingHpp);
                        updateCell(hppCells[1], incomingHpp);
                        updateCell(hppCells[2], outgoingHpp);
                        updateCell(hppCells[3], finalHpp);
                    });
                }

                // Auto-select first applied cost
                function autoSelectFirstCost() {
                    if (!appliedCostHistoryModal || !selectedAppliedCostId) return;
                    const firstCost = appliedCostHistory[0];
                    if (firstCost) {
                        currentSelectedCostId = firstCost.id;
                        selectedAppliedCostId.value = firstCost.id;
                        const radio = document.querySelector(
                            `input[name="appliedCostSelection"][value="${firstCost.id}"]`);
                        if (radio) radio.checked = true;
                        document.querySelectorAll('#historyTableBody tr').forEach(row => {
                            const badge = row.querySelector('.status-badge');
                            const rowRadio = row.querySelector('input[name="appliedCostSelection"]');
                            if (badge && rowRadio && rowRadio.value === firstCost.id) {
                                badge.textContent = 'Aktif';
                                badge.classList.remove('bg-secondary');
                                badge.classList.add('bg-success');
                            } else if (badge) {
                                badge.textContent = 'Tidak Aktif';
                                badge.classList.remove('bg-success');
                                badge.classList.add('bg-secondary');
                            }
                        });
                        updateHPPColumns(parseFloat(firstCost.total_nominal || 0));
                        if (form && tableFilterInput) {
                            form.action = '{{ route('stock_page') }}?start_date=' + document.getElementById(
                                    'start_date').value +
                                '&end_date=' + document.getElementById('end_date').value +
                                '&table_filter=' + tableFilterInput.value +
                                '&mode=management&applied_cost_id=' + firstCost.id;
                            form.submit();
                        }
                    } else {
                        alert('Tidak ada riwayat perhitungan beban untuk mode manajemen.');
                        updateHPPColumns(0);
                    }
                }

                // Applied Cost Modal Logic
                if (appliedCostHistoryModal) {
                    appliedCostHistoryModal.addEventListener('shown.bs.modal', function() {
                        const isManagementMode = managementMode && managementMode.checked;
                        const modeSelection = document.getElementById('appliedCostModeSelection');
                        if (modeSelection) {
                            modeSelection.style.display = isManagementMode ? 'block' : 'none';
                        }
                    });

                    if (historySearch) {
                        historySearch.addEventListener('input', debounce(filterHistory, 300));
                    }

                    if (historyDateFilter) {
                        historyDateFilter.addEventListener('change', filterHistory);
                    }

                    if (refreshHistoryBtn) {
                        refreshHistoryBtn.addEventListener('click', function() {
                            if (historySearch) historySearch.value = '';
                            if (historyDateFilter) historyDateFilter.value = '';
                            filterHistory();
                        });
                    }

                    if (applySelectedCostBtn) {
                        applySelectedCostBtn.addEventListener('click', function() {
                            if (!selectedAppliedCostId || !selectedAppliedCostId.value) {
                                alert('Silakan pilih riwayat perhitungan beban terlebih dahulu.');
                                return;
                            }
                            const selectedId = selectedAppliedCostId.value;
                            currentSelectedCostId = selectedId;
                            const selectedCost = appliedCostHistory.find(cost => cost.id == selectedId);
                            if (selectedCost) {
                                updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                            }
                            if (form && tableFilterInput) {
                                form.action = '{{ route('stock_page') }}?start_date=' + document
                                    .getElementById('start_date').value +
                                    '&end_date=' + document.getElementById('end_date').value +
                                    '&table_filter=' + tableFilterInput.value +
                                    '&mode=management&applied_cost_id=' + selectedId;
                                form.submit();
                            }
                        });
                    }

                    if (clearSelectedCostBtn) {
                        clearSelectedCostBtn.addEventListener('click', function() {
                            currentSelectedCostId = '';
                            if (selectedAppliedCostId) selectedAppliedCostId.value = '';
                            const radios = document.querySelectorAll('input[name="appliedCostSelection"]');
                            radios.forEach(radio => radio.checked = radio.value === '');
                            document.querySelectorAll('#historyTableBody tr').forEach(row => {
                                const badge = row.querySelector('.status-badge');
                                if (badge) {
                                    badge.textContent = 'Tidak Aktif';
                                    badge.classList.remove('bg-success');
                                    badge.classList.add('bg-secondary');
                                }
                            });
                            updateHPPColumns(0);
                            if (form && tableFilterInput) {
                                form.action = '{{ route('stock_page') }}?start_date=' + document
                                    .getElementById('start_date').value +
                                    '&end_date=' + document.getElementById('end_date').value +
                                    '&table_filter=' + tableFilterInput.value +
                                    '&mode=accounting';
                                form.submit();
                            }
                        });
                    }

                    document.querySelectorAll('.view-detail-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const costId = this.dataset.costId;
                            const row = document.querySelector(
                                `#historyTableBody tr:has(input[value="${costId}"])`);
                            if (row) {
                                const detailDiv = row.querySelector('.applied-cost-detail');
                                if (detailDiv) {
                                    detailDiv.style.display = detailDiv.style.display === 'none' ?
                                        'block' : 'none';
                                }
                            }
                        });
                    });

                    document.querySelectorAll('input[name="appliedCostSelection"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            const selectedId = this.value;
                            if (selectedAppliedCostId) selectedAppliedCostId.value = selectedId;
                            document.querySelectorAll('#historyTableBody tr').forEach(row => {
                                const badge = row.querySelector('.status-badge');
                                const rowRadio = row.querySelector(
                                    'input[name="appliedCostSelection"]');
                                if (badge && rowRadio && rowRadio.value === selectedId &&
                                    rowRadio.checked) {
                                    badge.textContent = 'Aktif';
                                    badge.classList.remove('bg-secondary');
                                    badge.classList.add('bg-success');
                                } else if (badge) {
                                    badge.textContent = 'Tidak Aktif';
                                    badge.classList.remove('bg-success');
                                    badge.classList.add('bg-secondary');
                                }
                            });
                            const selectedCost = appliedCostHistory.find(cost => cost.id == selectedId);
                            if (selectedCost) {
                                updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                            }
                        });
                    });

                    function filterHistory() {
                        const searchValue = (historySearch ? historySearch.value.trim().toLowerCase() : '');
                        const dateFilter = (historyDateFilter ? historyDateFilter.value : '');
                        const rows = document.querySelectorAll('#historyTableBody tr:not([data-date=""])');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const dateStr = row.dataset.date;
                            const description = (row.dataset.description || '').toLowerCase();
                            const date = new Date(dateStr);
                            const now = new Date();
                            let showRow = true;

                            if (searchValue && !description.includes(searchValue)) {
                                showRow = false;
                            }

                            if (dateFilter === 'today') {
                                showRow = showRow && date.toDateString() === now.toDateString();
                            } else if (dateFilter === 'week') {
                                showRow = showRow && date >= new Date(now.setDate(now.getDate() - 7));
                            } else if (dateFilter === 'month') {
                                showRow = showRow && date >= new Date(now.setMonth(now.getMonth() - 1));
                            }

                            row.style.display = showRow ? '' : 'none';
                            if (showRow) visibleCount++;
                        });

                        if (historyCount) {
                            historyCount.textContent = `Total: ${visibleCount} riwayat`;
                        }
                    }
                }

                // Table visibility
                function updateTableVisibility(filter) {
                    if (!tableSections || !dropdownButton || !tableFilterInput) return;
                    tableSections.forEach(section => {
                        section.classList.toggle('hidden', filter !== 'all' && filter !== section.dataset
                            .table);
                    });
                    applyGlobalFilter();
                }

                if (tableFilterInput && dropdownButton) {
                    const initialFilter = tableFilterInput.value || 'all';
                    updateTableVisibility(initialFilter);
                    dropdownButton.textContent = {
                        'all': 'Semua Tabel',
                        'stocks': 'Tabel Stok',
                        'transfer_stocks': 'Tabel Transfer Stok',
                        'used_stocks': 'Tabel Stok Terpakai'
                    } [initialFilter] || 'Semua Tabel';
                }

                document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const filterValue = this.dataset.filter;
                        if (tableFilterInput) tableFilterInput.value = filterValue;
                        if (dropdownButton) dropdownButton.textContent = this.textContent;
                        updateTableVisibility(filterValue);
                    });
                });

                // Global Filter
                function applyGlobalFilter() {
                    if (!globalSearch || !searchCount) return;
                    const searchValue = globalSearch.value.trim().toLowerCase();
                    let totalVisibleItems = 0;
                    let currentItem = '';
                    let itemRows = [];
                    let itemMatches = false;

                    document.querySelectorAll('.highlight').forEach(el => {
                        el.replaceWith(el.textContent);
                    });

                    tableSections.forEach(section => {
                        if (section.classList.contains('hidden')) return;
                        const rows = section.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            if (row.querySelector('td[colspan]')) return;
                            const item = row.dataset.item ? row.dataset.item.toLowerCase() : '';
                            const size = row.dataset.size ? row.dataset.size.toLowerCase() : '';

                            if (item !== currentItem) {
                                if (itemRows.length > 0) {
                                    if (itemMatches) {
                                        itemRows.forEach(r => {
                                            r.style.display = '';
                                            totalVisibleItems++;
                                        });
                                    } else {
                                        itemRows.forEach(r => r.style.display = 'none');
                                    }
                                }
                                currentItem = item;
                                itemRows = [];
                                itemMatches = false;
                            }

                            itemRows.push(row);

                            const matchesItem = item.includes(searchValue);
                            const matchesSize = size.includes(searchValue);
                            if (matchesItem || matchesSize) {
                                itemMatches = true;
                                if (searchValue) {
                                    if (matchesItem) {
                                        const nameTd = row.querySelector('.item-name');
                                        if (nameTd) {
                                            nameTd.innerHTML = nameTd.textContent.replace(new RegExp(
                                                    searchValue, 'gi'), match =>
                                                `<span class="highlight">${match}</span>`);
                                        }
                                    }
                                    if (matchesSize) {
                                        const sizeTd = row.querySelector('.item-size');
                                        if (sizeTd) {
                                            sizeTd.innerHTML = sizeTd.textContent.replace(new RegExp(
                                                    searchValue, 'gi'), match =>
                                                `<span class="highlight">${match}</span>`);
                                        }
                                    }
                                }
                            }
                        });

                        if (itemRows.length > 0) {
                            if (itemMatches) {
                                itemRows.forEach(r => {
                                    r.style.display = '';
                                    totalVisibleItems++;
                                });
                            } else {
                                itemRows.forEach(r => r.style.display = 'none');
                            }
                        }
                    });

                    searchCount.textContent = searchValue ? `${totalVisibleItems} item ditemukan` : '';
                }

                if (globalSearch) globalSearch.addEventListener('keyup', applyGlobalFilter);
                if (clearSearch) clearSearch.addEventListener('click', () => {
                    if (globalSearch) globalSearch.value = '';
                    applyGlobalFilter();
                });

                // Mode switch
                function updateMode(mode) {
                    if (!modeIndicator || !tableContainer) return;
                    const indicatorSpan = modeIndicator.querySelector('span');
                    if (mode === 'accounting') {
                        indicatorSpan.textContent = 'Akuntansi';
                        indicatorSpan.className = 'fw-bold text-primary';
                        tableContainer.classList.remove('management-mode');
                        document.querySelectorAll('.hpp-column').forEach(col => col.style.display = '');
                        const emptyRow = document.querySelector('td[colspan="12"]');
                        if (emptyRow) emptyRow.setAttribute('colspan', '12');
                        updateHPPColumns(0);
                    } else if (mode === 'management') {
                        indicatorSpan.textContent = 'Manajemen';
                        indicatorSpan.className = 'fw-bold text-success';
                        tableContainer.classList.add('management-mode');
                        document.querySelectorAll('.hpp-column').forEach(col => col.style.display = '');
                        const emptyRow = document.querySelector('td[colspan="12"]');
                        if (emptyRow) emptyRow.setAttribute('colspan', '12');
                        autoSelectFirstCost();
                    }
                    localStorage.setItem('stockViewMode', mode);
                    window.dispatchEvent(new CustomEvent('stockModeChanged', {
                        detail: {
                            mode
                        }
                    }));
                }

                if (accountingMode) {
                    accountingMode.addEventListener('change', function() {
                        if (this.checked) {
                            updateMode('accounting');
                            if (form && tableFilterInput) {
                                form.action = '{{ route('stock_page') }}?start_date=' + document
                                    .getElementById('start_date').value +
                                    '&end_date=' + document.getElementById('end_date').value +
                                    '&table_filter=' + tableFilterInput.value +
                                    '&mode=accounting';
                                form.submit();
                            }
                        }
                    });
                }

                if (managementMode) {
                    managementMode.addEventListener('change', function() {
                        if (this.checked) {
                            updateMode('management');
                        }
                    });
                }

                const savedMode = localStorage.getItem('stockViewMode') || 'accounting';
                if (savedMode === 'management' && managementMode) {
                    managementMode.checked = true;
                    updateMode('management');
                } else if (accountingMode) {
                    accountingMode.checked = true;
                    updateMode('accounting');
                }

                if (managementMode && managementMode.checked && currentSelectedCostId) {
                    const selectedCost = appliedCostHistory.find(cost => cost.id == currentSelectedCostId);
                    if (selectedCost) {
                        updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                    }
                }

                function debounce(func, wait) {
                    let timeout;
                    return function executedFunction(...args) {
                        const later = () => {
                            clearTimeout(timeout);
                            func(...args);
                        };
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                    };
                }
            });
        </script>
    @endsection
