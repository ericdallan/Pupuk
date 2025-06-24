@extends('layouts/app')

@section('title', 'Stock Barang Dagangan')

@section('content')

<!-- Filter Form -->
<form method="GET" action="{{ route('stock_page') }}" class="mb-1">
    <div class="row align-items-end">
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
                <div class="dropdown me-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="tableFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Filter Tabel
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="tableFilterDropdown">
                        <li><a class="dropdown-item" href="#" data-filter="all">Semua Table</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="stocks">Table Stocks</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="transfer_stocks">Table Transfer Stocks</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="used_stocks">Table Used Stocks</a></li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary me-2">Filter</button>

                <a href="{{ route('stock.export') . '?start_date=' . request('start_date', now()->startOfYear()->toDateString()) . '&end_date=' . request('end_date', now()->toDateString()) }}" class="btn btn-success">Export to Excel</a>
            </div>
        </div>
    </div>
</form>

<!-- Stocks Table -->
<div class="table-section" data-table="stocks">
    <h3>Stocks</h3>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Stok Tersedia</th>
                    <th>Saldo Awal</th>
                    <th>Masuk Barang</th>
                    <th>Keluar Barang</th>
                    <th>Akhir</th>
                    <th>Detail</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($stockData))
                @foreach ($stockData as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->item }}</td>
                    <td>{{ $stock->unit }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ $stock->opening_qty }}</td>
                    <td>{{ $stock->incoming_qty }}</td>
                    <td>{{ $stock->outgoing_qty }}</td>
                    <td>{{ $stock->final_stock_qty }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="alert alert-info mb-0">
                            Data stok belum ditemukan.
                        </div>
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
                    <th>Satuan</th>
                    <th>Stok Tersedia</th>
                    <th>Saldo Awal</th>
                    <th>Masuk Barang</th>
                    <th>Keluar Barang</th>
                    <th>Akhir</th>
                    <th>Detail</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th>Qty</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($transferStockData))
                @foreach ($transferStockData as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->item }}</td>
                    <td>{{ $stock->unit }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ $stock->opening_qty }}</td>
                    <td>{{ $stock->incoming_qty }}</td>
                    <td>{{ $stock->outgoing_qty }}</td>
                    <td>{{ $stock->final_stock_qty }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="alert alert-info mb-0">
                            Data transfer stok belum ditemukan.
                        </div>
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
                    <th>Satuan</th>
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
                @if (!empty($usedStockData))
                @foreach ($usedStockData as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->item }}</td>
                    <td>{{ $stock->unit }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                    <td>{{ $stock->opening_qty }}</td>
                    <td>{{ number_format($stock->opening_hpp ?? 0, 2) }}</td>
                    <td>{{ $stock->incoming_qty }}</td>
                    <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                    <td>{{ $stock->outgoing_qty }}</td>
                    <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                    <td>{{ $stock->final_stock_qty }}</td>
                    <td>{{ number_format($stock->average_hpp ?? 0, 2) }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="14" class="text-center">
                        <div class="alert alert-info mb-0">
                            Data stok yang digunakan belum ditemukan.
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Transaction Detail Modals -->
@foreach (array_merge($stockData, $transferStockData, $usedStockData) as $stock)
<div class="modal fade" id="detailModal{{ $stock->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $stock->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $stock->id }}">Detail Transaksi untuk {{ $stock->item }} ({{ $stock->table_name }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Filter for 7 days or 1 month -->
                <div class="mb-3">
                    <label for="modal_filter_{{ $stock->id }}" class="form-label">Tampilkan Transaksi</label>
                    <select id="modal_filter_{{ $stock->id }}" class="form-select modal-filter" data-stock-id="{{ $stock->id }}">
                        <option value="7_days">7 Hari Terakhir</option>
                        <option value="1_month">1 Bulan Terakhir</option>
                    </select>
                </div>
                <div class="transaction-table">
                    @if ($stock->transactions->count() > 0)
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
                                @if (!str_starts_with($transaction->description, 'HPP '))
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>
                                        @if ($transaction->voucher_type == 'PJ')
                                        Penjualan
                                        @elseif ($transaction->voucher_type == 'PB')
                                        Pembelian
                                        @else
                                        {{ $transaction->voucher_type }}
                                        @endif
                                    </td>
                                    <td>{{ $transaction->quantity }}</td>
                                    <td>{{ number_format($transaction->nominal ?? 0, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') }}</td>
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

<!-- JavaScript for Modal Filter and Table Filter -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Table Filter Logic
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');

                document.querySelectorAll('.table-section').forEach(section => {
                    if (filter === 'all') {
                        section.style.display = 'block';
                    } else {
                        section.style.display = section.getAttribute('data-table') === filter ? 'block' : 'none';
                    }
                });
            });
        });

        // Modal Filter Logic
        document.querySelectorAll('.modal-filter').forEach(select => {
            select.addEventListener('change', function() {
                const stockId = this.dataset.stockId;
                const filter = this.value;
                const transactionTable = document.querySelector(`#detailModal${stockId} .transaction-table`);

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
                                                    <th>Tipe Voucher</th>
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
                                            <td>${transaction.voucher_type}</td>
                                            <td>${transaction.quantity}</td>
                                            <td>${number_format(transaction.nominal ?? 0, 2)}</td>
                                            <td>${transaction.created_at}</td>
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
                    });
            });
        });

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