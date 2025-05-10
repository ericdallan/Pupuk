@extends('layouts/app')

@section('title', 'Stock Barang Dagangan')

@section('content')
<div class="container">
    <h2>Stock Barang Dagangan</h2>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('stock_page') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="filter_date" class="form-label">Pilih Tanggal</label>
                <input type="date" name="filter_date" id="filter_date" class="form-control" value="{{ request('filter_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('stock.export') . '?filter_date=' . request('filter_date', now()->toDateString()) }}" class="btn btn-success">Export to Excel</a>
            </div>
        </div>
    </form>

    <!-- Stock Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Satuan</th>
                    <th>Stok Tersedia</th>
                    <th>Masuk Barang</th>
                    <th>Keluar Barang</th>
                    <th>Akhir</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockData as $stock)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stock->item }}</td>
                    <td>{{ $stock->unit }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ $stock->incoming }}</td>
                    <td>{{ $stock->outgoing }}</td>
                    <td>{{ $stock->final_stock }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $stock->id }}">Detail</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Transaction Detail Modals -->
    @foreach ($stockData as $stock)
    <div class="modal fade" id="detailModal{{ $stock->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $stock->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel{{ $stock->id }}">Detail Transaksi untuk {{ $stock->item }}</h5>
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
                                        <th>Tipe Voucher</th>
                                        <th>Kuantitas</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stock->transactions as $transaction)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $transaction->description }}</td>
                                        <td>{{ $transaction->voucher_type }}</td>
                                        <td>{{ $transaction->quantity }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') }}</td>
                                    </tr>
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
</div>

<!-- JavaScript for Modal Filter -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.modal-filter').forEach(select => {
            select.addEventListener('change', function() {
                const stockId = this.dataset.stockId;
                const filter = this.value;
                const transactionTable = document.querySelector(`#detailModal${stockId} .transaction-table`);

                // Fetch filtered transactions
                fetch(`/stock/transactions/${stockId}?filter=${filter}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.transactions.length > 0) {
                            html = `
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover text-center">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Deskripsi</th>
                                            <th>Tipe Voucher</th>
                                            <th>Kuantitas</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                            data.transactions.forEach((transaction, index) => {
                                html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${transaction.description}</td>
                                    <td>${transaction.voucher_type}</td>
                                    <td>${transaction.quantity}</td>
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
    });
</script>
@endsection