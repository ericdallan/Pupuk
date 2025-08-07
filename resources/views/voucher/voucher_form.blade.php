<style>
    input:disabled,
    select:disabled {
        background-color: #f0f0f0;
        cursor: not-allowed;
    }
</style>
<div class="modal fade" id="voucherModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="voucherModalLabel"
    aria-hidden="true">
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
                                <input type="text" class="form-control" id="voucherNumber" name="voucher_number"
                                    value="[Auto Generate Number]" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="companyName" class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                            @if ($company && $company->company_name)
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="companyName" name="companyName"
                                        value="{{ $company->company_name }}" readonly>
                                </div>
                            @else
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="companyName" name="companyName"
                                        value="Not Found" readonly>
                                </div>
                            @endif
                        </div>
                        <div class="row mb-3">
                            <label for="useStock" class="col-sm-3 col-form-label">Transaksi Stok?</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useStockYes" name="use_stock"
                                        value="yes">
                                    <label class="form-check-label" for="useStockYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useStockNo" name="use_stock"
                                        value="no">
                                    <label class="form-check-label" for="useStockNo">Tidak</label>
                                </div>
                            </div>
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
                                    <option value="PH">Pemindahan</option>
                                    <option value="PK">Pemakaian</option>
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
                                <input type="text" class="form-control" id="preparedBy" name="prepared_by"
                                    value="{{ $admin ? $admin->name : 'N/A' }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="given_to" class="col-sm-3 col-form-label">Diberikan Kepada:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="given_to" name="given_to" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="approvedBy" class="col-sm-3 col-form-label">Disetujui Oleh:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="approvedBy" name="approved_by"
                                    value="{{ $company->director }}" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="transaction" class="col-sm-3 col-form-label">Transaksi:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="transaction" name="transaction"
                                    required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="useInvoice" class="col-sm-3 col-form-label">Gunakan Invoice?</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useInvoiceYes"
                                        name="use_invoice" value="yes">
                                    <label class="form-check-label" for="useInvoiceYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" type="radio" id="useInvoiceNo"
                                        name="use_invoice" value="no">
                                    <label class="form-check-label" for="useInvoiceNo">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3" id="existingInvoiceContainer">
                            <label class="col-sm-3 col-form-label">Gunakan Invoice yang Sudah Ada?</label>
                            <div class="col-sm-9">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useExistingInvoiceYes"
                                        name="use_existing_invoice" value="yes" disabled>
                                    <label class="form-check-label" for="useExistingInvoiceYes">Ya</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="useExistingInvoiceNo"
                                        name="use_existing_invoice" value="no" disabled>
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
                        <div class="row mb-3">
                            <label for="recipe" class="col-sm-3 col-form-label">Formula Produk:</label>
                            <div class="col-sm-9">
                                <div id="recipeFieldContainer">
                                    <input type="text" class="form-control" id="recipe" name="recipe"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h5>Rincian Transaksi</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="transactionTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th colspan="5">Rincian Transaksi</th>
                                            <th style="width: 80px;">Action</th>
                                        </tr>
                                        <tr class="text-center">
                                            <th>Deskripsi</th>
                                            <th>Ukuran</th>
                                            <th>Kuantitas</th>
                                            <th>Nominal</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-row-index="0">
                                            <td>
                                                <input type="text" class="form-control descriptionInput"
                                                    name="transactions[0][description]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control sizeInput"
                                                    name="transactions[0][size]">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1"
                                                    class="form-control quantityInput"
                                                    name="transactions[0][quantity]" value="1">
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="0.01"
                                                    class="form-control nominalInput" name="transactions[0][nominal]">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control totalInput"
                                                    name="transactions[0][total]" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-danger removeTransactionRowBtn">Hapus</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="addTransactionRowBtn" class="btn btn-primary">Tambah
                                    Transaksi</button>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="totalNominal" class="col-sm-3 col-form-label">Total Nominal:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="totalNominal" name="total_nominal"
                                    value="[Auto Calculate]" readonly>
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
                                                <input type="text" class="form-control accountCodeInput"
                                                    name="voucher_details[0][account_code]" list="dynamicAccountCodes"
                                                    placeholder="Ketik atau pilih kode akun">
                                                <datalist id="dynamicAccountCodes"></datalist>
                                            </td>
                                            <td><input type="text" class="form-control accountName"
                                                    name="voucher_details[0][account_name]" readonly></td>
                                            <td><input type="number" min="0" step="0.01"
                                                    class="form-control debitInput" name="voucher_details[0][debit]">
                                            </td>
                                            <td><input type="number" min="0" step="0.01"
                                                    class="form-control creditInput"
                                                    name="voucher_details[0][credit]"></td>
                                            <td class="text-center">
                                                <button type="button"
                                                    class="btn btn-danger removeVoucherDetailRowBtn">Hapus</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" id="addVoucherDetailRowBtn" class="btn btn-primary">Tambah
                                    Kode Akun</button>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="totalDebit" class="col-sm-3 col-form-label">Total Debit:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="totalDebit"
                                    name="total_debit_formatted" value="[Dihitung]" readonly>
                                <input type="hidden" name="total_debit" id="totalDebitRaw">
                            </div>
                            <label for="totalCredit" class="col-sm-3 col-form-label">Total Kredit:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="totalCredit"
                                    name="total_credit_formatted" value="[Dihitung]" readonly>
                                <input type="hidden" name="total_credit" id="totalCreditRaw">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="validation" class="col-sm-3 col-form-label">Pesan:</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="validation" name="validation"
                                    value="[Pesan]" readonly>
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
        const useStockYes = document.getElementById('useStockYes');
        const useStockNo = document.getElementById('useStockNo');
        const recipeInputContainer = document.getElementById('recipeFieldContainer');
        const recipeInput = document.getElementById('recipe');
        const existingInvoices = @json($existingInvoices);
        const storeNames = @json($storeNames);
        const subsidiaries = @json($subsidiariesData);
        const accounts = @json($accountsData);
        const stocks = @json($stocks);
        const transferStocks = @json($transferStocks);
        const usedStocks = @json($usedStocks);
        const transactions = @json($transactionsData);
        const recipes = @json($recipes);

        const voucherTypes = {
            PJ: {
                value: 'PJ',
                text: 'Penjualan',
                description: 'Voucher Penjualan - Dokumen internal perusahaan untuk mencatat transaksi penjualan barang atau jasa yang tidak dapat dicatat pada voucher lain.'
            },
            PB: {
                value: 'PB',
                text: 'Pembelian',
                description: 'Voucher Pembelian - Dokumen untuk mencatat transaksi pembelian barang atau jasa, seperti pembelian material, peralatan, atau layanan dari pemasok.'
            },
            PG: {
                value: 'PG',
                text: 'Pengeluaran',
                description: 'Voucher Pengeluaran - Dokumen untuk mencatat pengeluaran dana perusahaan, seperti pembayaran tagihan, pembelian material, atau biaya operasional, sebagai bukti otorisasi transaksi.'
            },
            PM: {
                value: 'PM',
                text: 'Pemasukan',
                description: 'Voucher Pemasukan - Dokumen internal perusahaan untuk mencatat penerimaan dana, seperti pembayaran dari pelanggan, setoran tunai, atau penerimaan lain yang masuk ke kas atau bank perusahaan.'
            },
            LN: {
                value: 'LN',
                text: 'Lainnya',
                description: 'Voucher Lainnya - Dokumen untuk mencatat transaksi yang tidak termasuk dalam kategori voucher lain, seperti koreksi jurnal atau transaksi khusus lainnya.'
            },
            PH: {
                value: 'PH',
                text: 'Pemindahan',
                description: 'Voucher Pemindahan - Dokumen untuk mencatat pemindahan stok barang dari satu lokasi ke lokasi lain dalam perusahaan.'
            },
            PK: {
                value: 'PK',
                text: 'Pemakaian',
                description: 'Voucher Pemakaian - Dokumen untuk mencatat pemakaian barang dalam operasional perusahaan.'
            },
        };

        function toggleSizeInputState() {
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            const sizeInputs = transactionTableBody.querySelectorAll('.sizeInput');
            sizeInputs.forEach(input => {
                if (input.tagName.toLowerCase() === 'select') {
                    input.disabled = useStock !== 'yes';
                    if (useStock !== 'yes') input.value = '';
                } else if (input.tagName.toLowerCase() === 'input') {
                    input.disabled = useStock !== 'yes';
                    if (useStock !== 'yes') input.value = '';
                }
            });
        }

        function createRecipeDropdown() {
            const select = document.createElement('select');
            select.className = 'form-control';
            select.id = 'recipe';
            select.name = 'recipe_id'; // Changed from 'recipe' to 'recipe_id'

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Formula Produk';
            select.appendChild(defaultOption);

            if (recipes && Array.isArray(recipes)) {
                recipes.forEach(recipe => {
                    if (recipe.id) {
                        const option = document.createElement('option');
                        option.value = recipe.id;
                        option.textContent = recipe.product_name || 'Unnamed Recipe';
                        select.appendChild(option);
                    } else {
                        console.warn('Recipe missing id:', recipe);
                    }
                });
            } else {
                console.error('Recipes data is invalid or undefined:', recipes);
            }

            const existingListener = select._eventListeners?.change;
            if (existingListener) {
                select.removeEventListener('change', existingListener);
            }
            select.addEventListener('change', handleRecipeChange);
            select._eventListeners = select._eventListeners || {};
            select._eventListeners.change = handleRecipeChange;

            return select;
        }

        function handleRecipeChange() {
            const selectedRecipeId = this.value;
            const voucherType = voucherTypeSelect.value;
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            console.log('Recipe Change:', {
                selectedRecipeId,
                voucherType,
                useStock
            });

            clearTimeout(this._debounceTimeout);
            this._debounceTimeout = setTimeout(() => {
                if (selectedRecipeId && voucherType === 'PK' && useStock === 'yes') {
                    addTransactionRowBtn.disabled = true;
                    // console.log('Disabling Tambah Transaksi button');
                    populateTransactionTableFromRecipe(selectedRecipeId);
                } else {
                    addTransactionRowBtn.disabled = false;
                    // console.log('Enabling Tambah Transaksi button');
                    refreshTransactionTable();
                }
            }, 100);
        }

        function populateTransactionTableFromRecipe(recipeId) {
            const selectedRecipe = recipes.find(r => r.id == recipeId);
            if (!selectedRecipe) {
                console.error('Invalid recipe data:', selectedRecipe);
                transactionTableBody.innerHTML = '<tr><td colspan="6">No valid recipe data available</td></tr>';
                return;
            }

            console.log('Selected Recipe:', selectedRecipe); // Debug selected recipe data
            transactionTableBody.innerHTML = '';
            const row = generateTransactionTableRow(0);
            transactionTableBody.appendChild(row);

            const descriptionInput = row.querySelector('.descriptionInput');
            const sizeInput = row.querySelector('.sizeInput');
            const quantityInput = row.querySelector('.quantityInput');
            const nominalInput = row.querySelector('.nominalInput');
            const totalInput = row.querySelector('.totalInput');

            if (descriptionInput && descriptionInput.tagName === 'INPUT') {
                descriptionInput.value = selectedRecipe.product_name || '';
                descriptionInput.readonly = true;
                // console.log('Description set to:', descriptionInput.value); // Debug
            }
            if (sizeInput && sizeInput.tagName === 'INPUT') {
                sizeInput.value = selectedRecipe.size || '';
                sizeInput.readonly = true;
                // console.log('Size set to:', sizeInput.value); // Debug
            }
            if (quantityInput) {
                quantityInput.value = 1;
                quantityInput.addEventListener('input', function() {
                    updateRowTotal(row);
                    updateAllCalculationsAndValidations();
                });
            }
            if (nominalInput) {
                const nominalValue = parseFloat(selectedRecipe.nominal) || 0;
                nominalInput.value = nominalValue.toFixed(2);
                nominalInput.readonly = true;
            }
            if (totalInput) {
                updateRowTotal(row);
            }

            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            toggleSizeInputState();
            updateAllCalculationsAndValidations();
        }

        function updateRowTotal(row) {
            const quantityInput = row.querySelector('.quantityInput');
            const nominalInput = row.querySelector('.nominalInput');
            const totalInput = row.querySelector('.totalInput');

            if (quantityInput && nominalInput && totalInput) {
                const quantity = parseFloat(quantityInput.value) || 0;
                const nominal = parseFloat(nominalInput.value) || 0;
                const total = quantity * nominal;
                totalInput.value = total.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        }

        function updateRecipeField() {
            recipeInputContainer.innerHTML = '';
            const voucherType = voucherTypeSelect.value;
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';

            if (voucherType === 'PK' && useStock === 'yes') {
                const recipeSelect = createRecipeDropdown();
                recipeInputContainer.appendChild(recipeSelect);
                if (recipeSelect.value) {
                    const changeEvent = new Event('change');
                    recipeSelect.dispatchEvent(changeEvent);
                }
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.id = 'recipe';
                input.name = 'recipe';
                input.disabled = true;
                recipeInputContainer.appendChild(input);
                addTransactionRowBtn.disabled = false;
            }
            refreshTransactionTable();
        }

        function updateVoucherTypeOptions() {
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            const currentValue = voucherTypeSelect.value;
            voucherTypeSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Tipe Voucher';
            voucherTypeSelect.appendChild(defaultOption);

            if (useStock === 'yes') {
                [voucherTypes.PB, voucherTypes.PJ, voucherTypes.PH, voucherTypes.PK].forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.value;
                    option.textContent = type.text;
                    voucherTypeSelect.appendChild(option);
                });
            } else {
                [voucherTypes.PG, voucherTypes.PM, voucherTypes.LN].forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.value;
                    option.textContent = type.text;
                    voucherTypeSelect.appendChild(option);
                });
            }

            voucherTypeSelect.value = currentValue && voucherTypeSelect.querySelector(
                `option[value="${currentValue}"]`) ? currentValue : '';
            deskripsiVoucherTextarea.value = voucherTypes[voucherTypeSelect.value]?.description || '';
            refreshTransactionTable();
            updateAllCalculationsAndValidations();
            toggleSizeInputState();
        }

        useStockYes.addEventListener('change', () => {
            updateVoucherTypeOptions();
            refreshTransactionTable();
            toggleSizeInputState();
            updateRecipeField();
        });
        useStockNo.addEventListener('change', () => {
            updateVoucherTypeOptions();
            refreshTransactionTable();
            toggleSizeInputState();
            updateRecipeField();
        });

        function isSubsidiaryCodeUsed() {
            const accountCodeInputs = voucherDetailsTableBody.querySelectorAll('.accountCodeInput');
            return Array.from(accountCodeInputs).some(input => subsidiaries.some(s => s.subsidiary_code ===
                input.value.trim()));
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
                        option.textContent =
                            `${subsidiary.subsidiary_code} - ${subsidiary.account_name}`;
                        datalist.appendChild(option);
                    });
                } else {
                    accounts.forEach(account => {
                        const option = document.createElement('option');
                        option.value = account.account_code;
                        option.textContent =
                            `${account.account_code} - ${account.account_name}`;
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
                    if (subsidiary) accountNameInput.value = subsidiary.account_name;
                } else {
                    const account = accounts.find(a => a.account_code === enteredCode);
                    if (account) accountNameInput.value = account.account_name;
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
                    const useInvoice = document.querySelector('input[name="use_invoice"]:checked')
                        ?.value || 'no';

                    if (useInvoice === 'yes' && subsidiaries.some(s => s.subsidiary_code ===
                            enteredCode)) {
                        const subsidiary = subsidiaries.find(s => s.subsidiary_code === enteredCode);
                        if (subsidiary) accountNameInput.value = subsidiary.account_name;
                    } else {
                        const account = accounts.find(a => a.account_code === enteredCode);
                        if (account) accountNameInput.value = account.account_name;
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

            select.addEventListener('change', function() {
                updateProductDropdownForPK();
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
            invoiceFieldContainer.innerHTML = '';
            const invoiceLabel = document.createElement('label');
            invoiceLabel.htmlFor = 'invoice';
            invoiceLabel.className = 'col-sm-3 col-form-label';
            invoiceLabel.textContent = 'Nomor Invoice:';
            const invoiceInputDiv = document.createElement('div');
            invoiceInputDiv.className = 'col-sm-9';
            let invoiceInput;
            if (useInvoice === 'yes' && document.querySelector('input[name="use_existing_invoice"]:checked')
                ?.value === 'yes') {
                invoiceInput = createInvoiceDropdown();
            } else {
                invoiceInput = createInvoiceInput();
            }
            invoiceInput.disabled = useInvoice !== 'yes';
            invoiceInputDiv.appendChild(invoiceInput);
            invoiceFieldContainer.appendChild(invoiceLabel);
            invoiceFieldContainer.appendChild(invoiceInputDiv);
            updateAccountCodeDatalist();
            updateProductDropdownForPK();
        }

        function updateDueDateField() {
            const useInvoice = document.querySelector('input[name="use_invoice"]:checked')?.value || 'no';
            const useExistingInvoice = document.querySelector('input[name="use_existing_invoice"]:checked')
                ?.value || 'no';
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
            if (useInvoice === 'yes' && useExistingInvoice === 'no') setTodayDueDate();
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
            updateProductDropdownForPK();
        }

        useInvoiceYes.addEventListener('change', updateInvoiceAndStoreFields);
        useInvoiceNo.addEventListener('change', updateInvoiceAndStoreFields);
        useExistingInvoiceYes.addEventListener('change', function() {
            updateInvoiceField();
            updateDueDateField();
            updateProductDropdownForPK();
        });
        useExistingInvoiceNo.addEventListener('change', function() {
            updateInvoiceField();
            updateDueDateField();
            updateProductDropdownForPK();
        });

        function createSizeInputWithDropdown(index, selectedItem) {
            const container = document.createElement('div');
            container.className = 'input-group';

            const select = document.createElement('select');
            select.className = 'form-control sizeInput';
            select.style.width = '50%';
            select.name = `transactions[${index}][size]`;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Ukuran';
            select.appendChild(defaultOption);

            if (selectedItem && stocks && Array.isArray(stocks) && stocks.length > 0) {
                const sizesWithQuantity = stocks
                    .filter(stock => stock.item === selectedItem && stock.size)
                    .map(stock => ({
                        size: stock.size,
                        quantity: stock.quantity || 0
                    }));

                // console.log(`Sizes for Item ${selectedItem}:`, sizesWithQuantity);

                if (sizesWithQuantity.length > 0) {
                    sizesWithQuantity.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.size;
                        option.textContent = `${item.size} (Stok: ${item.quantity})`;
                        select.appendChild(option);
                    });
                } else {
                    const noSizesOption = document.createElement('option');
                    noSizesOption.value = '';
                    noSizesOption.textContent = 'Tidak ada ukuran tersedia';
                    noSizesOption.disabled = true;
                    select.appendChild(noSizesOption);
                }
            }

            select.addEventListener('change', function() {
                const input = container.querySelector('input');
                input.value = this.value;
                const row = container.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || '';
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                // console.log(`Size changed: index=${rowIndex}, item=${description}, size=${this.value}`);
                updateAllCalculationsAndValidations();
            });

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control sizeInput';
            input.style.width = '50%';
            input.name = `transactions[${index}][size]`;
            input.addEventListener('input', function() {
                select.value = '';
                const row = container.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || '';
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                // console.log(`Size input changed: index=${rowIndex}, item=${description}, size=${this.value}`);
                updateAllCalculationsAndValidations();
            });

            container.appendChild(select);
            container.appendChild(input);
            return container;
        }

        function createSizeDropdown(index, selectedItem, voucherType) {
            const select = document.createElement('select');
            select.className = 'form-control sizeInput';
            select.name = `transactions[${index}][size]`;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Ukuran';
            select.appendChild(defaultOption);

            let stockData = [];
            if (voucherType === 'PJ') {
                stockData = [...usedStocks, ...stocks];
            } else if (voucherType === 'PH') {
                stockData = stocks;
            } else if (voucherType === 'PK') {
                stockData = transferStocks;
            }

            if (selectedItem && stockData && Array.isArray(stockData) && stockData.length > 0) {
                const sizesWithQuantity = stockData
                    .filter(stock => stock.item === selectedItem && stock.size && stock.quantity !== null && !
                        stock.item.startsWith('HPP'))
                    .map(stock => ({
                        size: stock.size,
                        quantity: stock.quantity,
                        source: stock.source || (usedStocks.includes(stock) ? 'used_stocks' :
                            'stocks')
                    }))
                    .filter(item => item.size !== null && item.size !== undefined);

                if (voucherType === 'PJ' && sizesWithQuantity.length > 0) {
                    const usedStockSizes = sizesWithQuantity.filter(item => item.source === 'used_stocks');
                    const StockSizes = sizesWithQuantity.filter(item => item.source ===
                        'stocks');

                    if (usedStockSizes.length > 0) {
                        const separator = document.createElement('option');
                        separator.value = '';
                        separator.textContent = '---- Used Stocks ----';
                        separator.disabled = true;
                        select.appendChild(separator);

                        usedStockSizes.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.size;
                            option.textContent = `${item.size} (Stok: ${item.quantity})`;
                            option.dataset.source = 'used_stocks';
                            select.appendChild(option);
                        });
                    }

                    if (StockSizes.length > 0) {
                        const separator = document.createElement('option');
                        separator.value = '';
                        separator.textContent = '---- Stocks ----';
                        separator.disabled = true;
                        select.appendChild(separator);

                        StockSizes.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.size;
                            option.textContent = `${item.size} (Stok: ${item.quantity})`;
                            option.dataset.source = 'stocks';
                            select.appendChild(option);
                        });
                    }

                    // Set default to the first available size
                    const firstSize = sizesWithQuantity.length > 0 ? sizesWithQuantity[0].size : '';
                    if (firstSize) {
                        select.value = firstSize;
                    }
                } else {
                    sizesWithQuantity.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.size;
                        option.textContent = `${item.size} (Stok: ${item.quantity})`;
                        select.appendChild(option);
                    });

                    // Set default to the first available size
                    const firstSize = sizesWithQuantity.length > 0 ? sizesWithQuantity[0].size : '';
                    if (firstSize) {
                        select.value = firstSize;
                    }
                }
            }

            select.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';
            select.addEventListener('change', function(event) {
                const row = select.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || '';
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                // console.log(`Size changed: index=${rowIndex}, item=${description}, size=${this.value}`);
                handleStockChange(rowIndex, event); // Trigger handleStockChange on size change
                updateAllCalculationsAndValidations();
            });

            return select;
        }

        function updateSizeDropdown(index, selectedItem) {
            const row = transactionTableBody.querySelector(`tr[data-row-index="${index}"]`);
            if (!row) return;

            const sizeCell = row.querySelector('td:nth-child(2)');
            if (!sizeCell) return;

            let currentSizeInput = sizeCell.querySelector('.sizeInput');
            const voucherType = voucherTypeSelect.value;
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            let newSizeElement;

            if (useStock === 'yes' && (voucherType === 'PB' || voucherType === 'PJ' || voucherType === 'PH' ||
                    voucherType === 'PK')) {
                if (voucherType === 'PB') {
                    newSizeElement = createSizeInputWithDropdown(index, selectedItem);
                } else {
                    newSizeElement = createSizeDropdown(index, selectedItem, voucherType);
                }
            } else {
                newSizeElement = document.createElement('input');
                newSizeElement.type = 'text';
                newSizeElement.className = 'form-control sizeInput';
                newSizeElement.name = `transactions[${index}][size]`;
                newSizeElement.disabled = true;
            }

            let previousValue = '';
            if (currentSizeInput) {
                previousValue = currentSizeInput.value || '';
            }

            while (sizeCell.firstChild) {
                sizeCell.removeChild(sizeCell.firstChild);
            }

            sizeCell.appendChild(newSizeElement);

            // Infer default size if previousValue is empty and stock data is available
            if (!previousValue && voucherType === 'PJ' && selectedItem) {
                const stockData = [...usedStocks, ...(stocks || [])];
                const sizes = stockData
                    .filter(s => s.item === selectedItem && !s.item.startsWith('HPP'))
                    .map(s => s.size);
                const defaultSize = sizes.length > 0 ? sizes[0] : null; // Use first available size
                if (defaultSize && newSizeElement.tagName === 'SELECT') {
                    newSizeElement.value = defaultSize;
                    previousValue = defaultSize; // Update previousValue with inferred size
                    // console.log(`Inferred default size for ${selectedItem}: ${defaultSize}`);
                }
            }

            if (previousValue) {
                if (newSizeElement.tagName === 'SELECT') {
                    const options = newSizeElement.querySelectorAll('option');
                    const validOption = Array.from(options).find(option => option.value === previousValue);
                    if (validOption) newSizeElement.value = previousValue;
                } else if (newSizeElement.tagName === 'DIV') {
                    const select = newSizeElement.querySelector('select');
                    const input = newSizeElement.querySelector('input');
                    const validOption = select && Array.from(select.querySelectorAll('option')).find(option =>
                        option.value === previousValue);
                    if (validOption) {
                        select.value = previousValue;
                        input.value = previousValue;
                    } else {
                        input.value = previousValue;
                    }
                } else {
                    newSizeElement.value = previousValue;
                }
            }

            // Trigger handleStockChange after setting the size
            if (newSizeElement.value) {
                const sizeChangeEvent = new Event('change', {
                    bubbles: true
                });
                newSizeElement.dispatchEvent(sizeChangeEvent);
                handleStockChange(index, {
                    target: newSizeElement
                }); // Explicitly call handleStockChange
            }

            attachTransactionInputListeners();
        }

        function createStockDropdown(index, voucherType) {
            const select = document.createElement('select');
            select.className = 'form-control descriptionInput';
            select.name = `transactions[${index}][description]`;

            // Create default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Item Stok';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            select.appendChild(defaultOption);

            // Initialize stock data based on voucher type
            let stockData = [];
            if (voucherType === 'PJ') {
                stockData = [...(usedStocks || []), ...(stocks || [])];
            } else if (voucherType === 'PH' || voucherType === 'PB') {
                stockData = stocks || [];
            } else if (voucherType === 'PK') {
                stockData = transferStocks || [];
            } else {
                // Handle invalid voucher type
                const invalidOption = document.createElement('option');
                invalidOption.value = '';
                invalidOption.textContent = 'Jenis voucher tidak valid';
                invalidOption.disabled = true;
                select.appendChild(invalidOption);
                select.disabled = true;
                return select;
            }

            // Populate items if stockData is valid
            if (stockData && Array.isArray(stockData) && stockData.length > 0) {
                const uniqueItems = [...new Set(stockData.map(stock => stock.item))]
                    .filter(item => !item.toLowerCase().startsWith('hpp'))
                    .sort(); // Sort items alphabetically

                if (uniqueItems.length === 0) {
                    const noItemsOption = document.createElement('option');
                    noItemsOption.value = '';
                    noItemsOption.textContent = 'Tidak ada item stok tersedia';
                    noItemsOption.disabled = true;
                    select.appendChild(noItemsOption);
                } else if (voucherType === 'PJ') {
                    // Separate usedStocks and stocks for PJ voucher type
                    const usedStockItems = uniqueItems.filter(item =>
                        stockData.some(stock => stock.item === item && (usedStocks || []).includes(stock))
                    );
                    const stockItems = uniqueItems.filter(item =>
                        stockData.some(stock => stock.item === item && (stocks || []).includes(stock))
                    );

                    if (stockItems.length > 0) {
                        const separator = document.createElement('option');
                        separator.value = '';
                        separator.textContent = '-- Bahan Baku --';
                        separator.disabled = true;
                        select.appendChild(separator);

                        stockItems.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item;
                            option.textContent = item;
                            option.dataset.source = 'stocks';
                            select.appendChild(option);
                        });
                    }

                    if (usedStockItems.length > 0) {
                        const separator = document.createElement('option');
                        separator.value = '';
                        separator.textContent = '-- Barang Jadi --';
                        separator.disabled = true;
                        select.appendChild(separator);

                        usedStockItems.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item;
                            option.textContent = item;
                            option.dataset.source = 'used_stocks';
                            select.appendChild(option);
                        });
                    }

                } else {
                    // Non-PJ voucher types: list all items without separators
                    uniqueItems.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item;
                        option.textContent = item;
                        option.dataset.source = stockData.find(stock => stock.item === item)?.source ||
                            'stocks';
                        select.appendChild(option);
                    });
                }
            } else {
                // No valid stock data
                const noDataOption = document.createElement('option');
                noDataOption.value = '';
                noDataOption.textContent = 'Tidak ada data stok';
                noDataOption.disabled = true;
                select.appendChild(noDataOption);
            }

            // Disable dropdown if use_stock is not 'yes'
            select.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';

            // Add change event listener
            select.addEventListener('change', function(event) {
                const selectedValue = this.value.trim();
                if (selectedValue) {
                    if (typeof handleStockChange === 'function') {
                        handleStockChange(index, event);
                    }
                    if (typeof updateSizeDropdown === 'function') {
                        updateSizeDropdown(index, selectedValue);
                    }
                }
            });

            return select;
        }

        function createSizeInputWithDropdown(index, selectedItem) {
            const container = document.createElement('div');
            container.className = 'input-group';

            const select = document.createElement('select');
            select.className = 'form-control sizeInput';
            select.style.width = '50%';
            select.name = `transactions[${index}][size]`;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Ukuran';
            select.appendChild(defaultOption);

            if (selectedItem && stocks && Array.isArray(stocks) && stocks.length > 0) {
                const sizesWithQuantity = stocks
                    .filter(stock => stock.item === selectedItem && stock.size)
                    .map(stock => ({
                        size: stock.size,
                        quantity: stock.quantity || 0
                    }));

                // console.log(`Sizes for Item ${selectedItem}:`, sizesWithQuantity);

                if (sizesWithQuantity.length > 0) {
                    sizesWithQuantity.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.size;
                        option.textContent = `${item.size} (Stok: ${item.quantity})`;
                        select.appendChild(option);
                    });
                } else {
                    const noSizesOption = document.createElement('option');
                    noSizesOption.value = '';
                    noSizesOption.textContent = 'Tidak ada ukuran tersedia';
                    noSizesOption.disabled = true;
                    select.appendChild(noSizesOption);
                }
            }

            select.addEventListener('change', function() {
                const input = container.querySelector('input');
                input.value = this.value;
                const row = container.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || '';
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                // console.log(`Size changed: index=${rowIndex}, item=${description}, size=${this.value}`);
                updateAllCalculationsAndValidations();
            });

            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control sizeInput';
            input.style.width = '50%';
            input.name = `transactions[${index}][size]`;
            input.addEventListener('input', function() {
                select.value = '';
                const row = container.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || '';
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                // console.log(`Size input changed: index=${rowIndex}, item=${description}, size=${this.value}`);
                updateAllCalculationsAndValidations();
            });

            container.appendChild(select);
            container.appendChild(input);
            return container;
        }

        function createDescriptionInput(index) {
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control descriptionInput';
            input.name = `transactions[${index}][description]`;
            return input;
        }

        function createProductDropdownForPK(index) {
            const select = document.createElement('select');
            select.className = 'form-control descriptionInput';
            select.name = `transactions[${index}][description]`;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            select.appendChild(defaultOption);

            const invoiceNumber = document.querySelector('#invoice')?.value;
            if (invoiceNumber) {
                const relatedStocks = usedStocks.filter(stock => stock.invoice_number === invoiceNumber);
                const uniqueItems = [...new Set(relatedStocks.map(stock => stock.item))];

                uniqueItems.forEach(item => {
                    const stock = relatedStocks.find(s => s.item === item);
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = `${item}`;
                    select.appendChild(option);
                });
            }

            select.addEventListener('change', function(event) {
                handleStockChange(index, event);
                updateSizeDropdown(index, this.value);
            });

            return select;
        }

        function updateProductDropdownForPK() {
            const voucherType = voucherTypeSelect.value;
            if (voucherType !== 'PK') return;

            const invoiceNumber = document.querySelector('#invoice')?.value;
            transactionTableBody.querySelectorAll('tr[data-is-new-item="true"]').forEach(row => {
                const index = parseInt(row.dataset.rowIndex);
                const descriptionCell = row.querySelector('td:nth-child(1)');
                if (!descriptionCell) return;

                const currentDescriptionInput = row.querySelector('.descriptionInput');
                const previousValue = currentDescriptionInput ? currentDescriptionInput.value : '';
                descriptionCell.innerHTML = '';

                const newDescriptionElement = createProductDropdownForPK(index);
                descriptionCell.appendChild(newDescriptionElement);

                if (previousValue && newDescriptionElement.querySelector(
                        `option[value="${previousValue}"]`)) {
                    newDescriptionElement.value = previousValue;
                }

                updateSizeDropdown(index, newDescriptionElement.value);
            });
        }

        function calculateAverageHpp(item, size, voucherType) {
            // Validate input parameters
            if (!item || typeof item !== 'string') {
                console.error(`Invalid item parameter: ${item}`);
                return null;
            }

            const trimmedItem = item.trim().toLowerCase();
            const trimmedSize = size ? size.trim().replace(/\s*\(.*?\)/g, '') : '';
            const effectiveVoucherType = voucherType || 'unknown'; // Handle undefined voucherType

            // Determine stock data source based on voucherType
            let stockData = [];
            if (effectiveVoucherType === 'PJ') {
                stockData = [...(usedStocks || []), ...(stocks || [])];
            } else {
                stockData = stocks || [];
            }

            // Normalize base item by removing "HPP" prefix
            const baseItem = trimmedItem.replace(/^hpp\s+/i, '');

            // Filter transactions for matching item, size, and voucher_type='PB'
            const matchingTransactions = transactions.filter(t => {
                const transactionDescription = t.description ? t.description.trim().toLowerCase() : '';
                const transactionSize = t.size ? t.size.trim().replace(/\s*\(.*?\)/g, '') : '';
                const transactionVoucherType = t.voucher_type || '';
                return (
                    transactionDescription === baseItem &&
                    (!trimmedSize || transactionSize === trimmedSize) &&
                    transactionVoucherType === 'PB'
                );
            });

            // Calculate HPP: total cost (nominal) / total quantity
            const totalCost = matchingTransactions.reduce((sum, t) => {
                const nominal = parseFloat(t.nominal) || 0;
                return sum + nominal;
            }, 0);

            const totalQuantity = matchingTransactions.reduce((sum, t) => {
                const quantity = parseFloat(t.quantity) || 1; // Fallback to 1 if quantity is missing
                return sum + quantity;
            }, 0);

            const averageHpp = totalCost / totalQuantity;

            // Return rounded integer or null if invalid
            return isNaN(averageHpp) ? null : Math.round(averageHpp);
        }

        function removeHppRowForItem(rowIndex) {
            const transactionTableBody = document.querySelector('#transactionTable tbody');
            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${rowIndex}"]`);
            if (!currentRow) return;

            let nextRow = currentRow.nextSibling;
            const rowsToRemove = [];

            while (nextRow && nextRow.dataset.isHppRow === 'true') {
                rowsToRemove.push(nextRow);
                nextRow = nextRow.nextSibling;
            }

            rowsToRemove.forEach(row => {
                const description = row.querySelector('.descriptionInput')?.value || '';
                const size = row.querySelector('.sizeInput')?.value || '';
                // console.log(`Removing HPP row: description=${description}, size=${size}, rowIndex=${row.dataset.rowIndex}`);
                row.remove();
            });

            updateTransactionRowIndices();
        }

        function addHppRowDirectly(currentIndex, selectedItem, size, quantity) {
            // Disabled HPP row generation for PB when useStock is yes
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            if (useStock === 'yes' && voucherTypeSelect.value === 'PB') return;

            if (!selectedItem || voucherTypeSelect.value !== 'PB') return;

            const transactionTableBody = document.querySelector('#transactionTable tbody');
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

            const sizeCell = document.createElement('td');
            const sizeInput = document.createElement('input');
            sizeInput.type = 'text';
            sizeInput.className = 'form-control sizeInput';
            sizeInput.name = `transactions[${newIndex}][size]`;
            sizeInput.value = size || '';
            sizeInput.readOnly = true;
            sizeInput.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';
            sizeCell.appendChild(sizeInput);
            hppRow.appendChild(sizeCell);

            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '0';
            quantityInput.step = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${newIndex}][quantity]`;
            quantityInput.value = quantity || 1;
            quantityInput.readOnly = true;
            quantityCell.appendChild(quantityInput);
            hppRow.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.step = '0.01';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${newIndex}][nominal]`;
            const averageHpp = calculateAverageHpp(selectedItem, size);
            nominalInput.value = averageHpp.toFixed(2);
            nominalInput.readOnly = true;
            nominalCell.appendChild(nominalInput);
            hppRow.appendChild(nominalCell);

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'text';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${newIndex}][total]`;
            totalInput.readOnly = true;
            totalInput.value = (quantity * averageHpp).toLocaleString('id-ID', {
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

            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
            if (currentRow.nextSibling) transactionTableBody.insertBefore(hppRow, currentRow.nextSibling);
            else transactionTableBody.appendChild(hppRow);

            // console.log(`Added HPP row for PB: item=${selectedItem}, size=${size}, rowIndex=${newIndex}`);
            updateTransactionRowIndices();
            updateAllCalculationsAndValidations();
        }

        function addHppRowForPJ(currentIndex, selectedItem, size, quantity) {
            if (!selectedItem || voucherTypeSelect.value !== 'PJ') return;

            const transactionTableBody = document.querySelector('#transactionTable tbody');
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const hppRow = document.createElement('tr');
            hppRow.dataset.rowIndex = newIndex;
            hppRow.dataset.isHppRow = 'true';
            hppRow.dataset.parentIndex = currentIndex; // Link to parent transaction row
            hppRow.dataset.item = selectedItem;
            hppRow.dataset.size = size || '';

            const descriptionCell = document.createElement('td');
            const descriptionInput = createDescriptionInput(newIndex);
            descriptionInput.value = `HPP ${selectedItem}`;
            descriptionInput.readOnly = true;
            descriptionCell.appendChild(descriptionInput);
            hppRow.appendChild(descriptionCell);

            const sizeCell = document.createElement('td');
            const sizeInput = document.createElement('input');
            sizeInput.type = 'text';
            sizeInput.className = 'form-control sizeInput';
            sizeInput.name = `transactions[${newIndex}][size]`;
            sizeInput.value = size || '';
            sizeInput.readOnly = true;
            sizeInput.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';
            sizeCell.appendChild(sizeInput);
            hppRow.appendChild(sizeCell);

            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '0';
            quantityInput.step = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${newIndex}][quantity]`;
            quantityInput.value = quantity || 1;
            quantityInput.readOnly = true;
            quantityCell.appendChild(quantityInput);
            hppRow.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.step = '1'; // Integer step since calculateAverageHpp returns integer
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${newIndex}][nominal]`;
            const averageHpp = calculateAverageHpp(selectedItem, size, 'PJ') || 0;
            nominalInput.value = averageHpp; // Integer HPP value
            nominalInput.readOnly = true;
            nominalCell.appendChild(nominalInput);
            hppRow.appendChild(nominalCell);

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'text';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${newIndex}][total]`;
            totalInput.readOnly = true;
            totalInput.value = (quantity * averageHpp).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
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

            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
            if (currentRow && currentRow.nextSibling) {
                transactionTableBody.insertBefore(hppRow, currentRow.nextSibling);
            } else {
                transactionTableBody.appendChild(hppRow);
            }

            updateTransactionRowIndices();
            updateAllCalculationsAndValidations();
        }

        function updateHppRowForPJ(currentIndex, selectedItem, size, quantity) {
            if (!selectedItem || voucherTypeSelect.value !== 'PJ') return;

            const transactionTableBody = document.querySelector('#transactionTable tbody');
            const currentRow = transactionTableBody.querySelector(`tr[data-row-index="${currentIndex}"]`);
            let hppRowToUpdate = null;

            // Find HPP row linked to this transaction row
            let nextRow = currentRow ? currentRow.nextSibling : null;
            while (nextRow && nextRow.dataset.isHppRow === 'true') {
                if (nextRow.dataset.parentIndex === String(currentIndex)) {
                    hppRowToUpdate = nextRow;
                    break;
                }
                nextRow = nextRow.nextSibling;
            }

            const averageHpp = calculateAverageHpp(selectedItem, size, 'PJ') || 0;

            if (hppRowToUpdate) {
                // Update existing HPP row
                const descriptionInput = hppRowToUpdate.querySelector('.descriptionInput');
                descriptionInput.value = `HPP ${selectedItem}`;
                const sizeInput = hppRowToUpdate.querySelector('.sizeInput');
                sizeInput.value = size || '';
                sizeInput.readOnly = true;
                sizeInput.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';
                const quantityInput = hppRowToUpdate.querySelector('.quantityInput');
                quantityInput.value = quantity || 1;
                quantityInput.readOnly = true;
                const nominalInput = hppRowToUpdate.querySelector('.nominalInput');
                nominalInput.value = averageHpp;
                nominalInput.readOnly = true;
                const totalInput = hppRowToUpdate.querySelector('.totalInput');
                totalInput.value = averageHpp > 0 ? (quantity * averageHpp).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }) : 'N/A';
                hppRowToUpdate.dataset.item = selectedItem;
                hppRowToUpdate.dataset.size = size || '';
                hppRowToUpdate.dataset.parentIndex = currentIndex;

                updateAllCalculationsAndValidations();
            } else {
                // Add new HPP row if none exists
                addHppRowForPJ(currentIndex, selectedItem, size, quantity);
            }
        }

        function handleStockChange(index, event) {
            const selectedElement = event.target;
            if (!selectedElement.classList.contains('descriptionInput') && !selectedElement.classList.contains(
                    'sizeInput')) {
                return;
            }

            if (selectedElement.dataset.isProcessing === 'true') {
                return;
            }
            selectedElement.dataset.isProcessing = 'true';

            try {
                const row = selectedElement.closest('tr');
                const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                const sizeElement = row.querySelector('.sizeInput');
                const initialSize = sizeElement ? (sizeElement.value || '').trim() : '';
                const voucherType = voucherTypeSelect.value;

                let selectedItem = (
                    row.querySelector('.descriptionInput:not([type="text"])')?.value ||
                    row.querySelector('.descriptionInput[type="text"]')?.value || ''
                ).trim();

                if (!selectedItem) {
                    // Remove HPP row if item is cleared
                    removeHppRowForItem(index);
                    return;
                }

                let isValidItem = false;
                let inferredSize = initialSize;

                if (voucherType === 'PJ') {
                    const stockData = [...usedStocks, ...(stocks || [])];
                    isValidItem = stockData.some(s => s.item.trim().toLowerCase() === selectedItem.trim()
                        .toLowerCase());
                    if (!isValidItem || !initialSize) {
                        const validItemFromSize = stockData.find(s => s.size === selectedItem)?.item;
                        if (validItemFromSize) {
                            selectedItem = validItemFromSize;
                            updateDescriptionInput(row, validItemFromSize);
                            isValidItem = true;
                        } else {
                            const similarItem = stockData.find(s => s.item.toLowerCase().includes(selectedItem
                                .toLowerCase()));
                            if (similarItem) {
                                selectedItem = similarItem.item;
                                updateDescriptionInput(row, similarItem.item);
                                isValidItem = true;
                            } else {
                                selectedItem = '';
                            }
                        }
                        if (!initialSize && isValidItem) {
                            const matchingStock = stockData.find(s => s.item === selectedItem);
                            inferredSize = matchingStock ? matchingStock.size : '';
                            if (sizeElement && inferredSize) {
                                sizeElement.value = inferredSize;
                            }
                        }
                    }
                } else {
                    isValidItem = stocks.some(s => s.item.trim().toLowerCase() === selectedItem.trim()
                        .toLowerCase());
                    if (!isValidItem || !initialSize) {
                        const validItemFromSize = stocks.find(s => s.size === selectedItem)?.item;
                        if (validItemFromSize) {
                            selectedItem = validItemFromSize;
                            updateDescriptionInput(row, validItemFromSize);
                            isValidItem = true;
                        } else {
                            const similarItem = stocks.find(s => s.item.toLowerCase().includes(selectedItem
                                .toLowerCase()));
                            if (similarItem) {
                                selectedItem = similarItem.item;
                                updateDescriptionInput(row, similarItem.item);
                                isValidItem = true;
                            } else {
                                selectedItem = '';
                            }
                        }
                        if (!initialSize && isValidItem) {
                            const matchingStock = stocks.find(s => s.item === selectedItem);
                            inferredSize = matchingStock ? matchingStock.size : '';
                            if (sizeElement && inferredSize) {
                                sizeElement.value = inferredSize;
                            }
                        }
                    }
                }

                if (!selectedItem || !inferredSize) {
                    removeHppRowForItem(index);
                    return;
                }

                // Remove existing HPP row for this transaction row
                removeHppRowForItem(index);

                if (selectedItem && voucherType === 'PJ') {
                    updateHppRowForPJ(index, selectedItem, inferredSize, quantity);
                }

                updateAllCalculationsAndValidations();
            } finally {
                selectedElement.dataset.isProcessing = 'false';
            }
        }

        // Helper function to update description input/select
        function updateDescriptionInput(row, value) {
            const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
            const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
            if (descriptionSelect) descriptionSelect.value = value;
            else if (descriptionInput) descriptionInput.value = value;
        }

        function generateTransactionTableRow(index) {
            const row = document.createElement('tr');
            row.dataset.rowIndex = index;

            const descriptionCell = document.createElement('td');
            let descriptionElement;
            const voucherType = voucherTypeSelect.value;
            const useStock = document.querySelector('input[name="use_stock"]:checked')?.value || 'no';
            const recipeSelected = document.querySelector('#recipe')?.value && voucherType === 'PK' &&
                useStock === 'yes';

            if (useStock === 'yes' && (voucherType === 'PJ' || voucherType === 'PB' || voucherType === 'PH' ||
                    voucherType === 'PK')) {
                if (voucherType === 'PB') {
                    descriptionElement = document.createElement('div');
                    descriptionElement.className = 'input-group';

                    const select = createStockDropdown(index, voucherType);
                    select.style.width = '50%';

                    const input = createDescriptionInput(index);
                    input.className = 'form-control';
                    input.style.width = '50%';

                    select.addEventListener('change', function() {
                        input.value = this.value;
                        updateSizeDropdown(index, this.value);
                        updateAllCalculationsAndValidations();
                    });

                    input.addEventListener('input', function() {
                        select.value = '';
                        updateSizeDropdown(index, '');
                        updateAllCalculationsAndValidations();
                    });

                    descriptionElement.appendChild(select);
                    descriptionElement.appendChild(input);
                } else if (voucherType === 'PK') {
                    // Use input instead of select for PK
                    descriptionElement = document.createElement('input');
                    descriptionElement.type = 'text';
                    descriptionElement.className = 'form-control descriptionInput';
                    descriptionElement.name = `transactions[${index}][description]`;
                } else {
                    descriptionElement = createStockDropdown(index, voucherType);
                }
                if (recipeSelected) {
                    descriptionElement.readOnly = true;
                }
            } else {
                descriptionElement = createDescriptionInput(index);
            }
            descriptionCell.appendChild(descriptionElement);
            row.appendChild(descriptionCell);

            const sizeCell = document.createElement('td');
            let sizeElement;
            if (useStock === 'yes' && voucherType === 'PB') {
                sizeElement = createSizeInputWithDropdown(index, descriptionElement.value);
            } else if (useStock === 'yes' && voucherType === 'PK') {
                sizeElement = document.createElement('input');
                sizeElement.type = 'text';
                sizeElement.className = 'form-control sizeInput';
                sizeElement.name = `transactions[${index}][size]`;
            } else {
                sizeElement = createSizeDropdown(index, descriptionElement.value, voucherType);
            }
            if (recipeSelected) {
                sizeElement.readOnly = true;
            }
            sizeCell.appendChild(sizeElement);
            row.appendChild(sizeCell);

            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '0';
            quantityInput.step = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${index}][quantity]`;
            quantityInput.value = '1';
            quantityInput.addEventListener('input', function() {
                updateRowTotal(row);
                updateAllCalculationsAndValidations();
            });
            quantityCell.appendChild(quantityInput);
            row.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.step = '0.01';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${index}][nominal]`;
            if (recipeSelected) {
                nominalInput.readonly = true;
            }
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

        function calculateNonNewItemTotals() {
            let totalNonNewItem = 0;
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                const isNewItemRow = row.dataset.isNewItem === 'true';
                const isHppRow = row.dataset.isHppRow === 'true';
                if (!isNewItemRow && !isHppRow) {
                    const total = calculateRowTotal(row);
                    totalNonNewItem += parseFloat(total.toString().replace(/[^0-9.-]+/g, '')) || 0;
                }
            });
            return totalNonNewItem;
        }

        function generateNewItemRow(index) {
            const row = document.createElement('tr');
            row.dataset.rowIndex = index;
            row.dataset.isNewItem = 'true';

            const descriptionCell = document.createElement('td');
            let descriptionInput;
            if (voucherTypeSelect.value === 'PK') {
                descriptionInput = createProductDropdownForPK(index);
            } else {
                descriptionInput = document.createElement('input');
                descriptionInput.type = 'text';
                descriptionInput.className = 'form-control descriptionInput';
                descriptionInput.name = `transactions[${index}][description]`;
                descriptionInput.placeholder = 'Masukkan Nama Barang Baru';
            }
            descriptionCell.appendChild(descriptionInput);
            row.appendChild(descriptionCell);

            const sizeCell = document.createElement('td');
            const sizeInput = document.createElement('input');
            sizeInput.type = 'text';
            sizeInput.className = 'form-control sizeInput';
            sizeInput.name = `transactions[${index}][size]`;
            sizeInput.disabled = document.querySelector('input[name="use_stock"]:checked')?.value !== 'yes';
            sizeCell.appendChild(sizeInput);
            row.appendChild(sizeCell);

            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.min = '0';
            quantityInput.step = '1';
            quantityInput.className = 'form-control quantityInput';
            quantityInput.name = `transactions[${index}][quantity]`;
            quantityInput.value = '1';
            quantityInput.addEventListener('input', function() {
                updateAllCalculationsAndValidations();
            });
            quantityCell.appendChild(quantityInput);
            row.appendChild(quantityCell);

            const nominalCell = document.createElement('td');
            const nominalInput = document.createElement('input');
            nominalInput.type = 'number';
            nominalInput.min = '0';
            nominalInput.step = '0.01';
            nominalInput.className = 'form-control nominalInput';
            nominalInput.name = `transactions[${index}][nominal]`;
            nominalInput.value = calculateNonNewItemTotals().toFixed(2);
            nominalInput.readOnly = true;
            nominalCell.appendChild(nominalInput);
            row.appendChild(nominalCell);

            const totalCell = document.createElement('td');
            const totalInput = document.createElement('input');
            totalInput.type = 'text';
            totalInput.className = 'form-control totalInput';
            totalInput.name = `transactions[${index}][total]`;
            totalInput.readOnly = true;
            const quantity = parseFloat(quantityInput.value) || 1;
            totalInput.value = (quantity * parseFloat(nominalInput.value)).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
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

        function updateNewItemNominal() {
            transactionTableBody.querySelectorAll('tr[data-is-new-item="true"]').forEach(row => {
                const nominalInput = row.querySelector('.nominalInput');
                const quantityInput = row.querySelector('.quantityInput');
                const totalInput = row.querySelector('.totalInput');

                if (nominalInput && totalInput) {
                    const newNominal = calculateNonNewItemTotals().toFixed(2);
                    nominalInput.value = newNominal;
                    const quantity = parseFloat(quantityInput.value) || 1;
                    totalInput.value = (quantity * parseFloat(newNominal)).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            });
        }

        function updateTransactionRowIndices() {
            transactionTableBody.querySelectorAll('tr').forEach((row, index) => {
                row.dataset.rowIndex = index;
                row.querySelectorAll('[name*="transactions["]').forEach(element => {
                    element.name = element.name.replace(/transactions\[\d+\]/,
                        `transactions[${index}]`);
                    if (element.tagName.toLowerCase() === 'select') {
                        element.removeEventListener('change', handleStockChange);
                        element.addEventListener('change', handleStockChange.bind(null, index));
                    }
                });
            });
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            toggleSizeInputState();
        }

        function attachTransactionRemoveButtonListeners() {
            transactionTableBody.querySelectorAll('.removeTransactionRowBtn').forEach(button => {
                const row = button.closest('tr');
                const isHppRow = row.dataset.isHppRow === 'true';
                const isNewItemRow = row.dataset.isNewItem === 'true';
                button.disabled = isHppRow;

                button.removeEventListener('click', handleRemoveTransactionRow);
                button.addEventListener('click', handleRemoveTransactionRow);
            });
        }

        function handleRemoveTransactionRow(event) {
            const totalRows = transactionTableBody.querySelectorAll('tr').length;
            if (totalRows > 1) {
                const row = event.target.closest('tr');
                const rowIndex = parseInt(row.dataset.rowIndex);
                const isHppRow = row.dataset.isHppRow === 'true';
                const voucherType = voucherTypeSelect.value;

                if (voucherType === 'PJ' && !isHppRow) {
                    const description = row.querySelector('.descriptionInput:not([type="text"])')?.value || row
                        .querySelector('.descriptionInput[type="text"]')?.value || '';
                    const size = row.querySelector('.sizeInput')?.value || '';
                    let nextRow = row.nextSibling;
                    while (nextRow) {
                        if (nextRow.dataset.isHppRow === 'true' && nextRow.querySelector('.descriptionInput')
                            ?.value === `HPP ${description}` && nextRow.querySelector('.sizeInput')?.value ===
                            size) {
                            nextRow.remove();
                            break;
                        }
                        nextRow = nextRow.nextSibling;
                    }
                }

                row.remove();
                updateTransactionRowIndices();
                updateAllCalculationsAndValidations();
                updateAddItemButtonState();
            } else {
                alert("Tidak dapat menghapus baris transaksi terakhir.");
            }
        }

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.querySelector('.quantityInput')?.value) || 0;
            const nominal = parseFloat(row.querySelector('.nominalInput')?.value) || 0;
            const total = quantity * nominal;
            const totalInput = row.querySelector('.totalInput');
            if (totalInput) {
                totalInput.value = total.toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            return total;
        }

        function syncHppQuantity(row) {
            const voucherType = voucherTypeSelect.value;
            if (voucherType !== 'PJ' && voucherType !== 'PB') return;

            const rowIndex = parseInt(row.dataset.rowIndex);
            const isHppRow = row.dataset.isHppRow === 'true';
            if (!isHppRow) {
                let nextRow = row.nextSibling;
                const description = row.querySelector('.descriptionInput:not([type="text"])')?.value || row
                    .querySelector('.descriptionInput[type="text"]')?.value || '';
                const size = row.querySelector('.sizeInput')?.value || '';
                while (nextRow) {
                    if (nextRow.dataset.isHppRow === 'true' && nextRow.querySelector('.descriptionInput')
                        ?.value === `HPP ${description}` && nextRow.querySelector('.sizeInput')?.value === size
                    ) {
                        const hppQuantityInput = nextRow.querySelector('.quantityInput');
                        const currentQuantity = parseFloat(row.querySelector('.quantityInput')?.value) || 1;
                        const hppNominal = parseFloat(nextRow.querySelector('.nominalInput')?.value) || 0;
                        hppQuantityInput.value = currentQuantity;
                        const hppTotalInput = nextRow.querySelector('.totalInput');
                        hppTotalInput.value = (currentQuantity * hppNominal).toLocaleString('id-ID', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        break;
                    }
                    nextRow = nextRow.nextSibling;
                }
            }
        }

        function validateStockQuantity(row) {
            const voucherType = voucherTypeSelect.value;
            const descriptionInput = row.querySelector('.descriptionInput');
            const quantityInput = row.querySelector('.quantityInput');
            const sizeInput = row.querySelector('.sizeInput');

            if (!descriptionInput || !quantityInput) {
                validationInput.value = 'Elemen input stok atau kuantitas tidak ditemukan di baris ini.';
                saveVoucherBtn.disabled = true;
                return {
                    isValid: false,
                    message: validationInput.value
                };
            }

            let description = descriptionInput.tagName.toLowerCase() === 'select' ? descriptionInput.value
                ?.trim() : descriptionInput.value?.trim() || '';
            const quantity = parseFloat(quantityInput.value) || 0;
            const size = sizeInput?.value?.trim() || '';
            const isHppRow = row.dataset.isHppRow === 'true';
            const isNewItemRow = row.dataset.isNewItem === 'true';

            if (isHppRow || !['PH', 'PK', 'PJ'].includes(voucherType)) {
                return {
                    isValid: true,
                    message: ''
                };
            }

            let stockData = [];
            let tableName = '';
            let targetTableName = '';

            if (voucherType === 'PH') {
                stockData = stocks;
                tableName = 'Stok';
            } else if (voucherType === 'PK') {
                stockData = usedStocks;
                tableName = 'used_stocks';
                targetTableName = 'used_stocks';
            } else if (voucherType === 'PJ') {
                stockData = [...usedStocks, ...stocks];
                tableName = 'Stok Pemakaian atau Stok Pemindahan';
            }

            if (!stockData || !Array.isArray(stockData) || stockData.length === 0) {
                validationInput.value = `Data tabel ${tableName} kosong atau tidak tersedia.`;
                saveVoucherBtn.disabled = true;
                return {
                    isValid: false,
                    message: validationInput.value
                };
            }

            let stock = null;
            if (!isNewItemRow) {
                const cleanDescription = description.replace(/\(Stok: \d+\)/, '').trim().toLowerCase();
                stock = stockData.find(s => s.item?.toLowerCase().replace(/\(stok: \d+\)/, '').trim() ===
                    cleanDescription && (!size || s.size === size));

                if (!description) {
                    validationInput.value = 'Item belum dipilih di baris ini.';
                    saveVoucherBtn.disabled = true;
                    return {
                        isValid: false,
                        message: validationInput.value
                    };
                }
                if (!stock && voucherType !== 'PK') {
                    validationInput.value =
                        `Item ${description}${size ? ' (' + size + ')' : ''} tidak ditemukan di tabel ${tableName}.`;
                    saveVoucherBtn.disabled = true;
                    return {
                        isValid: false,
                        message: validationInput.value
                    };
                }
                if (stock && stock.quantity < quantity && voucherType !== 'PK') {
                    validationInput.value =
                        `Kuantitas untuk item ${description}${size ? ' (' + size + ')' : ''} melebihi stok tersedia di tabel ${tableName}. Tersedia: ${stock.quantity}, Dibutuhkan: ${quantity}`;
                    saveVoucherBtn.disabled = true;
                    return {
                        isValid: false,
                        message: validationInput.value
                    };
                }
            }

            if (voucherType === 'PK' && !isNewItemRow && stock && stock.quantity >= quantity) {
                validationInput.value =
                    `Stok ${description}${size ? ' (' + size + ')' : ''} cukup untuk dipindahkan ke ${targetTableName}. Tersedia: ${stock.quantity}, Dibutuhkan: ${quantity}`;
                saveVoucherBtn.disabled = false;
                return {
                    isValid: true,
                    message: validationInput.value
                };
            }

            return {
                isValid: true,
                message: ''
            };
        }

        function attachTransactionInputListeners() {
            const transactionInputs = transactionTableBody.querySelectorAll(
                '.quantityInput, .nominalInput:not([disabled]), .descriptionInput:not([disabled]), .sizeInput:not([disabled])'
            );
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
            const transactions = Array.from(rows).map(row => {
                const descriptionInput = row.querySelector('.descriptionInput[type="text"]');
                const descriptionSelect = row.querySelector('.descriptionInput:not([type="text"])');
                const sizeInput = row.querySelector('.sizeInput');
                const quantity = row.querySelector('.quantityInput')?.value || '1';
                const nominal = row.querySelector('.nominalInput')?.value || '0';
                return {
                    description: descriptionInput?.value || descriptionSelect?.value || '',
                    size: sizeInput?.value || '',
                    quantity,
                    nominal,
                    isHppRow: row.dataset.isHppRow === 'true',
                    isNewItem: row.dataset.isNewItem === 'true'
                };
            });

            const nonHppTransactions = transactions.filter(t => !t.isHppRow);

            transactionTableBody.innerHTML = '';
            nonHppTransactions.forEach((t, index) => {
                const newRow = t.isNewItem ? generateNewItemRow(index) : generateTransactionTableRow(
                    index);
                transactionTableBody.appendChild(newRow);
                const rowDescriptionInput = newRow.querySelector('.descriptionInput[type="text"]');
                const rowDescriptionSelect = newRow.querySelector(
                    '.descriptionInput:not([type="text"])');
                const rowSizeInput = newRow.querySelector('.sizeInput');
                const rowQuantityInput = newRow.querySelector('.quantityInput');
                const rowNominalInput = newRow.querySelector('.nominalInput');
                if (rowDescriptionInput) rowDescriptionInput.value = t.description;
                if (rowDescriptionSelect) {
                    rowDescriptionSelect.value = t.description;
                    updateSizeDropdown(index, t.description);
                }
                if (rowSizeInput) rowSizeInput.value = t.size;
                rowQuantityInput.value = t.quantity;
                rowNominalInput.value = t.nominal;

                if (voucherTypeSelect.value === 'PJ' && t.description && !t.isNewItem) {
                    addHppRowForPJ(index, t.description, t.size, parseFloat(t.quantity));
                }
            });

            if (transactionTableBody.querySelectorAll('tr').length === 0) {
                const newRow = generateTransactionTableRow(0);
                transactionTableBody.appendChild(newRow);
            }

            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            toggleSizeInputState();
            updateAllCalculationsAndValidations();
        }

        addTransactionRowBtn.addEventListener('click', function() {
            const newIndex = transactionTableBody.querySelectorAll('tr').length;
            const newRow = generateTransactionTableRow(newIndex);
            transactionTableBody.appendChild(newRow);
            attachTransactionRemoveButtonListeners();
            attachTransactionInputListeners();
            toggleSizeInputState();
            updateAllCalculationsAndValidations();
        });

        voucherTypeSelect.addEventListener('change', function() {
            refreshTransactionTable();
            deskripsiVoucherTextarea.value = voucherTypes[this.value]?.description || '';
            updateAllCalculationsAndValidations();
            toggleSizeInputState();
            updateProductDropdownForPK();
            updateRecipeField();
        });

        function calculateTotalNominal() {
            let totalNominalRaw = 0;
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                const total = calculateRowTotal(row);
                totalNominalRaw += parseFloat(total.toString().replace(/[^0-9.-]+/g, '')) || 0;
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
                totalDebit += parseFloat(input.value.replace(/[^0-9.-]/g, '')) || 0;
            });

            voucherDetailsTableBody.querySelectorAll('.creditInput').forEach(input => {
                totalCredit += parseFloat(input.value.replace(/[^0-9.-]/g, '')) || 0;
            });

            totalDebitInput.value = totalDebit.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            totalCreditInput.value = totalCredit.toLocaleString('id-ID', {
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
                validationInput.value =
                    "Total Nominal pada Rincian Transaksi harus sama dengan Total Debit dan Total Kredit pada Rincian Voucher.";
                saveVoucherBtn.disabled = true;
                return {
                    isValid: false,
                    message: validationInput.value
                };
            } else if (totalDebitRaw !== totalCreditRaw) {
                validationInput.value = "Total Debit harus sama dengan Total Kredit.";
                saveVoucherBtn.disabled = true;
                return {
                    isValid: false,
                    message: validationInput.value
                };
            } else {
                validationInput.value = "Totalnya seimbang dan valid.";
                saveVoucherBtn.disabled = false;
                return {
                    isValid: true,
                    message: validationInput.value
                };
            }
        }

        function updateAllCalculationsAndValidations() {
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                calculateRowTotal(row);
                syncHppQuantity(row);
            });

            let allRowsValid = true;
            let stockValidationMessage = '';
            transactionTableBody.querySelectorAll('tr').forEach(row => {
                const validationResult = validateStockQuantity(row);
                if (!validationResult.isValid) {
                    allRowsValid = false;
                    stockValidationMessage = validationResult.message;
                }
            });

            const totalsValidation = validateTotals();
            if (!totalsValidation.isValid) {
                allRowsValid = false;
                stockValidationMessage = totalsValidation.message;
            }

            validationInput.value = stockValidationMessage || totalsValidation.message;
            saveVoucherBtn.disabled = !allRowsValid;
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

        function setTodayDueDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dueDate').value = today;
        }

        function setTodayVoucherDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            document.getElementById('voucherDate').value = `${year}-${month}-${day}`;
            updateVoucherDay();
        }
        // Initialize
        updateRecipeField();
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
        updateVoucherTypeOptions();
        refreshTransactionTable();
    });
</script>
