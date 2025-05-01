@extends('layouts/app')

@section('content')
@section('title', 'Buku Besar')

<form action="{{ route('generalledger_page') }}" method="GET" class="mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="month" class="form-label">Bulan:</label>
            <select name="month" id="month" class="form-select">
                @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="year" class="form-label">Tahun:</label>
            <select name="year" id="year" class="form-select">
                @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++) <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="accountNameDropdown" class="form-label">Nama Akun:</label>
            <div class="position-relative">
                <button type="button" class="btn btn-outline-secondary w-100 text-start" id="accountNameDropdown">
                    <span id="selectedAccountNames">{{ empty($selectedAccountName) ? '-- Semua Akun --' : implode(', ', $selectedAccountName) }}</span>
                    <i class="bi bi-caret-down-fill float-end"></i>
                </button>
                <div id="accountNameChecklist" class="card shadow-sm position-absolute mt-1 w-100 z-1 bg-white" style="display: none;">
                    <div class="card-body">
                        <input type="text" id="accountNameSearch" class="form-control mb-2" placeholder="Cari Akun...">
                        <div id="accountList" style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 5px;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="all_accounts" name="account_name[]" value="" {{ empty($selectedAccountName) ? 'checked' : '' }}>
                                <label class="form-check-label" for="all_accounts">-- Semua Akun --</label>
                            </div>
                            @foreach ($availableAccountNames as $name)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input account-checkbox" id="account_{{ Str::slug($name) }}" name="account_name[]" value="{{ $name }}" {{ in_array($name, $selectedAccountName) ? 'checked' : '' }}>
                                <label class="form-check-label" for="account_{{ Str::slug($name) }}">{{ $name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="hiddenSelectedAccounts" name="account_name_hidden" value="{{ implode(',', $selectedAccountName) }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('generalledger_print', ['month' => $month, 'year' => $year, 'account_name_hidden' => implode(',', $selectedAccountName)]) }}" class="btn btn-secondary">Export as Excel</a>
        </div>
    </div>
</form>

<div class="table-responsive">
    @php
    $groupedDetails = $voucherDetails->groupBy('account_code');
    @endphp

    @if($groupedDetails->isEmpty())
    <div class="alert alert-info">Data buku besar belum ditemukan, silakan buat voucher terlebih dahulu.</div>
    @else
    @foreach($groupedDetails as $accountCode => $details)
    @php
    $accountName = $details->first()->account_name ?? 'Tidak Ada Nama Akun';
    @endphp

    @if (empty($selectedAccountName) || in_array($accountName, $selectedAccountName))
    <h3 class="mt-3">Akun {{ $accountName }} ({{ $accountCode }})</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Tanggal</th>
                <th>Transaksi</th>
                <th>Referensi</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>
                <th>Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
            $saldo = 0;
            @endphp
            @if($details->isEmpty())
            <tr>
                <td colspan="6" class="text-center">Data Tidak Ditemukan.</td>
            </tr>
            @else
            @foreach($details as $detail)
            @php
            $saldo += $detail->debit - $detail->credit;
            @endphp
            <tr class="text-center">
                <td>{{ \Carbon\Carbon::parse($detail->voucher->voucher_date)->isoFormat('dddd, DD MMMM') }}</td>
                <td>{{ $detail->voucher->transaction }}</td>
                <td>
                    <a href="{{ route('voucher_detail', $detail->voucher->id) }}" class="text-decoration-none">
                        {{ $detail->voucher->voucher_number }}
                    </a>
                </td>
                <td>{{ number_format($detail->debit, 2, ',', '.') }}</td>
                <td>{{ number_format($detail->credit, 2, ',', '.') }}</td>
                <td>{{ number_format($saldo, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
    @endif
    @endforeach
    @endif
</div>

@push('scripts')
<script>
    const accountNameDropdown = document.getElementById('accountNameDropdown');
    const accountNameChecklist = document.getElementById('accountNameChecklist');
    const accountCheckboxes = document.querySelectorAll('.account-checkbox');
    const allAccountsCheckbox = document.getElementById('all_accounts');
    const selectedAccountNamesSpan = document.getElementById('selectedAccountNames');
    const hiddenSelectedAccountsInput = document.getElementById('hiddenSelectedAccounts');
    const accountNameSearchInput = document.getElementById('accountNameSearch');
    const accountListDiv = document.getElementById('accountList');
    const initialAccountListHTML = accountListDiv.innerHTML;

    function updateSelectedAccountsText() {
        const checkedAccounts = Array.from(accountCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (allAccountsCheckbox.checked || checkedAccounts.length === 0) {
            selectedAccountNamesSpan.textContent = '-- Semua Akun --';
            hiddenSelectedAccountsInput.value = '';
        } else {
            selectedAccountNamesSpan.textContent = checkedAccounts.join(', ');
            hiddenSelectedAccountsInput.value = checkedAccounts.join(',');
        }
    }

    accountNameDropdown.addEventListener('click', () => {
        accountNameChecklist.style.display = accountNameChecklist.style.display === 'none' ? 'block' : 'none';
        accountNameSearchInput.value = '';
        accountListDiv.innerHTML = initialAccountListHTML;
    });

    allAccountsCheckbox.addEventListener('change', () => {
        if (allAccountsCheckbox.checked) {
            accountCheckboxes.forEach(checkbox => checkbox.checked = false);
        }
        updateSelectedAccountsText();
    });

    accountCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                allAccountsCheckbox.checked = false;
            }
            updateSelectedAccountsText();
        });
    });

    accountNameSearchInput.addEventListener('input', () => {
        const searchTerm = accountNameSearchInput.value.toLowerCase();
        const accountItems = accountListDiv.querySelectorAll('.form-check');

        accountItems.forEach(item => {
            const label = item.querySelector('.form-check-label').textContent.toLowerCase();
            item.style.display = label.includes(searchTerm) ? 'block' : 'none';
        });
    });

    document.addEventListener('click', (event) => {
        if (!accountNameDropdown.contains(event.target) && !accountNameChecklist.contains(event.target)) {
            accountNameChecklist.style.display = 'none';
        }
    });

    // Initial update
    updateSelectedAccountsText();
</script>
@endpush
@endsection