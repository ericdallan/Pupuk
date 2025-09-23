@extends('layouts/app')

@section('title', 'Stock Barang Dagangan')

@section('content')
    <style>
        .table-section {
            display: block;
        }

        .table-section.hidden {
            display: none;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 10px;
        }

        .loading-spinner.active {
            display: block;
        }

        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }

        .table thead {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #343a40;
            color: #fff;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .recipe-highlight {
            background-color: yellow;
            font-weight: bold;
        }

        #recipeTable .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .ingredients-table {
            margin-bottom: 0 !important;
            font-size: 0.85em;
        }

        .recipe-row.hidden {
            display: none;
        }

        #recipeSearchCount {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .no-results-message {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
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

        .btn-group .btn {
            margin-right: 0;
            position: relative;
            flex: 0 1 auto;
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

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            line-height: 1.5;
        }

        .btn-sm .fas {
            font-size: 0.7rem;
            margin-right: 0.25rem;
        }

        .recipe-row .btn-group {
            white-space: nowrap;
        }

        .recipe-row .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .recipe-row .btn-warning:hover:not(:disabled) {
            background-color: #ffca2c;
            border-color: #ffc720;
        }

        .recipe-row .btn-warning:disabled {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .recipe-row .btn-danger:disabled {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
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

        #recipeTable th:last-child,
        #recipeTable td:last-child {
            min-width: 120px;
            width: 120px;
        }

        .loading-edit-ingredients {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }

        .loading-edit-ingredients .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }

        #editIngredientsContainer .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .mode-switch-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-check:checked+.btn-outline-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .btn-check:checked+.btn-outline-success {
            background-color: #198754;
            border-color: #198754;
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

        /* Animation for mode switch */
        .table {
            transition: all 0.3s ease;
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

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        input[name="appliedCostSelection"]:checked+td {
            background-color: rgba(255, 193, 7, 0.2);
        }

        #appliedCostModeSelection {
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

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

    <div class="mt-4">
        <!-- Main Filter Form -->
        <div class="mb-4">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('stock_page') }}"
                        class="d-flex flex-wrap align-items-end gap-3">
                        <div class="flex-shrink-0">
                            <label for="start_date" class="form-label small">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date', now()->startOfYear()->toDateString()) }}"
                                min="{{ now()->subYears(5)->startOfYear()->toDateString() }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="flex-shrink-0">
                            <label for="end_date" class="form-label small">End Date</label>
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
                                    {{ request('table_filter', 'all') == 'all' ? 'Semua Table' : (request('table_filter') == 'stocks' ? 'Table Stocks' : (request('table_filter') == 'transfer_stocks' ? 'Table Transfer Stocks' : 'Table Used Stocks')) }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="tableFilterDropdown">
                                    <li><a class="dropdown-item" href="#" data-filter="all">Semua Table</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="stocks">Table Stok</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="transfer_stocks">Table Stok
                                            Pemindahan</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="used_stocks">Table Stok Barang
                                            Jadi</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}"
                                class="btn btn-success">Export to Excel</a>
                            <a href="{{ route('stock.transfer.print') . '?table_filter=' . request('table_filter', 'all') }}"
                                class="btn btn-secondary me-2" hidden>Print Form</a>
                            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#RecipeList"
                                hidden>Daftar Formula</button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                data-bs-target="#createRecipeModal" hidden>Buat Formula</button>
                            <!-- New Button: Perhitungan Beban -->
                            @if (App\Http\Controllers\Auth\AuthController::isMaster())
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#loadCalculationModal">
                                    Perhitungan Beban
                                </button>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                    data-bs-target="#appliedCostHistoryModal">
                                    <i class="fas fa-history me-1"></i>
                                    Riwayat Perhitungan Beban
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
                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
            </div>
            <small id="searchCount" class="form-text text-muted mt-2"></small>
        </div>

        <!-- Tables Section -->
        <div class="table-container">
            <!-- Stocks Table -->
            <div class="table-section mb-4" data-table="stocks">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">Stok Barang Dagangan</h3>

                    <!-- Toggle Switch for Accounting/Management Mode -->
                    @if (App\Http\Controllers\Auth\AuthController::isMaster())
                        <div class="mode-switch-container">
                            <div class="btn-group" role="group" aria-label="Mode Selection">
                                <input type="radio" class="btn-check" name="stockMode" id="accounting-mode"
                                    value="accounting" checked>
                                <label class="btn btn-outline-primary" for="accounting-mode">
                                    <i class="fas fa-calculator me-1"></i>
                                    Akuntansi
                                </label>

                                <input type="radio" class="btn-check" name="stockMode" id="management-mode"
                                    value="management">
                                <label class="btn btn-outline-success" for="management-mode">
                                    <i class="fas fa-chart-line me-1"></i>
                                    Manajemen
                                </label>
                            </div>

                            <!-- Mode Indicator -->
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
                                            data-size="{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                                                <td rowspan="{{ count($sizes) }}" class="item-name">
                                                    {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                                            @endif
                                            <td class="item-size">{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}
                                            </td>
                                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                                            <td class="hpp-column">Rp.
                                                {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td class="hpp-column">Rp.
                                                {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td class="hpp-column">Rp.
                                                {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td class="hpp-column">Rp.
                                                {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm btn-primary detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="stocks">Detail</button>
                                                @else
                                                    <span>No Detail</span>
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

            <!-- Transfer Stocks Table -->
            <div class="table-section mb-4" data-table="transfer_stocks" hidden>
                <h3>Stok Pemindahan</h3>
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
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemCount = 1; @endphp
                            @if (is_array($transferStockData) && !empty($transferStockData))
                                @foreach ($transferStockData as $item => $sizes)
                                    @foreach ($sizes as $index => $stock)
                                        <tr data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                            data-size="{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                                                <td rowspan="{{ count($sizes) }}" class="item-name">
                                                    {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                                            @endif
                                            <td class="item-size">{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}
                                            </td>
                                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm btn-primary detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_transfer_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="transfer_stocks">Detail</button>
                                                @else
                                                    <span>No Detail</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="14" class="text-center">
                                        <div class="alert alert-info mb-0">Data transfer stok belum ditemukan.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Used Stocks Table -->
            <div class="table-section mb-4" data-table="used_stocks" hidden>
                <h3>Stok Barang Jadi</h3>
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
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th>Qty</th>
                                <th>HPP</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $itemCount = 1; @endphp
                            @if (is_array($usedStockData) && !empty($usedStockData))
                                @foreach ($usedStockData as $item => $sizes)
                                    @foreach ($sizes as $index => $stock)
                                        <tr data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                            data-size="{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}">
                                            @if ($index === 0)
                                                <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                                                <td rowspan="{{ count($sizes) }}" class="item-name">
                                                    {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                                            @endif
                                            <td class="item-size">{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}
                                            </td>
                                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                                            <td>Rp. {{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                @if ($stock->id)
                                                    <button class="btn btn-sm btn-primary detail-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailModal_used_stocks_{{ $stock->id }}"
                                                        data-stock-id="{{ $stock->id }}"
                                                        data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}"
                                                        data-table-name="used_stocks">Detail</button>
                                                @else
                                                    <span>No Detail</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="14" class="text-center">
                                        <div class="alert alert-info mb-0">Data stok yang digunakan belum ditemukan.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recipe List Modal -->
        <div class="modal fade" id="RecipeList" tabindex="-1" aria-labelledby="RecipeListLabel" aria-hidden="true"
            hidden>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="RecipeListLabel">Daftar Formula</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" id="recipeGlobalSearch" class="form-control"
                                    placeholder="Cari nama produk, ukuran, atau bahan baku...">
                                <button class="btn btn-outline-secondary" type="button"
                                    id="clearRecipeSearch">Clear</button>
                            </div>
                            <small id="recipeSearchCount" class="form-text text-muted mt-2"></small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover text-center" id="recipeTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Ukuran</th>
                                        <th>Total Nominal</th>
                                        <th>Detail Bahan Baku</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recipeCount = 1;
                                    @endphp
                                    @if (!empty($recipes))
                                        @foreach ($recipes as $recipe)
                                            @php
                                                $hasTransactions = DB::table('transactions')
                                                    ->where('description', $recipe->product_name)
                                                    ->where('size', $recipe->size)
                                                    ->exists();
                                            @endphp
                                            <tr class="recipe-row"
                                                data-product-name="{{ strtolower(htmlspecialchars($recipe->product_name ?? 'Unknown Product')) }}"
                                                data-product-size="{{ strtolower(htmlspecialchars($recipe->size ?? 'Unknown Size')) }}"
                                                data-ingredients="{{ strtolower(implode(' ',collect($recipe->transferStocks ?? [])->pluck('item')->toArray())) }}"
                                                data-ingredient-sizes="{{ strtolower(implode(' ',collect($recipe->transferStocks ?? [])->pluck('size')->toArray())) }}"
                                                data-recipe-id="{{ $recipe->id }}"
                                                data-has-transactions="{{ $hasTransactions ? 'true' : 'false' }}">
                                                <td>{{ $recipeCount++ }}</td>
                                                <td class="product-name-cell">
                                                    {{ htmlspecialchars($recipe->product_name ?? 'Unknown Product') }}</td>
                                                <td class="product-size-cell">
                                                    {{ htmlspecialchars($recipe->size ?? 'Unknown Size') }}</td>
                                                <td>
                                                    {{ number_format($recipe->transferStocks->sum('nominal') ?? 0, 2, ',', '.') }}
                                                </td>
                                                <td>
                                                    @if (!empty($recipe->transferStocks))
                                                        <table
                                                            class="table table-sm table-bordered mt-2 ingredients-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Bahan Baku</th>
                                                                    <th>Ukuran</th>
                                                                    <th>Kuantitas</th>
                                                                    <th>Nominal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($recipe->transferStocks as $transferStock)
                                                                    <tr>
                                                                        <td class="ingredient-name-cell">
                                                                            {{ htmlspecialchars($transferStock->item ?? 'Unknown Item') }}
                                                                        </td>
                                                                        <td class="ingredient-size-cell">
                                                                            {{ htmlspecialchars($transferStock->size ?? 'Unknown Size') }}
                                                                        </td>
                                                                        <td>{{ $transferStock->quantity ?? 0 }}</td>
                                                                        <td>{{ number_format($transferStock->nominal ?? 0, 2, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <span>Tidak ada bahan baku</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-warning edit-recipe-btn {{ $hasTransactions ? 'disabled' : '' }}"
                                                            data-recipe-id="{{ $recipe->id }}"
                                                            data-product-name="{{ $recipe->product_name }}"
                                                            data-product-size="{{ $recipe->size }}"
                                                            {{ $hasTransactions ? 'disabled' : '' }}
                                                            title="{{ $hasTransactions ? 'Recipe sudah digunakan dalam transaksi' : 'Edit Recipe' }}">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger delete-recipe-btn {{ $hasTransactions ? 'disabled' : '' }}"
                                                            data-recipe-id="{{ $recipe->id }}"
                                                            data-product-name="{{ $recipe->product_name }}"
                                                            data-product-size="{{ $recipe->size }}"
                                                            {{ $hasTransactions ? 'disabled' : '' }}
                                                            title="{{ $hasTransactions ? 'Recipe sudah digunakan dalam transaksi' : 'Hapus Recipe' }}">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="noRecipeData">
                                            <td colspan="6" class="text-center">
                                                <div class="alert alert-info mb-0">Tidak ada rumus yang ditemukan.</div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Create Recipe Modal -->
        <div class="modal fade" id="createRecipeModal" tabindex="-1" aria-labelledby="createRecipeModalLabel"
            aria-hidden="true" hidden>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form id="recipeForm" action="{{ route('recipe.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createRecipeModalLabel">Buat Rumus Produk</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Nama Produk</label>
                                <input type="text" name="product_name" id="product_name" class="form-control"
                                    required minlength="2" maxlength="100" pattern="[A-Za-z0-9\s]+"
                                    title="Nama produk hanya boleh berisi huruf, angka, dan spasi">
                                <div class="invalid-feedback">Masukkan nama produk yang valid (2-100 karakter, hanya huruf,
                                    angka, dan spasi)</div>
                            </div>
                            <div class="mb-3">
                                <label for="product_size" class="form-label">Ukuran Produk</label>
                                <input type="text" name="product_size" id="product_size" class="form-control"
                                    maxlength="50" pattern="[A-Za-z0-9\s\-\/]*"
                                    title="Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring">
                                <div class="invalid-feedback">Masukkan ukuran produk yang valid (maksimal 50 karakter)
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="total_nominal" class="form-label">Total Nominal</label>
                                <input type="number" name="total_nominal" id="total_nominal" class="form-control"
                                    value="0.00" step="0.01" readonly>
                                <div class="invalid-feedback">Total nominal tidak valid</div>
                            </div>
                            <div id="ingredientsContainer">
                                <div class="ingredient-row mb-3" data-row-id="0">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label for="transfer_stock_id_0" class="form-label">Bahan Baku</label>
                                            <select name="transfer_stock_id[]" id="transfer_stock_id_0"
                                                class="form-select transfer-stock-select" required>
                                                <option value="">Pilih Bahan Baku</option>
                                                @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                                                    @foreach (collect($transferStockData)->flatten() as $stock)
                                                        <option value="{{ $stock->id }}"
                                                            data-max-quantity="{{ $stock->quantity }}"
                                                            data-nominal="{{ $stock->nominal ?? 0 }}">
                                                            {{ htmlspecialchars($stock->item) }}
                                                            ({{ htmlspecialchars($stock->size) }})
                                                            - {{ $stock->quantity }} tersedia
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>Tidak ada bahan baku tersedia</option>
                                                @endif
                                            </select>
                                            <div class="invalid-feedback">Pilih bahan baku</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="quantity_0" class="form-label">Kuantitas</label>
                                            <input type="number" name="quantity[]" id="quantity_0" class="form-control"
                                                min="1" max="999999" step="1" required>
                                            <div class="invalid-feedback">Masukkan kuantitas yang valid (minimal 1)</div>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="nominal_0" class="form-label">Nominal</label>
                                            <input type="number" name="nominal[]" id="nominal_0" class="form-control"
                                                min="0" step="0.01" readonly>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-ingredient">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addIngredient" class="btn btn-secondary mt-2">Tambah Bahan
                                Baku</button>
                            <div id="errorMessage" class="text-danger mt-2" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Resep</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Recipe Modal -->
        <div class="modal fade" id="editRecipeModal" tabindex="-1" aria-labelledby="editRecipeModalLabel"
            aria-hidden="true" hidden>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <form id="editRecipeForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editRecipeModalLabel">Edit Rumus Produk</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit_recipe_id" name="recipe_id">
                            <div class="mb-3">
                                <label for="edit_product_name" class="form-label">Nama Produk</label>
                                <input type="text" name="product_name" id="edit_product_name" class="form-control"
                                    required minlength="2" maxlength="100" pattern="[A-Za-z0-9\s]+"
                                    title="Nama produk hanya boleh berisi huruf, angka, dan spasi">
                                <div class="invalid-feedback">Masukkan nama produk yang valid (2-100 karakter, hanya huruf,
                                    angka, dan spasi)</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_product_size" class="form-label">Ukuran Produk</label>
                                <input type="text" name="product_size" id="edit_product_size" class="form-control"
                                    maxlength="50" pattern="[A-Za-z0-9\s\-\/]*"
                                    title="Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring">
                                <div class="invalid-feedback">Masukkan ukuran produk yang valid (maksimal 50 karakter)
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_total_nominal" class="form-label">Total Nominal</label>
                                <input type="number" name="total_nominal" id="edit_total_nominal" class="form-control"
                                    value="0.00" step="0.01" readonly>
                                <div class="invalid-feedback">Total nominal tidak valid</div>
                            </div>
                            <div id="editIngredientsContainer">
                                <!-- Dynamic content will be loaded here -->
                            </div>
                            <button type="button" id="addEditIngredient" class="btn btn-secondary mt-2">Tambah Bahan
                                Baku</button>
                            <div id="editErrorMessage" class="text-danger mt-2" style="display: none;"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update Resep</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Recipe Confirmation Modal -->
        <div class="modal fade" id="deleteRecipeModal" tabindex="-1" aria-labelledby="deleteRecipeModalLabel"
            aria-hidden="true" hidden>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteRecipeModalLabel">Konfirmasi Hapus Recipe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus recipe ini?</p>
                        <div class="alert alert-warning">
                            <strong>Produk:</strong> <span id="deleteProductName"></span><br>
                            <strong>Ukuran:</strong> <span id="deleteProductSize"></span>
                        </div>
                        <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form id="deleteRecipeForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Ya, Hapus Recipe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Detail Modals -->
        @php
            $allStocks = [
                'stocks' => collect($stockData)->flatten()->toArray() ?? [],
                'transfer_stocks' => collect($transferStockData)->flatten()->toArray() ?? [],
                'used_stocks' => collect($usedStockData)->flatten()->toArray() ?? [],
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
                                        id="detailModalLabel_{{ $tableName }}_{{ $stock->id }}">Detail Transaksi
                                        untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }}
                                        ({{ $tableName }})
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-label">Tampilkan Transaksi</label>
                                        <select id="modal_filter_{{ $tableName }}_{{ $stock->id }}"
                                            class="form-select modal-filter" data-stock-id="{{ $stock->id }}">
                                            <option value="all"
                                                {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>Semua Transaksi
                                            </option>
                                            <option value="7_days"
                                                {{ request('filter', 'all') == '7_days' ? 'selected' : '' }}>7 Hari
                                                Terakhir</option>
                                            <option value="1_month"
                                                {{ request('filter', 'all') == '1_month' ? 'selected' : '' }}>1 Bulan
                                                Terakhir</option>
                                        </select>
                                    </div>
                                    <div class="transaction-table"
                                        id="transactionTable_{{ $tableName }}_{{ $stock->id }}">
                                        @if (isset($stock->transactions) && !empty($stock->transactions))
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover text-center">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>No</th>
                                                            <th>Nama Item</th>
                                                            <th>No Voucher</th>
                                                            <th>Tipe Transaksi</th>
                                                            <th>Kuantitas</th>
                                                            <th>Nominal</th>
                                                            <th>Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($stock->transactions as $transaction)
                                                            @if (!str_starts_with($transaction->description ?? '', 'HPP '))
                                                                <tr
                                                                    data-transaction-date="{{ $transaction->created_at }}">
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ htmlspecialchars($transaction->description ?? 'No Description') }}
                                                                    </td>
                                                                    <td>
                                                                        @if ($transaction->voucher_id && $transaction->voucher_number !== 'N/A')
                                                                            <a href="{{ route('voucher_detail', $transaction->voucher_id) }}"
                                                                                class="text-decoration-none">
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

                                                                            @case('PH')
                                                                                Pemindahan
                                                                            @break

                                                                            @case('PK')
                                                                                Pemakaian
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
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-center">Tidak ada transaksi terkait untuk barang ini.</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="loadCalculationForm" action="{{ route('applied_cost.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                            <div id="bebanInputs">
                                <!-- Default Initial Row -->
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
                            <button type="button" class="btn btn-outline-primary mb-3" id="addBebanRow">Tambah
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
                            <i class="fas fa-history me-2"></i>
                            Riwayat Perhitungan Beban
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Mode Selection for Applied Cost -->
                        <div class="row mb-3" id="appliedCostModeSelection" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Mode Manajemen Aktif:</strong> Pilih riwayat perhitungan beban untuk diterapkan
                                    pada perhitungan HPP.
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <label class="form-label mb-0">Pilih Riwayat yang Digunakan:</label>
                                    <select class="form-select w-auto" id="selectedAppliedCostId">
                                        <option value="">Pilih Riwayat...</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-success" id="applySelectedCost">
                                        <i class="fas fa-check me-1"></i>
                                        Terapkan
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" id="clearSelectedCost">
                                        <i class="fas fa-times me-1"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

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
                                <button type="button" class="btn btn-outline-primary w-100" id="refreshHistory">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Refresh
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
                                    <!-- Data will be loaded via AJAX -->
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav aria-label="History pagination" id="historyPagination" style="display: none;">
                            <ul class="pagination justify-content-center mb-0">
                                <!-- Pagination items will be generated dynamically -->
                            </ul>
                        </nav>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <span class="text-muted" id="historyCount">Total: 0 riwayat</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define all variables at the top
            const form = document.getElementById('filterForm');
            const tableFilterInput = document.getElementById('table_filter');
            const dropdownButton = document.getElementById('tableFilterDropdown');
            const tableSections = document.querySelectorAll('.table-section');
            const globalSearch = document.getElementById('globalSearch');
            const clearSearch = document.getElementById('clearSearch');
            const searchCount = document.getElementById('searchCount');
            const recipeGlobalSearch = document.getElementById('recipeGlobalSearch');
            const clearRecipeSearch = document.getElementById('clearRecipeSearch');
            const recipeSearchCount = document.getElementById('recipeSearchCount');
            const recipeTable = document.getElementById('recipeTable');
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
            document.getElementById('appliedCostHistoryModal').addEventListener('shown.bs.modal', function() {
                loadAppliedCostHistory();

                // Show mode selection if in management mode
                const isManagementMode = document.querySelector('.management-mode');
                if (isManagementMode) {
                    document.getElementById('appliedCostModeSelection').style.display = 'block';
                }
            });

            // Search functionality
            document.getElementById('historySearch').addEventListener('input', debounce(filterHistory, 300));

            // Date filter
            document.getElementById('historyDateFilter').addEventListener('change', filterHistory);

            // Refresh button
            document.getElementById('refreshHistory').addEventListener('click', loadAppliedCostHistory);

            // Apply selected cost
            document.getElementById('applySelectedCost').addEventListener('click', applySelectedAppliedCost);

            // Clear selected cost
            document.getElementById('clearSelectedCost').addEventListener('click', clearSelectedAppliedCost);

            // Listen for stock mode changes
            window.addEventListener('stockModeChanged', function(e) {
                const modeSelection = document.getElementById('appliedCostModeSelection');
                if (e.detail.mode === 'management') {
                    modeSelection.style.display = 'block';
                } else {
                    modeSelection.style.display = 'none';
                    clearSelectedAppliedCost();
                }
            });


            let ingredientCount = document.querySelectorAll('.ingredient-row').length - 1;
            let editIngredientCount = 0;

            // Table visibility function
            function updateTableVisibility(filter) {
                tableSections.forEach(section => {
                    section.classList.toggle('hidden', filter !== 'all' && filter !== section.dataset
                        .table);
                });
                applyGlobalFilter(); // Re-apply filter after visibility change
            }

            // Initialize table visibility
            if (tableFilterInput) { // Check if tableFilterInput exists
                const initialFilter = tableFilterInput.value || 'all';
                updateTableVisibility(initialFilter);
                dropdownButton.textContent = {
                    'all': 'Semua Table',
                    'stocks': 'Table Stocks',
                    'transfer_stocks': 'Table Transfer Stocks',
                    'used_stocks': 'Table Used Stocks'
                } [initialFilter] || 'Semua Table';
            }

            // Handle dropdown item clicks
            document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterValue = this.dataset.filter;
                    if (tableFilterInput) {
                        tableFilterInput.value = filterValue;
                    }
                    dropdownButton.textContent = this.textContent;
                    updateTableVisibility(filterValue);
                });
            });

            // Global Filter Function
            function applyGlobalFilter() {
                const searchValue = globalSearch.value.trim().toLowerCase();
                let totalVisibleItems = 0;
                let currentItem = '';
                let itemRows = [];
                let itemMatches = false;

                // Clear existing highlights
                document.querySelectorAll('.highlight').forEach(el => {
                    el.replaceWith(el.textContent);
                });

                tableSections.forEach(section => {
                    if (section.classList.contains('hidden')) return;
                    const rows = section.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        if (row.querySelector('td[colspan]')) return; // Skip no-data rows

                        const item = row.dataset.item.toLowerCase();
                        const size = row.dataset.size.toLowerCase();

                        // Check if new item group
                        if (item !== currentItem) {
                            // Process previous group
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
                            // Reset for new group
                            currentItem = item;
                            itemRows = [];
                            itemMatches = false;
                        }

                        itemRows.push(row);

                        // Check match for this row
                        const matchesItem = item.includes(searchValue);
                        const matchesSize = size.includes(searchValue);
                        if (matchesItem || matchesSize) {
                            itemMatches = true;
                            // Highlight
                            if (searchValue) {
                                if (matchesItem) {
                                    const nameTd = row.closest('tbody').querySelector(
                                        `td.item-name[data-original-text="${row.dataset.item}"]`
                                    ) || row.querySelector('.item-name');
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

                    // Process last group
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

            // Event listeners for global search
            globalSearch.addEventListener('keyup', applyGlobalFilter);
            clearSearch.addEventListener('click', () => {
                globalSearch.value = '';
                applyGlobalFilter();
            });

            // Modal filter logic for client-side filtering
            document.querySelectorAll('.modal-filter').forEach(select => {
                select.addEventListener('change', function() {
                    const stockId = this.dataset.stockId;
                    const tableName = this.closest('.modal-content').dataset.tableName;
                    const filter = this.value;
                    const transactionTable = document.getElementById(
                        `transactionTable_${tableName}_${stockId}`);
                    const rows = transactionTable.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const transactionDate = row.dataset.transactionDate;
                        if (!transactionDate) {
                            row.style.display = filter === 'all' ? '' : 'none';
                            return;
                        }

                        const date = new Date(transactionDate);
                        const now = new Date();
                        let showRow = true;

                        if (filter === '7_days') {
                            showRow = date >= new Date(now.setDate(now.getDate() - 7));
                        } else if (filter === '1_month') {
                            showRow = date >= new Date(now.setMonth(now.getMonth() - 1));
                        }

                        row.style.display = showRow || filter === 'all' ? '' : 'none';
                    });

                    // Show message if no transactions are visible
                    const visibleRows = Array.from(rows).filter(row => row.style.display !==
                        'none');
                    if (visibleRows.length === 0) {
                        transactionTable.innerHTML =
                            '<p class="text-center">Tidak ada transaksi terkait untuk periode ini.</p>';
                    } else if (transactionTable.querySelector('p.text-center')) {
                        location.reload(); // Simplest way to restore the table
                    }
                });
            });

            // Recipe form validation and dynamic ingredient rows
            function updateNominal(row, select, quantityInput) {
                const selectedOption = select.options[select.selectedIndex];
                const nominal = parseFloat(selectedOption?.dataset.nominal || 0);
                const quantity = parseInt(quantityInput.value) || 0;
                const nominalInput = row.querySelector('input[name="nominal[]"]');
                const feedback = row.querySelector('.invalid-feedback');

                if (isNaN(nominal) || nominal <= 0) {
                    nominalInput.value = '0.00';
                    feedback.textContent = 'Nominal tidak tersedia untuk bahan baku ini';
                    feedback.style.display = 'block';
                    return;
                }

                if (quantity > 0) {
                    nominalInput.value = (nominal * quantity).toFixed(2);
                    feedback.style.display = 'none';
                } else {
                    nominalInput.value = '0.00';
                    feedback.textContent = 'Masukkan kuantitas yang valid';
                    feedback.style.display = 'block';
                }
                updateTotalNominal();
            }

            function updateTotalNominal() {
                const nominalInputs = document.querySelectorAll('input[name="nominal[]"]');
                let totalNominal = 0;
                nominalInputs.forEach(input => {
                    totalNominal += parseFloat(input.value) || 0;
                });
                const totalNominalInput = document.getElementById('total_nominal');
                totalNominalInput.value = totalNominal.toFixed(2);
            }

            function validateQuantityInput(row, quantityInput, select) {
                quantityInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    const maxQuantity = select.options[select.selectedIndex]?.dataset.maxQuantity || 999999;
                    const feedback = row.querySelector('.invalid-feedback');

                    if (parseInt(this.value) > parseInt(maxQuantity)) {
                        this.value = maxQuantity;
                        feedback.textContent =
                            `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                        this.classList.add('is-invalid');
                    } else if (parseInt(this.value) < 1) {
                        this.value = 1;
                        feedback.textContent = 'Kuantitas minimal adalah 1';
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        feedback.style.display = 'none';
                    }
                    updateNominal(row, select, this);
                });

                select.addEventListener('change', function() {
                    const maxQuantity = this.options[this.selectedIndex]?.dataset.maxQuantity || 999999;
                    const feedback = row.querySelector('.invalid-feedback');

                    if (quantityInput.value && parseInt(quantityInput.value) > parseInt(maxQuantity)) {
                        quantityInput.value = maxQuantity;
                        quantityInput.classList.add('is-invalid');
                        feedback.textContent =
                            `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                    } else {
                        quantityInput.classList.remove('is-invalid');
                        feedback.style.display = 'none';
                    }
                    updateNominal(row, this, quantityInput);
                });
            }

            // Initialize validation for existing ingredient rows
            document.querySelectorAll('.ingredient-row').forEach(row => {
                const quantityInput = row.querySelector('input[name="quantity[]"]');
                const select = row.querySelector('.transfer-stock-select');
                if (quantityInput && select) {
                    validateQuantityInput(row, quantityInput, select);
                    updateNominal(row, select, quantityInput); // Initialize nominal
                }
            });

            // Add ingredient button handler
            const addIngredientButton = document.getElementById('addIngredient');
            addIngredientButton.addEventListener('click', function() {
                ingredientCount++;
                const container = document.getElementById('ingredientsContainer');
                const newRow = document.createElement('div');
                newRow.className = 'ingredient-row mb-3';
                newRow.dataset.rowId = ingredientCount;
                newRow.innerHTML = `
        <div class="row align-items-end">
            <div class="col-md-4">
                <label for="transfer_stock_id_${ingredientCount}" class="form-label">Bahan Baku</label>
                <select name="transfer_stock_id[]" id="transfer_stock_id_${ingredientCount}" class="form-select transfer-stock-select" required>
                    <option value="">Pilih Bahan Baku</option>
                    @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                        @foreach (collect($transferStockData)->flatten() as $stock)
                            <option value="{{ $stock->id }}" data-max-quantity="{{ $stock->quantity }}" data-nominal="{{ $stock->nominal ?? 0 }}">
                                {{ htmlspecialchars($stock->item) }} ({{ htmlspecialchars($stock->size) }}) - {{ $stock->quantity }} tersedia
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>Tidak ada bahan baku tersedia</option>
                    @endif
                </select>
                <div class="invalid-feedback">Pilih bahan baku</div>
            </div>
            <div class="col-md-3">
                <label for="quantity_${ingredientCount}" class="form-label">Kuantitas</label>
                <input type="number" name="quantity[]" id="quantity_${ingredientCount}" class="form-control" min="1" max="999999" step="1" required>
                <div class="invalid-feedback">Masukkan kuantitas yang valid (minimal 1)</div>
            </div>
            <div class="col-md-3">
                <label for="nominal_${ingredientCount}" class="form-label">Nominal</label>
                <input type="number" name="nominal[]" id="nominal_${ingredientCount}" class="form-control" min="0" step="0.01" readonly value="0.00">
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-ingredient">Hapus</button>
            </div>
        </div>
    `;
                container.appendChild(newRow);

                const quantityInput = newRow.querySelector('input[name="quantity[]"]');
                const select = newRow.querySelector('.transfer-stock-select');

                // Ensure event listeners are attached
                validateQuantityInput(newRow, quantityInput, select);

                // Trigger initial nominal update
                updateNominal(newRow, select, quantityInput);

                // Update remove button visibility
                document.querySelectorAll('.remove-ingredient').forEach(button => {
                    button.style.display = document.querySelectorAll('.ingredient-row').length > 1 ?
                        'block' : 'none';
                });

                updateTotalNominal();
            });
            // Remove ingredient handler
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-ingredient')) {
                    const row = e.target.closest('.ingredient-row');
                    if (document.querySelectorAll('.ingredient-row').length > 1) {
                        row.remove();
                        ingredientCount--;
                        document.querySelectorAll('.remove-ingredient').forEach(button => {
                            button.style.display = document.querySelectorAll('.ingredient-row')
                                .length > 1 ? 'block' : 'none';
                        });
                        updateTotalNominal();
                    }
                }
            });

            // Recipe form validation
            document.getElementById('recipeForm').addEventListener('submit', function(e) {
                const productName = document.getElementById('product_name');
                const productSize = document.getElementById('product_size');
                const nominalInputs = document.querySelectorAll('input[name="nominal[]"]');
                const totalNominalInput = document.getElementById('total_nominal');
                let isValid = true;

                // Validate product name
                if (!productName.value.match(/^[A-Za-z0-9\s]+$/)) {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent =
                        'Nama produk hanya boleh berisi huruf, angka, dan spasi';
                    isValid = false;
                } else if (productName.value.length < 2 || productName.value.length > 100) {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent = 'Nama produk harus antara 2-100 karakter';
                    isValid = false;
                } else {
                    productName.classList.remove('is-invalid');
                }

                // Validate product size
                if (productSize.value && !productSize.value.match(/^[A-Za-z0-9\s\-\/]*$/)) {
                    productSize.classList.add('is-invalid');
                    productSize.nextElementSibling.textContent =
                        'Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring';
                    isValid = false;
                } else if (productSize.value.length > 50) {
                    productSize.classList.add('is-invalid');
                    productSize.nextElementSibling.textContent = 'Ukuran produk maksimal 50 karakter';
                    isValid = false;
                } else {
                    productSize.classList.remove('is-invalid');
                }

                // Validate nominal values
                nominalInputs.forEach(input => {
                    if (parseFloat(input.value) === 0) {
                        const row = input.closest('.ingredient-row');
                        const feedback = row.querySelector('.invalid-feedback');
                        feedback.textContent = 'Nominal tidak valid untuk bahan baku ini';
                        feedback.style.display = 'block';
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                // Validate total nominal
                if (parseFloat(totalNominalInput.value) === 0) {
                    totalNominalInput.classList.add('is-invalid');
                    totalNominalInput.nextElementSibling.textContent =
                        'Total nominal harus lebih besar dari 0';
                    isValid = false;
                } else {
                    totalNominalInput.classList.remove('is-invalid');
                }

                if (!isValid) {
                    e.preventDefault();
                    document.getElementById('errorMessage').textContent =
                        'Harap perbaiki kesalahan pada formulir.';
                    document.getElementById('errorMessage').style.display = 'block';
                }
            });

            // Edit Recipe Button Handler
            document.addEventListener('click', function(e) {
                if (e.target.closest('.edit-recipe-btn')) {
                    const button = e.target.closest('.edit-recipe-btn');
                    if (button.disabled) return;

                    const recipeId = button.dataset.recipeId;
                    const productName = button.dataset.productName;
                    const productSize = button.dataset.productSize;

                    // Set form action and populate basic fields
                    document.getElementById('editRecipeForm').action = `/recipe/${recipeId}`;
                    document.getElementById('edit_recipe_id').value = recipeId;
                    document.getElementById('edit_product_name').value = productName;
                    document.getElementById('edit_product_size').value = productSize;

                    // Load recipe ingredients via AJAX
                    fetch(`/recipe/${recipeId}/ingredients`)
                        .then(response => response.json())
                        .then(data => {
                            loadEditIngredients(data.ingredients);
                            updateEditTotalNominal();
                        })
                        .catch(error => {
                            console.error('Error loading recipe ingredients:', error);
                            alert('Gagal memuat data recipe');
                        });

                    // Show edit modal
                    new bootstrap.Modal(document.getElementById('editRecipeModal')).show();
                }
            });

            // Delete Recipe Button Handler
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-recipe-btn')) {
                    const button = e.target.closest('.delete-recipe-btn');
                    if (button.disabled) return;

                    const recipeId = button.dataset.recipeId;
                    const productName = button.dataset.productName;
                    const productSize = button.dataset.productSize;

                    // Set form action and populate confirmation details
                    document.getElementById('deleteRecipeForm').action = `/recipe/${recipeId}`;
                    document.getElementById('deleteProductName').textContent = productName;
                    document.getElementById('deleteProductSize').textContent = productSize;

                    // Show delete modal
                    new bootstrap.Modal(document.getElementById('deleteRecipeModal')).show();
                }
            });

            // Function to load ingredients for editing
            function loadEditIngredients(ingredients) {
                const container = document.getElementById('editIngredientsContainer');
                container.innerHTML = '';
                editIngredientCount = 0;

                ingredients.forEach((ingredient, index) => {
                    addEditIngredientRow(ingredient);
                });

                // Add one empty row if no ingredients
                if (ingredients.length === 0) {
                    addEditIngredientRow();
                }
            }

            // Function to add ingredient row for editing
            function addEditIngredientRow(ingredient = null) {
                const container = document.getElementById('editIngredientsContainer');
                const newRow = document.createElement('div');
                newRow.className = 'ingredient-row mb-3';
                newRow.dataset.rowId = editIngredientCount;

                newRow.innerHTML = `
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="edit_transfer_stock_id_${editIngredientCount}" class="form-label">Bahan Baku</label>
                            <select name="transfer_stock_id[]" id="edit_transfer_stock_id_${editIngredientCount}" class="form-select transfer-stock-select" required>
                                <option value="">Pilih Bahan Baku</option>
                                @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                                    @foreach (collect($transferStockData)->flatten() as $stock)
                                        <option value="{{ $stock->id }}" data-max-quantity="{{ $stock->quantity }}" data-nominal="{{ $stock->nominal ?? 0 }}">
                                            {{ htmlspecialchars($stock->item) }} ({{ htmlspecialchars($stock->size) }}) - {{ $stock->quantity }} tersedia
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="invalid-feedback">Pilih bahan baku</div>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_quantity_${editIngredientCount}" class="form-label">Kuantitas</label>
                            <input type="number" name="quantity[]" id="edit_quantity_${editIngredientCount}" class="form-control" min="1" max="999999" step="1" required>
                            <div class="invalid-feedback">Masukkan kuantitas yang valid (minimal 1)</div>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_nominal_${editIngredientCount}" class="form-label">Nominal</label>
                            <input type="number" name="nominal[]" id="edit_nominal_${editIngredientCount}" class="form-control" min="0" step="0.01" readonly>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-edit-ingredient">Hapus</button>
                        </div>
                    </div>
                `;

                container.appendChild(newRow);

                // Set values if ingredient data provided
                if (ingredient) {
                    const select = newRow.querySelector('select');
                    const quantityInput = newRow.querySelector('input[name="quantity[]"]');
                    const nominalInput = newRow.querySelector('input[name="nominal[]"]');

                    select.value = ingredient.transfer_stock_id;
                    quantityInput.value = ingredient.quantity;
                    nominalInput.value = ingredient.nominal;
                }

                // Setup validation for new row
                const quantityInput = newRow.querySelector('input[name="quantity[]"]');
                const select = newRow.querySelector('.transfer-stock-select');
                validateEditQuantityInput(newRow, quantityInput, select);

                editIngredientCount++;

                // Update remove button visibility
                updateEditRemoveButtonsVisibility();
            }

            // Add ingredient button for edit modal
            document.getElementById('addEditIngredient').addEventListener('click', function() {
                addEditIngredientRow();
            });

            // Remove ingredient handler for edit modal
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-edit-ingredient')) {
                    const row = e.target.closest('.ingredient-row');
                    if (document.querySelectorAll('#editIngredientsContainer .ingredient-row').length > 1) {
                        row.remove();
                        editIngredientCount--;
                        updateEditRemoveButtonsVisibility();
                        updateEditTotalNominal();
                    }
                }
            });

            // Update remove buttons visibility for edit modal
            function updateEditRemoveButtonsVisibility() {
                const removeButtons = document.querySelectorAll(
                    '#editIngredientsContainer .remove-edit-ingredient');
                removeButtons.forEach(button => {
                    button.style.display = removeButtons.length > 1 ? 'block' : 'none';
                });
            }

            // Update total nominal for edit modal
            function updateEditTotalNominal() {
                const nominalInputs = document.querySelectorAll(
                    '#editIngredientsContainer input[name="nominal[]"]');
                let totalNominal = 0;
                nominalInputs.forEach(input => {
                    totalNominal += parseFloat(input.value) || 0;
                });
                document.getElementById('edit_total_nominal').value = totalNominal.toFixed(2);
            }

            // Edit Recipe Modal Functions
            function updateEditNominal(row, select, quantityInput) {
                const selectedOption = select.options[select.selectedIndex];
                const nominal = parseFloat(selectedOption?.dataset.nominal || 0);
                const quantity = parseInt(quantityInput.value) || 0;
                const nominalInput = row.querySelector('input[name="nominal[]"]');
                const feedback = row.querySelector('.invalid-feedback');

                if (nominal && quantity) {
                    nominalInput.value = (nominal * quantity).toFixed(2);
                    feedback.style.display = 'none';
                } else {
                    nominalInput.value = '0.00';
                    if (!nominal) {
                        feedback.textContent = 'Nominal tidak tersedia untuk bahan baku ini';
                        feedback.style.display = 'block';
                    } else if (!quantity) {
                        feedback.textContent = 'Masukkan kuantitas yang valid';
                        feedback.style.display = 'block';
                    } else {
                        feedback.style.display = 'none';
                    }
                }
                updateEditTotalNominal();
            }

            function validateEditQuantityInput(row, quantityInput, select) {
                quantityInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    const maxQuantity = select.options[select.selectedIndex]?.dataset.maxQuantity || 999999;
                    const feedback = row.querySelector('.invalid-feedback');

                    if (parseInt(this.value) > parseInt(maxQuantity)) {
                        this.value = maxQuantity;
                        feedback.textContent =
                            `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                        this.classList.add('is-invalid');
                    } else if (parseInt(this.value) < 1) {
                        this.value = 1;
                        feedback.textContent = 'Kuantitas minimal adalah 1';
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        feedback.style.display = 'none';
                    }
                    updateEditNominal(row, select, this);
                });

                select.addEventListener('change', function() {
                    const maxQuantity = this.options[this.selectedIndex]?.dataset.maxQuantity || 999999;
                    const feedback = row.querySelector('.invalid-feedback');

                    if (quantityInput.value && parseInt(quantityInput.value) > parseInt(maxQuantity)) {
                        quantityInput.value = maxQuantity;
                        quantityInput.classList.add('is-invalid');
                        feedback.textContent =
                            `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                    } else {
                        quantityInput.classList.remove('is-invalid');
                        feedback.style.display = 'none';
                    }
                    updateEditNominal(row, this, quantityInput);
                });
            }

            // Edit Recipe Form Validation
            document.getElementById('editRecipeForm').addEventListener('submit', function(e) {
                const productName = document.getElementById('edit_product_name');
                const productSize = document.getElementById('edit_product_size');
                const nominalInputs = document.querySelectorAll(
                    '#editIngredientsContainer input[name="nominal[]"]');
                const totalNominalInput = document.getElementById('edit_total_nominal');
                let isValid = true;

                // Validate product name
                if (!productName.value.match(/^[A-Za-z0-9\s]+$/)) {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent =
                        'Nama produk hanya boleh berisi huruf, angka, dan spasi';
                    isValid = false;
                } else if (productName.value.length < 2 || productName.value.length > 100) {
                    productName.classList.add('is-invalid');
                    productName.nextElementSibling.textContent = 'Nama produk harus antara 2-100 karakter';
                    isValid = false;
                } else {
                    productName.classList.remove('is-invalid');
                }

                // Validate product size
                if (productSize.value && !productSize.value.match(/^[A-Za-z0-9\s\-\/]*$/)) {
                    productSize.classList.add('is-invalid');
                    productSize.nextElementSibling.textContent =
                        'Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring';
                    isValid = false;
                } else if (productSize.value.length > 50) {
                    productSize.classList.add('is-invalid');
                    productSize.nextElementSibling.textContent = 'Ukuran produk maksimal 50 karakter';
                    isValid = false;
                } else {
                    productSize.classList.remove('is-invalid');
                }

                // Validate nominal values
                nominalInputs.forEach(input => {
                    if (parseFloat(input.value) === 0) {
                        const row = input.closest('.ingredient-row');
                        const feedback = row.querySelector('.invalid-feedback');
                        feedback.textContent = 'Nominal tidak valid untuk bahan baku ini';
                        feedback.style.display = 'block';
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });

                // Validate total nominal
                if (parseFloat(totalNominalInput.value) === 0) {
                    totalNominalInput.classList.add('is-invalid');
                    totalNominalInput.nextElementSibling.textContent =
                        'Total nominal harus lebih besar dari 0';
                    isValid = false;
                } else {
                    totalNominalInput.classList.remove('is-invalid');
                }

                if (!isValid) {
                    e.preventDefault();
                    document.getElementById('editErrorMessage').textContent =
                        'Harap perbaiki kesalahan pada formulir.';
                    document.getElementById('editErrorMessage').style.display = 'block';
                }
            });

            // Clear error messages when modals are hidden
            document.getElementById('editRecipeModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('editErrorMessage').style.display = 'none';
                document.querySelectorAll('#editIngredientsContainer .is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            });

            // Recipe Global Filter Function
            function applyRecipeGlobalFilter() {
                const searchValue = recipeGlobalSearch.value.trim().toLowerCase();
                const recipeRows = document.querySelectorAll('.recipe-row');
                let visibleCount = 0;

                // Clear existing highlights
                document.querySelectorAll('.recipe-highlight').forEach(el => {
                    const parent = el.parentNode;
                    parent.replaceChild(document.createTextNode(el.textContent), el);
                    parent.normalize();
                });

                // Handle empty search
                if (!searchValue) {
                    recipeRows.forEach(row => {
                        row.classList.remove('hidden');
                        row.style.display = '';
                        visibleCount++;
                    });
                    recipeSearchCount.textContent = '';
                    removeNoResultsMessage();
                    return;
                }

                // Apply filter
                recipeRows.forEach(row => {
                    const productName = row.dataset.productName || '';
                    const productSize = row.dataset.productSize || '';
                    const ingredients = row.dataset.ingredients || '';
                    const ingredientSizes = row.dataset.ingredientSizes || '';

                    const matchesProduct = productName.includes(searchValue);
                    const matchesSize = productSize.includes(searchValue);
                    const matchesIngredients = ingredients.includes(searchValue);
                    const matchesIngredientSizes = ingredientSizes.includes(searchValue);

                    if (matchesProduct || matchesSize || matchesIngredients || matchesIngredientSizes) {
                        row.classList.remove('hidden');
                        row.style.display = '';
                        visibleCount++;

                        // Highlight matches
                        highlightText(row.querySelector('.product-name-cell'), searchValue, matchesProduct);
                        highlightText(row.querySelector('.product-size-cell'), searchValue, matchesSize);

                        // Highlight ingredient matches
                        if (matchesIngredients || matchesIngredientSizes) {
                            const ingredientNameCells = row.querySelectorAll('.ingredient-name-cell');
                            const ingredientSizeCells = row.querySelectorAll('.ingredient-size-cell');

                            ingredientNameCells.forEach(cell => {
                                if (cell.textContent.toLowerCase().includes(searchValue)) {
                                    highlightText(cell, searchValue, true);
                                }
                            });

                            ingredientSizeCells.forEach(cell => {
                                if (cell.textContent.toLowerCase().includes(searchValue)) {
                                    highlightText(cell, searchValue, true);
                                }
                            });
                        }
                    } else {
                        row.classList.add('hidden');
                        row.style.display = 'none';
                    }
                });

                // Update search count
                recipeSearchCount.textContent = searchValue ? `${visibleCount} formula ditemukan` : '';

                // Show or hide no results message
                if (visibleCount === 0) {
                    showNoResultsMessage();
                } else {
                    removeNoResultsMessage();
                }
            }

            // Helper function to highlight text
            function highlightText(element, searchValue, shouldHighlight) {
                if (!element || !shouldHighlight) return;
                const text = element.textContent;
                const regex = new RegExp(`(${searchValue})`, 'gi');
                element.innerHTML = text.replace(regex, '<span class="recipe-highlight">$1</span>');
            }

            // Show no results message
            function showNoResultsMessage() {
                if (!document.getElementById('noResultsMessage')) {
                    const noResultsDiv = document.createElement('tr');
                    noResultsDiv.id = 'noResultsMessage';
                    noResultsDiv.innerHTML = `
                        <td colspan="6" class="text-center">
                            <div class="no-results-message">Tidak ada formula yang cocok dengan pencarian.</div>
                        </td>
                    `;
                    recipeTable.querySelector('tbody').appendChild(noResultsDiv);
                }
            }

            // Remove no results message
            function removeNoResultsMessage() {
                const noResultsMessage = document.getElementById('noResultsMessage');
                if (noResultsMessage) {
                    noResultsMessage.remove();
                }
            }

            // Event listeners for recipe search
            recipeGlobalSearch.addEventListener('keyup', applyRecipeGlobalFilter);
            clearRecipeSearch.addEventListener('click', () => {
                recipeGlobalSearch.value = '';
                applyRecipeGlobalFilter();
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Initialize form validation for existing inputs
            ['product_name', 'product_size'].forEach(id => {
                const input = document.getElementById(id);
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });

            // Ensure remove buttons are hidden for single ingredient row
            document.querySelectorAll('.remove-ingredient').forEach(button => {
                button.style.display = document.querySelectorAll('.ingredient-row').length > 1 ? 'block' :
                    'none';
            });
            // Function to calculate and update total
            function updateTotal() {
                let total = 0;
                const nominalInputs = bebanInputs.querySelectorAll('input[name="beban_nominal[]"]');
                nominalInputs.forEach(input => {
                    const value = parseFloat(input.value) || 0;
                    total += value;
                });
                totalAkumulasi.value = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(total);
                totalHidden.value = total.toFixed(2); // Store total for submission
                return total;
            }

            // Add new row
            addBebanRow.addEventListener('click', function() {
                const rowCount = bebanInputs.querySelectorAll('.beban-row').length + 1;
                const newRow = document.createElement('div');
                newRow.className = 'input-group mb-2 beban-row';
                newRow.innerHTML = `
                    <span class="input-group-text">Beban ${rowCount}</span>
                    <input type="text" class="form-control" name="beban_description[]" placeholder="Deskripsi Beban (e.g., Beban Operasional)" required>
                    <input type="number" class="form-control" name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0" required>
                    <button type="button" class="btn btn-outline-danger remove-row">Hapus</button>
                    <div class="invalid-feedback"></div>
                `;
                bebanInputs.appendChild(newRow);
                renumberRows();
                updateTotal();
            });

            // Remove row event delegation
            bebanInputs.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-row')) {
                    const row = e.target.closest('.beban-row');
                    if (bebanInputs.querySelectorAll('.beban-row').length > 1) {
                        row.remove();
                        renumberRows();
                        updateTotal();
                    }
                }
            });

            // Input change event for real-time update
            bebanInputs.addEventListener('input', function(e) {
                if (e.target.name === 'beban_nominal[]') {
                    updateTotal();
                }
                // Clear invalid feedback on input
                if (e.target.checkValidity()) {
                    e.target.classList.remove('is-invalid');
                    const feedback = e.target.nextElementSibling?.classList.contains('invalid-feedback') ?
                        e.target.nextElementSibling :
                        e.target.nextElementSibling?.nextElementSibling;
                    if (feedback) feedback.textContent = '';
                }
            });

            // Renumber rows after removal
            function renumberRows() {
                const rows = bebanInputs.querySelectorAll('.beban-row');
                rows.forEach((row, index) => {
                    row.querySelector('.input-group-text').textContent = `Beban ${index + 1}`;
                    row.querySelector('.remove-row').style.display = rows.length > 1 ? 'block' : 'none';
                });
            }

            // Client-side form validation
            loadCalculationForm.addEventListener('submit', function(e) {
                const descriptions = Array.from(bebanInputs.querySelectorAll(
                        'input[name="beban_description[]"]'))
                    .map(input => input.value.trim());
                const nominals = Array.from(bebanInputs.querySelectorAll('input[name="beban_nominal[]"]'))
                    .map(input => parseFloat(input.value) || 0);

                let isValid = true;
                if (descriptions.length === 0 || nominals.length === 0 || descriptions.length !== nominals
                    .length) {
                    errorMessage.textContent =
                        'Harap masukkan setidaknya satu beban dengan deskripsi dan nominal.';
                    errorMessage.style.display = 'block';
                    e.preventDefault();
                    return;
                }

                descriptions.forEach((desc, index) => {
                    const descInput = bebanInputs.querySelectorAll(
                        'input[name="beban_description[]"]')[index];
                    if (!desc) {
                        descInput.classList.add('is-invalid');
                        const feedback = descInput.nextElementSibling?.nextElementSibling
                            ?.nextElementSibling || descInput.nextElementSibling;
                        feedback.textContent = 'Deskripsi tidak boleh kosong.';
                        isValid = false;
                    }
                });

                nominals.forEach((nom, index) => {
                    const nomInput = bebanInputs.querySelectorAll('input[name="beban_nominal[]"]')[
                        index];
                    if (nom <= 0) {
                        nomInput.classList.add('is-invalid');
                        const feedback = nomInput.nextElementSibling?.nextElementSibling || nomInput
                            .nextElementSibling;
                        feedback.textContent = 'Nominal harus lebih besar dari 0.';
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    errorMessage.textContent = 'Harap perbaiki kesalahan pada formulir.';
                    errorMessage.style.display = 'block';
                }
            });

            // Clear error message and validation styles when modal is closed
            document.getElementById('loadCalculationModal').addEventListener('hidden.bs.modal', function() {
                errorMessage.style.display = 'none';
                loadCalculationForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                    'is-invalid'));
                loadCalculationForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent =
                    '');
                // Reset form to one row
                bebanInputs.innerHTML = `
                    <div class="input-group mb-2 beban-row">
                        <span class="input-group-text">Beban 1</span>
                        <input type="text" class="form-control" name="beban_description[]" placeholder="Deskripsi Beban (e.g., Beban Operasional)" required>
                        <input type="number" class="form-control" name="beban_nominal[]" placeholder="Nominal (Rp)" step="0.01" min="0" required>
                        <button type="button" class="btn btn-outline-danger remove-row" style="display: none;">Hapus</button>
                        <div class="invalid-feedback"></div>
                    </div>
                `;
                updateTotal();
            });

            // Initialize
            renumberRows();
            updateTotal();

            function updateMode(mode) {
                const indicatorSpan = modeIndicator.querySelector('span');

                if (mode === 'accounting') {
                    indicatorSpan.textContent = 'Akuntansi';
                    indicatorSpan.className = 'fw-bold text-primary';
                    tableContainer.classList.remove('management-mode');

                    // Show all HPP columns
                    document.querySelectorAll('.hpp-column').forEach(col => {
                        col.style.display = '';
                    });

                    // Update colspan for empty data row
                    const emptyRow = document.querySelector('td[colspan="12"]');
                    if (emptyRow) {
                        emptyRow.setAttribute('colspan', '12');
                    }

                } else if (mode === 'management') {
                    indicatorSpan.textContent = 'Manajemen';
                    indicatorSpan.className = 'fw-bold text-success';
                    tableContainer.classList.add('management-mode');

                    // Ensure HPP columns are visible
                    document.querySelectorAll('.hpp-column').forEach(col => {
                        col.style.display = ''; // Show HPP columns
                    });

                    // Update colspan for empty data row (keep all columns, so use 12 instead of 8)
                    const emptyRow = document.querySelector('td[colspan="12"]');
                    if (emptyRow) {
                        emptyRow.setAttribute('colspan', '12'); // Adjust to match total columns
                    }
                }

                // Save mode preference
                localStorage.setItem('stockViewMode', mode);

                // Trigger custom event for other scripts
                window.dispatchEvent(new CustomEvent('stockModeChanged', {
                    detail: {
                        mode: mode
                    }
                }));
            }

            // Event listeners
            accountingMode.addEventListener('change', function() {
                if (this.checked) {
                    updateMode('accounting');
                }
            });

            managementMode.addEventListener('change', function() {
                if (this.checked) {
                    updateMode('management');
                }
            });

            // Load saved mode preference
            const savedMode = localStorage.getItem('stockViewMode') || 'accounting';
            if (savedMode === 'management') {
                managementMode.checked = true;
                updateMode('management');
            } else {
                accountingMode.checked = true;
                updateMode('accounting');
            }

            // Add smooth transition effect
            const table = document.querySelector('.table');
            if (table) {
                table.style.transition = 'all 0.3s ease';
            }

            function loadAppliedCostHistory() {
                // Simulate API call - replace with actual endpoint
                fetch('/api/applied-cost/history')
                    .then(response => response.json())
                    .then(data => {
                        appliedCostHistory = data.data || [];
                        populateHistoryOptions();
                        renderHistoryTable();
                        updateHistoryCount();
                    })
                    .catch(error => {
                        console.error('Error loading history:', error);
                        // Mock data for demonstration
                        loadMockData();
                    });
            }

            function loadMockData() {
                // Mock data for demonstration
                appliedCostHistory = [{
                        id: 1,
                        created_at: '2024-01-15 10:30:00',
                        total_nominal: 150000,
                        details: [{
                                description: 'Biaya Listrik',
                                nominal: 75000
                            },
                            {
                                description: 'Biaya Air',
                                nominal: 25000
                            },
                            {
                                description: 'Biaya Transportasi',
                                nominal: 50000
                            }
                        ],
                        status: 'active'
                    },
                    {
                        id: 2,
                        created_at: '2024-01-14 15:20:00',
                        total_nominal: 200000,
                        details: [{
                                description: 'Biaya Tenaga Kerja',
                                nominal: 120000
                            },
                            {
                                description: 'Biaya Overhead',
                                nominal: 80000
                            }
                        ],
                        status: 'inactive'
                    },
                    {
                        id: 3,
                        created_at: '2024-01-13 09:15:00',
                        total_nominal: 300000,
                        details: [{
                                description: 'Biaya Bahan Bakar',
                                nominal: 150000
                            },
                            {
                                description: 'Biaya Pemeliharaan',
                                nominal: 100000
                            },
                            {
                                description: 'Biaya Operasional',
                                nominal: 50000
                            }
                        ],
                        status: 'inactive'
                    }
                ];

                populateHistoryOptions();
                renderHistoryTable();
                updateHistoryCount();
            }

            function populateHistoryOptions() {
                const select = document.getElementById('selectedAppliedCostId');
                select.innerHTML = '<option value="">Pilih Riwayat...</option>';

                appliedCostHistory.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent =
                        `#${item.id} - ${formatCurrency(item.total_nominal)} (${formatDate(item.created_at)})`;
                    select.appendChild(option);
                });
            }

            function renderHistoryTable() {
                const tbody = document.getElementById('historyTableBody');

                if (appliedCostHistory.length === 0) {
                    tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    Tidak ada riwayat perhitungan beban
                </td>
            </tr>
        `;
                    return;
                }

                tbody.innerHTML = appliedCostHistory.map(item => `
        <tr>
            <td>
                <input type="radio" name="appliedCostSelection" value="${item.id}" 
                       ${currentSelectedCostId == item.id ? 'checked' : ''}>
            </td>
            <td><strong>#${item.id}</strong></td>
            <td>${formatDate(item.created_at)}</td>
            <td><strong class="text-success">${formatCurrency(item.total_nominal)}</strong></td>
            <td>
                <div class="applied-cost-detail">
                    ${item.details.map(detail => `
                                        <div class="applied-cost-item">
                                            <span>${detail.description}</span>
                                            <span class="text-success">${formatCurrency(detail.nominal)}</span>
                                        </div>
                                    `).join('')}
                </div>
            </td>
            <td>
                <span class="badge status-badge ${item.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                    ${item.status === 'active' ? 'Aktif' : 'Tidak Aktif'}
                </span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary" 
                        onclick="viewAppliedCostDetail(${item.id})" title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');

                // Add event listeners for radio buttons
                document.querySelectorAll('input[name="appliedCostSelection"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value) {
                            document.getElementById('selectedAppliedCostId').value = this.value;
                        }
                    });
                });
            }

            function filterHistory() {
                // This would normally filter the data
                // For now, just re-render the existing data
                renderHistoryTable();
            }

            function applySelectedAppliedCost() {
                const selectedId = document.getElementById('selectedAppliedCostId').value;
                if (!selectedId) {
                    alert('Silakan pilih riwayat perhitungan beban terlebih dahulu.');
                    return;
                }

                const selectedCost = appliedCostHistory.find(item => item.id == selectedId);
                if (!selectedCost) {
                    alert('Riwayat tidak ditemukan.');
                    return;
                }

                currentSelectedCostId = selectedId;

                // Update all HPP calculations in the table
                updateHppWithAppliedCost(selectedCost.total_nominal);

                // Update UI to show applied cost
                showAppliedCostNotification(selectedCost);

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('appliedCostHistoryModal'));
                modal.hide();

                // Update status in history
                appliedCostHistory.forEach(item => {
                    item.status = item.id == selectedId ? 'active' : 'inactive';
                });

                renderHistoryTable();
            }

            function clearSelectedAppliedCost() {
                currentSelectedCostId = null;
                document.getElementById('selectedAppliedCostId').value = '';
                document.querySelectorAll('input[name="appliedCostSelection"]').forEach(radio => {
                    radio.checked = false;
                });

                // Reset HPP calculations
                resetHppCalculations();

                // Hide notification
                hideAppliedCostNotification();

                // Update status in history
                appliedCostHistory.forEach(item => {
                    item.status = 'inactive';
                });

                renderHistoryTable();
            }

            function updateHppWithAppliedCost(appliedCostTotal) {
                const hppCells = document.querySelectorAll('.hpp-column');
                const totalStockItems = document.querySelectorAll('tbody tr').length;

                if (totalStockItems === 0) return;

                const appliedCostPerItem = appliedCostTotal / totalStockItems;

                hppCells.forEach(cell => {
                    if (cell.textContent.includes('Rp.')) {
                        const originalValue = parseFloat(cell.textContent.replace(/[Rp.,\s]/g, ''));
                        if (!isNaN(originalValue)) {
                            const newValue = originalValue + appliedCostPerItem;
                            cell.innerHTML =
                                `Rp. ${formatNumber(newValue)} <small class="text-success">+${formatCurrency(appliedCostPerItem)}</small>`;
                        }
                    }
                });
            }

            function resetHppCalculations() {
                // This would reset HPP calculations to original values
                // For now, reload the page or reset the table data
                const hppCells = document.querySelectorAll('.hpp-column');
                hppCells.forEach(cell => {
                    if (cell.innerHTML.includes('<small')) {
                        // Remove the applied cost addition
                        const text = cell.textContent.split('+')[0].trim();
                        cell.textContent = text;
                    }
                });
            }

            function showAppliedCostNotification(selectedCost) {
                // Create or update notification
                let notification = document.getElementById('appliedCostNotification');
                if (!notification) {
                    notification = document.createElement('div');
                    notification.id = 'appliedCostNotification';
                    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; max-width: 400px;';
                    document.body.appendChild(notification);
                }

                notification.innerHTML = `
        <strong><i class="fas fa-check-circle me-2"></i>Perhitungan Beban Diterapkan</strong>
        <br>
        <small>Total: ${formatCurrency(selectedCost.total_nominal)} telah ditambahkan ke perhitungan HPP</small>
        <button type="button" class="btn-close" onclick="hideAppliedCostNotification()"></button>
    `;

                // Auto hide after 5 seconds
                setTimeout(hideAppliedCostNotification, 5000);
            }

            function hideAppliedCostNotification() {
                const notification = document.getElementById('appliedCostNotification');
                if (notification) {
                    notification.remove();
                }
            }

            function viewAppliedCostDetail(id) {
                const item = appliedCostHistory.find(cost => cost.id === id);
                if (!item) return;

                alert(`Detail Perhitungan Beban #${id}\n\n` +
                    item.details.map(d => `${d.description}: ${formatCurrency(d.nominal)}`).join('\n') +
                    `\n\nTotal: ${formatCurrency(item.total_nominal)}`);
            }

            function updateHistoryCount() {
                document.getElementById('historyCount').textContent = `Total: ${appliedCostHistory.length} riwayat`;
            }

            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function formatCurrency(amount) {
                return 'Rp. ' + formatNumber(amount);
            }

            function formatNumber(number) {
                return new Intl.NumberFormat('id-ID').format(number);
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
