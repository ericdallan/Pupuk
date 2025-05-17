<style>
    input:disabled,
    select:disabled {
        background-color: #f0f0f0;
        cursor: not-allowed;
    }
</style>
<div class="modal fade" id="voucherModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="voucherForm" method="POST" action="/voucher_form" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="container">
                        <h2 class="text-center">Voucher Form</h2>
                        <div class="row mb-3">
                            <label for="voucherNumber" class="col-sm-3 col-form-label">Nomor Voucher:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="voucherNumber" name="voucher_number" value="[Auto Generate Number]" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="companyName" class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                            @if ($company && $company->company_name)
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="companyName" name="companyName" value="{{$company->company_name}}" readonly>
                            </div>
                            @else
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="companyName" name="companyName" value="Not Found" readonly>
                            </div>
                            @endif
                        </div>
                        <div class="row mb-3">
                            <label for="voucherType" class="col-sm-3 col-form-label">Tipe Voucher:</label>
                            <div class="col-sm-3">
                                <select class="form-select" id="voucherType" name="voucher_type">
                                    <option value="">Pilih Tipe Voucher</option>
                                    <option value="PJ">Penjualan</option>
                                    <option value="PG">Pengeluaran</option>
                                    <option value="PM">Pemasukan</option>
                                    <option value="PB">Pembelian</option>
                                    <option value="LN">Lainnya</option>
                                </select>
                            </div>
                            <label for="voucherDate" class="col-sm-2 col-form-label">Tanggal:</label>
                            <div class="col-sm-2">
                                <input type="date" class="form-control" id="voucherDate" name="voucher_date">
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" id="voucherDay" name="voucher_day" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="deskripsi_voucher" class="col-sm-3 col-form-label">Deskripsi Voucher</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="deskripsi_voucher" name="deskripsi_voucher" rows="3" readonly></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="preparedBy" class="col-sm-3 col-form-label">Disiapkan Oleh:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="preparedBy" name="prepared_by" value="{{ $admin->name }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="preparedBy" class="col-sm-3 col-form-label">Diberikan Kepada:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="given_to" name="given_to">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="approvedBy" class="col-sm-3 col-form-label">Disetujui Oleh:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="approvedBy" name="approved_by" value="{{ $company->director }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="transaction" class="col-sm-3 col-form-label">Transaksi:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="transaction" name="transaction">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="useInvoice" class="col-sm-3 col-form-label">Gunakan Invoice?</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useInvoiceYes" name="use_invoice" value="yes">
                                    <label class="form-check-label" for="useInvoiceYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useInvoiceNo" name="use_invoice" value="no">
                                    <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3" id="existingInvoiceContainer">
                            <label class="col-sm-3 col-form-label">Gunakan Invoice yang Sudah Ada?</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useExistingInvoiceYes" name="use_existing_invoice" value="yes" disabled>
                                    <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useExistingInvoiceNo" name="use_existing_invoice" value="no" disabled>
                                    <label class="form-check-label" for="useExistingInvoiceNo">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3" id="dueDateContainer">
                            <label for="dueDate" class="col-sm-3 col-form-label">Tanggal Jatuh Tempo:</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="dueDate" name="dueDate" disabled>
                            </div>
                        </div>
                        <div class="row mb-3" id="invoiceFieldContainer">
                            <label for="invoice" class="col-sm-3 col-form-label">Nomor Invoice:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="invoice" name="invoice" disabled>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="store" class="col-sm-3 col-form-label">Nama Toko:</label>
                            <div class="col-sm-9">
                                <div id="storeFieldContainer">
                                    <input type="text" class="form-control" id="store" name="store">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h5>Rincian Transaksi</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="transactionTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th colspan="4">Rincian Transaksi</th>
                                            <th style="width: 80px;">Action</th>
                                        </tr>
                                        <tr class="text-center">
                                            <th>Deskripsi</th>
                                            <th>Quantitas</th>
                                            <th>Nominal</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-row-index="0">
                                            <td>
                                                <input type="text" class="form-control descriptionInput" name="transactions[0][description]">
                                            </td>
                                            <td>
                                                <input type="number" min="1" class="form-control quantityInput" name="transactions[0][quantity]" value="1">
                                            </td>
                                            <td>
                                                <input type="number" min="0" class="form-control nominalInput" name="transactions[0][nominal]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control totalInput" name="transactions[0][total]" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger removeTransactionRowBtn">Hapus</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="addTransactionRowBtn" class="btn btn-primary">Tambah Transaksi</button>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="totalNominal" class="col-sm-3 col-form-label">Total Nominal:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="totalNominal" name="total_nominal" value="[Auto Calculate]" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h5>Rincian Voucher</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="voucherDetailsTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th colspan="4">Rincian Voucher</th>
                                            <th style="width: 80px;">Action</th>
                                        </tr>
                                        <tr class="text-center">
                                            <th>Kode Akun</th>
                                            <th>Nama Akun</th>
                                            <th>Debit</th>
                                            <th>Kredit</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-row-index="0">
                                            <td>
                                                <input type="text" class="form-control accountCodeInput" name="voucher_details[0][account_code]" list="dynamicAccountCodes" placeholder="Ketik atau pilih kode akun">
                                                <datalist id="dynamicAccountCodes"></datalist>
                                            </td>
                                            <td><input type="text" class="form-control accountName" name="voucher_details[0][account_name]" readonly></td>
                                            <td><input type="number" min="0" class="form-control debitInput" name="voucher_details[0][debit]"></td>
                                            <td><input type="number" min="0" class="form-control creditInput" name="voucher_details[0][credit]"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger removeVoucherDetailRowBtn">Hapus</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="addVoucherDetailRowBtn" class="btn btn-primary">Tambah Kode Akun</button>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="totalDebit" class="col-sm-3 col-form-label">Total Debit:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="totalDebit" name="total_debit_formatted" value="[Dihitung]" readonly>
                                <input type="hidden" name="total_debit" id="totalDebitRaw">
                            </div>
                            <label for="totalCredit" class="col-sm-3 col-form-label">Total Kredit:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="totalCredit" name="total_credit_formatted" value="[Dihitung]" readonly>
                                <input type="hidden" name="total_credit" id="totalCreditRaw">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="validation" class="col-sm-3 col-form-label">Pesan:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="validation" name="validation" value="[Pesan]" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveVoucherBtn">Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const transactionTableBody = document.querySelector('#transactionTable tbody');
        const addTransactionRowBtn = document.getElementById('addTransactionRowBtn');
        const voucherDetailsTableBody = document.querySelector('#voucherDetailsTable tbody');
        const addVoucherDetailRowBtn = document.getElementById('addVoucherDetailRowBtn');
        const totalDebitInput = document.getElementById('totalDebit');
        const totalCreditInput = document.getElementById('totalCredit');
        const validationInput = document.getElementById('validation');
        const saveVoucherBtn = document.getElementById('saveVoucherBtn');
        const totalDebitRawInput = document.getElementById('totalDebitRaw');
        const totalCreditRawInput = document.getElementById('totalCreditRaw');
        const totalNominalInput = document.getElementById('totalNominal');
        const voucherTypeSelect = document.getElementById('voucherType');
        const deskripsiVoucherTextarea = document.getElementById('deskripsi_voucher');
        const useInvoiceYes = document.getElementById('useInvoiceYes');
        const useInvoiceNo = document.getElementById('useInvoiceNo');
        const invoiceFieldContainer = document.getElementById('invoiceFieldContainer');
        const dueDateContainer = document.getElementById('dueDateContainer');
        const storeFieldContainer = document.getElementById('storeFieldContainer');
        const existingInvoiceContainer = document.getElementById('existingInvoiceContainer');
        const useExistingInvoiceYes = document.getElementById('useExistingInvoiceYes');
        const useExistingInvoiceNo = document.getElementById('useExistingInvoiceNo');
        const existingInvoices = @json($existingInvoices);
        const storeNames = @json($storeNames);
        const subsidiaries = @json($subsidiariesData);
        const accounts = @json($accountsData);
        const stocks = @json($stocksData);
        const transactions = @json($transactionsData);

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

        function updateAccountCodeDatalist() {
            const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
            const datalists = document.querySelectorAll('#dynamicAccountCodes');
            const subsidiaryUsed = isSubsidiaryCodeUsed();

            datalists.forEach(datalist => {
                datalist.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Pilih Kode Akun';
                datalist.appendChild(defaultOption);

                if (useInvoice === 'yes' && !subsidiaryUsed) {
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

        function reindexVoucherDetailRows() {
            voucherDetailsTableBody.querySelectorAll('tr').forEach((row, index) => {
                row.dataset.rowIndex = index;
                row.querySelectorAll('[name^="voucher_details"]').forEach(input => {
                    const nameParts = input.name.split('[');
                    input.name = `voucher_details[${index}]${nameParts[1]}`;
                });
                attachVoucherDetailRowEventListeners(row, index);
            });
            updateAccountCodeDatalist();
        }

        addVoucherDetailRowBtn.addEventListener('click', function() {
            const firstRow = voucherDetailsTableBody.querySelector('tr');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);
            const inputs = newRow.querySelectorAll('input[type="text"], input[type="number"]');
            inputs.forEach(input => {
                input.value = '';
                input.disabled = false;
            });
            const newIndex = voucherDetailsTableBody.querySelectorAll('tr').length;
            newRow.querySelectorAll('[name]').forEach(element => {
                if (element.name.includes('[')) {
                    element.name = element.name.replace(/\[\d+\]/, `[${newIndex}]`);
                }
            });
            voucherDetailsTableBody.appendChild(newRow);
            attachVoucherDetailRowEventListeners(newRow, newIndex);
            attachVoucherDetailRemoveButtonListeners();
            updateAccountCodeDatalist();
            updateAllCalculationsAndValidations();
        });

        function attachVoucherDetailRemoveButtonListeners() {
            voucherDetailsTableBody.querySelectorAll('.removeVoucherDetailRowBtn').forEach(button => {
                button.addEventListener('click', function() {
                    if (voucherDetailsTableBody.querySelectorAll('tr').length > 1) {
                        this.closest('tr').remove();
                        reindexVoucherDetailRows();
                        updateAllCalculationsAndValidations();
                    } else {
                        alert("Tidak dapat menghapus baris detail voucher terakhir.");
                    }
                });
            });
        }

        function createStoreDropdown() {
            const select = document.createElement('select');
            select.className = 'form-control';
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

        function createStoreInput() {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = 'store';
            input.name = 'store';
            return input;
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
            const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
            if (useInvoice === 'yes') {
                const useExistingInvoice = document.querySelector('input[name="use_existing_invoice"]:checked')?.value || 'no';
                invoiceFieldContainer.innerHTML = '';
                const invoiceLabel = document.createElement('label');
                invoiceLabel.htmlFor = 'invoice';
                invoiceLabel.className = 'col-sm-3 col-form-label';
                invoiceLabel.textContent = 'Nomor Invoice:';
                const invoiceInputDiv = document.createElement('div');
                invoiceInputDiv.className = 'col-sm-9';
                let invoiceInput;
                if (useExistingInvoice === 'yes') {
                    invoiceInput = createInvoiceDropdown();
                } else {
                    invoiceInput = createInvoiceInput();
                }
                invoiceInput.disabled = false;
                invoiceInputDiv.appendChild(invoiceInput);
                invoiceFieldContainer.appendChild(invoiceLabel);
                invoiceFieldContainer.appendChild(invoiceInputDiv);
            }
            updateAccountCodeDatalist();
        }

        function updateDueDateField() {
            const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
            const useExistingInvoice = document.querySelector('input[name="use_existing_invoice"]:checked')?.value || 'no';
            dueDateContainer.innerHTML = '';
            const dueDateLabel = document.createElement('label');
            dueDateLabel.htmlFor = 'dueDate';
            dueDateLabel.className = 'col-sm-3 col-form-label';
            dueDateLabel.textContent = 'Tanggal Jatuh Tempo:';
            const dueDateInputDiv = document.createElement('div');
            dueDateInputDiv.className = 'col-sm-9';
            const dueDateInput = document.createElement('input');
            dueDateInput.type = 'date';
            dueDateInput.className = 'form-control';
            dueDateInput.id = 'dueDate';
            dueDateInput.name = 'dueDate';
            dueDateInput.disabled = useInvoice !== 'yes' || useExistingInvoice === 'yes';
            dueDateInputDiv.appendChild(dueDateInput);
            dueDateContainer.appendChild(dueDateLabel);
            dueDateContainer.appendChild(dueDateInputDiv);
            if (useInvoice === 'yes' && useExistingInvoice === 'no') {
                setTodayDueDate();
            }
        }

        function updateInvoiceAndStoreFields() {
            const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
            storeFieldContainer.innerHTML = '';

            useExistingInvoiceYes.disabled = useInvoice !== 'yes';
            useExistingInvoiceNo.disabled = useInvoice !== 'yes';

            if (useInvoice === 'yes') {
                storeFieldContainer.appendChild(createStoreDropdown());
                updateInvoiceField();
            } else {
                storeFieldContainer.appendChild(createStoreInput());
                invoiceFieldContainer.innerHTML = '';
                const invoiceLabel = document.createElement('label');
                invoiceLabel.htmlFor = 'invoice';
                invoiceLabel.className = 'col-sm-3 col-form-label';
                invoiceLabel.textContent = 'Nomor Invoice:';
                const invoiceInputDiv = document.createElement('div');
                invoiceInputDiv.className = 'col-sm-9';
                const invoiceInput = createInvoiceInput();
                invoiceInput.disabled = true;
                invoiceInputDiv.appendChild(invoiceInput);
                invoiceFieldContainer.appendChild(invoiceLabel);
                invoiceFieldContainer.appendChild(invoiceInputDiv);
                useExistingInvoiceYes.checked = false;
                useExistingInvoiceNo.checked = false;
            }
            updateDueDateField();
            updateAccountCodeDatalist();
        }

        useInvoiceYes.addEventListener('change', updateInvoiceAndStoreFields);
        useInvoiceNo.addEventListener('change', updateInvoiceAndStoreFields);
        useExistingInvoiceYes.addEventListener('change', function() {
            updateInvoiceField();
            updateDueDateField();
        });
        useExistingInvoiceNo.addEventListener('change', function() {
            updateInvoiceField();
            updateDueDateField();
        });

        function createStockDropdown(index) {
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
                select.appendChild(option);
            });

            return select;
        }

        function createDescriptionInput(index) {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control descriptionInput';
            input.name = `transactions[${index}][description]`;
            return input;
        }

        function calculateAverageHpp(item) {
            if (!transactions || !Array.isArray(transactions) || transactions.length === 0) {
                console.log('No transactions data available, returning 0');
                return 0;
            }

            const matchingTransactions = transactions.filter(t => t.description === item);
            if (matchingTransactions.length === 0) {
                console.log(`No matching transactions for ${item}, returning 0`);
                return 0;
            }

            const totalNominal = matchingTransactions.reduce((sum, t) => sum + (parseFloat(t.nominal) || 0), 0);
            const transactionCount = matchingTransactions.length;
            const averageHpp = transactionCount > 0 ? totalNominal / transactionCount : 0;
            console.log(`Matching transactions for ${item}:`, matchingTransactions);
            console.log(`Total Nominal: ${totalNominal}, Transaction Count: ${transactionCount}`);
            console.log(`Calculated average HPP for ${item}: ${averageHpp}`);
            return averageHpp;
        }

        function addHppRow(currentIndex, selectedItem, quantity) {
            if (!selectedItem) return;

            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const hppRow = document.createElement('tr');
            hppRow.dataset.rowIndex = newIndex;
            hppRow.dataset.isHppRow = 'true';

            const descriptionCell = document.createElement('td');
            const descriptionInput = createDescriptionInput(newIndex);
            descriptionInput.value = `HPP ${selectedItem}`;
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
            quantityInput.readOnly = voucherTypeSelect.value === 'PJ'; // Read-only for PJ to enforce sync
            quantityCell.appendChild(quantityInput);
            hppRow.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${newIndex}][nominal]`;
            const averageHpp = calculateAverageHpp(selectedItem);
            nominalInput.value = averageHpp;
            nominalInput.readOnly = true;
            console.log('Set HPP nominal to:', nominalInput.value, 'for item:', selectedItem);
            nominalCell.appendChild(nominalInput);
            hppRow.appendChild(nominalCell);

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'text';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${newIndex}][total]`;
            totalInput.readOnly = true;
            totalInput.value = (quantity * averageHpp).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
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
                    quantityInput.readOnly = voucherTypeSelect.value === 'PJ'; // Read-only for PJ
                    const nominalInput = nextRow.querySelector('.nominalInput');
                    const averageHpp = calculateAverageHpp(selectedItem);
                    nominalInput.value = averageHpp;
                    const totalInput = nextRow.querySelector('.totalInput');
                    totalInput.value = (quantity * averageHpp).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    console.log('Updated HPP row for', selectedItem, 'with nominal:', averageHpp, 'and quantity:', quantity);
                    updateAllCalculationsAndValidations();
                    return;
                }
                nextRow = nextRow.nextSibling;
            }

            addHppRow(currentIndex, selectedItem, quantity);
        }

        function handleStockChange(index, event) {
            const selectedItem = event.target.value;
            console.log('Dropdown changed to:', selectedItem, 'at index:', index);

            const row = event.target.closest('tr');
            const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;

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

        function generateTransactionTableRow(index) {
            const row = document.createElement('tr');
            row.dataset.rowIndex = index;

            const descriptionCell = document.createElement('td');
            let descriptionElement;
            const voucherType = voucherTypeSelect.value;

            if (voucherType === 'PJ') {
                descriptionElement = createStockDropdown(index);
                if (descriptionElement.dataset.listenerAttached === 'false') {
                    descriptionElement.addEventListener('change', handleStockChange.bind(null, index));
                    descriptionElement.dataset.listenerAttached = 'true';
                }
            } else if (voucherType === 'PB') {
                descriptionElement = document.createElement('div');
                descriptionElement.className = 'input-group';

                const select = createStockDropdown(index);
                select.className = 'form-control';
                select.style.width = '50%';

                const input = createDescriptionInput(index);
                input.className = 'form-control';
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
                descriptionElement = createDescriptionInput(index);
            }

            descriptionCell.appendChild(descriptionElement);
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

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'text';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${index}][total]`;
            totalInput.readOnly = true;
            totalInput.value = '0.00';
            totalCell.appendChild(totalInput);
            row.appendChild(totalCell);

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

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 0;
            const nominal = parseFloat(row.querySelector('.nominalInput')?.value) || 0;
            const total = quantity * nominal;
            const totalInput = row.querySelector('.totalInput');
            if (totalInput) {
                totalInput.value = total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            return total;
        }

        function syncHppQuantity(row) {
            if (voucherTypeSelect.value !== 'PJ') return;

            const rowIndex = parseInt(row.dataset.rowIndex);
            const isHppRow = row.dataset.isHppRow === 'true';
            if (!isHppRow) {
                // Find the next HPP row and sync its quantity
                let nextRow = row.nextSibling;
                while (nextRow) {
                    if (nextRow.dataset.isHppRow === 'true') {
                        const hppQuantityInput = nextRow.querySelector('.quantityInput');
                        const currentQuantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                        hppQuantityInput.value = currentQuantity;
                        const hppNominal = parseFloat(nextRow.querySelector('.nominalInput')?.value) || 0;
                        const hppTotalInput = nextRow.querySelector('.totalInput');
                        hppTotalInput.value = (currentQuantity * hppNominal).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        break;
                    }
                    nextRow = nextRow.nextSibling;
                }
            }
        }

        function attachTransactionInputListeners() {
            const transactionInputs = transactionTableBody.querySelectorAll('.quantityInput, .nominalInput, .descriptionInput');
            transactionInputs.forEach(input => {
                input.removeEventListener('input', handleTransactionInput);
                input.addEventListener('input', handleTransactionInput);
            });
        }

        function handleTransactionInput(event) {
            const row = event.target.closest('tr');
            syncHppQuantity(row);
            updateAllCalculationsAndValidations();
        }

        function refreshTransactionTable() {
            const rows = transactionTableBody.querySelectorAll('tr');
            const descriptions = Array.from(rows).map(row => {
                const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
                const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
                return descriptionInput?.value || descriptionSelect?.value || '';
            });

            const nonHppDescriptions = descriptions.filter(description => !description.startsWith('HPP '));

            transactionTableBody.innerHTML = '';
            nonHppDescriptions.forEach((description, index) => {
                const newRow = generateTransactionTableRow(index);
                transactionTableBody.appendChild(newRow);
                const rowDescriptionInput = newRow.querySelector('.descriptionInput[type="text"]');
                const rowDescriptionSelect = newRow.querySelector('.descriptionInput:not([type="text"])');
                if (rowDescriptionInput) rowDescriptionInput.value = description;
                if (rowDescriptionSelect) rowDescriptionSelect.value = description;
            });

            if (transactionTableBody.querySelectorAll('tr').length === 0) {
                const newRow = generateTransactionTableRow(0);
                transactionTableBody.appendChild(newRow);
            }

            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
        }

        addTransactionRowBtn.addEventListener('click', function() {
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const newRow = generateTransactionTableRow(newIndex);
            transactionTableBody.appendChild(newRow);
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            updateAllCalculationsAndValidations();
        });

        voucherTypeSelect.addEventListener('change', function() {
            refreshTransactionTable();
            const selectedValue = this.value;
            let defaultDescription = '';

            switch (selectedValue) {
                case 'PJ':
                    defaultDescription = 'Voucher Penjualan - Dokumen internal perusahaan untuk mencatat transaksi penjualan barang atau jasa yang tidak dapat dicatat pada voucher lain.';
                    break;
                case 'PG':
                    defaultDescription = 'Voucher Pengeluaran - Dokumen untuk mencatat pengeluaran dana perusahaan, seperti pembayaran tagihan, pembelian material, atau biaya operasional, sebagai bukti otorisasi transaksi.';
                    break;
                case 'PM':
                    defaultDescription = 'Voucher Pemasukan - Dokumen internal perusahaan untuk mencatat penerimaan dana, seperti pembayaran dari pelanggan, setoran tunai, atau penerimaan lain yang masuk ke kas atau bank perusahaan.';
                    break;
                case 'PB':
                    defaultDescription = 'Voucher Pembelian - Dokumen untuk mencatat transaksi pembelian barang atau jasa, seperti pembelian material, peralatan, atau layanan dari pemasok.';
                    break;
                case 'LN':
                    defaultDescription = 'Voucher Lainnya - Dokumen untuk mencatat transaksi yang tidak termasuk dalam kategori voucher lain, seperti koreksi jurnal atau transaksi khusus lainnya.';
                    break;
                default:
                    defaultDescription = '';
                    break;
            }

            deskripsiVoucherTextarea.value = defaultDescription;
        });

        function calculateTotalNominal() {
            let totalNominalRaw = 0;
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                const total = calculateRowTotal(row);
                totalNominalRaw += total;
                console.log(`Row: ${row.querySelector('.descriptionInput')?.value}, Total: ${total}`);
            });
            totalNominalInput.value = totalNominalRaw.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            console.log('Total Nominal Calculated:', totalNominalRaw);
            return totalNominalRaw;
        }

        function calculateTotalsAndValidate() {
            let totalDebit = 0;
            let totalCredit = 0;

            voucherDetailsTableBody.querySelectorAll('.debitInput').forEach(input => {
                totalDebit += parseFloat(input.value.replace(/[^0-9.-]/g, '')) || 0;
            });

            voucherDetailsTableBody.querySelectorAll('.creditInput').forEach(input => {
                totalCredit += parseFloat(input.value.replace(/[^0-9.-]/g, '')) || 0;
            });

            totalDebitInput.value = totalDebit.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            totalCreditInput.value = totalCredit.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            if (totalDebitRawInput) totalDebitRawInput.value = totalDebit.toFixed(2);
            if (totalCreditRawInput) totalCreditRawInput.value = totalCredit.toFixed(2);

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
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                calculateRowTotal(row);
                syncHppQuantity(row);
            });
            validateTotals();
        }

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
        document.getElementById('voucherDate').addEventListener('change', updateVoucherDay);

        function setTodayVoucherDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('voucherDate').value = `${year}-${month}-${day}`;
            updateVoucherDay();
        }

        function setTodayDueDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('dueDate').value = `${year}-${month}-${day}`;
        }

        attachTransactionInputListeners();
        attachTransactionRemoveButtonListeners();
        attachVoucherDetailRemoveButtonListeners();
        const initialVoucherDetailRow = voucherDetailsTableBody.querySelector('tr');
        if (initialVoucherDetailRow) {
            attachVoucherDetailRowEventListeners(initialVoucherDetailRow, 0);
        }
        updateAllCalculationsAndValidations();
        setTodayVoucherDate();
        updateInvoiceAndStoreFields();
        updateAccountCodeDatalist();
        refreshTransactionTable();
    });
</script>