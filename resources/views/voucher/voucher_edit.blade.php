@extends('layouts.app')

@section('content')
@section('title', 'Edit Voucher')

<style>
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
</style>

<div>
    @if (session('success'))
        <div id="success-message" class="alert alert-success" style="cursor: pointer;" onclick="this.remove();">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div id="error-message" class="alert alert-danger" style="cursor: pointer;" onclick="this.remove();">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @elseif (session('message'))
        <div id="error-message" class="alert alert-danger" style="cursor: pointer;" onclick="this.remove();">
            {{ session('message') }}
        </div>
    @endif
    <form id="voucherForm" method="POST" action="{{ route('voucher.update', $voucher->id) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="container mt-4">
            <h2 class="text-center">Formulir Edit {{ $headingText }} Voucher</h2>

            <!-- Voucher Number -->
            <div class="row mb-3">
                <label for="voucherNumber" class="col-sm-3 col-form-label">Nomor Voucher:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="voucherNumber" name="voucher_number"
                        value="{{ $voucher->voucher_number }}" readonly>
                </div>
            </div>
            <!-- Company Name -->
            <div class="row mb-3">
                <label for="companyName" class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                <div class="col-sm-9">
                    @if ($company)
                        <input type="text" class="form-control" id="companyName" value="{{ $company->company_name }}"
                            readonly>
                    @else
                        <input type="text" class="form-control" id="companyName" value="Nama Perusahaan Kosong"
                            readonly>
                        <small class="text-danger">Nama Perusahaan Belum Ditemukan.</small>
                    @endif
                </div>
            </div>
            <!-- Use Stock -->
            <div class="row mb-3">
                <label for="useStock" class="col-sm-3 col-form-label">Transaksi Stok?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useStockYes" name="use_stock" value="yes"
                            {{ in_array($voucher->voucher_type, ['PB', 'PJ', 'PH', 'PK']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="useStockYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useStockNo" name="use_stock" value="no"
                            {{ in_array($voucher->voucher_type, ['PG', 'PM', 'LN']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="useStockNo">Tidak</label>
                    </div>
                </div>
            </div>
            <!-- Voucher Type, Date, and Day -->
            <div class="row mb-3">
                <label for="voucherType" class="col-sm-3 col-form-label">Tipe Voucher:</label>
                <div class="col-sm-3">
                    <select class="form-select" id="voucherType" name="voucher_type" required aria-required="true">
                        <option value="PJ" {{ $voucher->voucher_type == 'PJ' ? 'selected' : '' }}>Penjualan</option>
                        <option value="PG" {{ $voucher->voucher_type == 'PG' ? 'selected' : '' }}>Pengeluaran
                        </option>
                        <option value="PM" {{ $voucher->voucher_type == 'PM' ? 'selected' : '' }}>Pemasukan
                        </option>
                        <option value="PB" {{ $voucher->voucher_type == 'PB' ? 'selected' : '' }}>Pembelian
                        </option>
                        <option value="LN" {{ $voucher->voucher_type == 'LN' ? 'selected' : '' }}>Lainnya</option>
                        <option value="PH" {{ $voucher->voucher_type == 'PH' ? 'selected' : '' }}>Pemindahan
                        </option>
                        <option value="PK" {{ $voucher->voucher_type == 'PK' ? 'selected' : '' }}>Pemakaian
                        </option>
                    </select>
                    <div class="invalid-feedback">Tipe Voucher wajib dipilih.</div>
                </div>
                <label for="voucherDate" class="col-sm-2 col-form-label">Tanggal:</label>
                <div class="col-sm-2">
                    <input type="date" class="form-control" id="voucherDate" name="voucher_date"
                        value="{{ $voucher->voucher_date->format('Y-m-d') }}" required aria-required="true">
                    <div class="invalid-feedback">Tanggal wajib diisi.</div>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="voucherDay" name="voucher_day"
                        value="{{ $voucher->voucher_day }}" readonly>
                </div>
            </div>
            <!-- Description -->
            <div class="row mb-3">
                <label for="description" class="col-sm-3 col-form-label">Deskripsi:</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="description" name="description" readonly>{{ $voucher->description }}</textarea>
                </div>
            </div>
            <!-- Prepared By -->
            <div class="row mb-3">
                <label for="preparedBy" class="col-sm-3 col-form-label">Dibuat Oleh:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="preparedBy" name="prepared_by"
                        value="{{ $voucher->prepared_by }}" readonly>
                </div>
            </div>
            <!-- Given To -->
            <div class="row mb-3">
                <label for="givenTo" class="col-sm-3 col-form-label">Diberikan Kepada:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="givenTo" name="given_to"
                        value="{{ $voucher->given_to }}">
                </div>
            </div>
            <!-- Approved By -->
            <div class="row mb-3">
                <label for="approvedBy" class="col-sm-3 col-form-label">Disetujui Oleh:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="approvedBy" name="approved_by"
                        value="{{ $voucher->approved_by }}" readonly>
                </div>
            </div>
            <!-- Transaction -->
            <div class="row mb-3">
                <label for="transaction" class="col-sm-3 col-form-label">Transaksi:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="transaction" name="transaction"
                        value="{{ $voucher->transaction }}">
                </div>
            </div>
            <!-- Use Invoice -->
            <div class="row mb-3">
                <label for="useInvoice" class="col-sm-3 col-form-label">Gunakan Invoice?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useInvoiceYes" name="use_invoice"
                            value="yes" {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'checked' : '' }}
                            required aria-required="true">
                        <label class="form-check-label" for="useInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useInvoiceNo" name="use_invoice"
                            value="no"
                            {{ !$voucher->invoice && $voucher->use_invoice !== 'yes' ? 'checked' : '' }} required
                            aria-required="true">
                        <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                    </div>
                    <div class="invalid-feedback">Pilih apakah menggunakan invoice.</div>
                </div>
            </div>
            <!-- Use Existing Invoice -->
            <div class="row mb-3" id="existingInvoiceContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label class="col-sm-3 col-form-label">Gunakan Invoice yang Sudah Ada?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceYes"
                            name="use_existing_invoice" value="yes"
                            {{ $voucher->use_existing_invoice === 'yes' ? 'checked' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceNo"
                            name="use_existing_invoice" value="no"
                            {{ $voucher->use_existing_invoice !== 'yes' ? 'checked' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceNo">Tidak</label>
                    </div>
                </div>
            </div>
            <!-- Invoice Number -->
            <div class="row mb-3" id="invoiceFieldContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="invoice" class="col-sm-3 col-form-label">Nomor Invoice:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="invoice" name="invoice"
                        value="{{ $voucher->invoice ?? '' }}">
                    <div class="invalid-feedback">Nomor Invoice wajib diisi jika menggunakan invoice.</div>
                </div>
            </div>
            <!-- Due Date -->
            <div class="row mb-3" id="dueDateContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="due_date" class="col-sm-3 col-form-label">Tanggal Jatuh Tempo:</label>
                <div class="col-sm-9">
                    <input type="date" class="form-control" id="due_date" name="due_date"
                        value="{{ $dueDate }}" {{ $voucher->use_existing_invoice === 'yes' ? 'disabled' : '' }}>
                    <div class="invalid-feedback">Tanggal Jatuh Tempo wajib diisi untuk invoice baru.</div>
                </div>
            </div>
            <!-- Store Name -->
            <div class="row mb-3" id="storeFieldContainer"
                style="display: {{ $voucher->invoice || $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="store" class="col-sm-3 col-form-label">Nama Toko:</label>
                <div class="col-sm-9">
                    <select class="form-select" id="store" name="store">
                        <option value="">Pilih Nama Toko</option>
                        @foreach ($storeNames as $storeName)
                            <option value="{{ $storeName }}"
                                {{ $voucher->store == $storeName ? 'selected' : '' }}>{{ $storeName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- Recipe Dropdown for PK -->
            @if ($voucher->voucher_type == 'PK')
                <div class="row mb-3" id="recipeContainer"
                    style="display: {{ $voucher->use_stock === 'yes' && $voucher->voucher_type === 'PK' ? 'block' : 'none' }};">
                    <label for="recipe" class="col-sm-3 col-form-label">Formula Produk:</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="recipe" name="recipe">
                            <option value="">Pilih Formula Produk</option>
                            @foreach ($recipes as $recipe)
                                <option value="{{ $recipe['id'] }}"
                                    {{ $voucher->recipe_id == $recipe['id'] ? 'selected' : '' }}>
                                    {{ $recipe['product_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            <!-- Transaction Details Table -->
            <div class="mb-3">
                <h5>Rincian Transaksi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="transactionDetailsTable">
                        <thead>
                            <tr class="text-center">
                                <th colspan="6">Rincian Transaksi</th>
                            </tr>
                            <tr class="text-center">
                                <th>Deskripsi</th>
                                <th>Ukuran</th>
                                <th>Quantitas</th>
                                <th>Nominal</th>
                                <th>Total</th>
                                <th style="width: 80px;">Aksi</th>
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
                                                    value="{{ $transaction->description }}" readonly>
                                            @elseif (in_array($voucher->voucher_type, ['PJ', 'PH', 'PK']))
                                                <select class="form-control descriptionInput"
                                                    name="transactions[{{ $index }}][description]"
                                                    data-initial-value="{{ $transaction->description }}">
                                                    <option value="">Pilih Nama Stock</option>
                                                    @foreach ($stocks as $stock)
                                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                                            <option value="{{ $stock['item'] }}"
                                                                {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>
                                                                {{ $stock['item'] }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @elseif ($voucher->voucher_type == 'PB')
                                                <div class="input-group">
                                                    <select class="form-control descriptionInput"
                                                        name="transactions[{{ $index }}][description_select]"
                                                        style="width: 50%;">
                                                        <option value="">Pilih Nama Stock</option>
                                                        @foreach ($stocks as $stock)
                                                            @if (!str_starts_with($stock['item'], 'HPP '))
                                                                <option value="{{ $stock['item'] }}"
                                                                    {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>
                                                                    {{ $stock['item'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <input type="text" class="form-control descriptionInput"
                                                        name="transactions[{{ $index }}][description]"
                                                        style="width: 50%;" value="{{ $transaction->description }}">
                                                </div>
                                            @else
                                                <input type="text" class="form-control descriptionInput"
                                                    name="transactions[{{ $index }}][description]"
                                                    value="{{ $transaction->description }}">
                                            @endif
                                        </td>
                                        <td>
                                            @if (in_array($voucher->voucher_type, ['PJ', 'PB', 'PH', 'PK']) && !str_starts_with($transaction->description, 'HPP '))
                                                <select class="form-control sizeInput"
                                                    name="transactions[{{ $index }}][size]">
                                                    <option value="">Pilih Ukuran</option>
                                                    @foreach ($stocks as $stock)
                                                        @if ($stock['item'] == $transaction->description)
                                                            <option value="{{ $stock['size'] }}"
                                                                {{ $transaction->size == $stock['size'] ? 'selected' : '' }}>
                                                                {{ $stock['size'] }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control sizeInput"
                                                    name="transactions[{{ $index }}][size]"
                                                    value="{{ $transaction->size }}"
                                                    {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" min="1" class="form-control quantityInput"
                                                name="transactions[{{ $index }}][quantity]"
                                                value="{{ $transaction->quantity }}"
                                                {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control nominalInput"
                                                name="transactions[{{ $index }}][nominal]"
                                                value="{{ $transaction->nominal }}"
                                                {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control totalInput"
                                                name="transactions[{{ $index }}][total]"
                                                value="{{ $transaction->quantity * $transaction->nominal }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger removeTransactionRowBtn"
                                                {{ str_starts_with($transaction->description, 'HPP ') ? 'disabled' : '' }}>Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr data-row-index="0">
                                    <td>
                                        @if (in_array($voucher->voucher_type, ['PJ', 'PH', 'PK']))
                                            <select class="form-control descriptionInput"
                                                name="transactions[0][description]">
                                                <option value="">Pilih Nama Stock</option>
                                                @foreach ($stocks as $stock)
                                                    @if (!str_starts_with($stock['item'], 'HPP '))
                                                        <option value="{{ $stock['item'] }}">{{ $stock['item'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @elseif ($voucher->voucher_type == 'PB')
                                            <div class="input-group">
                                                <select class="form-control descriptionInput"
                                                    name="transactions[0][description_select]" style="width: 50%;">
                                                    <option value="">Pilih Nama Stock</option>
                                                    @foreach ($stocks as $stock)
                                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                                            <option value="{{ $stock['item'] }}">{{ $stock['item'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control descriptionInput"
                                                    name="transactions[0][description]" style="width: 50%;">
                                            </div>
                                        @else
                                            <input type="text" class="form-control descriptionInput"
                                                name="transactions[0][description]">
                                        @endif
                                    </td>
                                    <td>
                                        @if (in_array($voucher->voucher_type, ['PJ', 'PB', 'PH', 'PK']))
                                            <select class="form-control sizeInput" name="transactions[0][size]">
                                                <option value="">Pilih Ukuran</option>
                                            </select>
                                        @else
                                            <input type="text" class="form-control sizeInput"
                                                name="transactions[0][size]">
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" min="1" class="form-control quantityInput"
                                            name="transactions[0][quantity]" value="1">
                                    </td>
                                    <td>
                                        <input type="number" min="0" class="form-control nominalInput"
                                            name="transactions[0][nominal]">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control totalInput"
                                            name="transactions[0][total]" value="0" readonly>
                                    </td>
                                    <td class="text-center"></td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total Nominal:</strong></td>
                                <td>
                                    <input type="text" class="form-control" id="totalNominal"
                                        name="total_nominal"
                                        value="{{ number_format($voucher->total_nominal, 2, ',', '.') }}" readonly>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="addTransactionRowBtn" class="btn btn-primary">Tambah
                        Transaksi</button>
                </div>
            </div>
            <!-- Voucher Details Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="voucherDetailsTable">
                    <thead>
                        <tr class="text-center">
                            <th colspan="4">Rincian Voucher</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                        <tr class="text-center">
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="voucherDetailsTableBody">
                        @foreach ($voucher->voucherDetails as $index => $detail)
                            <tr>
                                <td>
                                    <input type="text" class="form-control accountCodeInput"
                                        name="voucher_details[{{ $index }}][account_code]"
                                        list="dynamicAccountCodes" value="{{ $detail->account_code }}"
                                        placeholder="Ketik atau pilih kode akun" required aria-required="true">
                                    <datalist id="dynamicAccountCodes">
                                        <option value="">Pilih Kode Akun</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->account_code }}">{{ $account->account_code }}
                                                - {{ $account->account_name }}</option>
                                        @endforeach
                                        @foreach ($subsidiariesData as $subsidiary)
                                            <option value="{{ $subsidiary['subsidiary_code'] }}">
                                                {{ $subsidiary['subsidiary_code'] }} -
                                                {{ $subsidiary['account_name'] }}</option>
                                        @endforeach
                                    </datalist>
                                    <div class="invalid-feedback">Kode Akun wajib diisi.</div>
                                </td>
                                <td>
                                    <input type="text" class="form-control accountName"
                                        name="voucher_details[{{ $index }}][account_name]"
                                        value="{{ $detail->account_name }}" readonly>
                                </td>
                                <td>
                                    <input type="number" min="0" class="form-control debitInput"
                                        name="voucher_details[{{ $index }}][debit]"
                                        value="{{ $detail->debit > 0 ? $detail->debit : '' }}">
                                </td>
                                <td>
                                    <input type="number" min="0" class="form-control creditInput"
                                        name="voucher_details[{{ $index }}][credit]"
                                        value="{{ $detail->credit > 0 ? $detail->credit : '' }}">
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                        class="btn btn-danger removeVoucherDetailRowBtn">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" id="addVoucherDetailRowBtn" class="btn btn-primary">Tambah Kode Akun</button>
            </div>
            <!-- Totals -->
            <div class="row mb-3">
                <label for="totalDebit" class="col-sm-3 col-form-label">Total Debit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="totalDebit" name="total_debit"
                        value="{{ number_format($voucher->total_debit, 2, ',', '.') }}" readonly>
                    <input type="hidden" id="totalDebitRaw" name="total_debit_raw"
                        value="{{ $voucher->total_debit }}">
                </div>
                <label for="totalCredit" class="col-sm-3 col-form-label">Total Kredit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="totalCredit" name="total_credit"
                        value="{{ number_format($voucher->total_credit, 2, ',', '.') }}" readonly>
                    <input type="hidden" id="totalCreditRaw" name="total_credit_raw"
                        value="{{ $voucher->total_credit }}">
                </div>
            </div>
            <!-- Validation Message -->
            <div class="row mb-3">
                <label for="validation" class="col-sm-3 col-form-label">Pesan:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="validation" readonly
                        value="Silakan isi formulir.">
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary" id="saveVoucherBtn">Simpan Perubahan</button>
            </div>
        </div>
    </form>
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
            const recipeContainer = document.getElementById('recipeContainer');
            const descriptionTextArea = document.getElementById('description');

            // --- Data from Laravel ---
            const existingInvoices = @json($existingInvoices);
            const storeNames = @json($storeNames);
            const subsidiaries = @json($subsidiariesData);
            const accounts = @json($accountsData);
            const stocks = @json($stocks);
            const transferStocks = @json($transferStocks);
            const usedStocks = @json($usedStocks);
            const transactionsData = @json($transactionsData);
            const recipes = @json($recipes);
            const currentVoucherType = @json($voucher->voucher_type);
            const hasInvoice = @json($voucher->invoice ? true : false);
            const voucherRecipeId = @json($voucher->recipe_id);
            // const voucherCreatedAt = @json($voucher->created_at ? $voucher->created_at : now() . toISOString());
            const voucherCreatedAt = @json($voucherCreatedAt ?? now()->toIso8601String());
            // --- Voucher Types ---
            const voucherTypes = {
                PJ: {
                    value: 'PJ',
                    text: 'Penjualan',
                    description: 'Voucher Penjualan - Dokumen internal perusahaan untuk mencatat transaksi penjualan barang atau jasa yang tidak dapat dicatat pada voucher lain.',
                    stock: true
                },
                PB: {
                    value: 'PB',
                    text: 'Pembelian',
                    description: 'Voucher Pembelian - Dokumen untuk mencatat transaksi pembelian barang atau jasa, seperti pembelian material, peralatan, atau layanan dari pemasok.',
                    stock: true
                },
                PG: {
                    value: 'PG',
                    text: 'Pengeluaran',
                    description: 'Voucher Pengeluaran - Dokumen untuk mencatat pengeluaran dana perusahaan, seperti pembayaran tagihan, pembelian material, atau biaya operasional, sebagai bukti otorisasi transaksi.',
                    stock: false
                },
                PM: {
                    value: 'PM',
                    text: 'Pemasukan',
                    description: 'Voucher Pemasukan - Dokumen internal perusahaan untuk mencatat penerimaan dana, seperti pembayaran dari pelanggan, setoran tunai, atau penerimaan lain yang masuk ke kas atau bank perusahaan.',
                    stock: false
                },
                LN: {
                    value: 'LN',
                    text: 'Lainnya',
                    description: 'Voucher Lainnya - Dokumen untuk mencatat transaksi yang tidak termasuk dalam kategori voucher lain, seperti koreksi jurnal atau transaksi khusus lainnya.',
                    stock: false
                },
                PH: {
                    value: 'PH',
                    text: 'Pemindahan',
                    description: 'Voucher Pemindahan - Dokumen untuk mencatat pemindahan stok barang dari satu lokasi ke lokasi lain dalam perusahaan.',
                    stock: true
                },
                PK: {
                    value: 'PK',
                    text: 'Pemakaian',
                    description: 'Voucher Pemakaian - Dokumen untuk mencatat pemakaian barang dalam operasional perusahaan.',
                    stock: true
                }
            };
            const voucherTypeOptions = Object.values(voucherTypes);

            // --- Update Voucher Type Dropdown ---
            function updateVoucherTypeOptions() {
                const useStock = useStockYes.checked ? 'yes' : 'no';
                voucherTypeSelect.innerHTML = '';
                const validOptions = voucherTypeOptions.filter(option => useStock === 'yes' ? option.stock : !option
                    .stock);
                validOptions.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value === currentVoucherType) opt.selected = true;
                    voucherTypeSelect.appendChild(opt);
                });
                if (!validOptions.some(opt => opt.value === voucherTypeSelect.value)) {
                    voucherTypeSelect.value = validOptions[0]?.value || '';
                }
                updateDescription();
                refreshTransactionTable();
                updateRecipeContainer();
                updateAllCalculationsAndValidations();
            }

            // --- Update Description ---
            function updateDescription() {
                const selectedType = voucherTypeSelect.value;
                descriptionTextArea.value = voucherTypes[selectedType]?.description || '';
            }

            // --- Recipe Handling ---
            function updateRecipeContainer() {
                const useStock = useStockYes.checked ? 'yes' : 'no';
                const voucherType = voucherTypeSelect.value;
                if (recipeContainer) {
                    recipeContainer.style.display = (useStock === 'yes' && voucherType === 'PK') ? 'block' : 'none';
                }
                const recipeSelect = document.getElementById('recipe');
                if (recipeSelect) {
                    recipeSelect.disabled = !(useStock === 'yes' && voucherType === 'PK');
                    if (useStock === 'yes' && voucherType === 'PK') {
                        handleRecipeChange();
                    } else {
                        refreshTransactionTable();
                    }
                }
            }

            function createRecipeDropdown() {
                if (!recipeContainer) return;
                recipeContainer.innerHTML = '';
                const label = document.createElement('label');
                label.htmlFor = 'recipe';
                label.className = 'col-sm-3 col-form-label';
                label.textContent = 'Formula Produk:';
                const selectDiv = document.createElement('div');
                selectDiv.className = 'col-sm-9';
                const select = document.createElement('select');
                select.className = 'form-select';
                select.id = 'recipe';
                select.name = 'recipe';
                select.innerHTML = '<option value="">Pilih Formula Produk</option>';
                recipes.forEach(recipe => {
                    const option = document.createElement('option');
                    option.value = recipe.id;
                    option.textContent = recipe.product_name;
                    if (recipe.id == voucherRecipeId) option.selected = true;
                    select.appendChild(option);
                });
                selectDiv.appendChild(select);
                recipeContainer.appendChild(label);
                recipeContainer.appendChild(selectDiv);
                select.addEventListener('change', debounce(handleRecipeChange, 300));
            }

            function handleRecipeChange() {
                const recipeSelect = document.getElementById('recipe');
                const useStock = useStockYes.checked ? 'yes' : 'no';
                const voucherType = voucherTypeSelect.value;
                if (useStock !== 'yes' || voucherType !== 'PK') return;
                const selectedRecipeId = recipeSelect.value;
                if (selectedRecipeId) {
                    populateTransactionTableFromRecipe(selectedRecipeId);
                    addTransactionRowBtn.disabled = true;
                    transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(btn => btn.disabled =
                        true);
                } else {
                    populateTransactionTableFromVoucher();
                    addTransactionRowBtn.disabled = false;
                    transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(btn => btn.disabled =
                        false);
                }
                updateAllCalculationsAndValidations();
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
                    if (useStockYes.checked && ['PJ', 'PB', 'PH', 'PK'].includes(voucherTypeSelect.value)) {
                        updateSizeDropdown(newRow, '');
                    }
                }
                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();
                updateAllCalculationsAndValidations();
            }

            function populateTransactionTableFromRecipe(recipeId) {
                const recipe = recipes.find(r => r.id == recipeId);
                if (!recipe || !recipe.transfer_stocks) {
                    console.warn('Recipe or transfer stocks not found for ID:', recipeId);
                    validationInput.value = 'Formula produk tidak memiliki data stok transfer.';
                    return;
                }
                if (!recipe.transfer_stocks.length) {
                    console.warn('No transfer stocks for recipe ID:', recipeId);
                    validationInput.value = 'Formula produk tidak memiliki stok transfer terkait.';
                    return;
                }
                transactionTableBody.innerHTML = '';
                recipe.transfer_stocks.forEach((stock, index) => {
                    const row = generateTransactionTableRow(index, {
                        description: stock.item,
                        size: stock.size,
                        quantity: stock.quantity,
                        nominal: stock.nominal || 0,
                        total: (stock.quantity * (stock.nominal || 0)).toFixed(2),
                        isHppRow: false
                    });
                    transactionTableBody.appendChild(row);
                });
                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();
                updateAllCalculationsAndValidations();
            }

            // --- Stock and Size Handling ---
            function getStockSource() {
                const voucherType = voucherTypeSelect.value;
                if (voucherType === 'PJ') return [...stocks, ...usedStocks]; // Combine stocks and usedStocks for PJ
                if (voucherType === 'PH') return stocks; // Use stocks for PH
                if (voucherType === 'PK') return usedStocks; // Use usedStocks for PK
                return stocks; // Default to stocks for other types
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

                if (voucherTypeSelect.value === 'PJ') {
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

                    // Add Barang Jadi (usedStocks)
                    const barangJadiItems = [...new Set(usedStocks.filter(s => !s.item.startsWith('HPP ')).map(s =>
                        s.item))];
                    if (barangJadiItems.length > 0) {
                        const optgroupBarangJadi = document.createElement('optgroup');
                        optgroupBarangJadi.label = '--Barang Jadi--';
                        barangJadiItems.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item;
                            option.textContent = item;
                            if (item === initialValue) option.selected = true;
                            optgroupBarangJadi.appendChild(option);
                        });
                        select.appendChild(optgroupBarangJadi);
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
                    if (oldItem && oldItem !== newItem && voucherTypeSelect.value === 'PJ') {
                        // Hapus corresponding HPP untuk oldItem
                        const oldHppRow = getCorrespondingHppRow(row, oldItem);
                        if (oldHppRow) {
                            oldHppRow.remove();
                            updateTransactionRowIndices();
                        }
                    }
                    updateSizeDropdown(row, newItem);
                    if (voucherTypeSelect.value === 'PJ' && newItem) {
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
                    if (voucherTypeSelect.value === 'PJ' && item && newSize) {
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
                    if (voucherTypeSelect.value === 'PJ') {
                        const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                        updateHppRow(index, item, currentSize, quantity);
                    }
                });
            }

            // --- HPP Logic ---
            function calculateAverageHpp(item, size, currentCreatedAt) {
                console.log('Calculating HPP for:', {
                    item,
                    size,
                    currentCreatedAt
                });
                console.log('Available transactionsData:', transactionsData);
                if (!transactionsData || !Array.isArray(transactionsData)) {
                    console.warn('transactionsData is empty or not an array');
                    validationInput.value = 'Data transaksi historis kosong.';
                    return 0;
                }
                if (!currentCreatedAt) {
                    console.warn('currentCreatedAt is undefined, using current time as fallback');
                    currentCreatedAt = new Date().toISOString();
                }
                const matchingTransactions = transactionsData.filter(t =>
                    t.description === item &&
                    t.size === size &&
                    !t.description.startsWith('HPP ') &&
                    t.created_at &&
                    new Date(t.created_at) < new Date(currentCreatedAt)
                );
                console.log('Matching transactions details:', matchingTransactions.map(t => ({
                    description: t.description,
                    size: t.size,
                    nominal: t.nominal,
                    created_at: t.created_at
                })));
                if (matchingTransactions.length === 0) {
                    console.warn(`No matching transactions found for item: ${item}, size: ${size}`);
                    validationInput.value =
                        `Tidak ada transaksi sebelumnya untuk item "${item}" dengan ukuran "${size}".`;
                    return 0;
                }
                const totalNominal = matchingTransactions.reduce((sum, t) => sum + (parseFloat(t.nominal) || 0), 0);
                console.log('Total nominal:', totalNominal, 'Count:', matchingTransactions.length);
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

                console.log('Adding HPP row for:', {
                    currentIndex,
                    item,
                    size,
                    quantity
                });

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
                    console.log('Skipping HPP update: missing item/size or in progress', {
                        item,
                        size,
                        isUpdatingHpp
                    });
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
                        // Update existing corresponding HPP row
                        console.log('Found corresponding HPP row, updating...', {
                            item,
                            size,
                            quantity,
                            nominal: averageHpp
                        });
                        hppRow.querySelector('.sizeInput').value = size;
                        hppRow.querySelector('.quantityInput').value = quantity;
                        hppRow.querySelector('.nominalInput').value = averageHpp.toFixed(2);
                        hppRow.querySelector('.totalInput').value = (quantity * averageHpp).toFixed(2);
                    } else {
                        // Create new HPP row after parent
                        console.log('No corresponding HPP found, creating new...', {
                            item,
                            size,
                            quantity,
                            nominal: averageHpp
                        });
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
                if (!['PJ', 'PH', 'PK'].includes(voucherType)) return true;
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
                } else if (useStock === 'yes' && ['PJ', 'PH', 'PK'].includes(voucherType)) {
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
                } else if (voucherType === 'PB' && useStock === 'yes') {
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
                if (isHppRow || useStock !== 'yes' || !['PJ', 'PB', 'PH', 'PK'].includes(voucherType)) {
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
                            console.log('Quantity changed, updating HPP row for:', {
                                item,
                                size,
                                quantity: quantityInput.value
                            });
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
                deleteButton.disabled = isHppRow || (useStock === 'yes' && voucherType === 'PK' && document
                    .getElementById('recipe')?.value);
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
                            if (voucherTypeSelect.value === 'PJ' && item) {
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
                const recipeSelected = document.getElementById('recipe')?.value;

                if (useStock === 'yes' && voucherType === 'PK' && recipeSelected) {
                    populateTransactionTableFromRecipe(recipeSelected);
                } else {
                    transactionsData.forEach((data, index) => {
                        const newRow = generateTransactionTableRow(index, data);
                        transactionTableBody.appendChild(newRow);
                        if (useStock === 'yes' && ['PJ', 'PB', 'PH', 'PK'].includes(voucherType) && !data
                            .isHppRow) {
                            updateSizeDropdown(newRow, data.description || data.description_select);
                        }
                    });
                    if (transactionTableBody.querySelectorAll('tr').length === 0) {
                        const newRow = generateTransactionTableRow(0);
                        transactionTableBody.appendChild(newRow);
                        if (useStock === 'yes' && ['PJ', 'PB', 'PH', 'PK'].includes(voucherType)) {
                            updateSizeDropdown(newRow, '');
                        }
                    }
                }
                updateTransactionRowIndices();
                if (voucherType === 'PJ') {
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
                    validateForm();
                });

                debitInput?.addEventListener('input', () => {
                    creditInput.value = debitInput.value ? '' : creditInput.value;
                    creditInput.disabled = !!debitInput.value;
                    updateAllCalculationsAndValidations();
                });

                creditInput?.addEventListener('input', () => {
                    debitInput.value = creditInput.value ? '' : debitInput.value;
                    debitInput.disabled = !!creditInput.value;
                    updateAllCalculationsAndValidations();
                });
            }

            function attachVoucherDetailRowEventListenersToAll() {
                voucherDetailsTableBody.querySelectorAll('tr').forEach((row, index) => {
                    attachVoucherDetailRowEventListeners(row, index);
                });
            }

            // --- Invoice and Store Fields ---
            function createStoreDropdown() {
                const select = document.createElement('select');
                select.className = 'form-select';
                select.id = 'store';
                select.name = 'store';
                select.innerHTML = '<option value="">Pilih Nama Toko</option>';
                storeNames.forEach(store => {
                    const option = document.createElement('option');
                    option.value = store;
                    option.textContent = store;
                    if (store === @json($voucher->store)) option.selected = true;
                    select.appendChild(option);
                });
                return select;
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
                const invoiceLabel = document.createElement('label');
                invoiceLabel.htmlFor = 'invoice';
                invoiceLabel.className = 'col-sm-3 col-form-label';
                invoiceLabel.textContent = 'Nomor Invoice:';
                const invoiceInputDiv = document.createElement('div');
                invoiceInputDiv.className = 'col-sm-9';
                const invoiceInput = useInvoice === 'yes' && useExistingInvoice === 'yes' ?
                    createInvoiceDropdown() : createInvoiceInput();
                invoiceInputDiv.appendChild(invoiceInput);
                const invalidFeedback = document.createElement('div');
                invalidFeedback.className = 'invalid-feedback';
                invalidFeedback.textContent = 'Nomor Invoice wajib diisi jika menggunakan invoice.';
                invoiceInputDiv.appendChild(invalidFeedback);
                invoiceFieldContainer.appendChild(invoiceLabel);
                invoiceFieldContainer.appendChild(invoiceInputDiv);
                updateAccountCodeDatalist();
                validateForm();
            }

            function updateDueDateField() {
                const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
                const useExistingInvoice = useExistingInvoiceYes.checked ? 'yes' : 'no';
                dueDateContainer.innerHTML = '';
                const dueDateLabel = document.createElement('label');
                dueDateLabel.htmlFor = 'due_date';
                dueDateLabel.className = 'col-sm-3 col-form-label';
                dueDateLabel.textContent = 'Tanggal Jatuh Tempo:';
                const dueDateInputDiv = document.createElement('div');
                dueDateInputDiv.className = 'col-sm-9';
                const dueDateInput = document.createElement('input');
                dueDateInput.type = 'date';
                dueDateInput.className = 'form-control';
                dueDateInput.id = 'due_date';
                dueDateInput.name = 'due_date';
                dueDateInput.disabled = useInvoice !== 'yes' || useExistingInvoice === 'yes';
                dueDateInput.value = useInvoice === 'yes' && @json($dueDate) ?
                    @json($dueDate) : '';
                dueDateInputDiv.appendChild(dueDateInput);
                const invalidFeedback = document.createElement('div');
                invalidFeedback.className = 'invalid-feedback';
                invalidFeedback.textContent = 'Tanggal Jatuh Tempo wajib diisi untuk invoice baru.';
                dueDateInputDiv.appendChild(invalidFeedback);
                dueDateContainer.appendChild(dueDateLabel);
                dueDateContainer.appendChild(dueDateInputDiv);
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
                totalCreditRawInput.value = totalDebit.toFixed(2);
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
                    return false;
                } else if (totalDebitRaw !== totalCreditRaw) {
                    validationInput.value = "Total Debit harus sama dengan Total Kredit.";
                    saveVoucherBtn.disabled = true;
                    return false;
                } else {
                    validationInput.value = "Totalnya seimbang dan valid.";
                    saveVoucherBtn.disabled = false;
                    return true;
                }
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

                // Validate stock quantities for PJ, PH, PK
                const voucherType = voucherTypeSelect.value;
                if (['PJ', 'PH', 'PK'].includes(voucherType)) {
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
                    if (voucherType === 'PJ') {
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
                updateRecipeContainer();
                refreshTransactionTable();
                updateAllCalculationsAndValidations();
            });

            useInvoiceYes.addEventListener('change', updateInvoiceAndStoreFields);
            useInvoiceNo.addEventListener('change', updateInvoiceAndStoreFields);
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
                const recipeSelected = document.getElementById('recipe')?.value;
                if (useStock === 'yes' && voucherType === 'PK' && recipeSelected) {
                    alert("Tidak dapat menambah baris transaksi saat formula produk dipilih.");
                    return;
                }
                const newIndex = transactionTableBody.querySelectorAll('tr').length;
                const newRow = generateTransactionTableRow(newIndex);
                transactionTableBody.appendChild(newRow);
                if (useStock === 'yes' && ['PJ', 'PB', 'PH', 'PK'].includes(voucherType)) {
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
                if (voucherType !== 'PJ') return;

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
                updateVoucherTypeOptions();
                if (recipeContainer) createRecipeDropdown();
                updateInvoiceAndStoreFields();
                updateAccountCodeDatalist();
                // Initialize recipe and transactions for PK vouchers
                if (currentVoucherType === 'PK' && useStockYes.checked && voucherRecipeId) {
                    const recipeSelect = document.getElementById('recipe');
                    if (recipeSelect) {
                        recipeSelect.value = voucherRecipeId;
                        handleRecipeChange(); // Populate transactions based on selected recipe
                    }
                } else {
                    refreshTransactionTable();
                }
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
            if (recipeContainer) createRecipeDropdown();
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
</div>
@endsection
