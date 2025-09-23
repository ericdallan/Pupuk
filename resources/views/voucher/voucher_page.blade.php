@extends('layouts/app')

@section('content')
@section('title', 'Transaksi Akuntansi')

<style>
    /* Enhanced Button Styles */
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

    .create-voucher-button {
        background: linear-gradient(45deg, #28a745, #1e7e34);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .create-voucher-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        background: linear-gradient(45deg, #218838, #1a6030);
    }

    .btn-disabled {
        background-color: #6c757d;
        border-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.65;
        transition: opacity 0.2s;
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

    /* Table Enhancements */
    .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table tbody tr:hover {
        background-color: #e9ecef;
        transition: background-color 0.2s;
    }

    /* Pagination Styling */
    .pagination {
        margin-top: 20px;
        justify-content: center;
    }

    .pagination .page-item .page-link {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border: none;
        color: #007bff;
        transition: background-color 0.2s, transform 0.2s;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
    }

    .pagination .page-item .page-link:hover {
        transform: translateY(-2px);
        background: linear-gradient(45deg, #0056b3, #003d80);
        color: white;
    }

    /* Alert Animations */
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

    /* Badge Styles */
    .voucher-type-badge {
        font-size: 0.9em;
        padding: 4px 8px;
        border-radius: 12px;
    }

    /* Date Color Based on Recency */
    .recent-date {
        color: #28a745;
        font-weight: bold;
    }

    .old-date {
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

<div class="mt-2">
    <form action="{{ route('voucher_page') }}" method="GET" class="mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Cari Voucher/Invoice:</label>
                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}"
                    placeholder="Masukkan nomor voucher/invoice">
            </div>
            <div class="col-md-3">
                <label for="voucher_type" class="form-label">Tipe Voucher:</label>
                <select name="voucher_type" id="voucher_type" class="form-select">
                    <option value="">Semua</option>
                    <optgroup label="Stok">
                        <option value="PJ" {{ request('voucher_type') == 'PJ' ? 'selected' : '' }}>Penjualan
                        </option>
                        <option value="PB" {{ request('voucher_type') == 'PB' ? 'selected' : '' }}>Pembelian
                        </option>
                        <option value="PH" {{ request('voucher_type') == 'PH' ? 'selected' : '' }} hidden>Pemindahan
                        </option>
                        <option value="PK" {{ request('voucher_type') == 'PK' ? 'selected' : '' }} hidden>Pemakaian
                        </option>
                    </optgroup>
                    <optgroup label="Keuangan">
                        <option value="PG" {{ request('voucher_type') == 'PG' ? 'selected' : '' }}>Pengeluaran
                        </option>
                        <option value="PM" {{ request('voucher_type') == 'PM' ? 'selected' : '' }}>Pemasukan
                        </option>
                        <option value="LN" {{ request('voucher_type') == 'LN' ? 'selected' : '' }}>Lainnya</option>
                    </optgroup>
                    <optgroup label="Penyesuaian">
                        <option value="PYB" {{ request('voucher_type') == 'PYB' ? 'selected' : '' }}>Penyesuaian
                            Bertambah</option>
                        <option value="PYK" {{ request('voucher_type') == 'PYK' ? 'selected' : '' }}>Penyesuaian
                            Berkurang</option>
                        <option value="PYL" {{ request('voucher_type') == 'PYL' ? 'selected' : '' }}>Penyesuaian
                            Lainnya</option>
                    </optgroup>
                    <optgroup label="Retur Barang">
                        <option value="RPB" {{ request('voucher_type') == 'RPB' ? 'selected' : '' }}>Retur Pembelian
                        </option>
                        <option value="RPJ" {{ request('voucher_type') == 'RPJ' ? 'selected' : '' }}>Retur Penjualan
                        </option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan:</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun:</label>
                <select name="year" id="year" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                            {{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2 filter-button" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Terapkan filter berdasarkan kriteria yang dipilih">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <button type="submit" class="btn btn-info me-2 search-button" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Cari voucher atau invoice berdasarkan nomor">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
                <button type="button" class="btn btn-success create-voucher-button" data-bs-toggle="modal"
                    data-bs-target="#voucherModal" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Create a new voucher">
                    <i class="fas fa-plus me-1"></i> Create Voucher
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
                        @php
                            $carbonDate = $voucher_item->voucher_date
                                ? \Carbon\Carbon::parse($voucher_item->voucher_date)
                                : null;
                            $dateStr = $carbonDate ? $carbonDate->format('d-m-Y') : 'N/A';
                            $dayNameId = $carbonDate ? $carbonDate->locale('id')->isoFormat('dddd') : 'N/A';
                            $dayNameEn = $carbonDate ? $carbonDate->locale('en')->format('l') : 'N/A';
                            $isRecent = $carbonDate && $carbonDate->diffInDays(now()) <= 7;
                            $dateClass = $isRecent ? 'recent-date' : 'old-date';
                        @endphp
                        <tr>
                            <td>{{ $voucher_item->voucher_number }}</td>
                            <td>
                                @if ($voucher_item->voucher_type == 'PJ')
                                    Penjualan
                                @elseif($voucher_item->voucher_type == 'PG')
                                    Pengeluaran
                                @elseif($voucher_item->voucher_type == 'PM')
                                    Pemasukan
                                @elseif($voucher_item->voucher_type == 'PB')
                                    Pembelian
                                @elseif($voucher_item->voucher_type == 'LN')
                                    Lainnya
                                @elseif($voucher_item->voucher_type == 'PH')
                                    Pemindahan
                                @elseif($voucher_item->voucher_type == 'PK')
                                    Pemakaian
                                @elseif($voucher_item->voucher_type == 'PYB')
                                    Penyesuaian Bertambah
                                @elseif($voucher_item->voucher_type == 'PYK')
                                    Penyesuaian Berkurang
                                @elseif($voucher_item->voucher_type == 'PYL')
                                    Penyesuaian Lainnya
                                @elseif($voucher_item->voucher_type == 'RPB')
                                    Retur Pembelian
                                @elseif($voucher_item->voucher_type == 'RPJ')
                                    Retur Penjualan
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
                            <td class="{{ $dateClass }}" data-date="{{ $dateStr }}"
                                data-day-id="{{ $dayNameId }}" data-day-en="{{ $dayNameEn }}">
                                {{ $carbonDate ? $carbonDate->locale('id')->isoFormat('dddd, DD MMMM YYYY') : 'N/A' }}
                            </td>
                            <td>{{ $voucher_item->transaction }}</td>
                            <td>Rp {{ number_format($voucher_item->total_debit, 2, ',', '.') }}</td>
                            <td><a href="{{ route('voucher_detail', $voucher_item->id) }}"
                                    class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="Lihat detail voucher"><i class="fas fa-eye"></i></a></td>
                            <td>
                                @if (
                                    $voucher_item->invoices()->exists() &&
                                        $voucher_item->invoices()->whereIn('id', DB::table('invoice_payments')->pluck('invoice_id'))->exists())
                                    <button class="btn btn-warning btn-sm btn-disabled" disabled
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Tidak dapat diedit karena terkait pembayaran invoice">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @else
                                    <a href="{{ route('voucher_edit', $voucher_item->id) }}"
                                        class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit voucher"><i class="fas fa-edit"></i></a>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('voucher.delete', $voucher_item->id) }}" method="POST"
                                    style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    @if ($voucher_item->has_stock)
                                        <button type="button" class="btn btn-danger btn-sm" disabled
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Tidak dapat dihapus karena memiliki data stok">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @elseif ($voucher_item->invoices()->exists() && $voucher_item->invoices()->whereHas('invoice_payments')->exists())
                                        <button type="button" class="btn btn-danger btn-sm" disabled
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Tidak dapat dihapus karena terkait pembayaran invoice">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @elseif ($voucher_item->invoices()->exists() && !$voucher_item->invoice_payments()->exists())
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Data invoice terkait akan dihapus.')"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Hapus voucher dengan konfirmasi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @elseif (!$voucher_item->invoices()->exists() && $voucher_item->invoice_payments()->exists())
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Data pembayaran terkait akan dihapus.')"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Hapus voucher dengan konfirmasi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus voucher ini?')"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Hapus voucher dengan konfirmasi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('voucher_pdf', $voucher_item->id) }}"
                                    class="btn btn-secondary btn-sm" target="_blank" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Unduh voucher sebagai PDF"><i
                                        class="fas fa-file-pdf"></i></a>
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
            <div class="alert alert-info mt-3">Tidak ada halaman tambahan untuk ditampilkan karena data vouchers kurang
                dari {{ $vouchers->perPage() }}.</div>
        @endif
    @else
        <div class="alert alert-info">Data Transaksi belum ditemukan, silahkan membuat voucher.</div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => successMessage.classList.add('fade'), 3000);
        }
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            setTimeout(() => errorMessage.classList.add('fade'), 5000);
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
