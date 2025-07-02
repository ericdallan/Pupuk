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
                                <li><a class="dropdown-item" href="#" data-filter="stocks">Table Stocks</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="transfer_stocks">Table Transfer Stocks</a></li>
                                <li><a class="dropdown-item" href="#" data-filter="used_stocks">Table Used Stocks</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-me-2">Filter</button>
                        <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}" class="btn btn-success me-2">Export to Excel</a>
                        <a href="{{ route('stock.transfer.print') . '?table_filter=' . request('table_filter', 'all') }}" class="btn btn-secondary me-2">Print Form</a>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createRecipeModal">Rumus Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="table-container">
        <!-- Stocks Table -->
        <div class="table-section" data-table="stocks">
            <h3>Stocks</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Ukuran</th>
                            <th colspan="2">Stok Tersedia</th>
                            <th colspan="2">Saldo Awal</th>
                            <th colspan="2">Masuk Barang</th>
                            <th colspan="2">Keluar Barang</th>
                            <th colspan="2">Akhir</th>
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
                            <td rowspan="{{ count($sizes) }}">{{ $stock->item ?? 'Unknown Item' }}</td>
                            @endif
                            <td>{{ $stock->size ?? 'Unknown Size' }}</td>
                            <td>{{ $stock->quantity ?? 0 }}</td>
                            <td>{{ number_format($stock->average_hpp ?? 0, 2, ',', '.') }}</td>
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
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}" data-table="stocks">Detail</button>
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
            <h3>Transfer Stocks</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Ukuran</th>
                            <th colspan="2">Stok Tersedia</th>
                            <th colspan="2">Saldo Awal</th>
                            <th colspan="2">Masuk Barang</th>
                            <th colspan="2">Keluar Barang</th>
                            <th colspan="2">Akhir</th>
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
                            <td rowspan="{{ count($sizes) }}">{{ $stock->item ?? 'Unknown Item' }}</td>
                            @endif
                            <td>{{ $stock->size ?? 'Unknown Size' }}</td>
                            <td>{{ $stock->quantity ?? 0 }}</td>
                            <td>{{ number_format($stock->average_hpp ?? 0, 2, ',', '.') }}</td>
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
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}" data-table="transfer_stocks">Detail</button>
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
            <h3>Used Stocks</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Ukuran</th>
                            <th colspan="2">Stok Tersedia</th>
                            <th colspan="2">Saldo Awal</th>
                            <th colspan="2">Masuk Barang</th>
                            <th colspan="2">Keluar Barang</th>
                            <th colspan="2">Akhir</th>
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
                            <td rowspan="{{ count($sizes) }}">{{ $stock->item ?? 'Unknown Item' }}</td>
                            @endif
                            <td>{{ $stock->size ?? 'Unknown Size' }}</td>
                            <td>{{ $stock->quantity ?? 0 }}</td>
                            <td>{{ number_format($stock->average_hpp ?? 0, 2, ',', '.') }}</td>
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
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}" data-table="used_stocks">Detail</button>
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

    <!-- Transaction Detail Modals -->
    @php
    $allStocks = array_merge(
    collect($stockData)->flatten()->map(function ($stock) { return array_merge((array)$stock, ['table_name' => 'stocks']); })->toArray() ?? [],
    collect($transferStockData)->flatten()->map(function ($stock) { return array_merge((array)$stock, ['table_name' => 'transfer_stocks']); })->toArray() ?? [],
    collect($usedStockData)->flatten()->map(function ($stock) { return array_merge((array)$stock, ['table_name' => 'used_stocks']); })->toArray() ?? []
    );
    @endphp
    @if (!empty($allStocks))
    @foreach ($allStocks as $stock)
    <div class="modal fade" id="detailModal{{ $stock['id'] ?? '' }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $stock['id'] ?? '' }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel{{ $stock['id'] ?? '' }}">Detail Transaksi untuk {{ $stock['item'] ?? 'Unknown Item' }} ({{ $stock['size'] ?? 'Unknown Size' }} - {{ $stock['table_name'] ?? 'Unknown Table' }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_filter_{{ $stock['id'] ?? '' }}" class="form-label">Tampilkan Transaksi</label>
                        <select id="modal_filter_{{ $stock['id'] ?? '' }}" class="form-select modal-filter" data-stock-id="{{ $stock['id'] ?? '' }}" data-table-name="{{ $stock['table_name'] ?? '' }}">
                            <option value="7_days">7 Hari Terakhir</option>
                            <option value="1_month">1 Bulan Terakhir</option>
                        </select>
                    </div>
                    <div class="transaction-table" id="transactionTable_{{ $stock['id'] ?? '' }}">
                        <p class="text-center text-muted">Pilih filter untuk melihat transaksi...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif
    <!-- Create Recipe Modal -->
    <div class="modal fade" id="createRecipeModal" tabindex="-1" aria-labelledby="createRecipeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
                            <input type="text" name="product_size" id="product_size" class="form-control" required maxlength="255">
                            <div class="invalid-feedback">Masukkan ukuran produk yang valid (maksimal 255 karakter)</div>
                        </div>
                        <div id="ingredientsContainer">
                            <div class="ingredient-row mb-3" data-row-id="0">
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <label for="transfer_stock_id_0" class="form-label">Bahan Baku</label>
                                        <select name="transfer_stock_id[]" id="transfer_stock_id_0" class="form-select transfer-stock-select" required>
                                            <option value="">Pilih Bahan Baku</option>
                                            @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                                            @foreach (collect($transferStockData)->flatten() as $stock)
                                            <option value="{{ $stock->id }}" data-max-quantity="{{ $stock->quantity }}">{{ $stock->item }} ({{ $stock->size }}) - {{ $stock->quantity }} tersedia</option>
                                            @endforeach
                                            @else
                                            <option value="" disabled>Tidak ada bahan baku tersedia</option>
                                            @endif
                                        </select>
                                        <div class="invalid-feedback">Pilih bahan baku</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="quantity_0" class="form-label">Kuantitas</label>
                                        <input type="number" name="quantity[]" id="quantity_0" class="form-control" min="1" max="999999" step="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                        <div class="invalid-feedback">Masukkan kuantitas yang valid (angka bulat, minimal 1)</div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ingredient" style="display: none;">Hapus</button>
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
        // Debug DOM elements
        const form = document.getElementById('filterForm');
        const tableFilterInput = document.getElementById('table_filter');
        const dropdownButton = document.getElementById('tableFilterDropdown');
        // console.log('Form exists:', !!form);
        // console.log('Table filter input exists:', !!tableFilterInput);
        // console.log('Dropdown button exists:', !!dropdownButton);
        // console.log('Dropdown items found:', document.querySelectorAll('.dropdown-item[data-filter]').length);
        // console.log('Table sections found:', document.querySelectorAll('.table-section').length);

        // Table Filter Logic (on click, no form submission)
        document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.dataset.filter;
                // console.log('Selected filter:', filter);

                // Update hidden input
                if (tableFilterInput) {
                    tableFilterInput.value = filter;
                    // console.log('Updated table_filter input to:', tableFilterInput.value);
                } else {
                    // console.error('table_filter input not found');
                    return;
                }

                // Update dropdown button text
                if (dropdownButton) {
                    dropdownButton.textContent = filter === 'all' ? 'Semua Table' :
                        (filter === 'stocks' ? 'Table Stocks' :
                            (filter === 'transfer_stocks' ? 'Table Transfer Stocks' : 'Table Used Stocks'));
                }

                // Toggle table visibility client-side
                document.querySelectorAll('.table-section').forEach(section => {
                    // console.log('Table:', section.dataset.table, 'Display:', filter === 'all' || section.dataset.table === filter ? 'block' : 'none');
                    section.classList.toggle('hidden', !(filter === 'all' || section.dataset.table === filter));
                });

                // Optional: Update URL parameters without reload (using History API)
                const url = new URL(window.location);
                url.searchParams.set('table_filter', filter);
                window.history.pushState({}, '', url);
            });
        });

        // Submit form only when Filter button is clicked
        const filterButton = form?.querySelector('button[type="submit"]');
        if (filterButton) {
            filterButton.addEventListener('click', function(e) {
                // console.log('Submitting form with table_filter:', tableFilterInput.value);
                form.submit();
            });
        }

        // Modal Filter Logic
        document.querySelectorAll('.modal-filter').forEach(select => {
            select.addEventListener('change', function() {
                const stockId = this.dataset.stockId;
                const filter = this.value;
                const tableName = this.dataset.tableName;
                const transactionTable = document.getElementById(`transactionTable_${stockId}`);

                console.log('Fetching transactions for stock:', stockId, 'Filter:', filter, 'Table:', tableName);

                if (!stockId || !tableName) {
                    console.error('Missing stockId or tableName:', {
                        stockId,
                        tableName
                    });
                    transactionTable.innerHTML = '<p class="text-center text-danger">Gagal memuat data: Informasi stok tidak lengkap.</p>';
                    return;
                }

                // Show loading state
                transactionTable.innerHTML = '<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat transaksi...</p>';

                fetch(`/stock/transactions/${stockId}?filter=${filter}&table=${tableName}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        let html = '';
                        const filteredTransactions = data.transactions.filter(transaction => !transaction.description?.startsWith('HPP '));

                        if (filteredTransactions.length > 0) {
                            html = `
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
                    `;
                            filteredTransactions.forEach((transaction, index) => {
                                html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${transaction.description || 'Tidak ada deskripsi'}</td>
                                <td>${(() => {
                                    switch (transaction.voucher_type) {
                                        case 'PJ': return 'Penjualan';
                                        case 'PB': return 'Pembelian';
                                        case 'PH': return 'Pemindahan';
                                        case 'PK': return 'Pemakaian';
                                        default: return transaction.voucher_type || 'Tidak diketahui';
                                    }
                                })()}</td>
                                <td>${transaction.quantity || 0}</td>
                                <td>${window.number_format(transaction.nominal || 0, 2, ',', '.')}</td>
                                <td>${transaction.created_at ? new Date(transaction.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '-'}</td>
                            </tr>
                        `;
                            });
                            html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                        } else {
                            html = '<p class="text-center text-muted">Tidak ada transaksi untuk periode ini.</p>';
                        }
                        transactionTable.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching transactions:', error);
                        transactionTable.innerHTML = '<p class="text-center text-danger">Terjadi kesalahan saat memuat transaksi. Silakan coba lagi.</p>';
                    });
            });
        });

        // Dynamic Ingredient Rows with Quantity Validation
        let ingredientCount = 0;

        // Initialize validation for existing ingredient rows
        document.querySelectorAll('.ingredient-row').forEach(row => {
            const quantityInput = row.querySelector('input[name="quantity[]"]');
            const select = row.querySelector('.transfer-stock-select');

            if (quantityInput && select) {
                quantityInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, ''); // Hanya angka bulat
                    const maxQuantity = select.options[select.selectedIndex]?.dataset.maxQuantity || 999999;
                    if (parseInt(this.value) > parseInt(maxQuantity)) {
                        this.value = maxQuantity;
                        row.querySelector('.invalid-feedback').textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                        this.classList.add('is-invalid');
                    } else if (parseInt(this.value) < 1) {
                        this.value = 1;
                        row.querySelector('.invalid-feedback').textContent = 'Kuantitas minimal adalah 1';
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                });

                select.addEventListener('change', function() {
                    const maxQuantity = this.options[this.selectedIndex]?.dataset.maxQuantity || 999999;
                    if (quantityInput.value && parseInt(quantityInput.value) > parseInt(maxQuantity)) {
                        quantityInput.value = maxQuantity;
                        quantityInput.classList.add('is-invalid');
                        row.querySelector('.invalid-feedback').textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                    } else {
                        quantityInput.classList.remove('is-invalid');
                    }
                });
            }
        });

        // Add new ingredient row
        document.getElementById('addIngredient').addEventListener('click', function() {
            // console.log('Adding ingredient row:', ingredientCount + 1);
            ingredientCount++;
            const container = document.getElementById('ingredientsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'ingredient-row mb-3';
            newRow.dataset.rowId = ingredientCount;
            newRow.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label for="transfer_stock_id_${ingredientCount}" class="form-label">Bahan Baku</label>
                    <select name="transfer_stock_id[]" id="transfer_stock_id_${ingredientCount}" class="form-select transfer-stock-select" required>
                        <option value="">Pilih Bahan Baku</option>
                        @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                            @foreach (collect($transferStockData)->flatten() as $stock)
                                <option value="{{ $stock->id }}" data-max-quantity="{{ $stock->quantity }}">{{ $stock->item }} ({{ $stock->size }}) - {{ $stock->quantity }} tersedia</option>
                            @endforeach
                        @else
                            <option value="" disabled>Tidak ada bahan baku tersedia</option>
                        @endif
                    </select>
                    <div class="invalid-feedback">Pilih bahan baku</div>
                </div>
                <div class="col-md-5">
                    <label for="quantity_${ingredientCount}" class="form-label">Kuantitas</label>
                    <input type="number" name="quantity[]" id="quantity_${ingredientCount}" class="form-control" min="1" max="999999" step="1" required>
                    <div class="invalid-feedback">Masukkan kuantitas yang valid (minimal 1)</div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-ingredient">Hapus</button>
                </div>
            </div>
        `;
            container.appendChild(newRow);

            // Add validation for new row
            const quantityInput = newRow.querySelector('input[name="quantity[]"]');
            const select = newRow.querySelector('.transfer-stock-select');

            quantityInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                const maxQuantity = select.options[select.selectedIndex]?.dataset.maxQuantity || 999999;
                if (parseInt(this.value) > parseInt(maxQuantity)) {
                    this.value = maxQuantity;
                    newRow.querySelector('.invalid-feedback').textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                    this.classList.add('is-invalid');
                } else if (parseInt(this.value) < 1) {
                    this.value = 1;
                    newRow.querySelector('.invalid-feedback').textContent = 'Kuantitas minimal adalah 1';
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            select.addEventListener('change', function() {
                const maxQuantity = this.options[this.selectedIndex]?.dataset.maxQuantity || 999999;
                if (quantityInput.value && parseInt(quantityInput.value) > parseInt(maxQuantity)) {
                    quantityInput.value = maxQuantity;
                    quantityInput.classList.add('is-invalid');
                    newRow.querySelector('.invalid-feedback').textContent = `Kuantitas tidak boleh melebihi stok tersedia (${maxQuantity})`;
                } else {
                    quantityInput.classList.remove('is-invalid');
                }
            });

            // Update visibility of remove buttons
            document.querySelectorAll('.remove-ingredient').forEach(button => {
                button.style.display = ingredientCount > 0 ? 'block' : 'none';
            });
        });

        // Remove Ingredient Row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-ingredient')) {
                // console.log('Removing ingredient row');
                const row = e.target.closest('.ingredient-row');
                if (document.querySelectorAll('.ingredient-row').length > 1) {
                    row.remove();
                    ingredientCount--;
                    if (ingredientCount === 0) {
                        document.querySelectorAll('.remove-ingredient').forEach(button => {
                            button.style.display = 'none';
                        });
                    }
                }
            }
        });

        // Fallback number_format
        if (typeof number_format === 'undefined') {
            window.number_format = function(number, decimals, dec_point = ',', thousands_sep = '.') {
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 2 : Math.abs(decimals),
                    sep = thousands_sep,
                    dec = dec_point,
                    s = '',
                    toFixedFix = function(n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(dec);
            };
        }
    });
</script>
@endsection