@extends('layouts.app')

@section('content')
@section('title', 'Edit Voucher')

<div class="container-fluid mt-4">
    <style>
        /* Section Header Styling */
        .section-header {
            font-size: 1.2em;
            font-weight: bold;
            color: #343a40;
            padding: 10px 0;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form Control Enhancements */
        .form-control,
        .form-select {
            border-radius: 6px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }

        .required:after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }

        input:disabled,
        select:disabled {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
            background: linear-gradient(45deg, #0056b3, #003d80);
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            background: linear-gradient(45deg, #c82333, #b21f2d);
        }

        /* Radio Button Styling */
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .radio-group .form-check {
            margin-bottom: 0;
            min-width: 120px;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            cursor: pointer;
            font-weight: 500;
            margin-left: 5px;
        }

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            animation: fadeIn 0.5s;
            cursor: pointer;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Table Styling */
        .table {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
        }

        .table-primary {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
        }

        .table-success {
            background: linear-gradient(90deg, #28a745, #218838);
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }

        /* Field Group Styling */
        .field-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .section-header {
                font-size: 1.1em;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }

            .radio-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .radio-group .form-check {
                min-width: auto;
                width: 100%;
            }
        }
    </style>

    <!-- Alerts -->
    @if (session('success'))
        <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (session('message'))
        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="voucherForm" method="POST" action="{{ route('voucher.update', $voucher->id) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Basic Information Section -->
        <div class="section-header">
            <i class="fas fa-info-circle"></i> Informasi Dasar Voucher
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="voucherNumber" class="form-label required">
                    <i class="fas fa-hashtag"></i> Nomor Voucher
                </label>
                <input type="text" class="form-control" id="voucherNumber" name="voucher_number"
                    value="{{ $voucher->voucher_number }}" readonly data-bs-toggle="tooltip"
                    title="Nomor voucher unik untuk transaksi ini">
            </div>
            <div class="col-md-6">
                <label for="companyName" class="form-label">
                    <i class="fas fa-building"></i> Nama Perusahaan
                </label>
                @if ($company)
                    <input type="text" class="form-control" id="companyName" value="{{ $company->company_name }}"
                        readonly data-bs-toggle="tooltip" title="Nama perusahaan terkait voucher">
                @else
                    <input type="text" class="form-control is-invalid" id="companyName"
                        value="Nama Perusahaan Kosong" readonly>
                    <div class="invalid-feedback">Nama Perusahaan Belum Ditemukan.</div>
                @endif
            </div>
        </div>

        <!-- Voucher Category Section -->
        <div class="section-header">
            <i class="fas fa-tags"></i> Kategori Voucher
        </div>
        <div class="row g-3">
            <div class="col-md-8">
                <div class="field-group">
                    <label class="form-label required">
                        <i class="fas fa-list"></i> Kategori Voucher
                    </label>
                    <div class="radio-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useStockYes" name="use_stock"
                                value="yes"
                                {{ in_array($voucher->voucher_type, ['PB', 'PJ', 'PYK', 'PYB', 'RPB', 'RPJ']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Transaksi yang memengaruhi stok barang">
                            <label class="form-check-label" for="useStockYes">
                                <i class="fas fa-boxes"></i> Stok
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useStockNo" name="use_stock"
                                value="no"
                                {{ in_array($voucher->voucher_type, ['PG', 'PM', 'LN', 'PYL']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Transaksi keuangan tanpa memengaruhi stok">
                            <label class="form-check-label" for="useStockNo">
                                <i class="fas fa-dollar-sign"></i> Keuangan
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="adjustment" name="use_stock"
                                value="adjustment"
                                {{ in_array($voucher->voucher_type, ['PYK', 'PYB', 'PYL']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Penyesuaian stok atau nilai akuntansi">
                            <label class="form-check-label" for="adjustment">
                                <i class="fas fa-adjust"></i> Penyesuaian
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="return" name="use_stock"
                                value="return" {{ in_array($voucher->voucher_type, ['RPB', 'RPJ']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Retur barang pembelian atau penjualan">
                            <label class="form-check-label" for="return">
                                <i class="fas fa-undo"></i> Retur Barang
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-group" id="affectStockContainer"
                    style="display: {{ in_array($voucher->voucher_type, ['PB', 'PJ', 'PYK', 'PYB', 'RPB', 'RPJ']) ? 'block' : 'none' }};">
                    <label class="form-label required">
                        <i class="fas fa-warehouse"></i> Mempengaruhi Stok?
                    </label>
                    <div class="radio-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="affectStockYes" name="affect_stock"
                                value="yes"
                                {{ in_array($voucher->voucher_type, ['PB', 'PJ', 'PYK', 'PYB', 'RPB', 'RPJ']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Pilih jika transaksi memengaruhi stok">
                            <label class="form-check-label" for="affectStockYes">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="affectStockNo" name="affect_stock"
                                value="no"
                                {{ in_array($voucher->voucher_type, ['PG', 'PM', 'PYL']) ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Pilih jika transaksi tidak memengaruhi stok">
                            <label class="form-check-label" for="affectStockNo">Tidak</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voucher Type and Date -->
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label for="voucherType" class="form-label required">
                    <i class="fas fa-list"></i> Tipe Voucher
                </label>
                <select class="form-select" id="voucherType" name="voucher_type" required aria-required="true"
                    data-bs-toggle="tooltip" title="Pilih jenis transaksi voucher">
                    <option value="">Pilih Tipe Voucher</option>
                    <option value="PJ" {{ $voucher->voucher_type == 'PJ' ? 'selected' : '' }}>Penjualan</option>
                    <option value="PG" {{ $voucher->voucher_type == 'PG' ? 'selected' : '' }}>Pengeluaran</option>
                    <option value="PM" {{ $voucher->voucher_type == 'PM' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="PB" {{ $voucher->voucher_type == 'PB' ? 'selected' : '' }}>Pembelian</option>
                    <option value="LN" {{ $voucher->voucher_type == 'LN' ? 'selected' : '' }}>Lainnya</option>
                    <option value="PYK" {{ $voucher->voucher_type == 'PYK' ? 'selected' : '' }}>Penyesuaian
                        Berkurang</option>
                    <option value="PYB" {{ $voucher->voucher_type == 'PYB' ? 'selected' : '' }}>Penyesuaian
                        Bertambah</option>
                    <option value="PYL" {{ $voucher->voucher_type == 'PYL' ? 'selected' : '' }}>Penyesuaian Lainnya
                    </option>
                    <option value="RPB" {{ $voucher->voucher_type == 'RPB' ? 'selected' : '' }}>Retur Pembelian
                    </option>
                    <option value="RPJ" {{ $voucher->voucher_type == 'RPJ' ? 'selected' : '' }}>Retur Penjualan
                    </option>
                </select>
                <div class="invalid-feedback">Tipe Voucher wajib dipilih.</div>
            </div>
            <div class="col-md-4">
                <label for="voucherDate" class="form-label required">
                    <i class="fas fa-calendar"></i> Tanggal
                </label>
                <input type="date" class="form-control" id="voucherDate" name="voucher_date"
                    value="{{ $voucher->voucher_date->format('Y-m-d') }}" required aria-required="true"
                    data-bs-toggle="tooltip" title="Tanggal transaksi voucher">
                <div class="invalid-feedback">Tanggal wajib diisi.</div>
            </div>
            <div class="col-md-4">
                <label for="voucherDay" class="form-label">
                    <i class="fas fa-calendar-day"></i> Hari
                </label>
                <input type="text" class="form-control" id="voucherDay" name="voucher_day"
                    value="{{ $voucher->voucher_day }}" readonly data-bs-toggle="tooltip"
                    title="Hari transaksi berdasarkan tanggal">
            </div>
        </div>

        <!-- Description -->
        <div class="row g-3 mt-2">
            <div class="col-12">
                <label for="description" class="form-label">
                    <i class="fas fa-file-text"></i> Deskripsi Voucher
                </label>
                <textarea class="form-control" id="description" name="description" rows="3" readonly data-bs-toggle="tooltip"
                    title="Deskripsi otomatis berdasarkan tipe voucher">{{ $voucher->description }}</textarea>
            </div>
        </div>

        <!-- Invoice Section -->
        <div class="section-header">
            <i class="fas fa-file-invoice"></i> Pengaturan Invoice
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="field-group">
                    <label class="form-label required">
                        <i class="fas fa-file-invoice-dollar"></i> Gunakan Invoice?
                    </label>
                    <div class="radio-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useInvoiceYes" name="use_invoice"
                                value="yes"
                                {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'checked' : '' }} required
                                aria-required="true" data-bs-toggle="tooltip"
                                title="Pilih jika transaksi menggunakan invoice">
                            <label class="form-check-label" for="useInvoiceYes">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useInvoiceNo" name="use_invoice"
                                value="no"
                                {{ !$voucher->invoice && $voucher->use_invoice !== 'yes' ? 'checked' : '' }} required
                                aria-required="true" data-bs-toggle="tooltip"
                                title="Pilih jika transaksi tidak menggunakan invoice">
                            <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                        </div>
                    </div>
                    <div class="invalid-feedback">Pilih apakah menggunakan invoice.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-group" id="existingInvoiceContainer"
                    style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                    <label class="form-label">
                        <i class="fas fa-archive"></i> Invoice yang Sudah Ada?
                    </label>
                    <div class="radio-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useExistingInvoiceYes"
                                name="use_existing_invoice" value="yes"
                                {{ $voucher->use_existing_invoice === 'yes' ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Pilih jika menggunakan nomor invoice yang sudah ada">
                            <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="useExistingInvoiceNo"
                                name="use_existing_invoice" value="no"
                                {{ $voucher->use_existing_invoice !== 'yes' ? 'checked' : '' }}
                                data-bs-toggle="tooltip" title="Pilih jika membuat invoice baru">
                            <label class="form-check-label" for="useExistingInvoiceNo">Tidak</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6" id="invoiceFieldContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="invoice" class="form-label required">
                    <i class="fas fa-file-invoice"></i> Nomor Invoice
                </label>
                <input type="text" class="form-control" id="invoice" name="invoice"
                    value="{{ $voucher->invoice ?? '' }}" data-bs-toggle="tooltip"
                    title="Masukkan nomor invoice untuk transaksi">
                <div class="invalid-feedback">Nomor Invoice wajib diisi jika menggunakan invoice.</div>
            </div>
            <div class="col-md-6" id="dueDateContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="due_date" class="form-label required">
                    <i class="fas fa-calendar-times"></i> Tanggal Jatuh Tempo
                </label>
                <input type="date" class="form-control" id="due_date" name="due_date"
                    value="{{ $dueDate }}" {{ $voucher->use_existing_invoice === 'yes' ? 'disabled' : '' }}
                    data-bs-toggle="tooltip" title="Tanggal jatuh tempo untuk pembayaran invoice">
                <div class="invalid-feedback">Tanggal Jatuh Tempo wajib diisi untuk invoice baru.</div>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6" id="storeFieldContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="store" class="form-label">
                    <i class="fas fa-store"></i> Nama Toko
                </label>
                <select class="form-select" id="store" name="store" data-bs-toggle="tooltip"
                    title="Pilih nama toko terkait transaksi">
                    <option value="">Pilih Nama Toko</option>
                    @foreach ($storeNames as $storeName)
                        <option value="{{ $storeName }}" {{ $voucher->store == $storeName ? 'selected' : '' }}>
                            {{ $storeName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Personnel Information Section -->
        <div class="section-header">
            <i class="fas fa-users"></i> Informasi Personel
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="preparedBy" class="form-label">
                    <i class="fas fa-user-edit"></i> Disiapkan Oleh
                </label>
                <input type="text" class="form-control" id="preparedBy" name="prepared_by"
                    value="{{ $voucher->prepared_by }}" readonly data-bs-toggle="tooltip"
                    title="Nama pembuat voucher">
            </div>
            <div class="col-md-4">
                <label for="givenTo" class="form-label">
                    <i class="fas fa-user-check"></i> Diberikan Kepada
                </label>
                <input type="text" class="form-control" id="givenTo" name="given_to"
                    value="{{ $voucher->given_to }}" data-bs-toggle="tooltip" title="Penerima voucher">
            </div>
            <div class="col-md-4">
                <label for="approvedBy" class="form-label">
                    <i class="fas fa-user-shield"></i> Disetujui Oleh
                </label>
                <input type="text" class="form-control" id="approvedBy" name="approved_by"
                    value="{{ $voucher->approved_by }}" readonly data-bs-toggle="tooltip"
                    title="Nama penyetuju voucher">
            </div>
        </div>

        <!-- Transaction Information Section -->
        <div class="section-header">
            <i class="fas fa-exchange-alt"></i> Informasi Transaksi
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="transaction" class="form-label">
                    <i class="fas fa-clipboard-list"></i> Transaksi
                </label>
                <input type="text" class="form-control" id="transaction" name="transaction"
                    value="{{ $voucher->transaction }}" data-bs-toggle="tooltip"
                    title="Detail transaksi terkait voucher">
            </div>
            <div class="col-md-6">
                <label for="store" class="form-label">
                    <i class="fas fa-store"></i> Nama Toko
                </label>
                <div id="storeFieldContainer">
                    <input type="text" class="form-control" id="store" name="store"
                        value="{{ $voucher->store }}" data-bs-toggle="tooltip" title="Nama toko terkait transaksi">
                </div>
            </div>
        </div>

        <!-- Transaction Details Section -->
        <div class="section-header">
            <i class="fas fa-list-alt"></i> Rincian Transaksi
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="transactionDetailsTable">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th><i class="fas fa-align-left"></i> Deskripsi</th>
                        <th><i class="fas fa-ruler"></i> Ukuran</th>
                        <th><i class="fas fa-sort-numeric-up"></i> Kuantitas</th>
                        <th><i class="fas fa-money-bill"></i> Nominal</th>
                        <th><i class="fas fa-calculator"></i> Total</th>
                        <th style="width: 100px;"><i class="fas fa-cogs"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($voucher->transactions->isNotEmpty())
                        @foreach ($voucher->transactions as $index => $transaction)
                            <tr data-row-index="{{ $index }}"
                                data-is-hpp-row="{{ str_starts_with($transaction->description, 'HPP ') ? 'true' : 'false' }}">
                                <td>
                                    @if (str_starts_with($transaction->description, 'HPP '))
                                        <input type="text" class="form-control descriptionInput"
                                            name="transactions[{{ $index }}][description]"
                                            value="{{ $transaction->description }}" readonly data-bs-toggle="tooltip"
                                            title="Deskripsi HPP otomatis">
                                    @elseif (in_array($voucher->voucher_type, ['PJ', 'RPJ']))
                                        <select class="form-control descriptionInput"
                                            name="transactions[{{ $index }}][description]"
                                            data-initial-value="{{ $transaction->description }}"
                                            data-bs-toggle="tooltip" title="Pilih nama stok">
                                            <option value="">Pilih Nama Stock</option>
                                            @foreach (array_merge($stocks) as $stock)
                                                @if (!str_starts_with($stock['item'], 'HPP '))
                                                    <option value="{{ $stock['item'] }}"
                                                        {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>
                                                        {{ $stock['item'] }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @elseif (in_array($voucher->voucher_type, ['PB', 'RPB', 'PYB', 'PYK']))
                                        <div class="input-group">
                                            <select class="form-control descriptionInput"
                                                name="transactions[{{ $index }}][description_select]"
                                                style="width: 50%;" data-bs-toggle="tooltip" title="Pilih nama stok">
                                                <option value="">Pilih Nama Stock</option>
                                                @if (in_array($voucher->voucher_type, ['PYB', 'PYK']))
                                                    @foreach (array_merge($stocks) as $stock)
                                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                                            <option value="{{ $stock['item'] }}"
                                                                {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>
                                                                {{ $stock['item'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    @foreach ($stocks as $stock)
                                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                                            <option value="{{ $stock['item'] }}"
                                                                {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>
                                                                {{ $stock['item'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                            <input type="text" class="form-control descriptionInput"
                                                name="transactions[{{ $index }}][description]"
                                                style="width: 50%;" value="{{ $transaction->description }}"
                                                data-bs-toggle="tooltip" title="Detail tambahan untuk item">
                                        </div>
                                    @else
                                        <input type="text" class="form-control descriptionInput"
                                            name="transactions[{{ $index }}][description]"
                                            value="{{ $transaction->description }}" data-bs-toggle="tooltip"
                                            title="Masukkan deskripsi transaksi">
                                    @endif
                                </td>
                                <td>
                                    @if (in_array($voucher->voucher_type, ['PJ', 'RPJ']) && !str_starts_with($transaction->description, 'HPP '))
                                        <select class="form-control sizeInput"
                                            name="transactions[{{ $index }}][size]" data-bs-toggle="tooltip"
                                            title="Pilih ukuran item">
                                            <option value="">Pilih Ukuran</option>
                                            @foreach (array_merge($stocks) as $stock)
                                                @if ($stock['item'] == $transaction->description)
                                                    <option value="{{ $stock['size'] }}"
                                                        {{ $transaction->size == $stock['size'] ? 'selected' : '' }}>
                                                        {{ $stock['size'] }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @elseif (in_array($voucher->voucher_type, ['PB', 'RPB']) && !str_starts_with($transaction->description, 'HPP '))
                                        <select class="form-control sizeInput"
                                            name="transactions[{{ $index }}][size]" data-bs-toggle="tooltip"
                                            title="Pilih ukuran item">
                                            <option value="">Pilih Ukuran</option>
                                            @foreach ($stocks as $stock)
                                                @if ($stock['item'] == $transaction->description)
                                                    <option value="{{ $stock['size'] }}"
                                                        {{ $transaction->size == $stock['size'] ? 'selected' : '' }}>
                                                        {{ $stock['size'] }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @elseif (in_array($voucher->voucher_type, ['PYB', 'PYK']) && !str_starts_with($transaction->description, 'HPP '))
                                        <select class="form-control sizeInput"
                                            name="transactions[{{ $index }}][size]" data-bs-toggle="tooltip"
                                            title="Pilih ukuran item">
                                            <option value="">Pilih Ukuran</option>
                                            @foreach (array_merge($stocks) as $stock)
                                                @if ($stock['item'] == $transaction->description)
                                                    <option value="{{ $stock['size'] }}"
                                                        {{ $transaction->size == $stock['size'] ? 'selected' : '' }}>
                                                        {{ $stock['size'] }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control sizeInput"
                                            name="transactions[{{ $index }}][size]"
                                            value="{{ $transaction->size }}"
                                            {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}
                                            data-bs-toggle="tooltip" title="Masukkan ukuran item">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" min="1" class="form-control quantityInput"
                                        name="transactions[{{ $index }}][quantity]"
                                        value="{{ $transaction->quantity }}"
                                        {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}
                                        data-bs-toggle="tooltip" title="Jumlah item dalam transaksi">
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01"
                                        class="form-control nominalInput"
                                        name="transactions[{{ $index }}][nominal]"
                                        value="{{ $transaction->nominal }}"
                                        {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}
                                        data-bs-toggle="tooltip" title="Harga satuan item">
                                </td>
                                <td>
                                    <input type="text" class="form-control totalInput"
                                        name="transactions[{{ $index }}][total]"
                                        value="{{ number_format($transaction->quantity * $transaction->nominal, 2, ',', '.') }}"
                                        readonly data-bs-toggle="tooltip" title="Total harga (kuantitas x nominal)">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm removeTransactionRowBtn"
                                        {{ str_starts_with($transaction->description, 'HPP ') ? 'disabled' : '' }}
                                        data-bs-toggle="tooltip" title="Hapus baris transaksi ini">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr data-row-index="0">
                            <td>
                                @if (in_array($voucher->voucher_type, ['PJ', 'RPJ']))
                                    <select class="form-control descriptionInput" name="transactions[0][description]"
                                        data-bs-toggle="tooltip" title="Pilih nama stok">
                                        <option value="">Pilih Nama Stock</option>
                                        @foreach (array_merge($stocks) as $stock)
                                            @if (!str_starts_with($stock['item'], 'HPP '))
                                                <option value="{{ $stock['item'] }}">{{ $stock['item'] }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @elseif (in_array($voucher->voucher_type, ['PB', 'RPB', 'PYB', 'PYK']))
                                    <div class="input-group">
                                        <select class="form-control descriptionInput"
                                            name="transactions[0][description_select]" style="width: 50%;"
                                            data-bs-toggle="tooltip" title="Pilih nama stok">
                                            <option value="">Pilih Nama Stock</option>
                                            @if (in_array($voucher->voucher_type, ['PYB', 'PYK']))
                                                @foreach (array_merge($stocks) as $stock)
                                                    @if (!str_starts_with($stock['item'], 'HPP '))
                                                        <option value="{{ $stock['item'] }}">{{ $stock['item'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach ($stocks as $stock)
                                                    @if (!str_starts_with($stock['item'], 'HPP '))
                                                        <option value="{{ $stock['item'] }}">{{ $stock['item'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="text" class="form-control descriptionInput"
                                            name="transactions[0][description]" style="width: 50%;"
                                            data-bs-toggle="tooltip" title="Detail tambahan untuk item">
                                    </div>
                                @else
                                    <input type="text" class="form-control descriptionInput"
                                        name="transactions[0][description]" data-bs-toggle="tooltip"
                                        title="Masukkan deskripsi transaksi">
                                @endif
                            </td>
                            <td>
                                @if (in_array($voucher->voucher_type, ['PJ', 'RPJ']))
                                    <select class="form-control sizeInput" name="transactions[0][size]"
                                        data-bs-toggle="tooltip" title="Pilih ukuran item">
                                        <option value="">Pilih Ukuran</option>
                                        @foreach (array_merge($stocks) as $stock)
                                            <option value="{{ $stock['size'] }}">{{ $stock['size'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif (in_array($voucher->voucher_type, ['PB', 'RPB']))
                                    <select class="form-control sizeInput" name="transactions[0][size]"
                                        data-bs-toggle="tooltip" title="Pilih ukuran item">
                                        <option value="">Pilih Ukuran</option>
                                        @foreach ($stocks as $stock)
                                            <option value="{{ $stock['size'] }}">{{ $stock['size'] }}</option>
                                        @endforeach
                                    </select>
                                @elseif (in_array($voucher->voucher_type, ['PYB', 'PYK']))
                                    <select class="form-control sizeInput" name="transactions[0][size]"
                                        data-bs-toggle="tooltip" title="Pilih ukuran item">
                                        <option value="">Pilih Ukuran</option>
                                        @foreach (array_merge($stocks) as $stock)
                                            <option value="{{ $stock['size'] }}">{{ $stock['size'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control sizeInput" name="transactions[0][size]"
                                        data-bs-toggle="tooltip" title="Masukkan ukuran item">
                                @endif
                            </td>
                            <td>
                                <input type="number" min="1" class="form-control quantityInput"
                                    name="transactions[0][quantity]" value="1" data-bs-toggle="tooltip"
                                    title="Jumlah item dalam transaksi">
                            </td>
                            <td>
                                <input type="number" min="0" step="0.01"
                                    class="form-control nominalInput" name="transactions[0][nominal]"
                                    data-bs-toggle="tooltip" title="Harga satuan item">
                            </td>
                            <td>
                                <input type="text" class="form-control totalInput" name="transactions[0][total]"
                                    value="0,00" readonly data-bs-toggle="tooltip"
                                    title="Total harga (kuantitas x nominal)">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeTransactionRowBtn"
                                    data-bs-toggle="tooltip" title="Hapus baris transaksi ini">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong><i class="fas fa-coins"></i> Total
                                Nominal:</strong></td>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="totalNominal" name="total_nominal"
                                    value="{{ number_format($voucher->total_nominal, 2, ',', '.') }}" readonly
                                    data-bs-toggle="tooltip" title="Total nominal dari semua transaksi">
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="p-3">
                <button type="button" id="addTransactionRowBtn" class="btn btn-primary" data-bs-toggle="tooltip"
                    title="Tambah baris transaksi baru">
                    <i class="fas fa-plus"></i> Tambah Transaksi
                </button>
            </div>
        </div>

        <!-- Voucher Details Section -->
        <div class="section-header">
            <i class="fas fa-clipboard-check"></i> Rincian Voucher
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="voucherDetailsTable">
                <thead class="table-success">
                    <tr class="text-center">
                        <th><i class="fas fa-code"></i> Kode Akun</th>
                        <th><i class="fas fa-tag"></i> Nama Akun</th>
                        <th><i class="fas fa-plus-circle text-success"></i> Debit</th>
                        <th><i class="fas fa-minus-circle text-danger"></i> Kredit</th>
                        <th style="width: 100px;"><i class="fas fa-cogs"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody id="voucherDetailsTableBody">
                    @foreach ($voucher->voucherDetails as $index => $detail)
                        <tr>
                            <td>
                                <input type="text" class="form-control accountCodeInput"
                                    name="voucher_details[{{ $index }}][account_code]"
                                    list="dynamicAccountCodes" value="{{ $detail->account_code }}"
                                    placeholder="Ketik atau pilih kode akun" required aria-required="true"
                                    data-bs-toggle="tooltip" title="Masukkan atau pilih kode akun">
                                <datalist id="dynamicAccountCodes">
                                    <option value="">Pilih Kode Akun</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->account_code }}">
                                            {{ $account->account_code }} - {{ $account->account_name }}</option>
                                    @endforeach
                                    @foreach ($subsidiariesData as $subsidiary)
                                        <option value="{{ $subsidiary['subsidiary_code'] }}">
                                            {{ $subsidiary['subsidiary_code'] }} - {{ $subsidiary['account_name'] }}
                                        </option>
                                    @endforeach
                                </datalist>
                                <div class="invalid-feedback">Kode Akun wajib diisi.</div>
                            </td>
                            <td>
                                <input type="text" class="form-control accountName"
                                    name="voucher_details[{{ $index }}][account_name]"
                                    value="{{ $detail->account_name }}" readonly data-bs-toggle="tooltip"
                                    title="Nama akun berdasarkan kode akun">
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" min="0" step="0.01"
                                        class="form-control debitInput"
                                        name="voucher_details[{{ $index }}][debit]"
                                        value="{{ $detail->debit > 0 ? $detail->debit : '' }}"
                                        data-bs-toggle="tooltip" title="Masukkan jumlah debit">
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" min="0" step="0.01"
                                        class="form-control creditInput"
                                        name="voucher_details[{{ $index }}][credit]"
                                        value="{{ $detail->credit > 0 ? $detail->credit : '' }}"
                                        data-bs-toggle="tooltip" title="Masukkan jumlah kredit">
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeVoucherDetailRowBtn"
                                    data-bs-toggle="tooltip" title="Hapus baris akun ini">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-3">
                <button type="button" id="addVoucherDetailRowBtn" class="btn btn-primary" data-bs-toggle="tooltip"
                    title="Tambah baris akun baru">
                    <i class="fas fa-plus"></i> Tambah Kode Akun
                </button>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="section-header">
            <i class="fas fa-chart-line"></i> Ringkasan & Validasi
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="totalDebit" class="form-label">
                    <i class="fas fa-plus-circle text-success"></i> Total Debit
                </label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="totalDebit" name="total_debit_formatted"
                        value="{{ number_format($voucher->total_debit, 2, ',', '.') }}" readonly
                        data-bs-toggle="tooltip" title="Total jumlah debit dari rincian voucher">
                    <input type="hidden" id="totalDebitRaw" name="total_debit_raw"
                        value="{{ $voucher->total_debit }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="totalCredit" class="form-label">
                    <i class="fas fa-minus-circle text-danger"></i> Total Kredit
                </label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="totalCredit" name="total_credit_formatted"
                        value="{{ number_format($voucher->total_credit, 2, ',', '.') }}" readonly
                        data-bs-toggle="tooltip" title="Total jumlah kredit dari rincian voucher">
                    <input type="hidden" id="totalCreditRaw" name="total_credit_raw"
                        value="{{ $voucher->total_credit }}">
                </div>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-12">
                <label for="validation" class="form-label">
                    <i class="fas fa-check-circle"></i> Status Validasi
                </label>
                <input type="text" class="form-control validation-message" id="validation"
                    value="Silakan isi formulir." readonly data-bs-toggle="tooltip" title="Status validasi formulir">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn btn-primary" id="saveVoucherBtn" data-bs-toggle="tooltip"
                title="Simpan perubahan pada voucher">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Element References ---
        const voucherForm = document.getElementById('voucherForm');
        const transactionTableBody = document.querySelector('#transactionDetailsTable tbody');
        const addTransactionRowBtn = document.getElementById('addTransactionRowBtn');
        const voucherDetailsTableBody = document.querySelector('#voucherDetailsTable tbody');
        const addVoucherDetailRowBtn = document.getElementById('addVoucherDetailRowBtn');
        const totalDebitInput = document.getElementById('totalDebit');
        const totalCreditInput = document.getElementById('totalCredit');
        const totalNominalInput = document.getElementById('totalNominal');
        const totalDebitRawInput = document.getElementById('totalDebitRaw');
        const totalCreditRawInput = document.getElementById('totalCreditRaw');
        const validationInput = document.getElementById('validation');
        const saveVoucherBtn = document.getElementById('saveVoucherBtn');
        const voucherTypeSelect = document.getElementById('voucherType');
        const useStockYes = document.getElementById('useStockYes');
        const useStockNo = document.getElementById('useStockNo');
        const useInvoiceYes = document.getElementById('useInvoiceYes');
        const useInvoiceNo = document.getElementById('useInvoiceNo');
        const invoiceFieldContainer = document.getElementById('invoiceFieldContainer');
        const dueDateContainer = document.getElementById('dueDateContainer');
        const storeFieldContainer = document.getElementById('storeFieldContainer');
        const existingInvoiceContainer = document.getElementById('existingInvoiceContainer');
        const useExistingInvoiceYes = document.getElementById('useExistingInvoiceYes');
        const useExistingInvoiceNo = document.getElementById('useExistingInvoiceNo');
        const descriptionTextArea = document.getElementById('description');

        // --- Data from Laravel ---
        const existingInvoices = @json($existingInvoices);
        const storeNames = @json($storeNames);
        const subsidiaries = @json($subsidiariesData);
        const accounts = @json($accountsData);
        const stocks = @json($stocks);
        const transactionsData = @json($transactionsData);
        const currentVoucherType = @json($voucher->voucher_type);
        const hasInvoice = @json($voucher->invoice ? true : false);
        // const voucherCreatedAt = @json($voucher->created_at ? $voucher->created_at : now() . toISOString());
        const voucherCreatedAt = @json($voucherCreatedAt ?? now()->toIso8601String());
        // --- Voucher Types ---
        const voucherTypes = {
            PJ: {
                value: 'PJ',
                text: 'Penjualan',
                description: 'Voucher Penjualan digunakan untuk mencatat transaksi penjualan barang atau jasa yang dilakukan oleh perusahaan kepada pelanggan, yang tidak dapat dicatat pada jenis voucher lain. Voucher ini mencakup detail seperti nama barang, jumlah, harga jual, dan informasi pelanggan. Contoh penggunaan termasuk penjualan produk jadi, jasa layanan, atau barang dagang. Voucher ini juga dapat digunakan untuk menghitung Harga Pokok Penjualan (HPP) untuk mencatat biaya barang yang dijual, memastikan akurasi laporan keuangan.'
            },
            PB: {
                value: 'PB',
                text: 'Pembelian',
                description: 'Voucher Pembelian digunakan untuk mendokumentasikan transaksi pembelian barang atau jasa dari pemasok atau vendor. Voucher ini mencatat detail seperti nama barang, ukuran, jumlah, harga satuan, dan total biaya pembelian. Contoh penggunaan meliputi pembelian bahan baku, peralatan kantor, atau layanan seperti perawatan mesin. Voucher ini penting untuk memperbarui stok barang di sistem inventaris dan mencatat kewajiban pembayaran kepada pemasok dalam laporan keuangan.'
            },
            PG: {
                value: 'PG',
                text: 'Pengeluaran',
                description: 'Voucher Pengeluaran digunakan untuk mencatat semua pengeluaran dana perusahaan, baik dalam bentuk tunai, transfer bank, maupun metode pembayaran lainnya. Voucher ini berfungsi sebagai bukti otorisasi transaksi pengeluaran, seperti pembayaran tagihan listrik, sewa kantor, gaji karyawan, atau pembelian material kecil. Voucher ini mencakup informasi seperti penerima dana, jumlah, dan tujuan pengeluaran, memastikan bahwa semua pengeluaran terdokumentasi dengan baik untuk audit dan pelaporan keuangan.'
            },
            PM: {
                value: 'PM',
                text: 'Pemasukan',
                description: 'Voucher Pemasukan digunakan untuk mencatat semua penerimaan dana yang masuk ke kas atau rekening bank perusahaan. Ini mencakup pembayaran dari pelanggan atas penjualan barang atau jasa, setoran tunai, bunga bank, atau penerimaan dana lain seperti pengembalian pinjaman. Voucher ini mencatat detail seperti sumber dana, jumlah, dan tanggal penerimaan, memastikan bahwa semua pemasukan didokumentasikan dengan akurat untuk pelaporan keuangan dan rekonsiliasi kas.'
            },
            LN: {
                value: 'LN',
                text: 'Lainnya',
                description: 'Voucher Lainnya digunakan untuk mencatat transaksi khusus atau non-standar yang tidak termasuk dalam kategori voucher lain seperti Penjualan, Pembelian, Pengeluaran, atau Pemasukan. Voucher ini mencakup transaksi seperti donasi, hadiah, penyelesaian sengketa keuangan, atau transaksi internal khusus seperti pemindahan dana antar akun perusahaan tanpa kaitan dengan operasional rutin. Contoh penggunaan termasuk pencatatan penerimaan hibah dari pihak eksternal, pembayaran denda atau penalti, atau transaksi barter barang yang tidak memengaruhi stok. Voucher ini mencatat detail seperti deskripsi transaksi, jumlah, pihak terkait, dan akun yang terpengaruh, memastikan dokumentasi yang jelas untuk pelaporan keuangan dan kepatuhan audit.'
            },
            PYB: {
                value: 'PYB',
                text: 'Penyesuaian Bertambah',
                description: 'Voucher Penyesuaian Bertambah digunakan untuk mencatat penambahan stok barang akibat penyesuaian, seperti penerimaan barang tambahan dari pemasok, temuan stok yang tidak tercatat, atau koreksi kesalahan inventaris. Voucher ini mencatat detail seperti nama barang, ukuran, jumlah tambahan, dan alasan penyesuaian. Contoh penggunaan termasuk menambahkan stok setelah audit fisik menemukan kelebihan barang. Voucher ini memastikan akurasi data inventaris dalam sistem.'
            },
            PYK: {
                value: 'PYK',
                text: 'Penyesuaian Berkurang',
                description: 'Voucher Penyesuaian Berkurang digunakan untuk mencatat pengurangan stok barang akibat penyesuaian, seperti kerusakan barang, kehilangan stok, atau koreksi kesalahan inventaris. Voucher ini mencatat detail seperti nama barang, ukuran, jumlah yang dikurangi, dan alasan penyesuaian. Contoh penggunaan termasuk pengurangan stok karena barang rusak selama penyimpanan atau pencurian. Voucher ini membantu menjaga integritas data inventaris dan laporan keuangan.'
            },
            PYL: {
                value: 'PYL',
                text: 'Penyesuaian Lainnya',
                description: 'Voucher Penyesuaian Lainnya digunakan untuk mencatat penyesuaian yang tidak memengaruhi jumlah stok fisik, tetapi memengaruhi catatan akuntansi atau data lainnya, seperti penyesuaian nilai aset, penyusutan, atau koreksi harga barang. Voucher ini mencatat detail seperti deskripsi penyesuaian, akun yang terpengaruh, dan jumlah. Contoh penggunaan termasuk penyesuaian nilai buku barang karena perubahan harga pasar. Voucher ini memastikan akurasi laporan keuangan tanpa mengubah stok fisik.'
            },
            RPB: {
                value: 'RPB',
                text: 'Retur Pembelian',
                description: 'Voucher Retur Pembelian digunakan untuk mencatat pengembalian barang yang telah dibeli dari pemasok karena alasan seperti barang cacat, tidak sesuai pesanan, atau kerusakan. Voucher ini mencatat detail seperti nama barang, ukuran, jumlah yang dikembalikan, dan alasan retur. Contoh penggunaan termasuk pengembalian bahan baku yang tidak memenuhi standar kualitas. Voucher ini penting untuk memperbarui stok barang di sistem inventaris dan mencatat pengembalian dana atau kredit kepada pemasok dalam laporan keuangan.'
            },
            RPJ: {
                value: 'RPJ',
                text: 'Retur Penjualan',
                description: 'Voucher Retur Penjualan digunakan untuk mencatat pengembalian barang dari pelanggan ke perusahaan karena alasan seperti barang cacat, salah kirim, atau ketidaksesuaian dengan pesanan. Voucher ini mencatat detail seperti nama barang, ukuran, jumlah yang dikembalikan, dan alasan retur. Contoh penggunaan termasuk pengembalian produk jadi oleh pelanggan karena kerusakan. Voucher ini penting untuk memperbarui stok barang di sistem inventaris dan mencatat pengembalian dana atau kredit kepada pelanggan dalam laporan keuangan.'
            }
        };
        const voucherTypeOptions = Object.values(voucherTypes);

        // --- Update Voucher Type Dropdown ---
        function updateVoucherTypeOptions() {
            const useStock = useStockYes.checked ? 'yes' : 'no';
            voucherTypeSelect.innerHTML = '';
            const validOptions = voucherTypeOptions.filter(option => useStock === 'yes' ? option.stock : !option
                .stock);

            // Pastikan currentVoucherType tetap ada sebagai opsi terpilih
            let selectedFound = false;
            validOptions.forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.text;
                if (option.value === currentVoucherType) {
                    opt.selected = true;
                    selectedFound = true;
                }
                voucherTypeSelect.appendChild(opt);
            });

            // Jika currentVoucherType tidak ada dalam validOptions, tambahkan sebagai opsi terpilih
            if (!selectedFound && currentVoucherType) {
                const opt = document.createElement('option');
                opt.value = currentVoucherType;
                opt.textContent = voucherTypes[currentVoucherType]?.text || currentVoucherType;
                opt.selected = true;
                voucherTypeSelect.appendChild(opt);
            }

            // Pastikan nilai dipilih jika ada opsi
            if (voucherTypeSelect.value === '' && voucherTypeSelect.options.length > 0) {
                voucherTypeSelect.value = currentVoucherType || voucherTypeSelect.options[0].value;
            }

            updateDescription();
            refreshTransactionTable();
            updateAllCalculationsAndValidations();
        }
        // --- Update Description ---
        function updateDescription() {
            const selectedType = voucherTypeSelect.value;
            descriptionTextArea.value = voucherTypes[selectedType]?.description || '';
        }

        function populateTransactionTableFromVoucher() {
            transactionTableBody.innerHTML = '';
            transactionsData.forEach((item, index) => {
                const row = generateTransactionTableRow(index, {
                    description: item.description,
                    size: item.size,
                    quantity: item.quantity,
                    nominal: item.nominal || 0,
                    total: (item.quantity * (item.nominal || 0)).toFixed(2),
                    isHppRow: item.description.startsWith('HPP ')
                });
                transactionTableBody.appendChild(row);
            });
            if (transactionTableBody.querySelectorAll('tr').length === 0) {
                const newRow = generateTransactionTableRow(0);
                transactionTableBody.appendChild(newRow);
                if (useStockYes.checked && ['PJ', 'PB', 'PYB', 'PYK', 'RPJ', 'RPB'].includes(
                        voucherTypeSelect.value)) {
                    updateSizeDropdown(newRow, '');
                }
            }
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            updateAllCalculationsAndValidations();
        }
        // --- Stock and Size Handling ---
        function getStockSource() {
            const voucherType = voucherTypeSelect.value;
            let stockData = []; // Default to empty array

            if (voucherType === 'PJ' || voucherType === 'RPJ') {
                const stockItems = [...new Set([...(stocks || [])].map(s => s.item + s
                    .size))];
                const combinedStocks = [];
                stockItems.forEach(key => {
                    const stock = (stocks || []).find(s => (s.item + s.size) === key);
                    if (stock) combinedStocks.push(stock);
                });
                return combinedStocks;
            } else if (voucherType === 'PYB' || voucherType === 'PYK') {
                stockData = [...(stocks || [])];
            } else if (voucherType === 'PB' || voucherType === 'RPB') {
                stockData = stocks || [];
            }

            return stockData;
        }

        function getCorrespondingHppRow(parentRow, item) {
            let nextRow = parentRow.nextElementSibling;
            while (nextRow) {
                if (nextRow.dataset.isHppRow === 'true' && nextRow.querySelector('.descriptionInput')?.value
                    .trim() === `HPP ${item.trim()}`) {
                    return nextRow;
                }
                // Stop jika hit row non-HPP (parent lain)
                if (nextRow.dataset.isHppRow !== 'true') break;
                nextRow = nextRow.nextElementSibling;
            }
            return null;
        }

        function createStockDropdown(index, initialValue = '') {
            const select = document.createElement('select');
            select.className = 'form-control descriptionInput';
            select.name = `transactions[${index}][description]`;
            select.dataset.listenerAttached = 'false';
            select.innerHTML = '<option value="">Pilih Nama Stock</option>';

            const stockSource = getStockSource();
            const filteredStocks = stockSource.filter(s => !s.item.startsWith('HPP '));
            const uniqueItems = [...new Set(filteredStocks.map(s => s.item))];

            if (voucherTypeSelect.value === 'PJ' || voucherTypeSelect.value === 'RPJ') {
                // Add Bahan Baku (stocks)
                const bahanBakuItems = [...new Set(stocks.filter(s => !s.item.startsWith('HPP ')).map(s => s
                    .item))];
                if (bahanBakuItems.length > 0) {
                    const optgroupBahanBaku = document.createElement('optgroup');
                    optgroupBahanBaku.label = '--Bahan Baku--';
                    bahanBakuItems.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item;
                        option.textContent = item;
                        if (item === initialValue) option.selected = true;
                        optgroupBahanBaku.appendChild(option);
                    });
                    select.appendChild(optgroupBahanBaku);
                }
            } else {
                uniqueItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = item;
                    if (item === initialValue) option.selected = true;
                    select.appendChild(option);
                });
            }
            select.addEventListener('focus', (e) => {
                e.target.dataset.oldValue = e.target.value;
            });

            select.addEventListener('change', (e) => {
                const oldItem = e.target.dataset.oldValue.trim();
                const newItem = e.target.value.trim();
                const row = e.target.closest('tr');
                const index = parseInt(row.dataset.rowIndex);
                if (oldItem && oldItem !== newItem && voucherTypeSelect.value === 'PJ' ||
                    voucherTypeSelect.value === 'RPJ') {
                    // Hapus corresponding HPP untuk oldItem
                    const oldHppRow = getCorrespondingHppRow(row, oldItem);
                    if (oldHppRow) {
                        oldHppRow.remove();
                        updateTransactionRowIndices();
                    }
                }
                updateSizeDropdown(row, newItem);
                if ((voucherTypeSelect.value === 'PJ' || voucherTypeSelect.value === 'RPJ') &&
                    newItem) {
                    const sizeSelect = row.querySelector('.sizeInput');
                    const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                    let size = sizeSelect.value;
                    if (sizeSelect.options.length > 1 && !size) {
                        sizeSelect.selectedIndex = 1; // Auto-select first size jika belum dipilih
                        size = sizeSelect.value;
                    }
                    if (size) {
                        updateHppRow(row, newItem, size, quantity,
                            voucherCreatedAt); // Pass row bukan index
                    }
                }
                updateAllCalculationsAndValidations();
            });

            return select;
        }

        function createSizeDropdown(index, item = '', initialValue = '') {
            const select = document.createElement('select');
            select.className = 'form-control sizeInput';
            select.name = `transactions[${index}][size]`;
            select.innerHTML = '<option value="">Pilih Ukuran</option>';
            if (item) {
                const stockSource = getStockSource();
                const sizes = stockSource.filter(s => s.item === item).map(s => s.size);
                sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size;
                    option.textContent = size;
                    if (size === initialValue) option.selected = true;
                    select.appendChild(option);
                });
            }
            select.addEventListener('change', (e) => {
                const row = e.target.closest('tr');
                const item = row.querySelector('.descriptionInput:not([type="text"])')?.value.trim() ||
                    row.querySelector('.descriptionInput[type="text"]')?.value.trim() || '';
                const newSize = e.target.value.trim();
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                if ((voucherTypeSelect.value === 'PJ' || voucherTypeSelect.value === 'RPJ') && item &&
                    newSize) {
                    updateHppRow(row, item, newSize, quantity,
                        voucherCreatedAt); // Update corresponding HPP
                }
                updateAllCalculationsAndValidations();
            });
            return select;
        }

        function updateSizeDropdown(row, item) {
            const index = row.dataset.rowIndex;
            const sizeCell = row.querySelector('td:nth-child(2)');
            const currentSize = row.querySelector('.sizeInput')?.value || '';
            sizeCell.innerHTML = '';
            const sizeSelect = createSizeDropdown(index, item, currentSize);
            sizeCell.appendChild(sizeSelect);
            sizeSelect.addEventListener('change', () => {
                updateAllCalculationsAndValidations();
                if ((voucherTypeSelect.value === 'PJ' || voucherTypeSelect.value === 'RPJ')) {
                    const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                    updateHppRow(index, item, currentSize, quantity);
                }
            });
        }

        // --- HPP Logic ---
        function calculateAverageHpp(item, size, currentCreatedAt) {

            if (!transactionsData || !Array.isArray(transactionsData)) {
                validationInput.value = 'Data transaksi historis kosong.';
                return 0;
            }
            if (!currentCreatedAt) {
                currentCreatedAt = new Date().toISOString();
            }
            const matchingTransactions = transactionsData.filter(t =>
                t.description === item &&
                t.size === size &&
                !t.description.startsWith('HPP ') &&
                t.created_at &&
                new Date(t.created_at) < new Date(currentCreatedAt)
            );
            if (matchingTransactions.length === 0) {
                validationInput.value =
                    `Tidak ada transaksi sebelumnya untuk item "${item}" dengan ukuran "${size}".`;
                return 0;
            }
            const totalNominal = matchingTransactions.reduce((sum, t) => sum + (parseFloat(t.nominal) || 0), 0);
            const average = totalNominal / matchingTransactions.length;
            return Math.round(average);
        }

        function addHppRow(currentIndex, item, size, quantity, currentCreatedAt) {
            if (!item || !size) return;

            const allRows = Array.from(transactionTableBody.querySelectorAll('tr'));
            const existingHppRow = allRows.find(row =>
                row.dataset.isHppRow === 'true' &&
                row.querySelector('.descriptionInput')?.value === `HPP ${item}` &&
                row.querySelector('.sizeInput')?.value === size
            );

            if (existingHppRow) {
                const quantityInput = existingHppRow.querySelector('.quantityInput');
                const nominalInput = existingHppRow.querySelector('.nominalInput');
                const totalInput = existingHppRow.querySelector('.totalInput');
                quantityInput.value = quantity;
                const averageHpp = calculateAverageHpp(item, size, currentCreatedAt);
                nominalInput.value = averageHpp.toFixed(2);
                totalInput.value = (quantity * averageHpp).toFixed(2);
            } else {
                const newIndex = transactionTableBody.querySelectorAll('tr').length;
                const hppRow = document.createElement('tr');
                hppRow.dataset.rowIndex = newIndex;
                hppRow.dataset.isHppRow = 'true';

                const descriptionCell = document.createElement('td');
                const descriptionInput = document.createElement('input');
                descriptionInput.type = 'text';
                descriptionInput.className = 'form-control descriptionInput';
                descriptionInput.name = `transactions[${newIndex}][description]`;
                descriptionInput.value = `HPP ${item}`;
                descriptionInput.readOnly = true;
                descriptionCell.appendChild(descriptionInput);
                hppRow.appendChild(descriptionCell);

                const sizeCell = document.createElement('td');
                const sizeInput = document.createElement('input');
                sizeInput.type = 'text';
                sizeInput.className = 'form-control sizeInput';
                sizeInput.name = `transactions[${newIndex}][size]`;
                sizeInput.value = size;
                sizeInput.readOnly = true;
                sizeCell.appendChild(sizeInput);
                hppRow.appendChild(sizeCell);

                const quantityCell = document.createElement('td');
                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.min = '1';
                quantityInput.className = 'form-control quantityInput';
                quantityInput.name = `transactions[${newIndex}][quantity]`;
                quantityInput.value = quantity;
                quantityInput.readOnly = true;
                quantityCell.appendChild(quantityInput);
                hppRow.appendChild(quantityCell);

                const nominalCell = document.createElement('td');
                const nominalInput = document.createElement('input');
                nominalInput.type = 'number';
                nominalInput.min = '0';
                nominalInput.className = 'form-control nominalInput';
                nominalInput.name = `transactions[${newIndex}][nominal]`;
                const averageHpp = calculateAverageHpp(item, size, currentCreatedAt);
                nominalInput.value = averageHpp.toFixed(2);
                nominalInput.readOnly = true;
                nominalCell.appendChild(nominalInput);
                hppRow.appendChild(nominalCell);

                const totalCell = document.createElement('td');
                const totalInput = document.createElement('input');
                totalInput.type = 'number';
                totalInput.className = 'form-control totalInput';
                totalInput.name = `transactions[${newIndex}][total]`;
                totalInput.value = (quantity * averageHpp).toFixed(2);
                totalInput.readOnly = true;
                totalCell.appendChild(totalInput);
                hppRow.appendChild(totalCell);

                const actionCell = document.createElement('td');
                actionCell.className = 'text-center';
                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
                deleteButton.textContent = 'Hapus';
                deleteButton.disabled = true;
                actionCell.appendChild(deleteButton);
                hppRow.appendChild(actionCell);

                const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
                if (currentRow && currentRow.nextSibling) {
                    transactionTableBody.insertBefore(hppRow, currentRow.nextSibling);
                } else {
                    transactionTableBody.appendChild(hppRow);
                }

                updateTransactionRowIndices();
                updateAllCalculationsAndValidations();
            }
        }

        function addHppRowDirectly(index, description, size, quantity, nominal, currentCreatedAt) {
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const hppRow = document.createElement('tr');
            hppRow.dataset.rowIndex = newIndex;
            hppRow.dataset.isHppRow = 'true';

            const descriptionCell = document.createElement('td');
            const descriptionInput = document.createElement('input');
            descriptionInput.type = 'text';
            descriptionInput.className = 'form-control descriptionInput';
            descriptionInput.name = `transactions[${newIndex}][description]`;
            descriptionInput.value = `HPP ${description}`;
            descriptionInput.readOnly = true;
            descriptionCell.appendChild(descriptionInput);
            hppRow.appendChild(descriptionCell);

            const sizeCell = document.createElement('td');
            const sizeInput = document.createElement('input');
            sizeInput.type = 'text';
            sizeInput.className = 'form-control sizeInput';
            sizeInput.name = `transactions[${newIndex}][size]`;
            sizeInput.value = size;
            sizeInput.readOnly = true;
            sizeCell.appendChild(sizeInput);
            hppRow.appendChild(sizeCell);

            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${newIndex}][quantity]`;
            quantityInput.value = quantity;
            quantityInput.readOnly = true;
            quantityCell.appendChild(quantityInput);
            hppRow.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${newIndex}][nominal]`;
            // Konversi nominal ke angka dan tangani nilai tidak valid
            const nominalValue = parseFloat(nominal) || 0;
            nominalInput.value = nominalValue.toFixed(2);
            nominalInput.readOnly = true;
            nominalCell.appendChild(nominalInput);
            hppRow.appendChild(nominalCell);

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'number';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${newIndex}][total]`;
            totalInput.value = (quantity * nominalValue).toFixed(2);
            totalInput.readOnly = true;
            totalCell.appendChild(totalInput);
            hppRow.appendChild(totalCell);

            const actionCell = document.createElement('td');
            actionCell.className = 'text-center';
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
            deleteButton.textContent = 'Hapus';
            deleteButton.disabled = true;
            actionCell.appendChild(deleteButton);
            hppRow.appendChild(actionCell);

            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${index}"]`);
            if (currentRow.nextSibling) {
                transactionTableBody.insertBefore(hppRow, currentRow.nextSibling);
            } else {
                transactionTableBody.appendChild(hppRow);
            }

            updateTransactionRowIndices();
            updateAllCalculationsAndValidations();
        }

        function updateHppRow(parentRow, item, size, quantity, currentCreatedAt) {
            if (!item || !size || isUpdatingHpp) {
                return;
            }
            item = item.trim();
            size = size.trim();
            currentCreatedAt = currentCreatedAt || voucherCreatedAt || new Date().toISOString();

            isUpdatingHpp = true;
            try {
                const averageHpp = calculateAverageHpp(item, size, currentCreatedAt);
                const hppRow = getCorrespondingHppRow(parentRow, item);

                if (hppRow) {
                    // Update existing corresponding HPP row=
                    hppRow.querySelector('.sizeInput').value = size;
                    hppRow.querySelector('.quantityInput').value = quantity;
                    hppRow.querySelector('.nominalInput').value = averageHpp.toFixed(2);
                    hppRow.querySelector('.totalInput').value = (quantity * averageHpp).toFixed(2);
                } else {
                    // Create new HPP row after parent
                    const newIndex = transactionTableBody.querySelectorAll('tr').length;
                    const hppRow = document.createElement('tr');
                    hppRow.dataset.rowIndex = newIndex;
                    hppRow.dataset.isHppRow = 'true';

                    const descriptionCell = document.createElement('td');
                    const descriptionInput = document.createElement('input');
                    descriptionInput.type = 'text';
                    descriptionInput.className = 'form-control descriptionInput';
                    descriptionInput.name = `transactions[${newIndex}][description]`;
                    descriptionInput.value = `HPP ${item}`;
                    descriptionInput.readOnly = true;
                    descriptionCell.appendChild(descriptionInput);
                    hppRow.appendChild(descriptionCell);

                    const sizeCell = document.createElement('td');
                    const sizeInput = document.createElement('input');
                    sizeInput.type = 'text';
                    sizeInput.className = 'form-control sizeInput';
                    sizeInput.name = `transactions[${newIndex}][size]`;
                    sizeInput.value = size;
                    sizeInput.readOnly = true;
                    sizeCell.appendChild(sizeInput);
                    hppRow.appendChild(sizeCell);

                    const quantityCell = document.createElement('td');
                    const quantityInput = document.createElement('input');
                    quantityInput.type = 'number';
                    quantityInput.min = '1';
                    quantityInput.className = 'form-control quantityInput';
                    quantityInput.name = `transactions[${newIndex}][quantity]`;
                    quantityInput.value = quantity;
                    quantityInput.readOnly = true;
                    quantityCell.appendChild(quantityInput);
                    hppRow.appendChild(quantityCell);

                    const nominalCell = document.createElement('td');
                    const nominalInput = document.createElement('input');
                    nominalInput.type = 'number';
                    nominalInput.min = '0';
                    nominalInput.className = 'form-control nominalInput';
                    nominalInput.name = `transactions[${newIndex}][nominal]`;
                    nominalInput.value = averageHpp.toFixed(2);
                    nominalInput.readOnly = true;
                    nominalCell.appendChild(nominalInput);
                    hppRow.appendChild(nominalCell);

                    const totalCell = document.createElement('td');
                    const totalInput = document.createElement('input');
                    totalInput.type = 'number';
                    totalInput.className = 'form-control totalInput';
                    totalInput.name = `transactions[${newIndex}][total]`;
                    totalInput.value = (quantity * averageHpp).toFixed(2);
                    totalInput.readOnly = true;
                    totalCell.appendChild(totalInput);
                    hppRow.appendChild(totalCell);

                    const actionCell = document.createElement('td');
                    actionCell.className = 'text-center';
                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
                    deleteButton.textContent = 'Hapus';
                    deleteButton.disabled = true;
                    actionCell.appendChild(deleteButton);
                    hppRow.appendChild(actionCell);

                    parentRow.parentNode.insertBefore(hppRow, parentRow.nextSibling);
                    updateTransactionRowIndices();
                }
                updateAllCalculationsAndValidations();
            } finally {
                isUpdatingHpp = false;
            }
        }

        // --- Stock Validation ---
        function validateStockQuantity(item, size, quantity) {
            const voucherType = voucherTypeSelect.value;
            if (!['PJ'].includes(voucherType)) {
                return true;
            }
            const stockSource = getStockSource();
            const stock = stockSource.find(s => s.item === item && s.size === size);
            if (!stock) {
                validationInput.value = `Stok untuk item "${item}" dengan ukuran "${size}" tidak ditemukan.`;
                return false;
            }
            const availableQuantity = parseFloat(stock.quantity) || 0;
            if (quantity > availableQuantity) {
                validationInput.value =
                    `Kuantitas untuk item "${item}" dengan ukuran "${size}" melebihi stok tersedia (${availableQuantity}).`;
                return false;
            }
            return true;
        }

        // --- Transaction Table Row Generation ---
        function generateTransactionTableRow(index, transactionData = null) {
            const row = document.createElement('tr');
            row.dataset.rowIndex = index;
            row.dataset.isHppRow = transactionData?.isHppRow ? 'true' : 'false';

            const voucherType = voucherTypeSelect.value;
            const useStock = useStockYes.checked ? 'yes' : 'no';
            const isHppRow = row.dataset.isHppRow === 'true';

            // Description Cell
            const descriptionCell = document.createElement('td');
            let descriptionElement;
            if (isHppRow) {
                descriptionElement = document.createElement('input');
                descriptionElement.type = 'text';
                descriptionElement.className = 'form-control descriptionInput';
                descriptionElement.name = `transactions[${index}][description]`;
                descriptionElement.value = transactionData?.description || '';
                descriptionElement.readOnly = true;
            } else if (useStock === 'yes' && ['PJ', 'PYB', 'PYK', 'RPB', 'RPJ'].includes(
                    voucherType)) {
                descriptionElement = createStockDropdown(index, transactionData?.description);
                descriptionElement.addEventListener('change', (e) => {
                    const row = e.target.closest('tr');
                    const item = e.target.value;
                    const index = parseInt(row.dataset.rowIndex);
                    updateSizeDropdown(row, item);
                    if (voucherType === 'PJ' && item) {
                        const sizeSelect = row.querySelector('.sizeInput');
                        const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                        if (sizeSelect.options.length > 1) {
                            sizeSelect.selectedIndex = 1; // Auto-select first size
                            const size = sizeSelect.value;
                            updateHppRow(index, item, size, quantity, voucherCreatedAt);
                        }
                    }
                    updateAllCalculationsAndValidations();
                });
            } else if ((voucherType === 'PB' || voucherType === 'RPB' || voucherType === 'PYB') && useStock ===
                'yes') {
                const inputGroup = document.createElement('div');
                inputGroup.className = 'input-group';
                const select = createStockDropdown(index, transactionData?.description_select || transactionData
                    ?.description);
                select.name = `transactions[${index}][description_select]`;
                select.style.width = '50%';
                select.addEventListener('change', (e) => {
                    const row = e.target.closest('tr');
                    const item = e.target.value;
                    const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
                    descriptionInput.value = item;
                    updateSizeDropdown(row, item);
                    updateAllCalculationsAndValidations();
                });
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control descriptionInput';
                input.name = `transactions[${index}][description]`;
                input.value = transactionData?.description || '';
                input.style.width = '50%';
                input.addEventListener('input', () => updateAllCalculationsAndValidations());
                inputGroup.appendChild(select);
                inputGroup.appendChild(input);
                descriptionElement = inputGroup;
            } else {
                descriptionElement = document.createElement('input');
                descriptionElement.type = 'text';
                descriptionElement.className = 'form-control descriptionInput';
                descriptionElement.name = `transactions[${index}][description]`;
                descriptionElement.value = transactionData?.description || '';
                descriptionElement.addEventListener('input', () => updateAllCalculationsAndValidations());
            }
            descriptionCell.appendChild(descriptionElement);
            row.appendChild(descriptionCell);

            // Size Cell
            const sizeCell = document.createElement('td');
            let sizeElement;
            if (isHppRow || useStock !== 'yes' || !['PJ', 'PB', 'PYB', 'PYK', 'RPB', 'RPJ']
                .includes(voucherType)) {
                sizeElement = document.createElement('input');
                sizeElement.type = 'text';
                sizeElement.className = 'form-control sizeInput';
                sizeElement.name = `transactions[${index}][size]`;
                sizeElement.value = transactionData?.size || '';
                sizeElement.readOnly = isHppRow;
            } else {
                sizeElement = createSizeDropdown(index, transactionData?.description, transactionData?.size);
                sizeElement.addEventListener('change', (e) => {
                    const row = e.target.closest('tr');
                    const index = parseInt(row.dataset.rowIndex);
                    const item = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                        row.querySelector('.descriptionInput[type="text"]')?.value || '';
                    const size = e.target.value;
                    const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                    if (voucherType === 'PJ' && item && size) {
                        // Remove any existing HPP row for the same item but different size
                        const allRows = Array.from(transactionTableBody.querySelectorAll('tr'));
                        const oldHppRow = allRows.find(r =>
                            r.dataset.isHppRow === 'true' &&
                            r.querySelector('.descriptionInput')?.value === `HPP ${item}` &&
                            r.querySelector('.sizeInput')?.value !== size
                        );
                        if (oldHppRow) {
                            oldHppRow.remove();
                            updateTransactionRowIndices();
                        }
                        // Update or create HPP row for the new size
                        updateHppRow(index, item, size, quantity, voucherCreatedAt);
                    }
                    updateAllCalculationsAndValidations();
                });
            }
            sizeCell.appendChild(sizeElement);
            row.appendChild(sizeCell);
            // Quantity Cell
            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${index}][quantity]`;
            quantityInput.value = transactionData?.quantity || '1';
            quantityInput.readOnly = isHppRow;
            quantityInput.addEventListener('input', () => {
                const row = quantityInput.closest('tr');
                updateRowTotal(row);
                if (voucherType === 'PJ' && !isHppRow) {
                    const item = row.querySelector('.descriptionInput:not([type="text"])')?.value || row
                        .querySelector('.descriptionInput[type="text"]')?.value || '';
                    const size = row.querySelector('.sizeInput')?.value || '';
                    const index = parseInt(row.dataset.rowIndex);
                    if (item && size) {
                        updateHppRow(index, item, size, parseFloat(quantityInput.value) || 1,
                            voucherCreatedAt);
                    }
                }
                updateAllCalculationsAndValidations();
            });
            quantityCell.appendChild(quantityInput);
            row.appendChild(quantityCell);

            // Nominal Cell
            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${index}][nominal]`;
            nominalInput.value = transactionData?.nominal || '';
            nominalInput.readOnly = isHppRow;
            nominalInput.addEventListener('input', () => {
                updateRowTotal(nominalInput.closest('tr'));
                updateAllCalculationsAndValidations();
            });
            nominalCell.appendChild(nominalInput);
            row.appendChild(nominalCell);

            // Total Cell
            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'number';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${index}][total]`;
            totalInput.value = transactionData?.total || '0';
            totalInput.readOnly = true;
            totalCell.appendChild(totalInput);
            row.appendChild(totalCell);

            // Action Cell
            const actionCell = document.createElement('td');
            actionCell.className = 'text-center';
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
            deleteButton.textContent = 'Hapus';
            actionCell.appendChild(deleteButton);
            row.appendChild(actionCell);

            return row;
        }

        function updateTransactionRowIndices() {
            transactionTableBody.querySelectorAll('tr').forEach((row, index) => {
                row.dataset.rowIndex = index;
                row.querySelectorAll('[name*="transactions["]').forEach(element => {
                    element.name = element.name.replace(/transactions\[\d+\]/,
                        `transactions[${index}]`);
                });
            });
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
        }

        function attachTransactionRemoveButtonListeners() {
            transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(button => {
                button.removeEventListener('click', handleRemoveTransactionRow);
                button.addEventListener('click', handleRemoveTransactionRow);
            });
        }

        function handleRemoveTransactionRow(event) {
            const row = event.target.closest('tr');
            const totalRows = transactionTableBody.querySelectorAll('tr').length;
            if (totalRows <= 1) {
                alert("Tidak dapat menghapus baris transaksi terakhir.");
                return;
            }
            const rowIndex = parseInt(row.dataset.rowIndex);
            const isHppRow = row.dataset.isHppRow === 'true';
            const voucherType = voucherTypeSelect.value;

            if (voucherType === 'PJ' && !isHppRow) {
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value || row
                    .querySelector('.descriptionInput[type="text"]')?.value || '';
                let nextRow = row.nextSibling;
                while (nextRow) {
                    if (nextRow.dataset.isHppRow === 'true' && nextRow.querySelector('.descriptionInput')
                        ?.value === `HPP ${description}`) {
                        nextRow.remove();
                        break;
                    }
                    nextRow = nextRow.nextSibling;
                }
            }

            row.remove();
            updateTransactionRowIndices();
            updateAllCalculationsAndValidations();
        }

        function attachTransactionInputListeners() {
            transactionTableBody.querySelectorAll(
                '.quantityInput, .nominalInput, .descriptionInput, .sizeInput').forEach(input => {
                input.removeEventListener('input', handleTransactionInput);
                input.addEventListener('input', handleTransactionInput);
            });
            transactionTableBody.querySelectorAll('.descriptionInput:not([type="text"])').forEach(select => {
                if (select.dataset.listenerAttached !== 'true') {
                    select.dataset.listenerAttached = 'true';
                    select.addEventListener('change', (e) => {
                        const row = e.target.closest('tr');
                        const index = parseInt(row.dataset.rowIndex);
                        const item = e.target.value;
                        updateSizeDropdown(row, item);
                        if ((voucherTypeSelect.value === 'PJ' || voucherTypeSelect.value ===
                                'RPJ') && item) {
                            const quantity = parseFloat(row.querySelector('.quantityInput')
                                ?.value) || 1;
                            const size = row.querySelector('.sizeInput')?.value || '';
                            updateHppRow(index, item, size, quantity);
                        }
                        updateAllCalculationsAndValidations();
                    });
                }
            });
        }

        let isUpdatingHpp = false;

        function handleTransactionInput(event) {
            const row = event.target.closest('tr');
            const isHppRow = row.dataset.isHppRow === 'true';
            if (isHppRow) return;

            const item = row.querySelector('.descriptionInput')?.value.trim() || '';
            const size = row.querySelector('.sizeInput')?.value.trim() || '';
            const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;

            if (item && size && voucherTypeSelect.value === 'PJ') {
                updateHppRow(row, item, size, quantity, voucherCreatedAt); // Pass row
            }

            updateRowTotal(row);
            updateAllCalculationsAndValidations();
        }

        function refreshTransactionTable() {
            const rows = transactionTableBody.querySelectorAll('tr');
            const transactionsData = Array.from(rows).map(row => {
                const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
                const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
                const sizeInput = row.querySelector('.sizeInput');
                const quantityInput = row.querySelector('.quantityInput');
                const nominalInput = row.querySelector('.nominalInput');
                const totalInput = row.querySelector('.totalInput');
                return {
                    description: descriptionInput?.value || descriptionSelect?.value || '',
                    description_select: row.querySelector('[name$="[description_select]"]')?.value ||
                        '',
                    size: sizeInput?.value || '',
                    quantity: quantityInput?.value || '1',
                    nominal: nominalInput?.value || '0',
                    total: totalInput?.value || '0',
                    isHppRow: row.dataset.isHppRow === 'true'
                };
            });

            transactionTableBody.innerHTML = '';
            const useStock = useStockYes.checked ? 'yes' : 'no';
            const voucherType = voucherTypeSelect.value;
            transactionsData.forEach((data, index) => {
                const newRow = generateTransactionTableRow(index, data);
                transactionTableBody.appendChild(newRow);
                if (useStock === 'yes' && ['PJ', 'PB', 'PYB', 'PYK', 'RPJ', 'RPB'].includes(
                        voucherType) && !data.isHppRow) {
                    updateSizeDropdown(newRow, data.description || data.description_select);
                }
            });
            if (transactionTableBody.querySelectorAll('tr').length === 0) {
                const newRow = generateTransactionTableRow(0);
                transactionTableBody.appendChild(newRow);
                if (useStock === 'yes' && ['PJ', 'PB', 'PYB', 'PYK', 'RPJ', 'RPB'].includes(voucherType)) {
                    updateSizeDropdown(newRow, '');
                }
            }
            updateTransactionRowIndices();
            if (voucherType === 'PJ' || voucherType === 'RPJ') {
                initializeHppRows(); // Re-init HPP rows setelah refresh
            }
        }

        // --- Voucher Detail Table ---
        function generateVoucherDetailTableRow(index) {
            const row = document.createElement('tr');
            const accountCodeCell = document.createElement('td');
            const accountCodeInput = document.createElement('input');
            accountCodeInput.type = 'text';
            accountCodeInput.className = 'form-control accountCodeInput';
            accountCodeInput.name = `voucher_details[${index}][account_code]`;
            accountCodeInput.placeholder = 'Ketik atau pilih kode akun';
            accountCodeInput.setAttribute('list', 'dynamicAccountCodes');
            accountCodeInput.required = true;
            accountCodeCell.appendChild(accountCodeInput);
            const invalidFeedback = document.createElement('div');
            invalidFeedback.className = 'invalid-feedback';
            invalidFeedback.textContent = 'Kode Akun wajib diisi.';
            accountCodeCell.appendChild(invalidFeedback);
            row.appendChild(accountCodeCell);

            const accountNameCell = document.createElement('td');
            const accountNameInput = document.createElement('input');
            accountNameInput.type = 'text';
            accountNameInput.className = 'form-control accountName';
            accountNameInput.name = `voucher_details[${index}][account_name]`;
            accountNameInput.readOnly = true;
            accountNameCell.appendChild(accountNameInput);
            row.appendChild(accountNameCell);

            const debitCell = document.createElement('td');
            const debitInput = document.createElement('input');
            debitInput.type = 'number';
            debitInput.min = '0';
            debitInput.className = 'form-control debitInput';
            debitInput.name = `voucher_details[${index}][debit]`;
            debitCell.appendChild(debitInput);
            row.appendChild(debitCell);

            const creditCell = document.createElement('td');
            const creditInput = document.createElement('input');
            creditInput.type = 'number';
            creditInput.min = '0';
            creditInput.className = 'form-control creditInput';
            creditInput.name = `voucher_details[${index}][credit]`;
            creditCell.appendChild(creditInput);
            row.appendChild(creditCell);

            const actionCell = document.createElement('td');
            actionCell.className = 'text-center';
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-danger removeVoucherDetailRowBtn';
            deleteButton.textContent = 'Hapus';
            actionCell.appendChild(deleteButton);
            row.appendChild(actionCell);

            return row;
        }

        function updateVoucherDetailRowIndices() {
            voucherDetailsTableBody.querySelectorAll('tr').forEach((row, index) => {
                row.dataset.rowIndex = index;
                row.querySelectorAll('[name*="voucher_details["]').forEach(input => {
                    input.name = input.name.replace(/voucher_details\[\d+\]/,
                        `voucher_details[${index}]`);
                });
            });
            attachVoucherDetailRemoveButtonListeners();
            attachVoucherDetailRowEventListenersToAll();
        }

        function attachVoucherDetailRemoveButtonListeners() {
            voucherDetailsTableBody.querySelectorAll('.removeVoucherDetailRowBtn').forEach(button => {
                button.removeEventListener('click', handleRemoveVoucherDetailRow);
                button.addEventListener('click', handleRemoveVoucherDetailRow);
            });
        }

        function handleRemoveVoucherDetailRow() {
            if (voucherDetailsTableBody.querySelectorAll('tr').length > 1) {
                this.closest('tr').remove();
                updateVoucherDetailRowIndices();
                updateAccountCodeDatalist();
                updateAllCalculationsAndValidations();
            } else {
                alert("Tidak dapat menghapus baris detail voucher terakhir.");
            }
        }

        function attachVoucherDetailRowEventListeners(row, index) {
            const accountCodeInput = row.querySelector('.accountCodeInput');
            const accountNameInput = row.querySelector('.accountName');
            const debitInput = row.querySelector('.debitInput');
            const creditInput = row.querySelector('.creditInput');

            accountCodeInput?.addEventListener('input', () => {
                const enteredCode = accountCodeInput.value.trim();
                accountNameInput.value = '';
                const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
                if (useInvoice === 'yes' && subsidiaries.some(s => s.subsidiary_code === enteredCode)) {
                    const subsidiary = subsidiaries.find(s => s.subsidiary_code === enteredCode);
                    if (subsidiary) accountNameInput.value = subsidiary.account_name;
                } else {
                    const account = accounts.find(a => a.account_code === enteredCode);
                    if (account) accountNameInput.value = account.account_name;
                }
                updateAccountCodeDatalist();
                updateAllCalculationsAndValidations(); // Trigger calculations on account code change
            });

            debitInput?.addEventListener('input', () => {
                creditInput.value = debitInput.value ? '' : creditInput.value;
                creditInput.disabled = !!debitInput.value;
                updateAllCalculationsAndValidations(); // Trigger calculations on debit change
            });

            creditInput?.addEventListener('input', () => {
                debitInput.value = creditInput.value ? '' : debitInput.value;
                debitInput.disabled = !!creditInput.value;
                updateAllCalculationsAndValidations(); // Trigger calculations on credit change
            });
        }

        function attachVoucherDetailRowEventListenersToAll() {
            voucherDetailsTableBody.querySelectorAll('tr').forEach((row, index) => {
                attachVoucherDetailRowEventListeners(row, index);
            });
        }

        // --- Invoice and Store Fields ---
        function createStoreDropdown() {
            const container = document.createElement('div');
            const label = document.createElement('label');
            const select = document.createElement('select');
            select.className = 'form-select';
            select.id = 'store';
            select.name = 'store';
            label.className = 'form-label';
            label.setAttribute('for', 'store');
            label.innerHTML = '<i class="fas fa-store"></i> Nama Toko';
            label.setAttribute('data-bs-toggle', 'tooltip');
            select.innerHTML = '<option value="">Pilih Nama Toko</option>';
            storeNames.forEach(store => {
                const option = document.createElement('option');
                option.value = store;
                option.textContent = store;
                if (store === @json($voucher->store)) option.selected = true;
                select.appendChild(option);
            });
            container.appendChild(label);
            container.appendChild(select);
            return container;
        }

        function createInvoiceDropdown() {
            const select = document.createElement('select');
            select.className = 'form-control';
            select.id = 'invoice';
            select.name = 'invoice';
            select.innerHTML = '<option value="">Pilih Nomor Invoice</option>';
            existingInvoices.forEach(invoice => {
                if (invoice) {
                    const option = document.createElement('option');
                    option.value = invoice;
                    option.textContent = invoice;
                    if (invoice === @json($voucher->invoice)) option.selected = true;
                    select.appendChild(option);
                }
            });
            return select;
        }

        function createInvoiceInput() {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = 'invoice';
            input.name = 'invoice';
            input.value = @json($voucher->invoice ?? '');
            return input;
        }

        function updateInvoiceField() {
            const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
            const useExistingInvoice = useExistingInvoiceYes.checked ? 'yes' : 'no';
            invoiceFieldContainer.innerHTML = '';

            // Create label with icon
            const invoiceLabel = document.createElement('label');
            invoiceLabel.setAttribute('for', 'invoice');
            invoiceLabel.className = 'form-label';
            invoiceLabel.innerHTML = '<i class="fas fa-file-invoice"></i> Nomor Invoice';
            invoiceLabel.setAttribute('data-bs-toggle', 'tooltip');
            invoiceLabel.setAttribute('title', 'Masukkan nomor invoice untuk transaksi');

            // Create input container
            const invoiceInputDiv = document.createElement('div');
            invoiceInputDiv.className = 'col-12'; // Use full width to match modal's layout

            // Create input (dropdown or text input based on conditions)
            const invoiceInput = useInvoice === 'yes' && useExistingInvoice === 'yes' ?
                createInvoiceDropdown() : createInvoiceInput();
            invoiceInputDiv.appendChild(invoiceInput);

            // Add validation feedback
            const invalidFeedback = document.createElement('div');
            invalidFeedback.className = 'invalid-feedback';
            invalidFeedback.textContent = 'Nomor Invoice wajib diisi jika menggunakan invoice.';
            invoiceInputDiv.appendChild(invalidFeedback);

            // Append label and input to container
            invoiceFieldContainer.appendChild(invoiceLabel);
            invoiceFieldContainer.appendChild(invoiceInputDiv);

            // Reinitialize tooltips for new elements
            const tooltipElements = invoiceFieldContainer.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipElements.forEach(el => new bootstrap.Tooltip(el));

            updateAccountCodeDatalist();
            validateForm();
        }

        function updateDueDateField() {
            const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
            const useExistingInvoice = useExistingInvoiceYes.checked ? 'yes' : 'no';
            dueDateContainer.innerHTML = '';

            // Create label with icon
            const dueDateLabel = document.createElement('label');
            dueDateLabel.setAttribute('for', 'due_date');
            dueDateLabel.className = 'form-label';
            dueDateLabel.innerHTML = '<i class="fas fa-calendar-times"></i> Tanggal Jatuh Tempo';
            dueDateLabel.setAttribute('data-bs-toggle', 'tooltip');
            dueDateLabel.setAttribute('title', 'Tanggal jatuh tempo untuk pembayaran invoice');

            // Create input container
            const dueDateInputDiv = document.createElement('div');
            dueDateInputDiv.className = 'col-12'; // Use full width to match modal's layout

            // Create input
            const dueDateInput = document.createElement('input');
            dueDateInput.type = 'date';
            dueDateInput.className = 'form-control';
            dueDateInput.id = 'due_date';
            dueDateInput.name = 'due_date';
            dueDateInput.disabled = useInvoice !== 'yes' || useExistingInvoice === 'yes';
            dueDateInput.value = useInvoice === 'yes' && @json($dueDate) ?
                @json($dueDate) : '';
            dueDateInput.setAttribute('data-bs-toggle', 'tooltip');
            dueDateInput.setAttribute('title', 'Tanggal jatuh tempo untuk pembayaran invoice');
            dueDateInputDiv.appendChild(dueDateInput);

            // Add validation feedback
            const invalidFeedback = document.createElement('div');
            invalidFeedback.className = 'invalid-feedback';
            invalidFeedback.textContent = 'Tanggal Jatuh Tempo wajib diisi untuk invoice baru.';
            dueDateInputDiv.appendChild(invalidFeedback);

            // Append label and input to container
            dueDateContainer.appendChild(dueDateLabel);
            dueDateContainer.appendChild(dueDateInputDiv);

            // Reinitialize tooltips for new elements
            const tooltipElements = dueDateContainer.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipElements.forEach(el => new bootstrap.Tooltip(el));

            // Set default due date if needed
            if (useInvoice === 'yes' && useExistingInvoice === 'no' && !dueDateInput.value) {
                setTodayDueDate();
            }

            validateForm();
        }

        function updateInvoiceAndStoreFields() {
            const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
            storeFieldContainer.innerHTML = '';
            useExistingInvoiceYes.disabled = useInvoice !== 'yes';
            useExistingInvoiceNo.disabled = useInvoice !== 'yes';
            if (useInvoice === 'yes') {
                storeFieldContainer.appendChild(createStoreDropdown());
                invoiceFieldContainer.style.display = 'block';
                storeFieldContainer.style.display = 'block';
                existingInvoiceContainer.style.display = 'block';
                updateInvoiceField();
                updateDueDateField();
            } else {
                storeFieldContainer.appendChild(createStoreDropdown());
                storeFieldContainer.querySelector('#store').value = '';
                invoiceFieldContainer.style.display = 'none';
                storeFieldContainer.style.display = 'none';
                existingInvoiceContainer.style.display = 'none';
                useExistingInvoiceYes.checked = false;
                useExistingInvoiceNo.checked = false;
                updateInvoiceField();
                updateDueDateField();
            }
            updateAccountCodeDatalist();
        }

        // --- Account Code Datalist ---
        function isSubsidiaryCodeUsed() {
            const accountCodeInputs = voucherDetailsTableBody.querySelectorAll('.accountCodeInput');
            return Array.from(accountCodeInputs).some(input => subsidiaries.some(s => s.subsidiary_code ===
                input.value.trim()));
        }

        function updateAccountCodeDatalist(isNewRow = false) {
            const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
            const datalists = document.querySelectorAll('#dynamicAccountCodes');
            const subsidiaryUsed = isSubsidiaryCodeUsed();
            datalists.forEach(datalist => {
                datalist.innerHTML = '<option value="">Pilih Kode Akun</option>';
                if (useInvoice === 'yes' && !subsidiaryUsed && !isNewRow) {
                    subsidiaries.forEach(subsidiary => {
                        datalist.innerHTML +=
                            `<option value="${subsidiary.subsidiary_code}">${subsidiary.subsidiary_code} - ${subsidiary.account_name}</option>`;
                    });
                } else {
                    accounts.forEach(account => {
                        datalist.innerHTML +=
                            `<option value="${account.account_code}">${account.account_code} - ${account.account_name}</option>`;
                    });
                    subsidiaries.forEach(subsidiary => {
                        datalist.innerHTML +=
                            `<option value="${subsidiary.subsidiary_code}">${subsidiary.subsidiary_code} - ${subsidiary.account_name}</option>`;
                    });
                }
            });
            voucherDetailsTableBody.querySelectorAll('.accountCodeInput').forEach(input => {
                const row = input.closest('tr');
                const accountNameInput = row.querySelector('.accountName');
                const enteredCode = input.value.trim();
                accountNameInput.value = '';
                if (useInvoice === 'yes' && subsidiaries.some(s => s.subsidiary_code === enteredCode)) {
                    const subsidiary = subsidiaries.find(s => s.subsidiary_code === enteredCode);
                    if (subsidiary) accountNameInput.value = subsidiary.account_name;
                } else {
                    const account = accounts.find(a => a.account_code === enteredCode);
                    if (account) accountNameInput.value = account.account_name;
                }
            });
        }

        // --- Calculations and Validations ---
        function updateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 0;
            const nominal = parseFloat(row.querySelector('.nominalInput')?.value) || 0;
            const totalInput = row.querySelector('.totalInput');
            if (totalInput) {
                totalInput.value = (quantity * nominal).toFixed(2);
            }
        }

        function calculateTotalNominal() {
            let totalNominalRaw = 0;
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                const total = parseFloat(row.querySelector('.totalInput')?.value) || 0;
                totalNominalRaw += total;
            });
            totalNominalInput.value = totalNominalRaw.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return totalNominalRaw;
        }

        function calculateTotalsAndValidate() {
            let totalDebit = 0,
                totalCredit = 0;
            voucherDetailsTableBody.querySelectorAll('.debitInput').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });
            voucherDetailsTableBody.querySelectorAll('.creditInput').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });
            // Update visible inputs with formatted values for display
            totalDebitInput.value = totalDebit.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            totalCreditInput.value = totalCredit.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            // Update hidden inputs with raw numeric values for submission
            totalDebitRawInput.value = totalDebit.toFixed(2);
            totalCreditRawInput.value = totalCredit.toFixed(2);

            // Add event listeners for real-time updates
            const debounceUpdate = debounce(() => {
                validateTotals();
            }, 20);

            voucherDetailsTableBody.querySelectorAll('.debitInput, .creditInput').forEach(input => {
                input.addEventListener('input', debounceUpdate);
            });

            return {
                totalDebitRaw: totalDebit,
                totalCreditRaw: totalCredit
            };
        }

        function validateTotals() {
            const totalNominalRaw = calculateTotalNominal();
            const {
                totalDebitRaw,
                totalCreditRaw
            } = calculateTotalsAndValidate();
            if (totalNominalRaw !== totalDebitRaw || totalNominalRaw !== totalCreditRaw) {
                validationInput.value =
                    "Total Nominal pada Rincian Transaksi harus sama dengan Total Debit dan Total Kredit pada Rincian Voucher.";
                saveVoucherBtn.disabled = true;
            } else if (totalDebitRaw !== totalCreditRaw) {
                validationInput.value = "Total Debit harus sama dengan Total Kredit.";
                saveVoucherBtn.disabled = true;
            } else {
                validationInput.value = "Totalnya seimbang dan valid.";
                saveVoucherBtn.disabled = false;
            }
            return totalDebitRaw === totalCreditRaw && totalNominalRaw === totalDebitRaw;
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function validateForm() {
            let isValid = true;
            const requiredFields = voucherForm.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
            const useExistingInvoice = useExistingInvoiceYes.checked ? 'yes' : 'no';
            const invoiceInput = document.getElementById('invoice');
            const dueDateInput = document.getElementById('due_date');
            if (useInvoice === 'yes' && !invoiceInput.value.trim()) {
                invoiceInput.classList.add('is-invalid');
                isValid = false;
            } else {
                invoiceInput.classList.remove('is-invalid');
            }
            if (useInvoice === 'yes' && useExistingInvoice === 'no' && !dueDateInput.value) {
                dueDateInput.classList.add('is-invalid');
                isValid = false;
            } else {
                dueDateInput.classList.remove('is-invalid');
            }

            // Validate stock quantities for PJ
            const voucherType = voucherTypeSelect.value;
            if (['PJ', 'PYB', 'PYK', 'RPB', 'RPJ'].includes(voucherType)) {
                const stockDescriptions = new Set();
                transactionTableBody.querySelectorAll('tr').forEach(row => {
                    const description = row.querySelector('.descriptionInput')?.value || '';
                    const size = row.querySelector('.sizeInput')?.value || '';
                    const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 0;
                    const isHpp = row.dataset.isHppRow === 'true';
                    if (!isHpp && description && size) {
                        stockDescriptions.add(`${description}|${size}`);
                        if (!validateStockQuantity(description, size, quantity)) {
                            isValid = false;
                        }
                    }
                });

                // Validate HPP rows for PJ
                if (voucherType === 'PJ' || voucherType === 'RPJ') {
                    transactionTableBody.querySelectorAll('tr').forEach(row => {
                        const description = row.querySelector('.descriptionInput')?.value || '';
                        const size = row.querySelector('.sizeInput')?.value || '';
                        const isHpp = row.dataset.isHppRow === 'true';
                        if (isHpp && description) {
                            const stockItem = description.replace(/^HPP /, '');
                            if (!stockDescriptions.has(`${stockItem}|${size}`)) {
                                validationInput.value =
                                    `Baris HPP untuk "${stockItem}" dengan ukuran "${size}" tidak memiliki transaksi stok yang sesuai.`;
                                isValid = false;
                            }
                        }
                    });
                }
            }

            // Ensure total_debit and total_credit are numeric
            const totalDebitRaw = parseFloat(totalDebitRawInput.value) || 0;
            const totalCreditRaw = parseFloat(totalCreditRawInput.value) || 0;
            if (isNaN(totalDebitRaw)) {
                validationInput.value = "Total Debit harus berupa angka.";
                totalDebitInput.classList.add('is-invalid');
                isValid = false;
            } else {
                totalDebitInput.classList.remove('is-invalid');
            }
            if (isNaN(totalCreditRaw)) {
                validationInput.value = "Total Kredit harus berupa angka.";
                totalCreditInput.classList.add('is-invalid');
                isValid = false;
            } else {
                totalCreditInput.classList.remove('is-invalid');
            }

            return isValid;
        }

        function updateAllCalculationsAndValidations() {
            transactionTableBody.querySelectorAll('tr').forEach(row => updateRowTotal(row));
            const totalsValid = validateTotals();
            const formValid = validateForm();
            saveVoucherBtn.disabled = !(totalsValid && formValid);
        }

        // --- Date Management ---
        function updateVoucherDay() {
            const voucherDate = document.getElementById('voucherDate').value;
            if (voucherDate) {
                const date = new Date(voucherDate);
                const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                document.getElementById('voucherDay').value = days[date.getDay()];
            } else {
                document.getElementById('voucherDay').value = "";
            }
        }

        function setTodayDueDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('due_date').value = `${year}-${month}-${day}`;
        }

        // --- Debounce Utility ---
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // --- Event Listeners ---
        useStockYes.addEventListener('change', () => {
            updateVoucherTypeOptions();
            updateInvoiceAndStoreFields();
            updateAllCalculationsAndValidations();
        });

        useStockNo.addEventListener('change', () => {
            updateVoucherTypeOptions();
            updateInvoiceAndStoreFields();
            updateAllCalculationsAndValidations();
        });

        voucherTypeSelect.addEventListener('change', () => {
            updateDescription();
            refreshTransactionTable();
            updateAllCalculationsAndValidations();
        });

        useInvoiceYes.addEventListener('change', updateInvoiceAndStoreFields);
        useInvoiceNo.addEventListener(
            'change', updateInvoiceAndStoreFields);
        useExistingInvoiceYes.addEventListener('change', () => {
            updateInvoiceField();
            updateDueDateField();
            updateAllCalculationsAndValidations();
        });
        useExistingInvoiceNo.addEventListener('change', () => {
            updateInvoiceField();
            updateDueDateField();
            updateAllCalculationsAndValidations();
        });

        document.getElementById('voucherDate')?.addEventListener('change', updateVoucherDay);

        addTransactionRowBtn.addEventListener('click', () => {
            const useStock = useStockYes.checked ? 'yes' : 'no';
            const voucherType = voucherTypeSelect.value;
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const newRow = generateTransactionTableRow(newIndex);
            transactionTableBody.appendChild(newRow);
            if (useStock === 'yes' && ['PJ', 'PB', 'PYK', 'PYB', 'RPJ', 'RPB'].includes(
                    voucherType)) {
                updateSizeDropdown(newRow, '');
            }
            updateTransactionRowIndices();
            updateAllCalculationsAndValidations();
        });

        addVoucherDetailRowBtn.addEventListener('click', () => {
            const newRow = generateVoucherDetailTableRow(voucherDetailsTableBody.querySelectorAll('tr')
                .length);
            voucherDetailsTableBody.appendChild(newRow);
            updateVoucherDetailRowIndices();
            updateAccountCodeDatalist(true);
            updateAllCalculationsAndValidations();
        });

        voucherForm.addEventListener('submit', (event) => {
            event.preventDefault(); // Prevent default submission for custom handling
            if (!validateForm() || !validateTotals()) {
                alert('Silakan perbaiki kesalahan pada formulir sebelum mengirim.');
                return;
            }
            // Ensure only raw numeric values are submitted
            totalDebitInput.name = 'total_debit_display'; // Rename display field to avoid validation
            totalCreditInput.name = 'total_credit_display'; // Rename display field to avoid validation
            totalDebitRawInput.name = 'total_debit'; // Use raw value for submission
            totalCreditRawInput.name = 'total_credit'; // Use raw value for submission

            // Submit the form
            voucherForm.submit();
        });

        // --- Initialize Existing HPP Rows ---
        function initializeHppRows() {
            const voucherType = voucherTypeSelect.value;
            if (voucherType !== 'PJ' || voucherType !== 'RPJ') return;

            const existingTransactions = @json($voucher->transactions) || [];
            const currentCreatedAt = @json($voucherCreatedAt) || new Date().toISOString();

            transactionTableBody.querySelectorAll('tr[data-is-hpp-row="true"]').forEach(row => row.remove());

            transactionTableBody.querySelectorAll('tr[data-is-hpp-row="false"]').forEach((parentRow) => {
                const item = parentRow.querySelector('.descriptionInput')?.value.trim();
                const size = parentRow.querySelector('.sizeInput')?.value.trim();
                const quantity = parseFloat(parentRow.querySelector('.quantityInput')?.value) || 1;
                if (item && size) {
                    updateHppRow(parentRow, item, size, quantity, currentCreatedAt);
                }
            });

            updateAllCalculationsAndValidations();
        }

        function initializePage() {
            // Set initial useStock based on voucher type
            const isStockVoucher = ['PJ', 'PB', 'RPJ', 'RPB', 'PYJ', 'PYB'].includes(
                currentVoucherType);
            useStockYes.checked = isStockVoucher;
            useStockNo.checked = !isStockVoucher;

            updateVoucherTypeOptions();
            updateInvoiceAndStoreFields();
            updateAccountCodeDatalist();
            initializeHppRows();
            updateAllCalculationsAndValidations();
            updateVoucherDay();

            const voucherDateInput = document.getElementById('voucherDate');
            if (voucherDateInput && !voucherDateInput.value) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                voucherDateInput.value = `${year}-${month}-${day}`;
                updateVoucherDay();
            }
        }

        // --- Initialization ---
        initializePage();
        updateVoucherTypeOptions();
        updateInvoiceAndStoreFields();
        updateAccountCodeDatalist();
        refreshTransactionTable();
        initializeHppRows();
        updateAllCalculationsAndValidations();
        updateVoucherDay();

        const voucherDateInput = document.getElementById('voucherDate');
        if (voucherDateInput && !voucherDateInput.value) {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            voucherDateInput.value = `${year}-${month}-${day}`;
            updateVoucherDay();
        }
    });
</script>
@endsection
