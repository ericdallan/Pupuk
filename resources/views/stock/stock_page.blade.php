@extends('layouts.app')

@section('title', 'Stock Barang Dagangan')

@section('content')
    <style>
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

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
            position: relative;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {
            .table-responsive {
                max-height: 400px;
            }

            .table thead th {
                font-size: 0.85rem;
                padding: 8px;
            }

            .table tbody td {
                font-size: 0.85rem;
            }
        }

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

        .status-badge {
            font-size: 0.9em;
            padding: 4px 8px;
            border-radius: 12px;
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

        .detail-button {
            background: linear-gradient(45deg, #17a2b8, #117a8b);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .detail-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
            background: linear-gradient(45deg, #138496, #0d5d6b);
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

        .modal-header {
            background: linear-gradient(90deg, #343a40, #212529);
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
        }

        /* New Styles for Combined Modal */
        .modal-tab-content {
            display: none;
        }

        .modal-tab-content.active {
            display: block;
        }

        .modal-nav-tabs .nav-link {
            transition: all 0.3s ease;
        }

        .modal-nav-tabs .nav-link.active {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-color: #007bff;
        }

        .modal-nav-tabs .nav-link:hover {
            background: linear-gradient(45deg, #e9ecef, #dee2e6);
        }

        .delete-button {
            background: linear-gradient(45deg, #dc3545, #a71d2a);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .delete-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            background: linear-gradient(45deg, #c82333, #8c1a24);
        }

        .modal-xl {
            max-width: 90%;
        }

        .transaction-row {
            transition: all 0.3s ease;
        }

        .transaction-row:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.75rem;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
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
                        <input type="hidden" name="mode" id="modeInput" value="{{ request('mode', 'accounting') }}">
                        <input type="hidden" name="applied_cost_id" id="appliedCostInput"
                            value="{{ request('applied_cost_id') }}">
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
                                <button type="button" class="btn history-button" data-bs-toggle="modal"
                                    data-bs-target="#combinedBebanModal" data-mode="history" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Lihat riwayat perhitungan beban">
                                    <i class="fas fa-history me-1"></i> Perhitungan dan Riwayat Beban
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
                <input type="text" id="globalSearch" class="form-control" placeholder="Cari nama barang atau ukuran...">
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
                                                <div class="hpp-plus-indicator" hidden></div>
                                            </td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator" hidden></div>
                                            </td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->outgoing_hpp ?? 0, 2, '.', '.') }}
                                                <div class="hpp-plus-indicator" hidden></div>
                                            </td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td class="hpp-column">
                                                Rp. {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}
                                                <div class="hpp-plus-indicator" hidden></div>
                                            </td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm detail-button" data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="stocks" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Lihat detail transaksi untuk item ini">
                                                        <i class="fas fa-eye me-1"></i> Detail
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
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content" data-table-name="{{ $tableName }}">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="detailModalLabel_{{ $tableName }}_{{ $stock->id }}">
                                        Detail Transaksi untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}
                                        @if ($stock->size)
                                            - Size: {{ $stock->size }}
                                        @endif
                                        ({{ $tableName }})
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- HPP Calculation Breakdown -->
                                    <div class="mb-4">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Rincian
                                                    Perhitungan HPP</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="card bg-info text-white h-100">
                                                            <div class="card-body text-center">
                                                                <h6 class="card-title">HPP Awal</h6>
                                                                <p class="card-text fs-5 fw-bold">
                                                                    {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}
                                                                </p>
                                                                <small>Qty: {{ $stock->opening_qty ?? 0 }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-success text-white h-100">
                                                            <div class="card-body text-center">
                                                                <h6 class="card-title">HPP Masuk</h6>
                                                                <p class="card-text fs-5 fw-bold">
                                                                    {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}
                                                                </p>
                                                                <small>Qty: {{ $stock->incoming_qty ?? 0 }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-warning text-white h-100">
                                                            <div class="card-body text-center">
                                                                <h6 class="card-title">HPP Keluar</h6>
                                                                <p class="card-text fs-5 fw-bold">
                                                                    {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}
                                                                </p>
                                                                <small>Qty: {{ $stock->outgoing_qty ?? 0 }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-danger text-white h-100">
                                                            <div class="card-body text-center">
                                                                <h6 class="card-title">HPP Akhir</h6>
                                                                <p class="card-text fs-5 fw-bold">
                                                                    {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}
                                                                </p>
                                                                <small>Qty: {{ $stock->final_stock_qty ?? 0 }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- HPP Calculation Formula -->
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <small class="text-muted">
                                                        <strong>Rumus Perhitungan:</strong><br>
                                                        HPP Akhir = ((HPP Awal × Qty Awal) + (HPP Masuk × Qty Masuk)) ÷ (Qty
                                                        Awal + Qty Masuk)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Summary Section -->
                                    @if (isset($stock->transactions) && !empty($stock->transactions))
                                        <div class="mb-4 p-3 bg-light rounded shadow-sm">
                                            <h6 class="fw-bold"><i class="fas fa-chart-line me-2"></i>Ringkasan Transaksi
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Kuantitas</small>
                                                    <p class="mb-0 fw-bold text-primary">
                                                        {{ collect($stock->transactions)->sum('quantity') ?? (collect($stock->transactions)->sum('transaction_quantity') ?? 0) }}
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Nominal</small>
                                                    <p class="mb-0 fw-bold text-success">
                                                        Rp
                                                        {{ number_format(collect($stock->transactions)->sum('nominal') ?? 0, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Jumlah Transaksi</small>
                                                    <p class="mb-0 fw-bold text-info">{{ count($stock->transactions) }}
                                                    </p>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Rata-rata HPP</small>
                                                    <p class="mb-0 fw-bold text-warning">
                                                        Rp {{ number_format($stock->average_pb_hpp ?? 0, 2, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Transaction Type Summary -->
                                    @if (isset($stock->transactions) && !empty($stock->transactions))
                                        @php
                                            $transactions = collect($stock->transactions);
                                            $pembelianTypes = ['PB', 'PYB', 'RPJ'];
                                            $penjualanTypes = ['PJ', 'PYK', 'RPB'];

                                            $pembelianTransactions = $transactions->whereIn(
                                                'voucher_type',
                                                $pembelianTypes,
                                            );
                                            $penjualanTransactions = $transactions->whereIn(
                                                'voucher_type',
                                                $penjualanTypes,
                                            );
                                        @endphp

                                        <div class="mb-4">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card border-success">
                                                        <div class="card-header bg-success text-white">
                                                            <h6 class="mb-0"><i
                                                                    class="fas fa-arrow-down me-2"></i>Transaksi Masuk</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <small class="text-muted">Total Qty</small>
                                                                    <p class="fw-bold text-success mb-0">
                                                                        {{ $pembelianTransactions->sum('quantity') }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted">Total Nominal</small>
                                                                    <p class="fw-bold text-success mb-0">
                                                                        Rp
                                                                        {{ number_format($pembelianTransactions->sum('nominal'), 2, ',', '.') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $pembelianTransactions->count() }} transaksi
                                                                (PB: Pembelian, PYB: Penyesuaian +, RPJ: Retur Penjualan)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border-danger">
                                                        <div class="card-header bg-danger text-white">
                                                            <h6 class="mb-0"><i
                                                                    class="fas fa-arrow-up me-2"></i>Transaksi Keluar</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <small class="text-muted">Total Qty</small>
                                                                    <p class="fw-bold text-danger mb-0">
                                                                        {{ $penjualanTransactions->sum('quantity') }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted">Total Nominal</small>
                                                                    <p class="fw-bold text-danger mb-0">
                                                                        Rp
                                                                        {{ number_format($penjualanTransactions->sum('nominal'), 2, ',', '.') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $penjualanTransactions->count() }} transaksi
                                                                (PJ: Penjualan, PYK: Penyesuaian -, RPB: Retur Pembelian)
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Filter Section -->
                                    <div class="mb-3">
                                        <div class="row align-items-end">
                                            <div class="col-md-6">
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
                                            <div class="col-md-6">
                                                <label for="modal_type_filter_{{ $tableName }}_{{ $stock->id }}"
                                                    class="form-label">Filter Tipe Transaksi</label>
                                                <select id="modal_type_filter_{{ $tableName }}_{{ $stock->id }}"
                                                    class="form-select modal-type-filter"
                                                    data-stock-id="{{ $stock->id }}">
                                                    <option value="all">Semua Tipe</option>
                                                    <option value="masuk">Transaksi Masuk</option>
                                                    <option value="keluar">Transaksi Keluar</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Transaction Table -->
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover text-center">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th data-bs-toggle="tooltip" title="Nomor urut transaksi">No</th>
                                                    <th data-bs-toggle="tooltip" title="Nama item yang ditransaksikan">
                                                        Nama Item</th>
                                                    <th data-bs-toggle="tooltip" title="Nomor voucher terkait">No Voucher
                                                    </th>
                                                    <th data-bs-toggle="tooltip" title="Jenis transaksi">Tipe Transaksi
                                                    </th>
                                                    <th data-bs-toggle="tooltip" title="Kategori transaksi">Kategori</th>
                                                    <th data-bs-toggle="tooltip" title="Jumlah item yang ditransaksikan">
                                                        Kuantitas</th>
                                                    <th data-bs-toggle="tooltip" title="Nilai nominal transaksi">Nominal
                                                    </th>
                                                    <th data-bs-toggle="tooltip" title="Tanggal transaksi">Tanggal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modal_transactions_tbody_{{ $tableName }}_{{ $stock->id }}">
                                                @if (isset($stock->transactions) && !empty($stock->transactions))
                                                    @php
                                                        $filteredTransactions = collect($stock->transactions)->filter(
                                                            function ($transaction) {
                                                                return !str_starts_with(
                                                                    $transaction->description ?? '',
                                                                    'HPP ',
                                                                );
                                                            },
                                                        );
                                                    @endphp
                                                    @foreach ($filteredTransactions as $transaction)
                                                        @php
                                                            $pembelianTypes = ['PB', 'PYB', 'RPJ'];
                                                            $penjualanTypes = ['PJ', 'PYK', 'RPB'];
                                                            $isIncoming = in_array(
                                                                $transaction->voucher_type,
                                                                $pembelianTypes,
                                                            );
                                                            $categoryClass = $isIncoming ? 'success' : 'danger';
                                                            $categoryText = $isIncoming ? 'Masuk' : 'Keluar';
                                                            $categoryIcon = $isIncoming ? 'arrow-down' : 'arrow-up';
                                                        @endphp
                                                        <tr data-transaction-date="{{ $transaction->created_at }}"
                                                            data-transaction-type="{{ $isIncoming ? 'masuk' : 'keluar' }}"
                                                            class="transaction-row">
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
                                                            <td>
                                                                <span
                                                                    class="badge bg-{{ $categoryClass }} d-inline-flex align-items-center">
                                                                    <i class="fas fa-{{ $categoryIcon }} me-1"></i>
                                                                    {{ $categoryText }}
                                                                </span>
                                                            </td>
                                                            <td class="fw-bold">
                                                                {{ $transaction->quantity ?? ($transaction->transaction_quantity ?? 0) }}
                                                            </td>
                                                            <td class="fw-bold text-{{ $categoryClass }}">
                                                                Rp
                                                                {{ number_format($transaction->nominal ?? 0, 2, ',', '.') }}
                                                            </td>
                                                            <td>@php
                                                                $date = \Carbon\Carbon::parse(
                                                                    $transaction->created_at ?? now(),
                                                                );
                                                                $dayName = $date->locale('id')->dayName;
                                                                $monthName = $date->locale('id')->monthName;
                                                                $formattedDate =
                                                                    $dayName .
                                                                    ', ' .
                                                                    $date->day .
                                                                    ' ' .
                                                                    $monthName .
                                                                    ' ' .
                                                                    $date->year .
                                                                    ' ' .
                                                                    $date->format('H:i');
                                                            @endphp
                                                                {{ $formattedDate }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="8" class="text-center">
                                                            <div class="alert alert-info mb-0">Tidak ada transaksi terkait
                                                                untuk barang ini.</div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination Controls -->
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="pagination-info">
                                            <small class="text-muted">
                                                Menampilkan <span
                                                    id="current_page_{{ $tableName }}_{{ $stock->id }}">1</span>
                                                dari <span
                                                    id="total_pages_{{ $tableName }}_{{ $stock->id }}">1</span>
                                                halaman
                                                (<span
                                                    id="total_records_{{ $tableName }}_{{ $stock->id }}">0</span>
                                                total transaksi)
                                            </small>
                                        </div>
                                        <nav>
                                            <ul class="pagination pagination-sm mb-0"
                                                id="modal_pagination_{{ $tableName }}_{{ $stock->id }}">
                                                <!-- Pagination will be generated by JavaScript -->
                                            </ul>
                                        </nav>
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

                    <!-- JavaScript untuk Pagination dan Filtering -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const modalId = 'detailModal_{{ $tableName }}_{{ $stock->id }}';
                            const stockId = '{{ $stock->id }}';
                            const tableName = '{{ $tableName }}';

                            let currentPage = 1;
                            const itemsPerPage = 10;
                            let allTransactions = [];
                            let filteredTransactions = [];

                            // Initialize transactions data
                            function initializeTransactions() {
                                const tbody = document.querySelector(`#modal_transactions_tbody_${tableName}_${stockId}`);
                                const rows = tbody.querySelectorAll('.transaction-row');

                                allTransactions = Array.from(rows).map(row => ({
                                    element: row,
                                    date: row.dataset.transactionDate,
                                    type: row.dataset.transactionType
                                }));

                                filteredTransactions = [...allTransactions];
                                updatePagination();
                                showPage(1);
                            }

                            // Filter functions
                            function applyFilters() {
                                const dateFilter = document.querySelector(`#modal_filter_${tableName}_${stockId}`).value;
                                const typeFilter = document.querySelector(`#modal_type_filter_${tableName}_${stockId}`).value;

                                filteredTransactions = allTransactions.filter(transaction => {
                                    let passDateFilter = true;
                                    let passTypeFilter = true;

                                    // Date filter
                                    if (dateFilter !== 'all') {
                                        const transactionDate = new Date(transaction.date);
                                        const now = new Date();

                                        switch (dateFilter) {
                                            case '7_days':
                                                const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                                                passDateFilter = transactionDate >= sevenDaysAgo;
                                                break;
                                            case '1_month':
                                                const oneMonthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now
                                                    .getDate());
                                                passDateFilter = transactionDate >= oneMonthAgo;
                                                break;
                                        }
                                    }

                                    // Type filter
                                    if (typeFilter !== 'all') {
                                        passTypeFilter = transaction.type === typeFilter;
                                    }

                                    return passDateFilter && passTypeFilter;
                                });

                                updatePagination();
                                showPage(1);
                            }

                            // Pagination functions
                            function updatePagination() {
                                const totalPages = Math.ceil(filteredTransactions.length / itemsPerPage);
                                document.querySelector(`#total_pages_${tableName}_${stockId}`).textContent = totalPages;
                                document.querySelector(`#total_records_${tableName}_${stockId}`).textContent = filteredTransactions
                                    .length;

                                generatePaginationButtons(totalPages);
                            }

                            function generatePaginationButtons(totalPages) {
                                const pagination = document.querySelector(`#modal_pagination_${tableName}_${stockId}`);
                                pagination.innerHTML = '';

                                // Previous button
                                const prevLi = document.createElement('li');
                                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                                prevLi.innerHTML =
                                    `<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>`;
                                pagination.appendChild(prevLi);

                                // Page numbers
                                const startPage = Math.max(1, currentPage - 2);
                                const endPage = Math.min(totalPages, currentPage + 2);

                                for (let i = startPage; i <= endPage; i++) {
                                    const li = document.createElement('li');
                                    li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                                    li.innerHTML = `<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>`;
                                    pagination.appendChild(li);
                                }

                                // Next button
                                const nextLi = document.createElement('li');
                                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                                nextLi.innerHTML =
                                    `<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>`;
                                pagination.appendChild(nextLi);
                            }

                            function showPage(page) {
                                currentPage = page;
                                document.querySelector(`#current_page_${tableName}_${stockId}`).textContent = page;

                                // Hide all transactions
                                allTransactions.forEach(transaction => {
                                    transaction.element.style.display = 'none';
                                });

                                // Show transactions for current page
                                const startIndex = (page - 1) * itemsPerPage;
                                const endIndex = startIndex + itemsPerPage;
                                const transactionsToShow = filteredTransactions.slice(startIndex, endIndex);

                                transactionsToShow.forEach((transaction, index) => {
                                    transaction.element.style.display = '';
                                    // Update row numbers
                                    const numberCell = transaction.element.querySelector('td:first-child');
                                    numberCell.textContent = startIndex + index + 1;
                                });

                                updatePagination();
                            }

                            // Global function for page changes
                            window.changePage = function(page) {
                                const totalPages = Math.ceil(filteredTransactions.length / itemsPerPage);
                                if (page >= 1 && page <= totalPages) {
                                    showPage(page);
                                }
                            };

                            // Event listeners
                            document.querySelector(`#modal_filter_${tableName}_${stockId}`).addEventListener('change',
                                applyFilters);
                            document.querySelector(`#modal_type_filter_${tableName}_${stockId}`).addEventListener('change',
                                applyFilters);

                            // Initialize when modal is shown
                            document.querySelector(`#${modalId}`).addEventListener('shown.bs.modal', function() {
                                initializeTransactions();
                            });
                        });
                    </script>
                @endif
            @endforeach
        @endforeach

        <!-- Combined Beban Modal -->
        <div class="modal fade" id="combinedBebanModal" tabindex="-1" aria-labelledby="combinedBebanModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="combinedBebanModalLabel">Perhitungan Beban</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Tabs for Form and History -->
                        <ul class="nav nav-tabs modal-nav-tabs mb-3" id="bebanTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="form-tab" data-bs-toggle="tab" href="#form-content"
                                    role="tab" aria-controls="form-content" aria-selected="true"
                                    data-bs-toggle="tooltip" title="Tambah atau edit perhitungan beban">
                                    <i class="fas fa-calculator me-1"></i> Form Perhitungan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history-content"
                                    role="tab" aria-controls="history-content" aria-selected="false"
                                    data-bs-toggle="tooltip" title="Lihat riwayat perhitungan beban">
                                    <i class="fas fa-history me-1"></i> Riwayat Perhitungan
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Form Tab Content -->
                            <div class="tab-pane modal-tab-content fade show active" id="form-content" role="tabpanel"
                                aria-labelledby="form-tab">
                                <form id="loadCalculationForm" action="{{ route('applied_cost.store') }}"
                                    method="POST">
                                    @csrf
                                    <input type="hidden" name="_method" id="formMethod" value="POST">
                                    <input type="hidden" name="id" id="appliedCostId">
                                    <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                                    <div id="bebanInputs">
                                        <div class="input-group mb-2 beban-row">
                                            <span class="input-group-text">Beban 1</span>
                                            <input type="text"
                                                class="form-control @error('beban_description.*') is-invalid @enderror"
                                                name="beban_description[]"
                                                placeholder="Deskripsi Beban (e.g., Beban Operasional)" required>
                                            <input type="number"
                                                class="form-control @error('beban_nominal.*') is-invalid @enderror"
                                                name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01"
                                                min="0" required>
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
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Tambah baris beban baru">Tambah Beban</button>
                                    <div class="row">
                                        <div class="col-md-8"></div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Total Akumulasi:</label>
                                            <input type="text" class="form-control bg-light" id="totalAkumulasi"
                                                readonly value="Rp 0">
                                            <input type="hidden" name="total" id="totalHidden" value="0">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" id="submitBebanBtn"
                                            data-bs-toggle="tooltip" title="Simpan perhitungan beban">Hitung &
                                            Simpan</button>
                                        <button type="button" class="btn btn-secondary" id="cancelEditBtn"
                                            style="display: none;" data-bs-toggle="tooltip"
                                            title="Batalkan pengeditan">Batal Edit</button>
                                    </div>
                                </form>
                            </div>

                            <!-- History Tab Content -->
                            <div class="tab-pane modal-tab-content fade" id="history-content" role="tabpanel"
                                aria-labelledby="history-tab">
                                @if ($currentMode == 'management')
                                    <div class="row mb-3" id="appliedCostModeSelection">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Mode Manajemen Aktif:</strong> Pilih riwayat perhitungan
                                                beban untuk diterapkan pada perhitungan HPP.
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
                                                <button type="button" class="btn btn-sm btn-success"
                                                    id="applySelectedCost" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Terapkan riwayat beban yang dipilih">
                                                    <i class="fas fa-check me-1"></i> Terapkan
                                                </button>
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    id="clearSelectedCost" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Hapus pilihan riwayat beban">
                                                    <i class="fas fa-times me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- History Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="appliedCostHistoryTable">
                                        <thead class="table-dark text-center">
                                            <tr>
                                                <th width="5%">
                                                    <input type="radio" name="appliedCostSelection" value=""
                                                        id="noCostSelection" checked>
                                                </th>
                                                <th width="15%">Tanggal</th>
                                                <th width="15%">Total Nominal</th>
                                                <th width="35%">Detail Beban</th>
                                                <th width="10%">Status</th>
                                                <th width="15%">Aksi</th>
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
                                                        data-description="{{ collect($item->details)->pluck('description')->implode(' ') }}"
                                                        data-cost-id="{{ $item->id }}"
                                                        data-details="{{ json_encode($item->details) }}"
                                                        data-total-nominal="{{ $item->total_nominal }}">
                                                        <td>
                                                            <input type="radio" name="appliedCostSelection"
                                                                value="{{ $item->id }}"
                                                                {{ $selectedAppliedCostId == $item->id ? 'checked' : '' }}>
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $item->created_at->format('d M Y H:i') }}</td>
                                                        <td class="text-center"><strong class="text-success">Rp.
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
                                                        <td class="text-center">
                                                            <span
                                                                class="badge status-badge {{ $selectedAppliedCostId == $item->id ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ $selectedAppliedCostId == $item->id ? 'Aktif' : 'Tidak Aktif' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary edit-cost-btn"
                                                                data-cost-id="{{ $item->id }}"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Edit perhitungan beban">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-danger delete-cost-btn"
                                                                data-cost-id="{{ $item->id }}"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Hapus perhitungan beban">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
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
                let isInitializing = true; // Flag to prevent auto-submit during initialization

                // Define variables with null checks
                const form = document.getElementById('filterForm');
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
                const combinedBebanModal = document.getElementById('combinedBebanModal');
                const applySelectedCostBtn = document.getElementById('applySelectedCost');
                const clearSelectedCostBtn = document.getElementById('clearSelectedCost');
                const historyCount = document.getElementById('historyCount');
                const selectedAppliedCostId = document.getElementById('selectedAppliedCostId');
                const submitBebanBtn = document.getElementById('submitBebanBtn');
                const cancelEditBtn = document.getElementById('cancelEditBtn');
                const appliedCostIdInput = document.getElementById('appliedCostId');

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

                // Function to update status badges
                function updateStatusBadges(selectedCostId) {
                    document.querySelectorAll('#historyTableBody tr').forEach(row => {
                        const badge = row.querySelector('.status-badge');
                        const rowCostId = row.dataset.costId;
                        if (badge) {
                            if (rowCostId === selectedCostId) {
                                badge.textContent = 'Aktif';
                                badge.classList.remove('bg-secondary');
                                badge.classList.add('bg-success');
                            } else {
                                badge.textContent = 'Tidak Aktif';
                                badge.classList.remove('bg-success');
                                badge.classList.add('bg-secondary');
                            }
                        }
                    });
                }

                function autoSelectFirstCost() {
                    if (!form) {
                        console.error('Form not found, cannot proceed with autoSelectFirstCost');
                        return;
                    }

                    if (!appliedCostHistory || appliedCostHistory.length === 0) {
                        console.warn('No applied cost history available');
                        return;
                    }

                    const firstCost = appliedCostHistory[0];
                    if (firstCost) {
                        currentSelectedCostId = firstCost.id.toString();

                        // Update selectedAppliedCostId if it exists
                        if (selectedAppliedCostId) {
                            selectedAppliedCostId.value = firstCost.id;
                        }

                        // Update radio button if it exists
                        const radio = document.querySelector(
                            `input[name="appliedCostSelection"][value="${firstCost.id}"]`);
                        if (radio) {
                            radio.checked = true;
                        }

                        // Update badges
                        updateStatusBadges(firstCost.id.toString());

                        updateHPPColumns(parseFloat(firstCost.total_nominal || 0));

                        // Update URL parameters instead of form action
                        const startDate = document.getElementById('start_date').value;
                        const endDate = document.getElementById('end_date').value;
                        const newUrl =
                            `{{ route('stock_page') }}?start_date=${startDate}&end_date=${endDate}&table_filter=&mode=management&applied_cost_id=${firstCost.id}`;

                        // Use replaceState to avoid triggering a page refresh
                        window.history.replaceState({}, '', newUrl);
                    }
                }

                // Combined Modal Logic
                if (combinedBebanModal) {
                    combinedBebanModal.addEventListener('shown.bs.modal', function(e) {
                        const mode = e.relatedTarget ? e.relatedTarget.dataset.mode || 'form' : 'form';
                        const formTab = document.getElementById('form-tab');
                        const historyTab = document.getElementById('history-tab');
                        const formContent = document.getElementById('form-content');
                        const historyContent = document.getElementById('history-content');
                        const modalTitle = document.getElementById('combinedBebanModalLabel');

                        if (mode === 'form') {
                            formTab.classList.add('active');
                            historyTab.classList.remove('active');
                            formContent.classList.add('show', 'active');
                            historyContent.classList.remove('show', 'active');
                            modalTitle.textContent = 'Perhitungan Beban';
                            resetForm();
                        } else {
                            historyTab.classList.add('active');
                            formTab.classList.remove('active');
                            historyContent.classList.add('show', 'active');
                            formContent.classList.remove('show', 'active');
                            modalTitle.textContent = 'Riwayat Perhitungan Beban';
                            const isManagementMode = managementMode && managementMode.checked;
                            const modeSelection = document.getElementById('appliedCostModeSelection');
                            if (modeSelection) {
                                modeSelection.style.display = isManagementMode ? 'block' : 'none';
                            }
                        }
                    });

                    // Reset form function
                    function resetForm() {
                        if (loadCalculationForm && bebanInputs) {
                            loadCalculationForm.action = '{{ route('applied_cost.store') }}';
                            bebanInputs.innerHTML = `
                    <div class="input-group mb-2 beban-row">
                        <span class="input-group-text">Beban 1</span>
                        <input type="text" class="form-control" name="beban_description[]" placeholder="Deskripsi Beban (e.g., Beban Operasional)" required>
                        <input type="number" class="form-control" name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0" required>
                        <button type="button" class="btn btn-outline-danger remove-row" style="display: none;">Hapus</button>
                    </div>
                `;
                            totalAkumulasi.value = 'Rp 0';
                            totalHidden.value = '0';
                            appliedCostIdInput.value = '';
                            submitBebanBtn.textContent = 'Hitung & Simpan';
                            cancelEditBtn.style.display = 'none';
                            updateTotal();
                        }
                    }

                    // Populate form for editing
                    function populateFormForEdit(costId) {
                        const row = document.querySelector(`#historyTableBody tr[data-cost-id="${costId}"]`);
                        if (!row) return;

                        const details = JSON.parse(row.dataset.details || '[]');
                        const totalNominal = parseFloat(row.dataset.totalNominal || 0);

                        // Switch to form tab
                        const formTab = document.getElementById('form-tab');
                        const historyTab = document.getElementById('history-tab');
                        const formContent = document.getElementById('form-content');
                        const historyContent = document.getElementById('history-content');
                        const modalTitle = document.getElementById('combinedBebanModalLabel');

                        formTab.classList.add('active');
                        historyTab.classList.remove('active');
                        formContent.classList.add('show', 'active');
                        historyContent.classList.remove('show', 'active');
                        modalTitle.textContent = 'Edit Perhitungan Beban';

                        // Populate form
                        loadCalculationForm.action = '{{ route('applied_cost.update') }}';
                        appliedCostIdInput.value = costId;
                        bebanInputs.innerHTML = '';
                        details.forEach((detail, index) => {
                            const row = document.createElement('div');
                            row.className = 'input-group mb-2 beban-row';
                            row.innerHTML = `
                    <span class="input-group-text">Beban ${index + 1}</span>
                    <input type="text" class="form-control" name="beban_description[]" value="${detail.description}" required>
                    <input type="number" class="form-control" name="beban_nominal[]" value="${detail.nominal}" step="0.01" min="0" required>
                    <button type="button" class="btn btn-outline-danger remove-row" style="${index === 0 ? 'display: none;' : ''}">Hapus</button>
                `;
                            bebanInputs.appendChild(row);
                        });
                        totalAkumulasi.value = formatCurrency(totalNominal);
                        totalHidden.value = totalNominal;
                        submitBebanBtn.textContent = 'Simpan Perubahan';
                        cancelEditBtn.style.display = 'inline-block';
                    }

                    // Handle edit button click
                    document.querySelectorAll('.edit-cost-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const costId = this.dataset.costId;
                            populateFormForEdit(costId);
                        });
                    });

                    // Handle delete button click
                    document.querySelectorAll('.delete-cost-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const costId = this.dataset.costId;
                            const historyContent = document.getElementById('history-content');

                            // Create or get the confirmation modal
                            let confirmModal = document.getElementById('deleteConfirmModal');
                            if (!confirmModal) {
                                confirmModal = document.createElement('div');
                                confirmModal.id = 'deleteConfirmModal';
                                confirmModal.className = 'modal fade';
                                confirmModal.tabIndex = -1;
                                confirmModal.setAttribute('aria-labelledby', 'deleteConfirmModalLabel');
                                confirmModal.setAttribute('aria-hidden', 'true');
                                confirmModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Penghapusan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin menghapus perhitungan beban ini? Tindakan ini tidak dapat dibatalkan.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                        </div>
                    </div>
                </div>
            `;
                                document.body.appendChild(confirmModal);
                            }

                            // Show the modal
                            const modalInstance = new bootstrap.Modal(confirmModal);
                            modalInstance.show();

                            // Handle confirm delete
                            const confirmDeleteBtn = confirmModal.querySelector('#confirmDeleteBtn');
                            confirmDeleteBtn.onclick = function() {
                                modalInstance.hide();

                                // Remove any existing alert to avoid duplicates
                                const existingAlert = historyContent.querySelector('.alert');
                                if (existingAlert) {
                                    existingAlert.remove();
                                }

                                fetch('{{ route('applied_cost.delete', ['id' => ':id']) }}'
                                        .replace(':id', costId), {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        })
                                    .then(response => response.json())
                                    .then(data => {
                                        // Create a dismissible Bootstrap alert
                                        const alert = document.createElement('div');
                                        alert.className =
                                            `alert ${data.success ? 'alert-success' : 'alert-danger'} alert-dismissible fade show`;
                                        alert.role = 'alert';
                                        alert.innerHTML = `
                    ${data.success ? 'Perhitungan beban berhasil dihapus.' : 'Gagal menghapus perhitungan beban: ' + (data.message || 'Kesalahan tidak diketahui')}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                                        // Prepend the alert to the history-content div
                                        historyContent.prepend(alert);

                                        // Auto-dismiss the alert after 5 seconds
                                        setTimeout(() => {
                                            alert.classList.remove('show');
                                            setTimeout(() => alert.remove(),
                                                150); // Remove after fade-out
                                        }, 5000);

                                        if (data.success) {
                                            const row = document.querySelector(
                                                `#historyTableBody tr[data-cost-id="${costId}"]`
                                            );
                                            if (row) row.remove();
                                            updateHistoryCount();
                                            if (currentSelectedCostId == costId) {
                                                currentSelectedCostId = '';
                                                if (selectedAppliedCostId) selectedAppliedCostId
                                                    .value = '';
                                                updateHPPColumns(0);

                                                // Switch back to accounting mode
                                                if (accountingMode) {
                                                    accountingMode.checked = true;
                                                    updateMode('accounting');
                                                }
                                            }
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error deleting applied cost:', error);

                                        // Create an error alert
                                        const alert = document.createElement('div');
                                        alert.className =
                                            'alert alert-danger alert-dismissible fade show';
                                        alert.role = 'alert';
                                        alert.innerHTML = `
                    Terjadi kesalahan saat menghapus perhitungan beban.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                                        // Prepend the alert to the history-content div
                                        historyContent.prepend(alert);

                                        // Auto-dismiss the alert after 5 seconds
                                        setTimeout(() => {
                                            alert.classList.remove('show');
                                            setTimeout(() => alert.remove(),
                                                150); // Remove after fade-out
                                        }, 5000);
                                    });
                            };
                        });
                    });

                    // Handle cancel edit
                    if (cancelEditBtn) {
                        cancelEditBtn.addEventListener('click', function() {
                            resetForm();
                            const formTab = document.getElementById('form-tab');
                            const historyTab = document.getElementById('history-tab');
                            const formContent = document.getElementById('form-content');
                            const historyContent = document.getElementById('history-content');
                            const modalTitle = document.getElementById('combinedBebanModalLabel');

                            historyTab.classList.add('active');
                            formTab.classList.remove('active');
                            historyContent.classList.add('show', 'active');
                            formContent.classList.remove('show', 'active');
                            modalTitle.textContent = 'Riwayat Perhitungan Beban';
                        });
                    }

                    // Update history count
                    function updateHistoryCount() {
                        const visibleRows = document.querySelectorAll(
                            '#historyTableBody tr:not([style*="display: none"])');
                        if (historyCount) {
                            historyCount.textContent = `Total: ${visibleRows.length} riwayat`;
                        }
                    }

                    // Handle apply selected cost button
                    if (applySelectedCostBtn) {
                        applySelectedCostBtn.addEventListener('click', function() {
                            if (!selectedAppliedCostId || !selectedAppliedCostId.value) {
                                alert('Silakan pilih riwayat perhitungan beban terlebih dahulu.');
                                return;
                            }

                            const selectedId = selectedAppliedCostId.value;
                            currentSelectedCostId = selectedId;

                            // Update radio button
                            const radio = document.querySelector(
                                `input[name="appliedCostSelection"][value="${selectedId}"]`);
                            if (radio) {
                                radio.checked = true;
                            }

                            // Update status badges
                            updateStatusBadges(selectedId);

                            const selectedCost = appliedCostHistory.find(cost => cost.id == selectedId);
                            if (selectedCost) {
                                updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                            }

                            // Update form and submit
                            const startDate = document.getElementById('start_date').value;
                            const endDate = document.getElementById('end_date').value;

                            // Update hidden input values
                            document.getElementById('modeInput').value = 'management';
                            document.getElementById('appliedCostInput').value = selectedId;

                            // Submit the form
                            form.submit();
                        });
                    }

                    // Handle clear selected cost button
                    if (clearSelectedCostBtn) {
                        clearSelectedCostBtn.addEventListener('click', function() {
                            currentSelectedCostId = '';
                            if (selectedAppliedCostId) selectedAppliedCostId.value = '';

                            // Clear radio selection
                            const noCostRadio = document.getElementById('noCostSelection');
                            if (noCostRadio) {
                                noCostRadio.checked = true;
                            }

                            // Update all badges to inactive
                            updateStatusBadges('');

                            updateHPPColumns(0);

                            // Update form and submit
                            document.getElementById('modeInput').value = 'accounting';
                            document.getElementById('appliedCostInput').value = '';

                            // Switch to accounting mode
                            if (accountingMode) {
                                accountingMode.checked = true;
                            }

                            form.submit();
                        });
                    }

                    // Handle radio button changes for applied cost selection
                    document.querySelectorAll('input[name="appliedCostSelection"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (this.checked) {
                                const selectedId = this.value;
                                if (selectedAppliedCostId) {
                                    selectedAppliedCostId.value = selectedId;
                                }
                                updateStatusBadges(selectedId);
                            }
                        });
                    });
                }

                // Global Filter
                const tableSections = document.querySelectorAll('.table-section');

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

                // Mode switch - FIXED VERSION
                function updateMode(mode) {
                    console.log('updateMode called with:', mode);

                    if (!modeIndicator || !tableContainer) {
                        console.warn('Required elements for mode update not found');
                        return;
                    }

                    const indicatorSpan = modeIndicator.querySelector('span');
                    if (!indicatorSpan) {
                        console.warn('Mode indicator span not found');
                        return;
                    }

                    if (mode === 'accounting') {
                        indicatorSpan.textContent = 'Akuntansi';
                        indicatorSpan.className = 'fw-bold text-primary';
                        tableContainer.classList.remove('management-mode');
                        updateHPPColumns(0);
                    } else if (mode === 'management') {
                        indicatorSpan.textContent = 'Manajemen';
                        indicatorSpan.className = 'fw-bold text-success';
                        tableContainer.classList.add('management-mode');

                        // Only auto-select first cost if no cost is currently selected
                        if (!currentSelectedCostId && appliedCostHistory && appliedCostHistory.length > 0) {
                            autoSelectFirstCost();
                        } else if (currentSelectedCostId) {
                            // Apply the currently selected cost
                            const selectedCost = appliedCostHistory.find(cost => cost.id == currentSelectedCostId);
                            if (selectedCost) {
                                updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                            }
                        }
                    }

                    localStorage.setItem('stockViewMode', mode);
                }

                // FIXED: Mode change event listeners - prevent auto-submit during initialization
                if (managementMode) {
                    managementMode.addEventListener('change', function() {
                        if (this.checked && !isInitializing) {
                            updateMode('management');

                            // Update hidden inputs
                            document.getElementById('modeInput').value = 'management';

                            // Only submit if we're not initializing
                            const startDate = document.getElementById('start_date').value;
                            const endDate = document.getElementById('end_date').value;

                            // Build URL with current parameters
                            let url =
                                `{{ route('stock_page') }}?start_date=${startDate}&end_date=${endDate}&mode=management`;

                            // Add applied_cost_id if one is selected
                            if (currentSelectedCostId) {
                                url += `&applied_cost_id=${currentSelectedCostId}`;
                                document.getElementById('appliedCostInput').value = currentSelectedCostId;
                            }

                            form.action = url;
                            form.submit();
                        }
                    });
                }

                if (accountingMode) {
                    accountingMode.addEventListener('change', function() {
                        if (this.checked && !isInitializing) {
                            updateMode('accounting');

                            // Update hidden inputs
                            document.getElementById('modeInput').value = 'accounting';
                            document.getElementById('appliedCostInput').value = '';

                            const startDate = document.getElementById('start_date').value;
                            const endDate = document.getElementById('end_date').value;

                            form.action =
                                `{{ route('stock_page') }}?start_date=${startDate}&end_date=${endDate}&mode=accounting`;
                            form.submit();
                        }
                    });
                }

                // Initialize mode based on current state
                const currentMode = '{{ $currentMode ?? 'accounting' }}';

                if (currentMode === 'management' && managementMode) {
                    managementMode.checked = true;
                    updateMode('management');

                    // Update status badges based on current selection
                    if (currentSelectedCostId) {
                        updateStatusBadges(currentSelectedCostId);
                        const selectedCost = appliedCostHistory.find(cost => cost.id == currentSelectedCostId);
                        if (selectedCost) {
                            updateHPPColumns(parseFloat(selectedCost.total_nominal || 0));
                        }
                    }
                } else if (accountingMode) {
                    accountingMode.checked = true;
                    updateMode('accounting');
                }

                // Set initialization flag to false after initial setup
                setTimeout(() => {
                    isInitializing = false;
                }, 100);

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
