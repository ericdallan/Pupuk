@extends('layouts/app')

@section('content')
@section('title', 'Edit Voucher')
<style>
    input:disabled,
    select:disabled {
        background-color: #f0f0f0;
        cursor: not-allowed;
    }
</style>
<div>
    @if (session('success'))
    <div id="success-message" class="alert alert-success" style="cursor: pointer;">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @elseif (session('message'))
    <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
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
                    <select class="form-select" id="voucherType" name="voucher_type">
                        <option value="JV" {{ $voucher->voucher_type == 'JV' ? 'selected' : '' }}>JV</option>
                        <option value="MP" {{ $voucher->voucher_type == 'MP' ? 'selected' : '' }}>MP</option>
                        <option value="MI" {{ $voucher->voucher_type == 'MI' ? 'selected' : '' }}>MI</option>
                        <option value="CG" {{ $voucher->voucher_type == 'CG' ? 'selected' : '' }}>CG</option>
                    </select>
                </div>
                <label for="voucherDate" class="col-sm-2 col-form-label">Tanggal:</label>
                <div class="col-sm-2">
                    <input type="date" class="form-control" id="voucherDate" name="voucher_date" value="{{ $voucher->voucher_date->format('Y-m-d') }}">
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" id="voucherDay" name="voucher_day" value="{{ $voucher->voucher_day }}">
                </div>
            </div>
            <div class="row mb-3">
                <label for="deskripsi_voucher" class="col-sm-3 col-form-label">Deskripsi Voucher</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="deskripsi_voucher" name="deskripsi_voucher" rows="3" readonly></textarea>
                </div>
            </div>
            <div class="row mb-3">
                <label for="preparedBy" class="col-sm-3 col-form-label">Dibuat Oleh:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="preparedBy" name="prepared_by" value="{{ $voucher->prepared_by }}">
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
                        <input class="form-check-input" type="radio" id="useInvoiceYes" name="use_invoice" value="yes" {{ $voucher->invoice ? 'checked' : '' }}>
                        <label class="form-check-label" for="useInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useInvoiceNo" name="use_invoice" value="no" {{ !$voucher->invoice ? 'checked' : '' }}>
                        <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                    </div>
                </div>
            </div>

            <!-- Gunakan Invoice yang Sudah Ada? -->
            <div class="row mb-3" id="existingInvoiceContainer" style="display: {{ $voucher->invoice ? 'block' : 'none' }};">
                <label class="col-sm-3 col-form-label">Gunakan Invoice yang Sudah Ada?</label>
                <div class="col-sm-9">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceYes" name="use_existing_invoice" value="yes" {{ $voucher->invoice ? 'checked' : '' }} {{ !$voucher->invoice ? 'disabled' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="useExistingInvoiceNo" name="use_existing_invoice" value="no" {{ !$voucher->invoice ? 'checked' : '' }} {{ !$voucher->invoice ? 'disabled' : '' }}>
                        <label class="form-check-label" for="useExistingInvoiceNo">Tidak</label>
                    </div>
                </div>
            </div>

            <!-- Nomor Invoice -->
            <div class="row mb-3" id="invoiceFieldContainer" style="display: {{ $voucher->invoice ? 'block' : 'none' }};">
                <label for="invoice" class="col-sm-3 col-form-label">Nomor Invoice:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="invoice" name="invoice" value="{{ $voucher->invoice ?? '' }}" {{ !$voucher->invoice ? 'disabled' : '' }}>
                </div>
            </div>

            <!-- Nama Toko -->
            <div class="row mb-3" id="storeFieldContainer" style="display: {{ $voucher->store ? 'block' : 'none' }};">
                <label for="store" class="col-sm-3 col-form-label">Nama Toko:</label>
                <div class="col-sm-9">
                    <select class="form-select" id="store" name="store" {{ !$voucher->store ? 'disabled' : '' }}>
                        <option value="">Pilih Nama Toko</option>
                        @foreach($storeNames as $storeName)
                        <option value="{{ $storeName }}" {{ $voucher->store == $storeName ? 'selected' : '' }}>{{ $storeName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <h5>Rincian Transaksi</h5>
                <div class="table-responsive ">
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
                            <tr>
                                <td>
                                    <input type="text" class="form-control transactionDescription" name="transactions[{{ $index }}][description]" value="{{ $transaction->description }}">
                                </td>
                                <td>
                                    <input type="number" min="1" class="form-control quantityInput" name="transactions[{{ $index }}][quantity]" value="{{ $transaction->quantity }}">
                                </td>
                                <td>
                                    <input type="number" min="0" class="form-control nominalInput" name="transactions[{{ $index }}][nominal]" value="{{ $transaction->nominal }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger removeTransactionRowBtn">Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td>
                                    <input type="text" class="form-control transactionDescription" name="transactions[0][description]">
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
                                    <input type="text" class="form-control" id="totalNominal" name="total_nominal" value="{{ $voucher->total_nominal }}" readonly>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="addTransactionRowBtn" class="btn btn-primary">Tambah Transaksi</button>
                </div>
            </div>
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
                                <input type="text" class="form-control accountCodeInput" name="voucher_details[{{ $index }}][account_code]" list="dynamicAccountCodes" value="{{ $detail->account_code }}" placeholder="Ketik atau pilih kode akun">
                                <datalist id="dynamicAccountCodes">
                                    <option value="">Pilih Kode Akun</option>
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->account_code }}">
                                        {{ $account->account_code }} - {{ $account->account_name }}
                                    </option>
                                    @endforeach
                                </datalist>
                            </td>
                            <td><input type="text" class="form-control accountName" name="voucher_details[{{ $index }}][account_name]" value="{{ $detail->account_name }}" readonly></td>
                            <td><input type="number" min="0" class="form-control debitInput" name="voucher_details[{{ $index }}][debit]" value="{{ $detail->debit }}"></td>
                            <td><input type="number" min="0" class="form-control creditInput" name="voucher_details[{{ $index }}][credit]" value="{{ $detail->credit }}"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger removeVoucherDetailRowBtn">Hapus</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" id="addVoucherDetailRowBtn" class="btn btn-primary">Tambah Kode Akun</button>
            </div>
            <div class="row mb-3">
                <label for="totalDebit" class="col-sm-3 col-form-label">Total Debit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="totalDebit" name="total_debit" value="{{ $voucher->total_debit }}" readonly>
                </div>
                <label for="totalCredit" class="col-sm-3 col-form-label">Total Kredit:</label>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="totalCredit" name="total_credit" value="{{ $voucher->total_credit }}" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <label for="validation" class="col-sm-3 col-form-label">Pesan:</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="validation" name="validation" value="[Pesan]" readonly>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary" id="saveVoucherBtn">Simpan Perubahan</button>
        </div>
    </form>
    <script>
        /**
         * @file Script untuk mengelola formulir voucher, termasuk penambahan/penghapusan baris transaksi dan detail voucher,
         * perhitungan total, validasi kesimbangan total, dan pengelolaan invoice serta kode akun.
         */
        document.addEventListener('DOMContentLoaded', function() {
            // --- Element References ---
            const transactionTableBody = document.querySelector('#transactionDetailsTable tbody');
            const addTransactionRowBtn = document.getElementById('addTransactionRowBtn');
            const voucherDetailsTableBody = document.querySelector('#voucherDetailsTable tbody');
            const addVoucherDetailRowBtn = document.getElementById('addVoucherDetailRowBtn');
            const totalDebitInput = document.getElementById('totalDebit');
            const totalCreditInput = document.getElementById('totalCredit');
            const totalNominalInput = document.getElementById('totalNominal');
            const validationInput = document.getElementById('validation');
            const saveVoucherBtn = document.getElementById('saveVoucherBtn');
            const deskripsiVoucherTextarea = document.getElementById('deskripsi_voucher');
            const voucherTypeSelect = document.getElementById('voucherType');
            const useInvoiceYes = document.getElementById('useInvoiceYes');
            const useInvoiceNo = document.getElementById('useInvoiceNo');
            const invoiceFieldContainer = document.getElementById('invoiceFieldContainer');
            const storeFieldContainer = document.getElementById('storeFieldContainer');
            const existingInvoiceContainer = document.getElementById('existingInvoiceContainer');
            const useExistingInvoiceYes = document.getElementById('useExistingInvoiceYes');
            const useExistingInvoiceNo = document.getElementById('useExistingInvoiceNo');
            const invoiceInput = document.getElementById('invoice');
            const storeSelect = document.getElementById('store');

            // --- Data from Laravel ---
            const existingInvoices = @json($existingInvoices);
            const storeNames = @json($storeNames);
            const subsidiaries = @json($subsidiariesData);
            const accounts = @json($accountsData);
            const hasInvoice = @json((bool) $voucher->invoice);

            // --- Voucher Description Logic ---
            function updateVoucherDescription() {
                if (voucherTypeSelect && deskripsiVoucherTextarea) {
                    const selectedValue = voucherTypeSelect.value;
                    let defaultDescription = '';
                    switch (selectedValue) {
                        case 'JV':
                            defaultDescription = 'Jurnal Voucher - Formulir atau dokumen internal perusahaan yang digunakan untuk mencatat transaksi-transaksi yang tidak dapat dicatat dalam jenis voucher lainnya.';
                            break;
                        case 'MP':
                            defaultDescription = 'Material Purchase - Dokumen yang digunakan untuk mencatat transaksi pembelian material atau persediaan. Voucher ini berfungsi sebagai bukti otorisasi pembelian dan penerimaan material.';
                            break;
                        case 'MI':
                            defaultDescription = 'Material Issuance - Formulir atau dokumen internal perusahaan yang digunakan untuk mencatat pengeluaran atau pemakaian material (bahan baku, bahan penolong, suku cadang, dll.) dari gudang untuk keperluan produksi, proyek, atau departemen lain dalam perusahaan.';
                            break;
                        case 'CG':
                            defaultDescription = 'Cash/Bank General - Dokumen yang digunakan untuk mencatat transaksi pemindahan dana (transfer) antara akun kas dan akun bank, atau antar beberapa akun bank yang dimiliki perusahaan.';
                            break;
                        default:
                            defaultDescription = '';
                            break;
                    }
                    deskripsiVoucherTextarea.value = defaultDescription;
                } else {
                    console.error("Elemen dengan ID 'voucherType' atau 'deskripsi_voucher' tidak ditemukan.");
                }
            }
            updateVoucherDescription();
            voucherTypeSelect.addEventListener('change', updateVoucherDescription);

            // --- Invoice and Store Field Logic ---
            function updateInvoiceAndStoreFields() {
                const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
                if (useInvoice === 'yes') {
                    invoiceFieldContainer.style.display = 'block';
                    storeFieldContainer.style.display = 'block';
                    existingInvoiceContainer.style.display = 'block';
                    invoiceInput.disabled = false;
                    storeSelect.disabled = false;
                    useExistingInvoiceYes.disabled = false;
                    useExistingInvoiceNo.disabled = false;
                    // Auto-select "Ya" untuk "Gunakan Invoice yang Sudah Ada?" jika invoice ada
                    if (hasInvoice) {
                        useExistingInvoiceYes.checked = true;
                    }
                } else {
                    invoiceFieldContainer.style.display = 'none';
                    storeFieldContainer.style.display = 'none';
                    existingInvoiceContainer.style.display = 'none';
                    invoiceInput.disabled = true;
                    storeSelect.disabled = true;
                    useExistingInvoiceYes.disabled = true;
                    useExistingInvoiceNo.disabled = true;
                }
                updateAccountCodeDatalist();
            }

            if (useInvoiceYes && useInvoiceNo) {
                useInvoiceYes.addEventListener('change', updateInvoiceAndStoreFields);
                useInvoiceNo.addEventListener('change', updateInvoiceAndStoreFields);
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
                const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
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

            // --- Transaction Table Row Generation ---
            function generateTransactionTableRow(index) {
                const row = document.createElement('tr');
                row.dataset.rowIndex = index;

                const descriptionCell = document.createElement('td');
                const descriptionInput = document.createElement('input');
                descriptionInput.type = 'text';
                descriptionInput.className = 'form-control transactionDescription';
                descriptionInput.name = `transactions[${index}][description]`;
                descriptionCell.appendChild(descriptionInput);
                row.appendChild(descriptionCell);

                const quantityCell = document.createElement('td');
                const quantityInput = document.createElement('input');
                quantityInput.type = 'number';
                quantityInput.min = '1';
                quantityInput.className = 'form-control quantityInput';
                quantityInput.name = `transactions[${index}][quantity]`;
                quantityInput.value = '1';
                quantityCell.appendChild(quantityInput);
                row.appendChild(quantityCell);

                const nominalCell = document.createElement('td');
                const nominalInput = document.createElement('input');
                nominalInput.type = 'number';
                nominalInput.min = '0';
                nominalInput.className = 'form-control nominalInput';
                nominalInput.name = `transactions[${index}][nominal]`;
                nominalCell.appendChild(nominalInput);
                row.appendChild(nominalCell);

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
                    row.querySelectorAll('[name*="transactions["]').forEach(input => {
                        input.name = input.name.replace(/transactions\[\d+\]/, `transactions[${index}]`);
                    });
                });
                attachTransactionRemoveButtonListeners();
                attachTransactionInputListeners();
            }

            addTransactionRowBtn.addEventListener('click', function() {
                const newRow = generateTransactionTableRow(transactionTableBody.querySelectorAll('tr').length);
                transactionTableBody.appendChild(newRow);
                updateTransactionRowIndices();
                updateAllCalculationsAndValidations();
            });

            function attachTransactionRemoveButtonListeners() {
                transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(button => {
                    button.addEventListener('click', function() {
                        if (transactionTableBody.querySelectorAll('tr').length > 1) {
                            this.closest('tr').remove();
                            updateTransactionRowIndices();
                            updateAllCalculationsAndValidations();
                        } else {
                            alert("Tidak dapat menghapus baris transaksi terakhir.");
                        }
                    });
                });
            }

            function attachTransactionInputListeners() {
                transactionTableBody.querySelectorAll('.quantityInput, .nominalInput').forEach(input => {
                    input.addEventListener('input', updateAllCalculationsAndValidations);
                });
            }

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
                accountCodeCell.appendChild(accountCodeInput);
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
                updateAccountCodeDatalist(true); // Baris baru menggunakan account_code
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
                        const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';

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
                } else if (totalDebitRaw !== totalCreditRaw) {
                    validationInput.value = "Total Debit harus sama dengan Total Kredit.";
                    saveVoucherBtn.disabled = true;
                } else {
                    validationInput.value = "Totalnya seimbang dan valid.";
                    saveVoucherBtn.disabled = false;
                }
            }

            function updateAllCalculationsAndValidations() {
                validateTotals();
            }

            // --- Voucher Day Update ---
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
            document.getElementById('voucherDate')?.addEventListener('change', updateVoucherDay);

            // --- Initialization ---
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            attachVoucherDetailRemoveButtonListeners();
            attachVoucherDetailRowEventListenersToAll();
            updateAllCalculationsAndValidations();
            updateInvoiceAndStoreFields();
            updateAccountCodeDatalist();

            // Set initial voucher date if needed
            const voucherDateInput = document.getElementById('voucherDate');
            if (voucherDateInput && !voucherDateInput.value) {
                const today = new Date().toISOString().split('T')[0];
                voucherDateInput.value = today;
                updateVoucherDay();
            }
        });
    </script>
</div>

@endsection