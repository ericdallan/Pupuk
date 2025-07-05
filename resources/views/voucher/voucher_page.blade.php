@extends('layouts/app')

@section('content')
@section('title', 'Transaksi Akuntansi')

<style>
    .filter-button {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    .filter-button:hover {
        background-color: table#0056b3;
        border-color: #0056b3;
    }

    .create-voucher-button {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .create-voucher-button:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-disabled {
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.65;
    }

    .search-button {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }

    .search-button:hover {
        background-color: #138496;
        border-color: #117a8b;
    }

    /* Ensure pagination is visible */
    .pagination {
        margin-top: 20px;
        justify-content: center;
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
    <form action="{{ route('voucher_page') }}" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Voucher/Invoice:</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Masukkan nomor voucher/invoice">
            </div>
            <div class="col-md-3">
                <label for="voucher_type" class="form-label">Tipe Voucher:</label>
                <select name="voucher_type" id="voucher_type" class="form-select">
                    <option value="">Semua</option>
                    <option value="PJ" {{ request('voucher_type') == 'PJ' ? 'selected' : '' }}>Penjualan</option>
                    <option value="PG" {{ request('voucher_type') == 'PG' ? 'selected' : '' }}>Pengeluaran</option>
                    <option value="PM" {{ request('voucher_type') == 'PM' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="PB" {{ request('voucher_type') == 'PB' ? 'selected' : '' }}>Pembelian</option>
                    <option value="LN" {{ request('voucher_type') == 'LN' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan:</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                        @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun:</label>
                <select name="year" id="year" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary me-2 filter-button">Filter</button>
                <button type="submit" class="btn btn-info me-2 search-button">Cari</button>
                <button type="button" class="btn btn-primary create-voucher-button" data-bs-toggle="modal" data-bs-target="#voucherModal">
                    Buat Voucher
                </button>
            </div>
        </div>
    </form>
    @extends('voucher/voucher_form')

    <!-- Debug Pagination Info -->
    <div class="alert alert-info mt-3">
        Total Vouchers: {{ $vouchers->total() }} |
        Current Page: {{ $vouchers->currentPage() }} |
        Per Page: {{ $vouchers->perPage() }} |
        Total Pages: {{ $vouchers->lastPage() }}
    </div>

    @if ($vouchers->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover text-center">
            <thead class="table-dark" style="text-align: center; vertical-align: middle;">
                <tr>
                    <th>Nomor Voucher</th>
                    <th>Tipe Voucher</th>
                    <th>Nomor Invoice</th>
                    <th>Stock</th>
                    <th>Tanggal Voucher</th>
                    <th>Transaksi</th>
                    <th>Total Nominal</th>
                    <th colspan="4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vouchers as $voucher_item)
                <tr>
                    <td>{{ $voucher_item->voucher_number }}</td>
                    <td>
                        @if($voucher_item->voucher_type == 'PJ')
                        Penjualan
                        @elseif($voucher_item->voucher_type == 'PG')
                        Pengeluaran
                        @elseif($voucher_item->voucher_type == 'PM')
                        Pemasukan
                        @elseif($voucher_item->voucher_type == 'PB')
                        Pembelian
                        @elseif($voucher_item->voucher_type == 'LN')
                        Lainnya
                        @elseif($voucher_item->voucher_type == 'PK')
                        Pemakaian
                        @elseif($voucher_item->voucher_type == 'PH')
                        Pemindahan
                        @else
                        {{ $voucher_item->voucher_type }}
                        @endif
                    </td>
                    <td>{{ !empty($voucher_item->invoice) ? $voucher_item->invoice : '-' }}</td>
                    <td>
                        @if (!empty($voucher_item->is_opening_stock))
                        Saldo Awal Stock: {{ implode(', ', $voucher_item->is_opening_stock) }}
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($voucher_item->voucher_date)->isoFormat('dddd, DD MMMM') }}</td>
                    <td>{{ $voucher_item->transaction }}</td>
                    <td>{{ number_format($voucher_item->total_debit, 2) }}</td>
                    <td><a href="{{ route('voucher_detail', $voucher_item->id) }}" class="btn btn-info btn-sm">Rincian</a></td>
                    <td>
                        @if ($voucher_item->invoices()->exists() && $voucher_item->invoices()->whereIn('id', DB::table('invoice_payments')->pluck('invoice_id'))->exists())
                        <button class="btn btn-warning btn-sm btn-disabled" disabled title="Tidak dapat mengedit karena voucher memiliki invoices yang terkait dengan pembayaran">Edit</button>
                        @else
                        <a href="{{ route('voucher_edit', $voucher_item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('voucher.delete', $voucher_item->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            @if ($voucher_item->has_stock)
                            <button type="button" class="btn btn-danger btn-sm" disabled title="Tidak dapat menghapus karena voucher memiliki data stok">Hapus</button>
                            @elseif ($voucher_item->invoices()->exists() && $voucher_item->invoices()->whereHas('invoice_payments')->exists())
                            <button type="button" class="btn btn-danger btn-sm" disabled title="Tidak dapat menghapus karena voucher memiliki invoice yang terkait dengan pembayaran">Hapus</button>
                            @elseif ($voucher_item->invoices()->exists() && !$voucher_item->invoice_payments()->exists())
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Data invoice terkait akan dihapus.')">Hapus</button>
                            @elseif (!$voucher_item->invoices()->exists() && $voucher_item->invoice_payments()->exists())
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Data pembayaran terkait akan dihapus.')">Hapus</button>
                            @else
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini?')">Hapus</button>
                            @endif
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('voucher_pdf', $voucher_item->id) }}" class="btn btn-secondary btn-sm" target="_blank">PDF</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Explicit Pagination Links -->
    @if ($vouchers->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mt-4">
            {{ $vouchers->links('pagination::bootstrap-5') }}
        </ul>
    </nav>
    @else
    <div class="alert alert-info mt-3">Tidak ada halaman tambahan untuk ditampilkan karena data vouchers kurang dari 10.</div>
    @endif
    @else
    <div class="alert alert-info">Data Transaksi belum ditemukan, silahkan membuat voucher.</div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            // Bootstrap alert handling
        }
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            // Bootstrap alert handling
        }
    });
</script>
@endsection