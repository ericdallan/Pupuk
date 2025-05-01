@extends('layouts.app')

@section('content')
@section('title', 'Buku Besar Pembantu Utang')

<div id="notification-area" class="alert alert-dismissible fade show d-none" role="alert">
    <span id="notification-message"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

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

<div class="d-flex align-items-end">
    <form action="{{ route('subsidiary_utang') }}" method="GET" class="row g-3 align-items-end flex-grow-1">
        <div class="col-md-3">
            <label for="toko" class="form-label">Filter Nama Akun Pembantu:</label>
            <select class="form-select" id="toko" name="toko" onchange="this.form.submit()">
                <option value="">Semua Nama Akun</option>
                @foreach ($utangUsaha->unique(function ($item) {
                return $item->store_name . '|' . $item->account_name;
                }) as $subsidiary)
                <option value="{{ $subsidiary->store_name }}" {{ request('toko') == $subsidiary->store_name ? 'selected' : '' }}>
                    {{ $subsidiary->account_name === 'Utang' ? 'Utang ' . $subsidiary->store_name : $subsidiary->store_name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#createSubsidiaryModal">
                Buat Akun Pembantu
            </button>
        </div>
    </form>
</div>

<!-- Tabel untuk Utang Usaha (account_code: 2.1.01.01) -->
<div class="mb-4 mt-3">
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center">
            <thead class="table-dark">
                <tr>
                    <th>Nama Toko</th>
                    <th>Nama Akun Pembantu</th>
                    <th>Kode Akun Pembantu</th>
                    <th colspan="3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($utangUsaha as $subsidiary)
                <tr>
                    <td>{{ $subsidiary->store_name }}</td>
                    <td>{{ $subsidiary->account_name }}</td>
                    <td>{{ $subsidiary->subsidiary_code }}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm detail-btn" data-bs-toggle="modal" data-bs-target="#detailModal" data-store-name="{{ $subsidiary->store_name }}" data-account-code="{{ $subsidiary->account_code }}">
                            Detail
                        </button>
                    </td>
                    <td>
                        @if ($subsidiary->invoices()->exists())
                        <!-- Kondisi 1: Subsidiary_code ada di tabel invoices, tombol Detail dinonaktifkan -->
                        <button class="btn btn-sm btn-warning detail-btn" data-subsidiary-id="{{ $subsidiary->id }}" data-store-name="{{ $subsidiary->store_name }}" data-account-name="{{ $subsidiary->account_name ?? '' }}" disabled>
                            Edit
                        </button>
                        @else
                        <button class="btn btn-sm btn-warning detail-btn" data-subsidiary-id="{{ $subsidiary->id }}" data-store-name="{{ $subsidiary->store_name }}" data-account-name="{{ $subsidiary->account_name ?? '' }}">
                            Edit
                        </button>
                        @endif
                    </td>
                    <td>
                        @if ($subsidiary->invoices()->exists())
                        <!-- Kondisi 1: Subsidiary_code ada di tabel invoices, tombol Hapus dinonaktifkan -->
                        <form action="{{ route('subsidiary.delete', $subsidiary->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" disabled onclick="return confirm('Apakah Anda yakin ingin menghapus subsidiary ini? Pastikan semua invoice telah dihapus terlebih dahulu.')">
                                Hapus
                            </button>
                        </form>
                        @else
                        <!-- Kondisi 2: Subsidiary_code tidak ada di tabel invoices, tombol Hapus diaktifkan -->
                        <form action="{{ route('subsidiary.delete', $subsidiary->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus subsidiary ini?')">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data Utang Usaha yang ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal untuk Detail Transaksi -->
<div class="modal fade" id="detailModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Transaksi Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-error" class="alert alert-danger d-none" role="alert"></div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="nomorInvoiceModal" class="form-label">Nomor Invoice:</label>
                        <select class="form-select" id="nomorInvoiceModal">
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="bulanModal" class="form-label">Bulan:</label>
                        <select name="bulanModal" id="bulanModal" class="form-select">
                            <option value="">Semua</option>
                            @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tahunModal" class="form-label">Tahun:</label>
                        <select name="tahunModal" id="tahunModal" class="form-select">
                            <option value="">Semua</option>
                            @for ($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center" id="detailTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Nomor Invoice</th>
                                <th>Nomor Voucher Pembelian</th>
                                <th>Nomor Voucher Pelunasan</th>
                                <th>Tanggal</th>
                                <th>Saldo Awal</th>
                                <th>Saldo Akhir</th>
                                <th>Sisa Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <!-- Data akan diisi oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-info me-2" id="filterButtonModal">Filter</button>
                    <button id="print-pdf-modal" class="btn btn-primary me-2">Print to PDF</button>
                    <button id="export-excel-modal" class="btn btn-success">Export to Excel</button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@include('subsidiary_utang.subsidiaryUtang_form')
@include('subsidiary_utang.subsidiary_edit')

<style>
    .payment-row td {
        padding-left: 20px;
        /* Indent payment rows */
        background-color: #f8f9fa;
        /* Light background for distinction */
    }

    .invoice-row {
        font-weight: bold;
        /* Bold invoice rows */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailButtons = document.querySelectorAll('.detail-btn');
        const detailModal = document.getElementById('detailModal');
        const detailTableBody = document.getElementById('detailTableBody');
        const nomorInvoiceModal = document.getElementById('nomorInvoiceModal');
        const bulanModal = document.getElementById('bulanModal');
        const tahunModal = document.getElementById('tahunModal');
        const filterButtonModal = document.getElementById('filterButtonModal');
        const printPdfModal = document.getElementById('print-pdf-modal');
        const exportExcelModal = document.getElementById('export-excel-modal');

        let currentStoreName = '';
        let currentAccountCode = '';

        // Function to populate invoice number dropdown
        function populateInvoiceDropdown(storeName, accountCode) {
            fetch(`{{ route('subsidiaryUtang.details') }}?store_name=${encodeURIComponent(storeName)}&account_code=${encodeURIComponent(accountCode)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    nomorInvoiceModal.innerHTML = '<option value="">Semua</option>'; // Changed to value="" for all invoices
                    const invoiceNumbers = [...new Set(data.filter(item => item.type === 'invoice').map(item => item.invoice))];
                    invoiceNumbers.forEach(invoice => {
                        const option = document.createElement('option');
                        option.value = invoice;
                        option.textContent = invoice;
                        nomorInvoiceModal.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching invoice numbers:', error.message);
                    document.getElementById('modal-error').textContent = `Gagal memuat nomor invoice: ${error.message}`;
                    document.getElementById('modal-error').classList.remove('d-none');
                });
        }

        // Function to fetch and display transaction details
        function fetchTransactionDetails(storeName, accountCode, filters = {}) {
            const queryParams = new URLSearchParams({
                store_name: storeName,
                account_code: accountCode,
                tipe_voucher: filters.tipeVoucher || '',
                bulan: filters.bulan || '',
                tahun: filters.tahun || ''
            }).toString();

            detailTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>';
            document.getElementById('modal-error').classList.add('d-none');

            fetch(`{{ route('subsidiaryUtang.details') }}?${queryParams}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}, StatusText: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    detailTableBody.innerHTML = '';
                    if (data.length === 0) {
                        detailTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada transaksi ditemukan.</td></tr>';
                        return;
                    }

                    data.forEach(transaction => {
                        const formattedDate = transaction.voucher_date ?
                            dayjs(transaction.voucher_date).format('DD MMMM YYYY') :
                            '-';
                        const voucherDetailUrl = transaction.voucher_id ?
                            `{{ route('voucher_detail', ':id') }}`.replace(':id', transaction.voucher_id) :
                            '#';
                        const paymentVoucherUrl = transaction.payment_voucher_id ?
                            `{{ route('voucher_detail', ':id') }}`.replace(':id', transaction.payment_voucher_id) :
                            '#';
                        const row = document.createElement('tr');
                        row.className = transaction.type === 'invoice' ? 'invoice-row' : 'payment-row';
                        row.innerHTML = `
                        <td>${transaction.invoice || '-'}</td>
                        <td>${transaction.nomor_voucher_pembelian === '-' ? '-' : `<a href="${voucherDetailUrl}" class="text-decoration-none">${transaction.nomor_voucher_pembelian}</a>`}</td>
                        <td>${transaction.nomor_voucher_pelunasan === '-' ? '-' : `<a href="${paymentVoucherUrl}" class="text-decoration-none">${transaction.nomor_voucher_pelunasan}</a>`}</td>
                        <td>${formattedDate}</td>
                        <td>${parseFloat(transaction.saldo_awal || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                        <td>${transaction.saldo_akhir === '-' ? '-' : parseFloat(transaction.saldo_akhir).toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                        <td>${parseFloat(transaction.sisa_saldo || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 })}</td>
                    `;
                        detailTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching transaction details:', error.message);
                    document.getElementById('modal-error').textContent = `Gagal memuat data: ${error.message}`;
                    document.getElementById('modal-error').classList.remove('d-none');
                    detailTableBody.innerHTML = `<tr><td colspan="7" class="text-center">Terjadi kesalahan saat mengambil data: ${error.message}</td></tr>`;
                });
        }

        // Handle detail button clicks
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                currentStoreName = this.getAttribute('data-store-name');
                currentAccountCode = this.getAttribute('data-account-code');

                document.getElementById('detailModalLabel').textContent = `Detail Transaksi untuk ${currentStoreName}`;
                nomorInvoiceModal.value = '';
                bulanModal.value = '';
                tahunModal.value = '';

                populateInvoiceDropdown(currentStoreName, currentAccountCode);
                fetchTransactionDetails(currentStoreName, currentAccountCode);
            });
        });

        // Handle filter button click
        if (filterButtonModal) {
            filterButtonModal.addEventListener('click', function() {
                const filters = {
                    tipeVoucher: nomorInvoiceModal.value,
                    bulan: bulanModal.value,
                    tahun: tahunModal.value
                };
                fetchTransactionDetails(currentStoreName, currentAccountCode, filters);
            });
        }

        // Handle print to PDF
        if (printPdfModal) {
            printPdfModal.addEventListener('click', function() {
                const queryParams = new URLSearchParams({
                    toko_pdf: currentStoreName,
                    account_code_pdf: currentAccountCode,
                    tipe_voucher_pdf: nomorInvoiceModal.value, // Empty string for all invoices
                    month_pdf: bulanModal.value,
                    year_pdf: tahunModal.value
                }).toString();
                const pdfUrl = `{{ route('subsidiary_utang_pdf') }}?${queryParams}`;
                window.open(pdfUrl, '_blank');
            });
        }

        // Handle export to Excel (unchanged)
        if (exportExcelModal) {
            exportExcelModal.addEventListener('click', function() {
                const queryParams = new URLSearchParams({
                    toko_excel: currentStoreName,
                    account_code_excel: currentAccountCode,
                    tipe_voucher_excel: nomorInvoiceModal.value,
                    month_excel: bulanModal.value,
                    year_excel: tahunModal.value,
                    type: 'utang',
                }).toString();
                const excelUrl = `/subsidiary/excel?${queryParams}`;
                window.location.href = excelUrl;
            });
        }
    });
</script>
@endsection