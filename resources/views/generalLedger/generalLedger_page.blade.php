@extends('layouts/app')

@section('content')
@section('title', 'Buku Besar')

<style>
    .highlight {
        background-color: yellow;
        font-weight: bold;
    }
</style>

<!-- Sertakan Moment.js dan lokal Indonesia -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

<form action="{{ route('generalledger_page') }}" method="GET" class="mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="month" class="form-label">Bulan:</label>
            <select name="month" id="month" class="form-select">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="year" class="form-label">Tahun:</label>
            <select name="year" id="year" class="form-select">
                @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="accountNameDropdown" class="form-label">Nama Akun:</label>
            <div class="position-relative">
                <button type="button" class="btn btn-outline-secondary w-100 text-start" id="accountNameDropdown">
                    <span
                        id="selectedAccountNames">{{ empty($selectedAccountName) ? '-- Semua Akun --' : implode(', ', $selectedAccountName) }}</span>
                    <i class="bi bi-caret-down-fill float-end"></i>
                </button>
                <div id="accountNameChecklist" class="card shadow-sm position-absolute mt-1 w-100 z-1 bg-white"
                    style="display: none;">
                    <div class="card-body">
                        <input type="text" id="accountNameSearch" class="form-control mb-2"
                            placeholder="Cari Akun...">
                        <div id="accountList"
                            style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 5px;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="all_accounts" name="account_name[]"
                                    value="" {{ empty($selectedAccountName) ? 'checked' : '' }}>
                                <label class="form-check-label" for="all_accounts">-- Semua Akun --</label>
                            </div>
                            @foreach ($availableAccountNames as $name)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input account-checkbox"
                                        id="account_{{ Str::slug($name) }}" name="account_name[]"
                                        value="{{ $name }}"
                                        {{ in_array($name, $selectedAccountName) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="account_{{ Str::slug($name) }}">{{ $name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="hiddenSelectedAccounts" name="account_name_hidden"
                value="{{ implode(',', $selectedAccountName) }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('generalledger_print', ['month' => $month, 'year' => $year, 'account_name_hidden' => implode(',', $selectedAccountName)]) }}"
                class="btn btn-success">Export as Excel</a>
        </div>
    </div>
</form>

<!-- Global Search Filter -->
<div class="mb-4">
    <div class="input-group">
        <input type="text" id="globalSearch" class="form-control"
            placeholder="Cari berdasarkan tanggal (DD-MM-YYYY, DD MMMM YYYY, atau nama hari)...">
        <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
    </div>
    <small id="searchCount" class="form-text text-muted mt-2"></small>
</div>

<div class="table-responsive">
    @php
        $groupedDetails = $voucherDetails->groupBy('account_code');
    @endphp

    @if ($groupedDetails->isEmpty())
        <div class="alert alert-info">Data buku besar belum ditemukan, silakan buat voucher terlebih dahulu.</div>
    @else
        @foreach ($groupedDetails as $accountCode => $details)
            @php
                $accountName = $details->first()->account_name ?? 'Tidak Ada Nama Akun';
            @endphp

            @if (empty($selectedAccountName) || in_array($accountName, $selectedAccountName))
                <div class="account-section" data-account-name="{{ $accountName }}"
                    data-account-code="{{ $accountCode }}">
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
                            @if ($details->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">Data Tidak Ditemukan.</td>
                                </tr>
                            @else
                                @foreach ($details as $detail)
                                    @php
                                        $saldo += $detail->debit - $detail->credit;
                                        $dateStr = \Carbon\Carbon::parse($detail->voucher->voucher_date)->format(
                                            'd-m-Y',
                                        );
                                        $dayNameId = \Carbon\Carbon::parse($detail->voucher->voucher_date)
                                            ->locale('id')
                                            ->isoFormat('dddd');
                                        $dayNameEn = \Carbon\Carbon::parse($detail->voucher->voucher_date)
                                            ->locale('en')
                                            ->format('l');
                                    @endphp
                                    <tr data-date="{{ $dateStr }}" data-day-id="{{ $dayNameId }}"
                                        data-day-en="{{ $dayNameEn }}">
                                        <td class="date-cell">
                                            {{ \Carbon\Carbon::parse($detail->voucher->voucher_date)->locale('id')->isoFormat('dddd, DD MMMM') }}
                                        </td>
                                        <td>{{ $detail->voucher->transaction }}</td>
                                        <td>
                                            <a href="{{ route('voucher_detail', $detail->voucher->id) }}"
                                                class="text-decoration-none">
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
                </div>
            @endif
        @endforeach
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set moment.js locale to Indonesian
            moment.locale('id');

            const accountNameDropdown = document.getElementById('accountNameDropdown');
            const accountNameChecklist = document.getElementById('accountNameChecklist');
            const accountCheckboxes = document.querySelectorAll('.account-checkbox');
            const allAccountsCheckbox = document.getElementById('all_accounts');
            const selectedAccountNamesSpan = document.getElementById('selectedAccountNames');
            const hiddenSelectedAccountsInput = document.getElementById('hiddenSelectedAccounts');
            const accountNameSearchInput = document.getElementById('accountNameSearch');
            const accountListDiv = document.getElementById('accountList');
            const globalSearch = document.getElementById('globalSearch');
            const clearSearch = document.getElementById('clearSearch');
            const searchCount = document.getElementById('searchCount');
            const initialAccountListHTML = accountListDiv.innerHTML;

            // Update selected accounts text
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
                applyGlobalFilter();
            }

            // Account dropdown toggle
            accountNameDropdown.addEventListener('click', () => {
                accountNameChecklist.style.display = accountNameChecklist.style.display === 'none' ?
                    'block' : 'none';
                accountNameSearchInput.value = '';
                accountListDiv.innerHTML = initialAccountListHTML;
                applyGlobalFilter();
            });

            // Handle "All Accounts" checkbox
            allAccountsCheckbox.addEventListener('change', () => {
                if (allAccountsCheckbox.checked) {
                    accountCheckboxes.forEach(checkbox => checkbox.checked = false);
                }
                updateSelectedAccountsText();
            });

            // Handle individual account checkboxes
            accountCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        allAccountsCheckbox.checked = false;
                    }
                    updateSelectedAccountsText();
                });
            });

            // Account name search
            accountNameSearchInput.addEventListener('input', () => {
                const searchTerm = accountNameSearchInput.value.toLowerCase();
                const accountItems = accountListDiv.querySelectorAll('.form-check');

                accountItems.forEach(item => {
                    const label = item.querySelector('.form-check-label').textContent.toLowerCase();
                    item.style.display = label.includes(searchTerm) ? 'block' : 'none';
                });
            });

            // Close account dropdown when clicking outside
            document.addEventListener('click', (event) => {
                if (!accountNameDropdown.contains(event.target) && !accountNameChecklist.contains(event
                        .target)) {
                    accountNameChecklist.style.display = 'none';
                }
            });

            // Global Filter Function
            function applyGlobalFilter() {
                const searchValue = globalSearch.value.trim().toLowerCase();
                let totalVisibleRows = 0;

                // Clear existing highlights
                document.querySelectorAll('.highlight').forEach(el => {
                    el.replaceWith(el.textContent);
                });

                const accountSections = document.querySelectorAll('.account-section');
                accountSections.forEach(section => {
                    const rows = section.querySelectorAll('tbody tr');
                    let sectionHasVisibleRows = false;

                    rows.forEach(row => {
                        if (row.querySelector('td[colspan]')) return; // Skip no-data rows

                        const date = row.dataset.date.toLowerCase();
                        const dayId = row.dataset.dayId.toLowerCase();
                        const dayEn = row.dataset.dayEn.toLowerCase();

                        // Try parsing input as a date
                        let parsedDate = moment(searchValue, ['DD-MM-YYYY', 'DD MMMM YYYY'], true);
                        let matches = false;

                        if (parsedDate.isValid()) {
                            // Match exact date
                            matches = date === parsedDate.format('DD-MM-YYYY');
                        } else {
                            // Match day name (Indonesian or English) or partial date
                            matches = dayId.includes(searchValue) || dayEn.includes(searchValue) ||
                                date.includes(searchValue);
                        }

                        if (matches || !searchValue) {
                            row.style.display = '';
                            sectionHasVisibleRows = true;
                            totalVisibleRows++;
                            if (searchValue) {
                                const dateCell = row.querySelector('.date-cell');
                                if (dateCell) {
                                    // Highlight the matching part
                                    let displayText = dateCell.textContent;
                                    if (dayId.includes(searchValue)) {
                                        displayText = displayText.replace(new RegExp(dayId, 'gi'),
                                            match => `<span class="highlight">${match}</span>`);
                                    } else if (dayEn.includes(searchValue)) {
                                        displayText = displayText.replace(new RegExp(dayEn, 'gi'),
                                            match => `<span class="highlight">${match}</span>`);
                                    } else {
                                        displayText = displayText.replace(new RegExp(searchValue,
                                                'gi'), match =>
                                            `<span class="highlight">${match}</span>`);
                                    }
                                    dateCell.innerHTML = displayText;
                                }
                            }
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    section.style.display = sectionHasVisibleRows ? '' : 'none';
                });

                searchCount.textContent = searchValue ? `${totalVisibleRows} transaksi ditemukan` : '';
            }

            // Event listeners for global search
            globalSearch.addEventListener('keyup', applyGlobalFilter);
            clearSearch.addEventListener('click', () => {
                globalSearch.value = '';
                applyGlobalFilter();
            });

            // Initial update
            updateSelectedAccountsText();
        });
    </script>
@endpush
@endsection
