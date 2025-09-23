@extends('layouts.app')

@section('title', 'Voucher Akuntansi')

@section('content')
    <style>
        /* Enhanced Button Styles */
        .back-button,
        .btn-print {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            border: none;
            color: white;
            transition: all 0.3s ease;
            font-weight: 500;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
        }

        .back-button:hover,
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            background: linear-gradient(45deg, #5a6268, #474e54);
            color: white;
        }

        /* Table Enhancements */
        .table {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table thead th {
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem 0.75rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e3f2fd;
            transform: scale(1.002);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
            padding: 0.875rem 0.75rem;
            border-color: #e9ecef;
        }

        .table td {
            font-size: 0.9rem;
        }

        /* Card Styling */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-radius: 12px 12px 0 0 !important;
        }

        .card-title {
            color: #495057;
            font-size: 1.1rem;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Enhanced Info Cards */
        .info-item {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            border-color: #007bff;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #0056b3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .info-icon i {
            color: white;
            font-size: 1.1rem;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            margin: 0;
            line-height: 1.2;
        }

        /* Enhanced Badges */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.5rem;
            display: inline-block;
        }

        .badge-balanced {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .badge-imbalanced {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }

        /* Signature Table */
        .signature-table {
            width: 85%;
            margin: 2rem auto 1rem auto;
        }

        .signature-table th {
            background: linear-gradient(135deg, #495057, #6c757d);
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        .signature-table td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
            text-align: center;
            position: relative;
        }

        .signature-space {
            height: 120px;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-radius: 6px;
            margin: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-style: italic;
        }

        .signature-name {
            padding: 1rem;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
        }

        /* Amount Styling */
        .amount-positive {
            color: #28a745 !important;
            font-weight: 700;
        }

        .amount-negative {
            color: #dc3545 !important;
            font-weight: 700;
        }

        .amount-zero {
            color: #6c757d !important;
        }

        /* Summary Cards */
        .summary-card {
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin: 0 auto 1rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .summary-debit {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .summary-credit {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }

        .summary-amount {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        /* Animation */
        .container-fluid {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .info-item {
                margin-bottom: 1rem;
            }

            .signature-table {
                width: 100%;
                font-size: 0.85rem;
            }

            .summary-card {
                margin-bottom: 1rem;
            }
        }
    </style>

    <div class="container-fluid">
        <!-- Enhanced Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-receipt me-3"></i>{{ $headingText }} Voucher</h1>
                    <p class="mb-0 opacity-75">Detail lengkap voucher akuntansi dengan informasi transaksi</p>
                </div>
                <a href="{{ route('voucher_page') }}" class="btn back-button">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <!-- Enhanced Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-icon summary-debit">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="info-label">Total Debit</div>
                    <div class="summary-amount text-success">Rp {{ number_format($voucher->total_debit, 2, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-icon summary-credit">
                        <i class="fas fa-minus"></i>
                    </div>
                    <div class="info-label">Total Kredit</div>
                    <div class="summary-amount text-danger">Rp {{ number_format($voucher->total_credit, 2, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="summary-icon"
                        style="background: linear-gradient(135deg, {{ $voucher->total_debit == $voucher->total_credit ? '#28a745, #20c997' : '#dc3545, #fd7e14' }});">
                        <i
                            class="fas fa-{{ $voucher->total_debit == $voucher->total_credit ? 'check' : 'exclamation-triangle' }}"></i>
                    </div>
                    <div class="info-label">Status Balance</div>
                    <span
                        class="status-badge {{ $voucher->total_debit == $voucher->total_credit ? 'badge-balanced' : 'badge-imbalanced' }}">
                        {{ $voucher->total_debit == $voucher->total_credit ? 'Balanced' : 'Imbalanced' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Enhanced Voucher Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi Voucher</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Nomor Voucher</div>
                                <div class="info-value">{{ $voucher->voucher_number }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Perusahaan</div>
                                <div class="info-value">
                                    {{ $company ? $company->company_name : 'Tidak Ditemukan' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Tipe Voucher</div>
                                <div class="info-value">{{ $voucher->voucher_type }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Tanggal Voucher</div>
                                <div class="info-value">
                                    {{ \Carbon\Carbon::parse($voucher->voucher_date)->isoFormat('dddd, DD MMMM YYYY') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Disiapkan Oleh</div>
                                <div class="info-value">{{ $voucher->prepared_by }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Disetujui Oleh</div>
                                <div class="info-value">{{ $voucher->approved_by }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Diberikan Kepada</div>
                                <div class="info-value">{{ $voucher->given_to }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-comment-alt"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Deskripsi Transaksi</div>
                                <div class="info-value">{{ $voucher->transaction }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Transaction Table -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-list-alt me-2"></i>Detail Transaksi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-file-alt me-1"></i>Deskripsi</th>
                                <th><i class="fas fa-ruler me-1"></i>Ukuran</th>
                                <th><i class="fas fa-sort-numeric-up me-1"></i>Kuantitas</th>
                                <th><i class="fas fa-money-bill-wave me-1"></i>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($voucherTransactions as $transaction)
                                <tr>
                                    <td class="text-start">{{ $transaction->description }}</td>
                                    <td>{{ $transaction->size ?? '-' }}</td>
                                    <td>{{ number_format($transaction->quantity) }}</td>
                                    <td class="amount-positive">
                                        Rp {{ number_format($transaction->nominal, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Tidak ada data transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enhanced Accounting Details Table -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-calculator me-2"></i>Rincian Akuntansi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>Kode Akun</th>
                                <th><i class="fas fa-tag me-1"></i>Nama Akun</th>
                                <th><i class="fas fa-arrow-up me-1"></i>Debit (Rp)</th>
                                <th><i class="fas fa-arrow-down me-1"></i>Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($voucherDetails as $detail)
                                <tr>
                                    <td class="text-start">
                                        <code class="bg-light px-2 py-1 rounded">{{ $detail->account_code }}</code>
                                    </td>
                                    <td class="text-start">{{ $detail->account_name }}</td>
                                    <td class="{{ $detail->debit > 0 ? 'amount-positive' : 'amount-zero' }}">
                                        Rp {{ number_format($detail->debit, 2, ',', '.') }}
                                    </td>
                                    <td class="{{ $detail->credit > 0 ? 'amount-negative' : 'amount-zero' }}">
                                        Rp {{ number_format($detail->credit, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Tidak ada rincian akuntansi</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="2" class="text-end">TOTAL:</th>
                                <th class="amount-positive">Rp {{ number_format($voucher->total_debit, 2, ',', '.') }}
                                </th>
                                <th class="amount-negative">Rp {{ number_format($voucher->total_credit, 2, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Enhanced Signature Section -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title"><i class="fas fa-signature me-2"></i>Tanda Tangan & Persetujuan</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered signature-table">
                    <thead>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <th>Diberikan Kepada</th>
                            <th>Diperiksa & Disetujui</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="signature-space">
                                    <span>Tanda Tangan</span>
                                </div>
                            </td>
                            <td>
                                <div class="signature-space">
                                    <span>Tanda Tangan</span>
                                </div>
                            </td>
                            <td>
                                <div class="signature-space">
                                    <span>Tanda Tangan</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="signature-name">{{ $voucher->prepared_by }}</td>
                            <td class="signature-name">{{ $voucher->given_to }}</td>
                            <td class="signature-name">{{ $company->director ?? 'Direktur' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        <small><i class="fas fa-info-circle me-1"></i>Dokumen ini sah setelah ditandatangani oleh pihak
                            yang berwenang</small>
                    </div>
                    <a href="{{ route('voucher_pdf', $voucher->id) }}" class="btn btn-print" target="_blank"
                        data-bs-toggle="tooltip" data-bs-placement="top" title="Cetak atau unduh voucher sebagai PDF">
                        <i class="fas fa-print me-2"></i>Cetak PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                    tooltipTriggerEl));

                // Add loading animation for print button
                document.querySelector('.btn-print').addEventListener('click', function() {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-print me-2"></i>Cetak PDF';
                    }, 2000);
                });
            });
        </script>
    @endpush
@endsection
