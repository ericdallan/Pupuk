@extends('layouts/app')

@section('content')
@section('title', 'Buku Besar')

<style>
    /* Enhanced Button Styles */
    .filter-button {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .filter-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        background: linear-gradient(45deg, #0056b3, #003d80);
    }

    .export-button {
        background: linear-gradient(45deg, #28a745, #1e7e34);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .export-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        background: linear-gradient(45deg, #218838, #1a6030);
    }

    .search-button {
        background: linear-gradient(45deg, #17a2b8, #117a8b);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .search-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        background: linear-gradient(45deg, #138496, #0d5d6b);
    }

    /* Table Enhancements */
    .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .table tbody tr:hover {
        background-color: #e9ecef;
        transition: background-color 0.2s;
    }

    /* Alert Animations */
    .alert {
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Badge Styles */
    .badge {
        font-size: 0.9em;
        padding: 4px 8px;
        border-radius: 12px;
    }

    /* Date Color Based on Recency */
    .recent-date {
        color: #28a745;
        font-weight: bold;
    }

    .old-date {
        color: #6c757d;
    }

    /* Dropdown Menu */
    .dropdown-menu {
        max-height: 300px;
        overflow-y: auto;
        border-radius: 8px;
    }

    .highlight {
        background-color: #fefcbf;
        font-weight: bold;
        padding: 2px 4px;
        border-radius: 4px;
    }

    .account-section {
        transition: all 0.3s ease;
    }

    .card-header {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }
</style>

@if (session('success'))
    <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('error'))
    <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if ($errors->any())
    <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <i class="bi bi-funnel me-2"></i>Filter Transaksi
        </div>
        <div class="card-body">
            <form action="{{ route('generalledger_page') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Bulan</label>
                        <select name="month" id="month" class="form-select">
                            <option value="">Semua</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label">Tahun</label>
                        <select name="year" id="year" class="form-select">
                            <option value="">Semua</option>
                            @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="accountNameDropdown" class="form-label">Nama Akun</label>
                        <div class="position-relative">
                            <button type="button" class="btn btn-outline-primary w-100 text-start filter-button"
                                id="accountNameDropdown" data-bs-toggle="tooltip" title="Pilih akun untuk difilter">
                                <span
                                    id="selectedAccountNames">{{ empty($selectedAccountName) ? '-- Semua Akun --' : implode(', ', $selectedAccountName) }}</span>
                                <i class="bi bi-caret-down-fill float-end"></i>
                            </button>
                            <div id="accountNameChecklist" class="dropdown-menu w-100 p-3" style="display: none;">
                                <input type="text" id="accountNameSearch" class="form-control mb-2"
                                    placeholder="Cari Akun...">
                                <div id="accountList" style="max-height: 200px; overflow-y: auto;">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="all_accounts"
                                            name="account_name[]" value=""
                                            {{ empty($selectedAccountName) ? 'checked' : '' }}>
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
                        <input type="hidden" id="hiddenSelectedAccounts" name="account_name_hidden"
                            value="{{ implode(',', $selectedAccountName) }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn filter-button" title="Filter data berdasarkan bulan dan tahun"
                            data-bs-toggle="tooltip" data-bs-title="Filter data berdasarkan bulan dan tahun">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                        <a href="{{ route('generalledger_print', ['month' => $month, 'year' => $year, 'account_name_hidden' => implode(',', $selectedAccountName)]) }}"
                            class="btn export-button" title="Ekspor data ke file Excel" data-bs-toggle="tooltip"
                            data-bs-title="Ekspor data ke file Excel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Global Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="globalSearch" class="form-control"
                    placeholder="Cari berdasarkan tanggal (DD-MM-YYYY, DD MMMM YYYY, atau nama hari)..."
                    data-bs-toggle="tooltip" title="Masukkan tanggal atau nama hari untuk mencari transaksi">
                <button class="btn search-button" type="button" id="clearSearch"><i class="bi bi-x-lg"></i>
                    Clear</button>
            </div>
            <small id="searchCount" class="form-text text-muted mt-2"></small>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="table-responsive">
        @php
            $groupedDetails = $voucherDetails->groupBy('account_code');
        @endphp

        @if ($groupedDetails->isEmpty())
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>Data buku besar belum ditemukan, silakan buat voucher terlebih
                dahulu.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @else
            @foreach ($groupedDetails as $accountCode => $details)
                @php
                    $accountName = $details->first()->account_name ?? 'Tidak Ada Nama Akun';
                @endphp

                @if (empty($selectedAccountName) || in_array($accountName, $selectedAccountName))
                    <div class="account-section mb-4" data-account-name="{{ $accountName }}"
                        data-account-code="{{ $accountCode }}">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="h5 mb-0"><i class="bi bi-journal-text me-2"></i>Akun {{ $accountName }}
                                    ({{ $accountCode }})
                                </h3>
                                <span class="badge bg-secondary">{{ $details->count() }} Transaksi</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-striped table-hover mb-0">
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

                                                    // For regular voucher details, use voucher_date
                                                    $carbonDate = \Carbon\Carbon::parse($detail->voucher->voucher_date);
                                                    $voucherNumber = $detail->voucher->voucher_number;
                                                    $detailRoute = route('voucher_detail', $detail->voucher->id);
                                                    $transaction = $detail->voucher->transaction;
                                                    $dateStr = $carbonDate->format('d-m-Y');
                                                    $dayNameId = $carbonDate->locale('id')->isoFormat('dddd');
                                                    $dayNameEn = $carbonDate->locale('en')->format('l');
                                                    $isRecent = $carbonDate->diffInDays(now()) <= 7;
                                                    $dateClass = $isRecent ? 'recent-date' : 'old-date';
                                                @endphp
                                                <tr data-date="{{ $dateStr }}"
                                                    data-day-id="{{ $dayNameId }}"
                                                    data-day-en="{{ $dayNameEn }}">
                                                    <td class="date-cell {{ $dateClass }}">
                                                        {{ $carbonDate->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}
                                                    </td>
                                                    <td>

                                                        {{ $transaction }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ $detailRoute }}"
                                                            class="text-decoration-none fw-bold"
                                                            data-bs-toggle="tooltip">
                                                            {{ $voucherNumber }}
                                                        </a>
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format($detail->debit, 2, ',', '.') }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format($detail->credit, 2, ',', '.') }}
                                                    </td>
                                                    <td
                                                        class="text-end {{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($saldo, 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
                tooltipTriggerEl));

            // Auto-dismiss alerts
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(() => successMessage.classList.add('fade'), 3000);
            }
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                setTimeout(() => errorMessage.classList.add('fade'), 5000);
            }

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
                    section.classList.toggle('fade-in', sectionHasVisibleRows);
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
