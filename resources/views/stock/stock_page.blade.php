@extends('layouts/app')

@section('title', 'Stock Barang Dagangan')

@section('content')
<!-- Main Filter Form -->
<div class="mb-4">
    <form method="GET" action="{{ route('stock_page') }}" class="row g-3">
        <!-- Row 1: Date Filters -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->startOfYear()->toDateString()) }}" min="{{ now()->subYears(5)->startOfYear()->toDateString() }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->toDateString()) }}" min="{{ request('start_date', now()->startOfYear()->toDateString()) }}" max="{{ now()->toDateString() }}">
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center">
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
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                </div>
            </div>
        </div>
        <!-- Row 2: Action Buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex align-items-center justify-content-start">
                    <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) . '&table_filter=' . request('table_filter', 'all') }}" class="btn btn-success me-2">Export to Excel</a>
                    <a href="{{ route('stock.transfer.print') . '?table_filter=' . request('table_filter', 'all') }}" class="btn btn-secondary me-2">Print Form</a>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#createRecipeModal">Rumus Produk</button>
                </div>
            </div>
        </div>
    </form>
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
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->opening_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->opening_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->incoming_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->incoming_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->outgoing_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>
                            @if ($stock->id)
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
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
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->opening_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->opening_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->incoming_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->incoming_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->outgoing_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>
                            @if ($stock->id)
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
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
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->opening_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->opening_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->incoming_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->incoming_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->outgoing_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->outgoing_hpp ?? 0, 2) }}</td>
                        <td>{{ $stock->final_stock_qty ?? 0 }}</td>
                        <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                        <td>
                            @if ($stock->id)
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
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
collect($stockData)->flatten()->toArray() ?? [],
collect($transferStockData)->flatten()->toArray() ?? [],
collect($usedStockData)->flatten()->toArray() ?? []
);
@endphp
@if (!empty($allStocks))
@foreach ($allStocks as $stock)
<div class="modal fade" id="detailModal{{ $stock->id ?? '' }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $stock->id ?? '' }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $stock->id ?? '' }}">Detail Transaksi untuk {{ $stock->item ?? 'Unknown Item' }} ({{ $stock->table_name ?? 'Unknown Table' }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modal_filter_{{ $stock->id ?? '' }}" class="form-label">Tampilkan Transaksi</label>
                    <select id="modal_filter_{{ $stock->id ?? '' }}" class="form-select modal-filter" data-stock-id="{{ $stock->id ?? '' }}">
                        <option value="7_days">7 Hari Terakhir</option>
                        <option value="1_month">1 Bulan Terakhir</option>
                    </select>
                </div>
                <div class="transaction-table" id="transactionTable_{{ $stock->id ?? '' }}">
                    @if (isset($stock->transactions) && $stock->transactions->isNotEmpty())
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
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $transaction->description ?? 'No Description' }}</td>
                                    <td>
                                        @if ($transaction->voucher_type == 'PJ')
                                        Penjualan
                                        @elseif ($transaction->voucher_type == 'PB')
                                        Pembelian
                                        @elseif ($transaction->voucher_type == 'PH')
                                        Pemindahan
                                        @elseif ($transaction->voucher_type == 'PK')
                                        Pemakaian
                                        @else
                                        {{ $transaction->voucher_type ?? 'Unknown' }}
                                        @endif
                                    </td>
                                    <td>{{ $transaction->quantity ?? 0 }}</td>
                                    <td>{{ number_format($transaction->nominal ?? 0, 2) }}</td>
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
@endforeach
@else
<!-- Optional: Add a message if no modals are needed -->
@endif

