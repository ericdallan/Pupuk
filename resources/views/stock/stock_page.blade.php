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
                        <div class="flex-shrink-0">
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
                                class="btn btn-secondary me-2">Print Form</a>
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#RecipeList">Daftar Formula</button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                data-bs-target="#createRecipeModal">Buat Formula</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Global Search Filter -->
        <div class="mb-4">
            <div class="input-group">
                <input type="text" id="globalSearch" class="form-control" placeholder="Cari nama barang atau ukuran...">
                <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
            </div>
            <small id="searchCount" class="form-text text-muted mt-2"></small>
        </div>

        <!-- Tables Section -->
        <div class="table-container">
            <!-- Stocks Table -->
            <div class="table-section mb-4" data-table="stocks">
                <h3>Stok Bahan Baku</h3>
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
                                    <td colspan="14" class="text-center">
                                        <div class="alert alert-info mb-0">Data stok belum ditemukan.</div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transfer Stocks Table -->
            <div class="table-section mb-4" data-table="transfer_stocks">
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
            <div class="table-section mb-4" data-table="used_stocks">
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
        <div class="modal fade" id="RecipeList" tabindex="-1" aria-labelledby="RecipeListLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="RecipeListLabel">Daftar Formula</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Search Input for Recipe Modal -->
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recipeCount = 1;
                                    @endphp

                                    @if (!empty($recipes))
                                        @foreach ($recipes as $recipe)
                                            <tr class="recipe-row"
                                                data-product-name="{{ strtolower(htmlspecialchars($recipe->product_name ?? 'Unknown Product')) }}"
                                                data-product-size="{{ strtolower(htmlspecialchars($recipe->size ?? 'Unknown Size')) }}"
                                                data-ingredients="{{ strtolower(implode(' ',collect($recipe->transferStocks ?? [])->pluck('item')->toArray())) }}"
                                                data-ingredient-sizes="{{ strtolower(implode(' ',collect($recipe->transferStocks ?? [])->pluck('size')->toArray())) }}">
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
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr id="noRecipeData">
                                            <td colspan="5" class="text-center">
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
                                        untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }} ({{ $tableName }})
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

        <!-- Create Recipe Modal -->
        <div class="modal fade" id="createRecipeModal" tabindex="-1" aria-labelledby="createRecipeModalLabel"
            aria-hidden="true">
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
                                                            -
                                                            {{ $stock->quantity }} tersedia</option>
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
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                recipeSearchCount.textContent = `${visibleCount} formula ditemukan`;

                // Show no results message if needed
                if (visibleCount === 0) {
                    showNoResultsMessage();
                } else {
                    removeNoResultsMessage();
                }
            }

            // Function to highlight text
            function highlightText(element, searchValue, shouldHighlight) {
                if (!element || !shouldHighlight || !searchValue) return;

                const text = element.textContent;
                const regex = new RegExp(`(${escapeRegExp(searchValue)})`, 'gi');
                const highlightedText = text.replace(regex, '<span class="recipe-highlight">$1</span>');

                if (highlightedText !== text) {
                    element.innerHTML = highlightedText;
                }
            }

            // Function to escape special regex characters
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Function to show no results message
            function showNoResultsMessage() {
                removeNoResultsMessage(); // Remove existing message first

                const tbody = recipeTable.querySelector('tbody');
                const noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noSearchResults';
                noResultsRow.innerHTML = `
            <td colspan="5" class="text-center">
                <div class="no-results-message">
                    <i class="fas fa-search mb-2"></i>
                    <p class="mb-0">Tidak ada formula yang cocok dengan pencarian "${recipeGlobalSearch.value}"</p>
                    <small class="text-muted">Coba kata kunci yang berbeda atau hapus pencarian untuk melihat semua formula</small>
                </div>
            </td>
        `;
                tbody.appendChild(noResultsRow);
            }

            // Function to remove no results message
            function removeNoResultsMessage() {
                const existingMessage = document.getElementById('noSearchResults');
                if (existingMessage) {
                    existingMessage.remove();
                }
            }

            // Event listeners for recipe search
            recipeGlobalSearch.addEventListener('input', applyRecipeGlobalFilter);
            recipeGlobalSearch.addEventListener('keyup', applyRecipeGlobalFilter);

            clearRecipeSearch.addEventListener('click', function() {
                recipeGlobalSearch.value = '';
                applyRecipeGlobalFilter();
                recipeGlobalSearch.focus();
            });

            // Reset search when modal is closed
            document.getElementById('RecipeList').addEventListener('hidden.bs.modal', function() {
                recipeGlobalSearch.value = '';
                applyRecipeGlobalFilter();
            });

            // Optional: Focus search input when modal opens
            document.getElementById('RecipeList').addEventListener('shown.bs.modal', function() {
                recipeGlobalSearch.focus();
            });
            // Table visibility function
            function updateTableVisibility(filter) {
                tableSections.forEach(section => {
                    section.classList.toggle('hidden', filter !== 'all' && filter !== section.dataset
                        .table);
                });
                applyGlobalFilter(); // Re-apply filter after visibility change
            }

            // Initialize table visibility
            const initialFilter = tableFilterInput.value || 'all';
            updateTableVisibility(initialFilter);
            dropdownButton.textContent = {
                'all': 'Semua Table',
                'stocks': 'Table Stocks',
                'transfer_stocks': 'Table Transfer Stocks',
                'used_stocks': 'Table Used Stocks'
            } [initialFilter] || 'Semua Table';

            // Handle dropdown item clicks
            document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterValue = this.dataset.filter;
                    tableFilterInput.value = filterValue;
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
                        // Re-render the table if it was replaced with a message
                        location
                            .reload(); // Simplest way to restore the table; alternatively, store the original HTML
                    }
                });
            });

            // Recipe form validation and dynamic ingredient rows
            let ingredientCount = 0;

            function updateNominal(row, select, quantityInput) {
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
                        feedback.textContent =
                            'Nominal tidak tersedia untuk bahan baku ini (tidak ada transaksi pemindahan atau pembelian)';
                        feedback.style.display = 'block';
                    } else if (!quantity) {
                        feedback.textContent = 'Masukkan kuantitas yang valid';
                        feedback.style.display = 'block';
                    } else {
                        feedback.style.display = 'none';
                    }
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

            document.querySelectorAll('.ingredient-row').forEach(row => {
                const quantityInput = row.querySelector('input[name="quantity[]"]');
                const select = row.querySelector('.transfer-stock-select');
                if (quantityInput && select) {
                    validateQuantityInput(row, quantityInput, select);
                }
            });

            document.getElementById('addIngredient').addEventListener('click', function() {
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
                            <option value="{{ $stock->id }}" data-max-quantity="{{ $stock->quantity }}" data-nominal="{{ $stock->nominal ?? 0 }}">{{ htmlspecialchars($stock->item) }} ({{ htmlspecialchars($stock->size) }}) - {{ $stock->quantity }} tersedia</option>
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
                        <input type="number" name="nominal[]" id="nominal_${ingredientCount}" class="form-control" min="0" step="0.01" readonly>
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
                validateQuantityInput(newRow, quantityInput, select);

                document.querySelectorAll('.remove-ingredient').forEach(button => {
                    button.style.display = ingredientCount > 0 ? 'block' : 'none';
                });

                updateTotalNominal();
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-ingredient')) {
                    const row = e.target.closest('.ingredient-row');
                    if (document.querySelectorAll('.ingredient-row').length > 1) {
                        row.remove();
                        ingredientCount--;
                        if (ingredientCount === 0) {
                            document.querySelectorAll('.remove-ingredient').forEach(button => {
                                button.style.display = 'none';
                            });
                        }
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
        });
    </script>
@endsection
