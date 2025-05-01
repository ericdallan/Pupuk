@extends('layouts/app')

@section('content')
@section('title', 'Voucher Akuntansi')
<style>
    /* Warna untuk tombol Filter */
    .filter-button {
        background-color: #007bff;
        /* Contoh: Biru */
        border-color: #007bff;
        color: white;
    }

    .filter-button:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    /* Warna untuk tombol Buat Voucher */
    .create-voucher-button {
        background-color: #28a745;
        /* Contoh: Hijau */
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
</style>

@if (session('success'))
<div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert" style="cursor: pointer;">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (session('error'))
<div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert" style="cursor: pointer;">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if ($errors->any())
<div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert" style="cursor: pointer;">
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
                <label for="voucher_type" class="form-label">Tipe Voucher:</label>
                <select name="voucher_type" id="voucher_type" class="form-select">
                    <option value="">Semua</option>
                    <option value="JV" {{ request('voucher_type') == 'JV' ? 'selected' : '' }}>JV</option>
                    <option value="MP" {{ request('voucher_type') == 'MP' ? 'selected' : '' }}>MP</option>
                    <option value="MI" {{ request('voucher_type') == 'MI' ? 'selected' : '' }}>MI</option>
                    <option value="CG" {{ request('voucher_type') == 'CG' ? 'selected' : '' }}>CG</option>
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
            <div class="col-md-3" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary me-2 filter-button">Filter</button>
                <button type="button" class="btn btn-primary create-voucher-button" data-bs-toggle="modal" data-bs-target="#voucherModal">
                    Buat Voucher
                </button>
                @extends('voucher/voucher_form')
            </div>
        </div>
    </form>
    @if (count($voucher) > 0)
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>Nomor Voucher</th>
                    <th>Tipe Voucher</th>
                    <th>Nomor Invoice</th>
                    <th>Tanggal Voucher</th>
                    <th>Transaksi</th>
                    <th>Total Nominal</th>
                    <th colspan="4" style="text-align: center; vertical-align: middle;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voucher as $voucher_item)
                <tr>
                    <td>{{ $voucher_item->voucher_number }}</td>
                    <td>{{ $voucher_item->voucher_type }}</td>
                    <td>{{ !empty($voucher_item->invoice) ? $voucher_item->invoice : '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($voucher_item->voucher_date)->isoFormat('dddd, DD MMMM') }}</td>
                    <td>{{ $voucher_item->transaction }}</td>
                    <td>{{ number_format($voucher_item->total_debit, 2) }}</td>
                    <td><a href="{{ route('voucher_detail', $voucher_item->id) }}" class="btn btn-info btn-sm">Rincian</a></td>
                    <td>
                        @if ($voucher_item->invoices()->exists() && $voucher_item->invoice_payments()->exists())
                        <!-- Kondisi 1: Ada invoices dan invoice_payments, tombol Edit dinonaktifkan -->
                        <button class="btn btn-warning btn-sm btn-disabled" disabled title="Tidak dapat mengedit karena voucher memiliki invoices dan invoice payments">Edit</button>
                        @elseif ($voucher_item->invoices()->exists() && !$voucher_item->invoice_payments()->exists())
                        <!-- Kondisi 2: Ada invoices, tidak ada invoice_payments, tombol Edit dinonaktifkan -->
                        <button class="btn btn-warning btn-sm btn-disabled" disabled title="Tidak dapat mengedit karena voucher memiliki invoices">Edit</button>
                        @else
                        <!-- Kondisi 3 & 4: Tidak ada invoices atau tidak ada invoices dan invoice_payments, tombol Edit diaktifkan -->
                        <a href="{{ route('voucher_edit', $voucher_item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('voucher.delete', $voucher_item->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            @if ($voucher_item->invoices()->exists() && $voucher_item->invoice_payments()->exists())
                            <!-- Kondisi 1: Ada invoices dan invoice_payments, tombol Hapus diaktifkan -->
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Pastikan semua invoice payments telah dihapus terlebih dahulu.')">Hapus</button>
                            @elseif ($voucher_item->invoices()->exists() && !$voucher_item->invoice_payments()->exists())
                            <!-- Kondisi 2: Ada invoices, tidak ada invoice_payments, tombol Hapus diaktifkan -->
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini?')">Hapus</button>
                            @else
                            <!-- Kondisi 3 & 4: Tidak ada invoices atau tidak ada invoices dan invoice_payments, tombol Hapus diaktifkan -->
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
    @else
    <p>Data Voucher belum ditemukan, silahkan membuat voucher.</p>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            // Bootstrap alert already has close button and dismiss functionality
        }
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            // Bootstrap alert already has close button and dismiss functionality
        }
    });
</script>
@endsection