<!-- Create Recipe Modal -->
<div class="modal fade" id="createRecipeModal" tabindex="-1" aria-labelledby="createRecipeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRecipeModalLabel">Buat Rumus Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="recipeForm" action="{{ route('recipe.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Nama Produk</label>
                        <input type="text" name="product_name" id="product_name" class="form-control" required>
                    </div>
                    <div id="ingredientsContainer">
                        <div class="ingredient-row mb-3">
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="transfer_stock_id_0" class="form-label">Bahan Baku</label>
                                    <select name="transfer_stock_id[]" id="transfer_stock_id_0" class="form-select transfer-stock-select" required>
                                        <option value="">Pilih Bahan Baku</option>
                                        @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                                        @foreach (collect($transferStockData)->flatten() as $stock)
                                        <option value="{{ $stock->id }}">{{ $stock->item }} ({{ $stock->size }}) - {{ $stock->quantity }} tersedia</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="quantity_0" class="form-label">Kuantitas</label>
                                    <input type="number" name="quantity[]" id="quantity_0" class="form-control" min="1" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-ingredient" style="display: none;">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addIngredient" class="btn btn-secondary mb-3">Tambah Bahan Baku</button>
                    <button type="submit" class="btn btn-primary">Simpan Resep</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Table Filter Logic
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                document.getElementById('table_filter').value = filter;

                document.querySelectorAll('.table-section').forEach(section => {
                    section.style.display = filter === 'all' ? 'block' : (section.getAttribute('data-table') === filter ? 'block' : 'none');
                });

                document.querySelector('form').submit();
            });
        });

        // Modal Filter Logic
        document.querySelectorAll('.modal-filter').forEach(select => {
            select.addEventListener('change', function() {
                const stockId = this.dataset.stockId;
                const filter = this.value;
                const transactionTable = document.getElementById(`transactionTable_${stockId}`);

                fetch(`/stock/transactions/${stockId}?filter=${filter}`)
                    .then(response => response.json())
                    .then(data => {
                        const filteredTransactions = data.transactions.filter(transaction => !transaction.description.startsWith('HPP '));
                        let html = '';
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
                                    <td>${transaction.description}</td>
                                    <td>${transaction.voucher_type || 'Unknown'}</td>
                                    <td>${transaction.quantity || 0}</td>
                                    <td>${number_format(transaction.nominal ?? 0, 2)}</td>
                                    <td>${transaction.created_at || ''}</td>
                                </tr>
                            `;
                            });
                            html += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        } else {
                            html = '<p class="text-center">Tidak ada transaksi terkait untuk barang ini.</p>';
                        }
                        transactionTable.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching transactions:', error);
                        transactionTable.innerHTML = '<p class="text-center">Terjadi kesalahan saat memuat transaksi.</p>';
                    });
            });
        });

        // Dynamic Ingredient Rows
        let ingredientCount = 0;
        document.getElementById('addIngredient').addEventListener('click', function() {
            ingredientCount++;
            const container = document.getElementById('ingredientsContainer');
            const newRow = document.createElement('div');
            newRow.className = 'ingredient-row mb-3';
            newRow.innerHTML = `
            <div class="row">
                <div class="col-md-5">
                    <label for="transfer_stock_id_${ingredientCount}" class="form-label">Bahan Baku</label>
                    <select name="transfer_stock_id[]" id="transfer_stock_id_${ingredientCount}" class="form-select transfer-stock-select" required>
                        <option value="">Pilih Bahan Baku</option>
                        @if (isset($transferStockData) && is_array($transferStockData) && !empty($transferStockData))
                            @foreach (collect($transferStockData)->flatten() as $stock)
                                <option value="{{ $stock->id }}">{{ $stock->item }} ({{ $stock->size }}) - {{ $stock->quantity }} tersedia</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="quantity_${ingredientCount}" class="form-label">Kuantitas</label>
                    <input type="number" name="quantity[]" id="quantity_${ingredientCount}" class="form-control" min="1" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-ingredient">Hapus</button>
                </div>
            </div>
        `;
            container.appendChild(newRow);

            document.querySelectorAll('.remove-ingredient').forEach(button => {
                button.style.display = ingredientCount > 0 ? 'block' : 'none';
            });
        });

        // Remove Ingredient Row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-ingredient')) {
                e.target.closest('.ingredient-row').remove();
                ingredientCount--;
                if (ingredientCount === 0) {
                    document.querySelectorAll('.remove-ingredient')[0].style.display = 'none';
                }
            }
        });

        // Initialize number_format if not defined
        if (typeof number_format === 'undefined') {
            number_format = function(number, decimals, dec_point, thousands_sep) {
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 2 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
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