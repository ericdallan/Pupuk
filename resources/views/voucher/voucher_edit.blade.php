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

    <form id="voucherForm" method="POST" action="{{ route('voucher.update', $voucher->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="container mt-4">
            <h2 class="text-center">Formulir Edit {{ $headingText }} Voucher</h2>

            <div class="row mb-3">
                <label for="voucherNumber" class="col-sm-3 col-form-label">Nomor Voucher:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="voucherNumber" name="voucher_number" value="{{ $voucher->voucher_number }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label for="companyName" class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                <div class="col-sm-9">
                    @if ($company)
                    <input type="text" class="form-control" id="companyName" value="{{ $company->company_name }}" readonly>
                    @else
                    <input type="text" class="form-control" id="companyName" value="Nama Perusahaan Kosong" readonly>
                    <small class="text-danger">Nama Perusahaan Belum Ditemukan.</small>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <label for="voucherType" class="col-sm-3 col-form-label">Tipe Voucher:</label>
                <div class="col-sm-3">
                    <select class="form-select" id="voucherType" name="voucher_type" required aria-required="true">
                        <option value="PJ" {{ $voucher->voucher_type == 'PJ' ? 'selected' : '' }}>Penjualan</option>
                        <option value="PG" {{ $voucher->voucher_type == 'PG' ? 'selected' : '' }}>Pengeluaran</option>
                        <option value="PM" {{ $voucher->voucher_type == 'PM' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="PB" {{ $voucher->voucher_type == 'PB' ? 'selected' : '' }}>Pembelian</option>
                        <option value="LN" {{ $voucher->voucher_type == 'LN' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <div class="invalid-feedback">Tipe Voucher wajib dipilih.</div>
                </div>
                <label for="voucherDate" class="col-sm-2 col-form-label">Tanggal:</label>
                <div class="col-sm-2">
                    <input type="date" class="form-control" id="voucherDate" name="voucher_date" value="{{ $voucher->voucher_date->format('Y-m-d') }}" required aria-required="true">
                    <div class="invalid-feedback">Tanggal wajib diisi.</div>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="voucherDay" name="voucher_day" value="{{ $voucher->voucher_day }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label for="preparedBy" class="col-sm-3 col-form-label">Dibuat Oleh:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="preparedBy" name="prepared_by" value="{{ $voucher->prepared_by }}" required aria-required="true">
                    <div class="invalid-feedback">Dibuat Oleh wajib diisi.</div>
                </div>
            </div>

            <div class="row mb-3">
                <label for="givenTo" class="col-sm-3 col-form-label">Diberikan Kepada:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="givenTo" name="given_to" value="{{ $voucher->given_to }}">
                </div>
            </div>

            <div class="row mb-3">
                <label for="approvedBy" class="col-sm-3 col-form-label">Disetujui Oleh:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="approvedBy" name="approved_by" value="{{ $voucher->approved_by }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <label for="transaction" class="col-sm-3 col-form-label">Transaksi:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="transaction" name="transaction" value="{{ $voucher->transaction }}">
                </div>
            </div>

            <!-- Gunakan Invoice? -->
            <div class="row mb-3">
                <label for="useInvoice" class="col-sm-3 col-form-label">Gunakan Invoice?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useInvoiceYes" name="use_invoice" value="yes" {{ $voucher->use_invoice === 'yes' ? 'checked' : '' }} required aria-required="true">
                        <label class="form-check-label" for="useInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useInvoiceNo" name="use_invoice" value="no" {{ $voucher->use_invoice !== 'yes' ? 'checked' : '' }} required aria-required="true">
                        <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                    </div>
                    <div class="invalid-feedback">Pilih apakah menggunakan invoice.</div>
                </div>
            </div>

            <!-- Gunakan Invoice yang Sudah Ada? -->
            <div class="row mb-3" id="existingInvoiceContainer" style="display: {{ $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label class="col-sm-3 col-form-label">Gunakan Invoice yang Sudah Ada?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceYes" name="use_existing_invoice" value="yes" {{ $voucher->use_existing_invoice === 'yes' ? 'checked' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceNo" name="use_existing_invoice" value="no" {{ $voucher->use_existing_invoice !== 'yes' ? 'checked' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceNo">Tidak</label>
                    </div>
                </div>
            </div>

            <!-- Nomor Invoice -->
            <div class="row mb-3" id="invoiceFieldContainer" style="display: {{ $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="invoice" class="col-sm-3 col-form-label">Nomor Invoice:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="invoice" name="invoice" value="{{ $voucher->invoice ?? '' }}">
                    <div class="invalid-feedback">Nomor Invoice wajib diisi jika menggunakan invoice.</div>
                </div>
            </div>

            <!-- Tanggal Jatuh Tempo -->
            <div class="row mb-3" id="dueDateContainer" style="display: {{ $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="due_date" class="col-sm-3 col-form-label">Tanggal Jatuh Tempo:</label>
                <div class="col-sm-9">
                    <input type="date" class="form-control" id="due_date" name="due_date" value="{{ $dueDate }}" {{ $voucher->use_existing_invoice === 'yes' ? 'disabled' : '' }}>
                    <div class="invalid-feedback">Tanggal Jatuh Tempo wajib diisi untuk invoice baru.</div>
                </div>
            </div>

            <!-- Nama Toko -->
            <div class="row mb-3" id="storeFieldContainer" style="display: {{ $voucher->use_invoice === 'yes' ? 'block' : 'none' }};">
                <label for="store" class="col-sm-3 col-form-label">Nama Toko:</label>
                <div class="col-sm-9">
                    <select class="form-select" id="store" name="store">
                        <option value="">Pilih Nama Toko</option>
                        @foreach($storeNames as $storeName)
                        <option value="{{ $storeName }}" {{ $voucher->store == $storeName ? 'selected' : '' }}>{{ $storeName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Rincian Transaksi -->
            <div class="mb-3">
                <h5>Rincian Transaksi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="transactionDetailsTable">
                        <thead>
                            <tr class="text-center">
                                <th colspan="3">Rincian Transaksi</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                            <tr class="text-center">
                                <th>Deskripsi</th>
                                <th>Quantitas</th>
                                <th>Nominal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($voucher->transactions->isNotEmpty())
                            @foreach($voucher->transactions as $index => $transaction)
                            <tr data-row-index="{{ $index }}" data-is-hpp-row="{{ str_starts_with($transaction->description, 'HPP ') ? 'true' : 'false' }}">
                                <td>
                                    @if (str_starts_with($transaction->description, 'HPP '))
                                    <input type="text" class="form-control descriptionInput" name="transactions[{{ $index }}][description]" value="{{ $transaction->description }}" readonly>
                                    @elseif ($voucher->voucher_type == 'PJ')
                                    <select class="form-control descriptionInput" name="transactions[{{ $index }}][description]" data-initial-value="{{ $transaction->description }}">
                                        <option value="">Pilih Nama Stock</option>
                                        @foreach($stocks as $stock)
                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                        <option value="{{ $stock['item'] }}" {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>{{ $stock['item'] }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @elseif ($voucher->voucher_type == 'PB')
                                    <div class="input-group">
                                        <select class="form-control descriptionInput" style="width: 50%;">
                                            <option value="">Pilih Nama Stock</option>
                                            @foreach($stocks as $stock)
                                            @if (!str_starts_with($stock['item'], 'HPP '))
                                            <option value="{{ $stock['item'] }}" {{ $transaction->description == $stock['item'] ? 'selected' : '' }}>{{ $stock['item'] }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control descriptionInput" style="width: 50%;" name="transactions[{{ $index }}][description]" value="{{ $transaction->description }}">
                                    </div>
                                    @else
                                    <input type="text" class="form-control descriptionInput" name="transactions[{{ $index }}][description]" value="{{ $transaction->description }}">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" min="1" class="form-control quantityInput" name="transactions[{{ $index }}][quantity]" value="{{ $transaction->quantity }}" {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}>
                                </td>
                                <td>
                                    <input type="number" min="0" class="form-control nominalInput" name="transactions[{{ $index }}][nominal]" value="{{ $transaction->nominal }}" {{ str_starts_with($transaction->description, 'HPP ') ? 'readonly' : '' }}>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger removeTransactionRowBtn" {{ str_starts_with($transaction->description, 'HPP ') ? 'disabled' : '' }}>Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr data-row-index="0">
                                <td>
                                    @if ($voucher->voucher_type == 'PJ')
                                    <select class="form-control descriptionInput" name="transactions[0][description]">
                                        <option value="">Pilih Nama Stock</option>
                                        @foreach($stocks as $stock)
                                        @if (!str_starts_with($stock['item'], 'HPP '))
                                        <option value="{{ $stock['item'] }}">{{ $stock['item'] }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @elseif ($voucher->voucher_type == 'PB')
                                    <div class="input-group">
                                        <select class="form-control descriptionInput" style="width: 50%;">
                                            <option value="">Pilih Nama Stock</option>
                                            @foreach($stocks as $stock)
                                            @if (!str_starts_with($stock['item'], 'HPP '))
                                            <option value="{{ $stock['item'] }}">{{ $stock['item'] }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control descriptionInput" style="width: 50%;" name="transactions[0][description]">
                                    </div>
                                    @else
                                    <input type="text" class="form-control descriptionInput" name="transactions[0][description]">
                                    @endif
                                </td>
                                <td>
                                    <input type="number" min="1" class="form-control quantityInput" name="transactions[0][quantity]" value="1">
                                </td>
                                <td>
                                    <input type="number" min="0" class="form-control nominalInput" name="transactions[0][nominal]">
                                </td>
                                <td class="text-center"></td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total Nominal:</strong></td>
                                <td>
                                    <input type="text" class="form-control" id="totalNominal" name="total_nominal" value="{{ number_format($voucher->total_nominal, 2, ',', '.') }}" readonly>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="addTransactionRowBtn" class="btn btn-primary">Tambah Transaksi</button>
                </div>
            </div>

            <!-- Rincian Voucher -->
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
                        @foreach($voucher->voucherDetails as $index => $detail)
                        <tr>
                            <td>
                                <input type="text" class="form-control accountCodeInput" name="voucher_details[{{ $index }}][account_code]" list="dynamicAccountCodes" value="{{ $detail->account_code }}" placeholder="Ketik atau pilih kode akun" required aria-required="true">
                                <datalist id="dynamicAccountCodes">
                                    <option value="">Pilih Kode Akun</option>
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->account_code }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                    @endforeach
                                    @foreach($subsidiariesData as $subsidiary)
                                    <option value="{{ $subsidiary['subsidiary_code'] }}">{{ $subsidiary['subsidiary_code'] }} - {{ $subsidiary['account_name'] }}</option>
                                    @endforeach
                                </datalist>
                                <div class="invalid-feedback">Kode Akun wajib diisi.</div>
                            </td>
                            <td>
                                <input type="text" class="form-control accountName" name="voucher_details[{{ $index }}][account_name]" value="{{ $detail->account_name }}" readonly>
                            </td>
                            <td>
                                <input type="number" min="0" class="form-control debitInput" name="voucher_details[{{ $index }}][debit]" value="{{ $detail->debit > 0 ? $detail->debit : '' }}">
                            </td>
                            <td>
                                <input type="number" min="0" class="form-control creditInput" name="voucher_details[{{ $index }}][credit]" value="{{ $detail->credit > 0 ? $detail->credit : '' }}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger removeVoucherDetailRowBtn">Hapus</button>
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
                    <input type="text" class="form-control" id="totalDebit" name="total_debit" value="{{ number_format($voucher->total_debit, 2, ',', '.') }}" readonly>
                    <input type="hidden" id="totalDebitRaw" name="total_debit_raw" value="{{ $voucher->total_debit }}">
                </div>
                <label for="totalCredit" class="col-sm-3 col-form-label">Total Kredit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="totalCredit" name="total_credit" value="{{ number_format($voucher->total_credit, 2, ',', '.') }}" readonly>
                    <input type="hidden" id="totalCreditRaw" name="total_credit_raw" value="{{ $voucher->total_credit }}">
                </div>
            </div>

            <!-- Validation Message -->
            <div class="row mb-3">
                <label for="validation" class="col-sm-3 col-form-label">Pesan:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="validation" readonly value="Silakan isi formulir.">
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" id="saveVoucherBtn">Simpan Perubahan</button>
            </div>
        </div>
    </form>

    <script>
        /**
         * @file Script untuk mengelola formulir edit voucher, termasuk penambahan/penghapusan baris transaksi dan detail voucher,
         * perhitungan total, validasi kesimbangan total, pengelolaan invoice, kode akun, dan HPP.
         */
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
            const useInvoiceYes = document.getElementById('useInvoiceYes');
            const useInvoiceNo = document.getElementById('useInvoiceNo');
            const invoiceFieldContainer = document.getElementById('invoiceFieldContainer');
            const dueDateContainer = document.getElementById('dueDateContainer');
            const storeFieldContainer = document.getElementById('storeFieldContainer');
            const existingInvoiceContainer = document.getElementById('existingInvoiceContainer');
            const useExistingInvoiceYes = document.getElementById('useExistingInvoiceYes');
            const useExistingInvoiceNo = document.getElementById('useExistingInvoiceNo');

            // --- Data from Laravel ---
            const existingInvoices = @json($existingInvoices);
            const storeNames = @json($storeNames);
            const subsidiaries = @json($subsidiariesData);
            const accounts = @json($accountsData);
            const stocks = @json($stocks);
            const transactions = @json($transactionsData);

            // --- Validation Functions ---
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

                // Validate HPP rows for PJ vouchers
                if (voucherTypeSelect.value === 'PJ') {
                    const rows = transactionTableBody.querySelectorAll('tr');
                    const stockDescriptions = new Set();
                    rows.forEach(row => {
                        const description = row.querySelector('.descriptionInput')?.value || '';
                        const isHpp = row.dataset.isHppRow === 'true';
                        if (!isHpp && description && !description.startsWith('HPP ')) {
                            stockDescriptions.add(description);
                        }
                    });
                    rows.forEach(row => {
                        const description = row.querySelector('.descriptionInput')?.value || '';
                        const isHpp = row.dataset.isHppRow === 'true';
                        if (isHpp && description) {
                            const stockItem = description.replace(/^HPP /, '');
                            if (!stockDescriptions.has(stockItem)) {
                                validationInput.value = `Baris HPP untuk "${stockItem}" tidak memiliki transaksi stok yang sesuai.`;
                                isValid = false;
                            }
                        }
                    });
                }

                return isValid;
            }

            // --- Subsidiary Code Check ---
            function isSubsidiaryCodeUsed() {
                const accountCodeInputs = voucherDetailsTableBody.querySelectorAll('.accountCodeInput');
                for (let input of accountCodeInputs) {
                    const code = input.value.trim();
                    if (subsidiaries.some(s => s.subsidiary_code === code)) {
                        return true;
                    }
                }
                return false;
            }

            // --- Update Account Code Datalist ---
            function updateAccountCodeDatalist(isNewRow = false) {
                const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';
                const datalists = document.querySelectorAll('#dynamicAccountCodes');
                const subsidiaryUsed = isSubsidiaryCodeUsed();

                datalists.forEach(datalist => {
                    datalist.innerHTML = '';
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Pilih Kode Akun';
                    datalist.appendChild(defaultOption);

                    if (useInvoice === 'yes' && !subsidiaryUsed && !isNewRow) {
                        subsidiaries.forEach(subsidiary => {
                            const option = document.createElement('option');
                            option.value = subsidiary.subsidiary_code;
                            option.textContent = `${subsidiary.subsidiary_code} - ${subsidiary.account_name}`;
                            datalist.appendChild(option);
                        });
                    } else {
                        accounts.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.account_code;
                            option.textContent = `${account.account_code} - ${account.account_name}`;
                            datalist.appendChild(option);
                        });
                        subsidiaries.forEach(subsidiary => {
                            const option = document.createElement('option');
                            option.value = subsidiary.subsidiary_code;
                            option.textContent = `${subsidiary.subsidiary_code} - ${subsidiary.account_name}`;
                            datalist.appendChild(option);
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
                        if (subsidiary) {
                            accountNameInput.value = subsidiary.account_name;
                        }
                    } else {
                        const account = accounts.find(a => a.account_code === enteredCode);
                        if (account) {
                            accountNameInput.value = account.account_name;
                        }
                    }
                });
            }

            // --- Invoice and Store Field Logic ---
            function createStoreDropdown() {
                const select = document.createElement('select');
                select.className = 'form-select';
                select.id = 'store';
                select.name = 'store';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Pilih Nama Toko';
                select.appendChild(defaultOption);

                storeNames.forEach(store => {
                    const option = document.createElement('option');
                    option.value = store;
                    option.textContent = store;
                    select.appendChild(option);
                });

                return select;
            }

            function createInvoiceDropdown() {
                const select = document.createElement('select');
                select.className = 'form-control';
                select.id = 'invoice';
                select.name = 'invoice';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Pilih Nomor Invoice';
                select.appendChild(defaultOption);

                existingInvoices.forEach(invoice => {
                    if (invoice) {
                        const option = document.createElement('option');
                        option.value = invoice;
                        option.textContent = invoice;
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
                let invoiceInput;
                if (useInvoice === 'yes') {
                    invoiceInput = useExistingInvoice === 'yes' ? createInvoiceDropdown() : createInvoiceInput();
                } else {
                    invoiceInput = createInvoiceInput();
                    invoiceInput.disabled = true;
                    invoiceInput.value = '';
                }
                invoiceInputDiv.appendChild(invoiceInput);
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
                if (useInvoice !== 'yes') {
                    dueDateInput.value = '';
                }
                dueDateInputDiv.appendChild(dueDateInput);
                dueDateContainer.appendChild(dueDateLabel);
                dueDateContainer.appendChild(dueDateInputDiv);
                if (useInvoice === 'yes' && useExistingInvoice === 'no') {
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

            useInvoiceYes?.addEventListener('change', updateInvoiceAndStoreFields);
            useInvoiceNo?.addEventListener('change', updateInvoiceAndStoreFields);
            useExistingInvoiceYes?.addEventListener('change', function() {
                updateInvoiceField();
                updateDueDateField();
            });
            useExistingInvoiceNo?.addEventListener('change', function() {
                updateInvoiceField();
                updateDueDateField();
            });

            // --- Stock and HPP Logic ---
            function createStockDropdown(index, initialValue = '') {
                const select = document.createElement('select');
                select.className = 'form-control descriptionInput';
                select.name = `transactions[${index}][description]`;
                select.dataset.listenerAttached = 'false';

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Pilih Nama Stock';
                select.appendChild(defaultOption);

                const filteredStocks = stocks.filter(stock => !stock.item.startsWith('HPP '));
                filteredStocks.forEach(stock => {
                    const option = document.createElement('option');
                    option.value = stock.item;
                    option.textContent = stock.item;
                    if (stock.item === initialValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                return select;
            }

            function createDescriptionInput(index, initialValue = '') {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control descriptionInput';
                input.name = `transactions[${index}][description]`;
                input.value = initialValue;
                return input;
            }

            function calculateAverageHpp(item) {
                if (!transactions || !Array.isArray(transactions) || transactions.length === 0) {
                    return 0;
                }

                const matchingTransactions = transactions.filter(t => t.description === item);
                if (matchingTransactions.length === 0) {
                    return 0;
                }

                const totalNominal = matchingTransactions.reduce((sum, t) => sum + (parseFloat(t.nominal) || 0), 0);
                const transactionCount = matchingTransactions.length;
                return transactionCount > 0 ? totalNominal / transactionCount : 0;
            }

            function addHppRow(currentIndex, selectedItem, quantity) {
                if (!selectedItem) return;

                const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
                const newIndex = transactionTableBody.querySelectorAll('tr').length;
                const hppRow = document.createElement('tr');
                hppRow.dataset.rowIndex = newIndex;
                hppRow.dataset.isHppRow = 'true';

                const descriptionCell = document.createElement('td');
                const descriptionInput = createDescriptionInput(newIndex, `HPP ${selectedItem}`);
                descriptionInput.readOnly = true;
                descriptionCell.appendChild(descriptionInput);
                hppRow.appendChild(descriptionCell);

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
                const averageHpp = calculateAverageHpp(selectedItem);
                nominalInput.value = averageHpp.toFixed(2);
                nominalInput.readOnly = true;
                nominalCell.appendChild(nominalInput);
                hppRow.appendChild(nominalCell);

                const actionCell = document.createElement('td');
                actionCell.className = 'text-center';
                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
                deleteButton.textContent = 'Hapus';
                deleteButton.disabled = true;
                actionCell.appendChild(deleteButton);
                hppRow.appendChild(actionCell);

                if (currentRow.nextSibling) {
                    transactionTableBody.insertBefore(hppRow, currentRow.nextSibling);
                } else {
                    transactionTableBody.appendChild(hppRow);
                }

                updateTransactionRowIndices();
                updateAllCalculationsAndValidations();
            }

            function updateHppRow(currentIndex, selectedItem, quantity) {
                if (!selectedItem) return;

                const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
                let nextRow = currentRow.nextSibling;

                while (nextRow) {
                    if (nextRow.dataset.isHppRow === 'true') {
                        const descriptionInput = nextRow.querySelector('.descriptionInput');
                        descriptionInput.value = `HPP ${selectedItem}`;
                        const quantityInput = nextRow.querySelector('.quantityInput');
                        quantityInput.value = quantity;
                        const nominalInput = nextRow.querySelector('.nominalInput');
                        const averageHpp = calculateAverageHpp(selectedItem);
                        nominalInput.value = averageHpp.toFixed(2);
                        updateAllCalculationsAndValidations();
                        return;
                    }
                    nextRow = nextRow.nextSibling;
                }

                addHppRow(currentIndex, selectedItem, quantity);
            }

            function handleStockChange(index, event) {
                const selectedItem = event.target.value;
                const row = event.target.closest('tr');
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;

                console.log(`handleStockChange called for row ${index}: selectedItem=${selectedItem}, quantity=${quantity}`);

                if (selectedItem) {
                    let nextRow = row.nextSibling;
                    let hppRowExists = false;
                    while (nextRow) {
                        if (nextRow.dataset.isHppRow === 'true') {
                            hppRowExists = true;
                            break;
                        }
                        nextRow = nextRow.nextSibling;
                    }

                    if (hppRowExists) {
                        updateHppRow(index, selectedItem, quantity);
                    } else {
                        addHppRow(index, selectedItem, quantity);
                    }
                } else {
                    let nextRow = row.nextSibling;
                    while (nextRow) {
                        if (nextRow.dataset.isHppRow === 'true') {
                            nextRow.remove();
                            updateTransactionRowIndices();
                            break;
                        }
                        nextRow = nextRow.nextSibling;
                    }
                }

                updateAllCalculationsAndValidations();
            }

            // --- Transaction Table Row Generation ---
            function generateTransactionTableRow(index, transactionData = null) {
                const row = document.createElement('tr');
                row.dataset.rowIndex = index;
                row.dataset.isHppRow = transactionData?.isHppRow ? 'true' : 'false';

                const descriptionCell = document.createElement('td');
                let descriptionElement;
                const voucherType = voucherTypeSelect.value;
                const description = transactionData?.description || '';
                const isHppRow = row.dataset.isHppRow === 'true';

                if (isHppRow) {
                    descriptionElement = createDescriptionInput(index, description);
                    descriptionElement.readOnly = true;
                } else if (voucherType === 'PJ') {
                    descriptionElement = createStockDropdown(index, description);
                    if (descriptionElement.dataset.listenerAttached === 'false') {
                        descriptionElement.addEventListener('change', handleStockChange.bind(null, index));
                        descriptionElement.dataset.listenerAttached = 'true';
                    }
                } else if (voucherType === 'PB') {
                    descriptionElement = document.createElement('div');
                    descriptionElement.className = 'input-group';

                    const select = createStockDropdown(index, description);
                    select.className = 'form-control descriptionInput';
                    select.style.width = '50%';

                    const input = createDescriptionInput(index, description);
                    input.className = 'form-control descriptionInput';
                    input.style.width = '50%';

                    select.addEventListener('change', function() {
                        input.value = this.value;
                        updateAllCalculationsAndValidations();
                    });

                    input.addEventListener('input', function() {
                        select.value = '';
                        updateAllCalculationsAndValidations();
                    });

                    descriptionElement.appendChild(select);
                    descriptionElement.appendChild(input);
                } else {
                    descriptionElement = createDescriptionInput(index, description);
                }

                descriptionCell.appendChild(descriptionElement);
                row.appendChild(descriptionCell);

                const quantityCell = document.createElement('td');
                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.min = '1';
                quantityInput.className = 'form-control quantityInput';
                quantityInput.name = `transactions[${index}][quantity]`;
                quantityInput.value = transactionData?.quantity || '1';
                if (isHppRow) quantityInput.readOnly = true;
                quantityCell.appendChild(quantityInput);
                row.appendChild(quantityCell);

                const nominalCell = document.createElement('td');
                const nominalInput = document.createElement('input');
                nominalInput.type = 'number';
                nominalInput.min = '0';
                nominalInput.className = 'form-control nominalInput';
                nominalInput.name = `transactions[${index}][nominal]`;
                nominalInput.value = transactionData?.nominal || '';
                if (isHppRow) nominalInput.readOnly = true;
                nominalCell.appendChild(nominalInput);
                row.appendChild(nominalCell);

                const actionCell = document.createElement('td');
                actionCell.className = 'text-center';
                const deleteButton = document.createElement('button');
                deleteButton.type = 'button';
                deleteButton.className = 'btn btn-danger removeTransactionRowBtn';
                deleteButton.textContent = 'Hapus';
                deleteButton.disabled = isHppRow;
                actionCell.appendChild(deleteButton);
                row.appendChild(actionCell);

                return row;
            }

            function updateTransactionRowIndices() {
                transactionTableBody.querySelectorAll('tr').forEach((row, index) => {
                    row.dataset.rowIndex = index;
                    row.querySelectorAll('[name*="transactions["]').forEach(element => {
                        element.name = element.name.replace(/transactions\[\d+\]/, `transactions[${index}]`);
                    });
                });
                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();
            }

            function attachTransactionRemoveButtonListeners() {
                transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(button => {
                    const row = button.closest('tr');
                    const isHppRow = row.dataset.isHppRow === 'true';
                    button.disabled = isHppRow;

                    button.addEventListener('click', function() {
                        const totalRows = transactionTableBody.querySelectorAll('tr').length;
                        if (totalRows > 1) {
                            const row = this.closest('tr');
                            const rowIndex = parseInt(row.dataset.rowIndex);
                            const isHppRow = row.dataset.isHppRow === 'true';
                            const voucherType = voucherTypeSelect.value;

                            if (voucherType === 'PJ') {
                                if (isHppRow) {
                                    alert("Baris HPP tidak dapat dihapus secara langsung. Hapus baris item terkait terlebih dahulu.");
                                    return;
                                } else {
                                    const description = row.querySelector('.descriptionInput:not([type="text"])')?.value || '';
                                    let nextRow = row.nextSibling;
                                    while (nextRow) {
                                        if (nextRow.dataset.isHppRow === 'true' && nextRow.querySelector('.descriptionInput')?.value === `HPP ${description}`) {
                                            nextRow.remove();
                                            break;
                                        }
                                        nextRow = nextRow.nextSibling;
                                    }
                                }
                            }

                            row.remove();
                            updateTransactionRowIndices();
                            updateAllCalculationsAndValidations();
                        } else {
                            alert("Tidak dapat menghapus baris transaksi terakhir.");
                        }
                    });
                });
            }

            function attachTransactionInputListeners() {
                const transactionInputs = transactionTableBody.querySelectorAll('.quantityInput, .nominalInput, .descriptionInput');
                transactionInputs.forEach(input => {
                    input.removeEventListener('input', updateAllCalculationsAndValidations);
                    input.addEventListener('input', updateAllCalculationsAndValidations);
                });

                // Re-attach change listeners for stock dropdowns
                transactionTableBody.querySelectorAll('.descriptionInput:not([type="text"])').forEach(select => {
                    if (select.dataset.listenerAttached !== 'true') {
                        const index = parseInt(select.closest('tr').dataset.rowIndex);
                        select.addEventListener('change', handleStockChange.bind(null, index));
                        select.dataset.listenerAttached = 'true';
                    }
                });
            }

            function refreshTransactionTable() {
                const rows = transactionTableBody.querySelectorAll('tr');
                const transactionsData = Array.from(rows).map(row => {
                    const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
                    const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
                    const quantityInput = row.querySelector('.quantityInput');
                    const nominalInput = row.querySelector('.nominalInput');
                    const isHppRow = row.dataset.isHppRow === 'true';
                    return {
                        description: descriptionInput?.value || descriptionSelect?.value || '',
                        quantity: quantityInput?.value || '1',
                        nominal: nominalInput?.value || '0',
                        isHppRow: isHppRow
                    };
                });

                console.log('Initial transactionsData:', transactionsData);

                transactionTableBody.innerHTML = '';
                transactionsData.forEach((data, index) => {
                    const newRow = generateTransactionTableRow(index, data);
                    transactionTableBody.appendChild(newRow);
                    console.log(`Row ${index}: description=${data.description}, isHppRow=${data.isHppRow}`);
                });

                if (transactionTableBody.querySelectorAll('tr').length === 0) {
                    const newRow = generateTransactionTableRow(0);
                    transactionTableBody.appendChild(newRow);
                }

                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();

                // Trigger handleStockChange for PJ vouchers to ensure HPP rows are added
                if (voucherTypeSelect.value === 'PJ') {
                    transactionTableBody.querySelectorAll('tr').forEach((row, index) => {
                        const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
                        if (descriptionSelect && descriptionSelect.value && row.dataset.isHppRow !== 'true') {
                            const event = new Event('change', {
                                bubbles: true
                            });
                            descriptionSelect.dispatchEvent(event);
                            console.log(`Dispatched change event for row ${index}, value=${descriptionSelect.value}`);
                        }
                    });
                }
            }

            addTransactionRowBtn.addEventListener('click', function() {
                const newIndex = transactionTableBody.querySelectorAll('tr').length;
                const newRow = generateTransactionTableRow(newIndex);
                transactionTableBody.appendChild(newRow);
                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();
                updateAllCalculationsAndValidations();
            });

            // --- Voucher Detail Table Row Generation ---
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
                accountCodeInput.setAttribute('aria-required', 'true');
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
                        input.name = input.name.replace(/voucher_details\[\d+\]/, `voucher_details[${index}]`);
                    });
                });
                attachVoucherDetailRemoveButtonListeners();
                attachVoucherDetailRowEventListenersToAll();
            }

            addVoucherDetailRowBtn.addEventListener('click', function() {
                const newRow = generateVoucherDetailTableRow(voucherDetailsTableBody.querySelectorAll('tr').length);
                voucherDetailsTableBody.appendChild(newRow);
                updateVoucherDetailRowIndices();
                updateAccountCodeDatalist(true);
                updateAllCalculationsAndValidations();
            });

            function attachVoucherDetailRemoveButtonListeners() {
                voucherDetailsTableBody.querySelectorAll('.removeVoucherDetailRowBtn').forEach(button => {
                    button.addEventListener('click', function() {
                        if (voucherDetailsTableBody.querySelectorAll('tr').length > 1) {
                            this.closest('tr').remove();
                            updateVoucherDetailRowIndices();
                            updateAllCalculationsAndValidations();
                            updateAccountCodeDatalist();
                        } else {
                            alert("Tidak dapat menghapus baris detail voucher terakhir.");
                        }
                    });
                });
            }

            function attachVoucherDetailRowEventListeners(row, index) {
                const accountCodeInput = row.querySelector('.accountCodeInput');
                const accountNameInput = row.querySelector('.accountName');
                const debitInput = row.querySelector('.debitInput');
                const creditInput = row.querySelector('.creditInput');

                if (accountCodeInput) {
                    accountCodeInput.addEventListener('input', function() {
                        const enteredCode = this.value.trim();
                        accountNameInput.value = '';
                        const useInvoice = useInvoiceYes.checked ? 'yes' : 'no';

                        if (useInvoice === 'yes' && subsidiaries.some(s => s.subsidiary_code === enteredCode)) {
                            const subsidiary = subsidiaries.find(s => s.subsidiary_code === enteredCode);
                            if (subsidiary) {
                                accountNameInput.value = subsidiary.account_name;
                            }
                        } else {
                            const account = accounts.find(a => a.account_code === enteredCode);
                            if (account) {
                                accountNameInput.value = account.account_name;
                            }
                        }
                        updateAccountCodeDatalist();
                        validateForm();
                    });
                    accountCodeInput.name = `voucher_details[${index}][account_code]`;
                }

                if (debitInput) {
                    debitInput.addEventListener('input', function() {
                        creditInput.value = this.value ? '' : creditInput.value;
                        creditInput.disabled = !!this.value;
                        updateAllCalculationsAndValidations();
                    });
                    debitInput.name = `voucher_details[${index}][debit]`;
                }

                if (creditInput) {
                    creditInput.addEventListener('input', function() {
                        debitInput.value = this.value ? '' : debitInput.value;
                        debitInput.disabled = !!this.value;
                        updateAllCalculationsAndValidations();
                    });
                    creditInput.name = `voucher_details[${index}][credit]`;
                }

                if (accountNameInput) {
                    accountNameInput.name = `voucher_details[${index}][account_name]`;
                }
            }

            function attachVoucherDetailRowEventListenersToAll() {
                voucherDetailsTableBody.querySelectorAll('tr').forEach((row, index) => {
                    attachVoucherDetailRowEventListeners(row, index);
                });
            }

            // --- Calculations and Validations ---
            function calculateTotalNominal() {
                let totalNominalRaw = 0;
                transactionTableBody.querySelectorAll('tr').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 0;
                    const nominal = parseFloat(row.querySelector('.nominalInput')?.value) || 0;
                    totalNominalRaw += quantity * nominal;
                });
                totalNominalInput.value = totalNominalRaw.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return totalNominalRaw;
            }

            function calculateTotalsAndValidate() {
                let totalDebit = 0;
                let totalCredit = 0;

                voucherDetailsTableBody.querySelectorAll('.debitInput').forEach(input => {
                    totalDebit += parseFloat(input.value) || 0;
                });

                voucherDetailsTableBody.querySelectorAll('.creditInput').forEach(input => {
                    totalCredit += parseFloat(input.value) || 0;
                });

                totalDebitInput.value = totalDebit.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                totalCreditInput.value = totalCredit.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                totalDebitRawInput.value = totalDebit.toFixed(2);
                totalCreditRawInput.value = totalCredit.toFixed(2);

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
                    validationInput.value = "Total Nominal pada Rincian Transaksi harus sama dengan Total Debit dan Total Kredit pada Rincian Voucher.";
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

            function updateAllCalculationsAndValidations() {
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

            document.getElementById('voucherDate')?.addEventListener('change', updateVoucherDay);

            // --- Form Submission ---
            voucherForm.addEventListener('submit', function(event) {
                if (!validateForm() || !validateTotals()) {
                    event.preventDefault();
                    alert('Silakan perbaiki kesalahan pada formulir sebelum mengirim.');
                }
            });

            // --- Initialization ---
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            attachVoucherDetailRemoveButtonListeners();
            attachVoucherDetailRowEventListenersToAll();
            updateAllCalculationsAndValidations();
            updateInvoiceAndStoreFields();
            updateAccountCodeDatalist();
            refreshTransactionTable();

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