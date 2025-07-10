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
                <form id="filterForm" method="GET" action="{{ route('stock_page') }}" class="d-flex flex-wrap align-items-end gap-3">
                    <div class="flex-shrink-0">
                        <label for="start_date" class="form-label small">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->startOfYear()->toDateString()) }}" min="{{ now()->subYears(5)->startOfYear()->toDateString() }}" max="{{ now()->toDateString() }}">
                    </div>
                    <div class="flex-shrink-0">
                        <label for="end_date" class="form-label small">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->toDateString()) }}" min="{{ request('start_date', now()->startOfYear()->toDateString()) }}" max="{{ now()->toDateString() }}">
                    </div>
                    <div class="flex-shrink-0">
                        <input type="hidden" name="table_filter" id="table_filter" value="{{ request('table_filter', 'all') }}">
                        <div class="dropdown me-2">
                            <button class="btn btn-light dropdown-toggle" type="button" id="tableFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ request('table_filter', 'all') == 'all' ? 'Semua Table' : (request('table_filter') == 'stocks' ? 'Table Stocks' : (request('table_filter') == 'transfer_stocks' ? 'Table Transfer Stocks' : 'Table Used Stocks')) }}
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="tableFilterDropdown">
                                <li><a class="dropdown-item" href="#" data-filter="all">Semua Table</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="stocks">Table Stok</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="transfer_stocks">Table Stok Pemindahan</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="used_stocks">Table Stok Barang Jadi</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}" class="btn btn-success">Export to Excel</a>
                        <a href="{{ route('stock.transfer.print') . '?table_filter=' . request('table_filter', 'all') }}" class="btn btn-secondary me-2">Print Form</a>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#RecipeList">Daftar Formula</button>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createRecipeModal">Buat Formula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="table-container">
        <!-- Stocks Table -->
        <div class="table-section" data-table="stocks">
            <h3>Stok</h3>
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
                        @if (is_array($stockData) && !empty($stockData))
                        @foreach ($stockData as $item => $sizes)
                        @foreach ($sizes as $index => $stock)
                        <tr>
                            @if ($index === 0)
                            <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                            <td rowspan="{{ count($sizes) }}">{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                            @endif
                            <td>{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}</td>
                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>
                                @if ($stock->id)
                                <button class="btn btn-sm btn-primary detail-btn" data-bs-toggle="modal" data-bs-target="#detailModal_stocks_{{ $stock->id }}" data-stock-id="{{ $stock->id }}" data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}" data-table-name="stocks">Detail</button>
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
        <div class="table-section" data-table="transfer_stocks">
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
                        <tr>
                            @if ($index === 0)
                            <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                            <td rowspan="{{ count($sizes) }}">{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                            @endif
                            <td>{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}</td>
                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>
                                @if ($stock->id)
                                <button class="btn btn-sm btn-primary detail-btn" data-bs-toggle="modal" data-bs-target="#detailModal_transfer_stocks_{{ $stock->id }}" data-stock-id="{{ $stock->id }}" data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}" data-table-name="transfer_stocks">Detail</button>
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
        <div class="table-section" data-table="used_stocks">
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
                        <tr>
                            @if ($index === 0)
                            <td rowspan="{{ count($sizes) }}">{{ $itemCount++ }}</td>
                            <td rowspan="{{ count($sizes) }}">{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}</td>
                            @endif
                            <td>{{ htmlspecialchars($stock->size ?? 'Unknown Size') }}</td>
                            <td>{{ $stock->opening_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->opening_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->incoming_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->incoming_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->outgoing_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                            <td>{{ number_format($stock->final_hpp ?? 0, 2, ',', '.') }}</td>
                            <td>
                                @if ($stock->id)
                                <button class="btn btn-sm btn-primary detail-btn" data-bs-toggle="modal" data-bs-target="#detailModal_used_stocks_{{ $stock->id }}" data-stock-id="{{ $stock->id }}" data-item="{{ htmlspecialchars($stock->item ?? 'Unknown Item') }}" data-table-name="used_stocks">Detail</button>
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
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover text-center">
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
                                @php $recipeCount = 1; @endphp
                                @if (isset($recipes) && !empty($recipes))
                                @foreach ($recipes as $recipe)
                                <tr>
                                    <td>{{ $recipeCount++ }}</td>
                                    <td>{{ htmlspecialchars($recipe->product_name ?? 'Unknown Product') }}</td>
                                    <td>{{ htmlspecialchars($recipe->size ?? 'Unknown Size') }}</td>
                                    <td>{{ number_format($recipe->nominal ?? 0, 2, ',', '.') }}</td>
                                    <td>
                                        @if (isset($recipe->transferStocks) && !empty($recipe->transferStocks))
                                        <table class="table table-sm table-bordered mt-2">
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
                                                    <td>{{ htmlspecialchars($transferStock->item ?? 'Unknown Item') }}</td>
                                                    <td>{{ htmlspecialchars($transferStock->size ?? 'Unknown Size') }}</td>
                                                    <td>{{ $transferStock->quantity ?? 0 }}</td>
                                                    <td>{{ number_format($transferStock->nominal ?? 0, 2, ',', '.') }}</td>
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
                                <tr>
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
    'used_stocks' => collect($usedStockData)->flatten()->toArray() ?? []
    ];
    @endphp
    @foreach ($allStocks as $tableName => $stocks)
    @foreach ($stocks as $stock)
    @if ($stock->id)
    <div class="modal fade" id="detailModal_{{ $tableName }}_{{ $stock->id }}" tabindex="-1" aria-labelledby="detailModalLabel_{{ $tableName }}_{{ $stock->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" data-table-name="{{ $tableName }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel_{{ $tableName }}_{{ $stock->id }}">Detail Transaksi untuk {{ htmlspecialchars($stock->item ?? 'Unknown Item') }} ({{ $tableName }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_filter_{{ $tableName }}_{{ $stock->id }}" class="form-label">Tampilkan Transaksi</label>
                        <select id="modal_filter_{{ $tableName }}_{{ $stock->id }}" class="form-select modal-filter" data-stock-id="{{ $stock->id }}">
                            <option value="all" {{ request('filter', 'all') == 'all' ? 'selected' : '' }}>Semua Transaksi</option>
                            <option value="7_days" {{ request('filter', 'all') == '7_days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                            <option value="1_month" {{ request('filter', 'all') == '1_month' ? 'selected' : '' }}>1 Bulan Terakhir</option>
                        </select>
                    </div>
                    <div class="transaction-table" id="transactionTable_{{ $tableName }}_{{ $stock->id }}">
                        @if (isset($stock->transactions) && !empty($stock->transactions))
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover text-center">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Deskripsi</th>
                                        <th>Tipe Transaksi</th>
                                        <th>Kuantitas</th>
                                        <th>Nominal</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stock->transactions as $transaction)
                                    @if (!str_starts_with($transaction->description ?? '', 'HPP '))
                                    <tr data-transaction-date="{{ $transaction->created_at }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ htmlspecialchars($transaction->description ?? 'No Description') }}</td>
                                        <td>
                                            @switch($transaction->voucher_type)
                                            @case('PJ') Penjualan @break
                                            @case('PB') Pembelian @break
                                            @case('PH') Pemindahan @break
                                            @case('PK') Pemakaian @break
                                            @default {{ htmlspecialchars($transaction->voucher_type ?? 'Unknown') }}
                                            @endswitch
                                        </td>
                                        <td>{{ $transaction->quantity ?? $transaction->transaction_quantity ?? 0 }}</td>
                                        <td>{{ number_format($transaction->nominal ?? 0, 2, ',', '.') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->created_at ?? now())->format('d-m-Y') }}</td>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
    @endforeach

    <!-- Create Recipe Modal -->
    <div class="modal fade" id="createRecipeModal" tabindex="-1" aria-labelledby="createRecipeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="recipeForm" action="{{ route('recipe.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createRecipeModalLabel">Buat Rumus Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Nama Produk</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required minlength="2" maxlength="100" pattern="[A-Za-z0-9\s]+" title="Nama produk hanya boleh berisi huruf, angka, dan spasi">
                            <div class="invalid-feedback">Masukkan nama produk yang valid (2-100 karakter, hanya huruf, angka, dan spasi)</div>
                        </div>
                        <div class="mb-3">
                            <label for="product_size" class="form-label">Ukuran Produk</label>
                            <input type="text" name="product_size" id="product_size" class="form-control" maxlength="50" pattern="[A-Za-z0-9\s\-\/]*" title="Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring">
                            <div class="invalid-feedback">Masukkan ukuran produk yang valid (maksimal 50 karakter)</div>
                        </div>
                        <div class="mb-3">
                            <label for="total_nominal" class="form-label">Total Nominal</label>
                            <input type="number" name="total_nominal" id="total_nominal" class="form-control" value="0.00" step="0.01" readonly>
                            <div class="invalid-feedback">Total nominal tidak valid</div>
                        </div>
                        <div id="ingredientsContainer">
                            <div class="ingredient-row mb-3" data-row-id="0">
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label for="transfer_stock_id_0" class="form-label">Bahan Baku</label>
                                        <select name="transfer_stock_id[]" id="transfer_stock_id_0" class="form-select transfer-stock-select" required>
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
                                        <label for="quantity_0" class="form-label">Kuantitas</label>
                                        <input type="number" name="quantity[]" id="quantity_0" class="form-control" min="1" max="999999" step="1" required>
                                        <div class="invalid-feedback">Masukkan kuantitas yang valid (minimal 1)</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="nominal_0" class="form-label">Nominal</label>
                                        <input type="number" name="nominal[]" id="nominal_0" class="form-control" min="0" step="0.01" readonly>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ingredient">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addIngredient" class="btn btn-secondary mt-2">Tambah Bahan Baku</button>
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

        // Table visibility function
        function updateTableVisibility(filter) {
            tableSections.forEach(section => {
                section.classList.toggle('hidden', filter !== 'all' && filter !== section.dataset.table);
            });
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

        // Modal filter logic for client-side filtering
        document.querySelectorAll('.modal-filter').forEach(select => {
            select.addEventListener('change', function() {
                const stockId = this.dataset.stockId;
                const tableName = this.closest('.modal-content').dataset.tableName;
                const filter = this.value;
                const transactionTable = document.getElementById(`transactionTable_${tableName}_${stockId}`);
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
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
                if (visibleRows.length === 0) {
                    transactionTable.innerHTML = '<p class="text-center">Tidak ada transaksi terkait untuk periode ini.</p>';
                } else if (transactionTable.querySelector('p.text-center')) {
                    // Re-render the table if it was replaced with a message
                    location.reload(); // Simplest way to restore the table; alternatively, store the original HTML
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
                    feedback.textContent = 'Nominal tidak tersedia untuk bahan baku ini (tidak ada transaksi pemindahan atau pembelian)';
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
                    feedback.textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
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
                    feedback.textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
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
                productName.nextElementSibling.textContent = 'Nama produk hanya boleh berisi huruf, angka, dan spasi';
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
                productSize.nextElementSibling.textContent = 'Ukuran produk hanya boleh berisi huruf, angka, spasi, tanda hubung, atau garis miring';
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
                totalNominalInput.nextElementSibling.textContent = 'Total nominal harus lebih besar dari 0';
                isValid = false;
            } else {
                totalNominalInput.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                document.getElementById('errorMessage').textContent = 'Harap perbaiki kesalahan pada formulir.';
                document.getElementById('errorMessage').style.display = 'block';
            }
        });
    });
</script>
@endsection