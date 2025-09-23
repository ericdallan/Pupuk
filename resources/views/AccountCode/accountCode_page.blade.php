@extends('layouts/app')

@section('content')
@section('title', 'Kode Perkiraan')

<style>
    /* Enhanced Button Styles */
    .create-button {
        background: linear-gradient(45deg, #28a745, #1e7e34);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .create-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        background: linear-gradient(45deg, #218838, #1a6030);
    }

    .pdf-button {
        background: linear-gradient(45deg, #6c757d, #5a6268);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .pdf-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        background: linear-gradient(45deg, #5a6268, #474e54);
    }

    .excel-button {
        background: linear-gradient(45deg, #17a2b8, #117a8b);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .excel-button:hover {
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

    /* Account Type, Section, and Subsection Styles */
    .account-type {
        font-weight: bold;
        color: #343a40;
        text-transform: uppercase;
        font-size: 0.9em;
        padding: 4px 8px;
        border-radius: 12px;
        background: #e9ecef;
    }

    .account-section {
        font-weight: bold;
        color: #007bff;
        font-size: 0.9em;
        padding: 4px 8px;
        border-radius: 12px;
        background: #e9ecef;
    }

    .account-subsection {
        color: #dc3545;
        font-size: 0.9em;
        padding: 4px 8px;
        border-radius: 12px;
        background: #e9ecef;
    }

    /* Table Column Widths */
    .table th,
    .table td {
        vertical-align: middle;
        text-align: center;
    }

    .table th:nth-child(1),
    .table td:nth-child(1) {
        width: 15%;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        width: 15%;
    }

    .table th:nth-child(3),
    .table td:nth-child(3) {
        width: 15%;
    }

    .table th:nth-child(4),
    .table td:nth-child(4) {
        width: 25%;
    }

    .table th:nth-child(5),
    .table td:nth-child(5) {
        width: 15%;
    }

    .table th:nth-child(6),
    .table td:nth-child(6) {
        width: 15%;
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

<div class="container">

    <div class="card mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button type="button" class="btn create-button btn-md" data-bs-toggle="modal"
                    data-bs-target="#accountModal" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Tambah akun perkiraan baru">
                    <i class="fas fa-plus me-2"></i>Buat Akun Baru
                </button>
                @extends('AccountCode.accountCode_form')
            </div>
            <div>
                <button id="print-pdf" class="btn pdf-button btn-md me-2" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Unduh daftar akun sebagai PDF">
                    <i class="fas fa-file-pdf me-2"></i>Print to PDF
                </button>
                <button id="export-excel" class="btn excel-button btn-md" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Ekspor daftar akun ke Excel">
                    <i class="fas fa-file-excel me-2"></i>Export to Excel
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark text-center align-middle">
                    <tr>
                        <th>Account Type</th>
                        <th>Account Section</th>
                        <th>Account Subsection</th>
                        <th>Account Name</th>
                        <th>Account Code</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sortedHierarkiAkun = collect($hierarkiAkun)
                            ->map(function ($accountSections) {
                                return collect($accountSections)
                                    ->map(function ($accountSubsections) {
                                        if (is_array($accountSubsections)) {
                                            return collect($accountSubsections)
                                                ->map(function ($accountNames) {
                                                    if (is_array($accountNames)) {
                                                        return collect($accountNames)
                                                            ->sortBy(function ($account) {
                                                                if (is_array($account) && isset($account[1])) {
                                                                    $parts = explode('.', $account[1]);
                                                                    return end($parts) * 1;
                                                                }
                                                                return null;
                                                            })
                                                            ->toArray();
                                                    }
                                                    return $accountNames;
                                                })
                                                ->toArray();
                                        }
                                        return $accountSubsections;
                                    })
                                    ->toArray();
                            })
                            ->toArray();
                    @endphp

                    @foreach ($sortedHierarkiAkun as $accountType => $accountSections)
                        @php
                            $formattedAccountType = str_replace('_', ' ', $accountType);
                            $firstSection = true;
                        @endphp
                        @foreach ($accountSections as $accountSection => $accountSubsections)
                            @php
                                $firstSubsection = true;
                            @endphp
                            @if (is_array($accountSubsections))
                                @foreach ($accountSubsections as $accountSubsection => $accountNames)
                                    @foreach ($accountNames as $accountName)
                                        <tr>
                                            @if ($firstSection)
                                                <td class="account-type text-center" style="vertical-align: middle;">
                                                    {{ $formattedAccountType }}</td>
                                                @php
                                                    $firstSection = false;
                                                @endphp
                                            @else
                                                <td></td>
                                            @endif
                                            @if ($firstSubsection)
                                                <td class="account-section">{{ $accountSection }}</td>
                                                @php
                                                    $firstSubsection = false;
                                                @endphp
                                            @else
                                                <td></td>
                                            @endif
                                            @if (is_array($accountNames))
                                                @if (is_array($accountName))
                                                    <td class="account-subsection">
                                                        {{ $loop->first ? $accountSubsection : '' }}</td>
                                                    <td class="pl-4">{{ $accountName[0] }}</td>
                                                    <td>{{ $accountName[1] }}</td>
                                                @else
                                                    <td class="account-subsection">
                                                        {{ $loop->first ? $accountSubsection : '' }}</td>
                                                    <td class="pl-4">{{ $accountName }}</td>
                                                    <td></td>
                                                @endif
                                            @else
                                                <td class="pl-4">{{ $accountName }}</td>
                                                <td></td>
                                            @endif
                                            <td>
                                                @php
                                                    $accountCode = is_array($accountName)
                                                        ? (isset($accountName[1])
                                                            ? $accountName[1]
                                                            : null)
                                                        : (isset($accountName->account_code)
                                                            ? $accountName->account_code
                                                            : null);
                                                @endphp
                                                @if ($accountCode)
                                                    <a href="{{ route('accountCode_edit', $accountCode) }}"
                                                        class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit akun perkiraan">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                @foreach ($accountSubsections as $accountName)
                                    <tr>
                                        @if ($firstSection)
                                            <td class="account-type text-center" style="vertical-align: middle;">
                                                {{ $formattedAccountType }}</td>
                                            @php
                                                $firstSection = false;
                                            @endphp
                                        @else
                                            <td></td>
                                        @endif
                                        @if ($firstSubsection)
                                            <td class="account-section">{{ $accountSection }}</td>
                                            @php
                                                $firstSubsection = false;
                                            @endphp
                                        @else
                                            <td></td>
                                        @endif
                                        @if (is_array($accountName))
                                            <td></td>
                                            <td class="pl-4">{{ $accountName[0] }}</td>
                                            <td>{{ $accountName[1] }}</td>
                                        @else
                                            <td></td>
                                            <td class="pl-4">{{ $accountName }}</td>
                                            <td></td>
                                        @endif
                                        <td>
                                            @php
                                                $accountCode = is_array($accountName)
                                                    ? (isset($accountName[1])
                                                        ? $accountName[1]
                                                        : null)
                                                    : (isset($accountName->account_code)
                                                        ? $accountName->account_code
                                                        : null);
                                            @endphp
                                            @if ($accountCode)
                                                <a href="{{ route('accountCode_edit', $accountCode) }}"
                                                    class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Edit akun perkiraan">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Button event listeners
        document.getElementById('print-pdf').addEventListener('click', function() {
            window.location.href = "{{ route('account-codes.pdf') }}";
        });

        document.getElementById('export-excel').addEventListener('click', function() {
            window.location.href = "{{ route('account-codes.excel') }}";
        });

        // Auto-dismiss alerts
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => successMessage.classList.add('fade'), 3000);
        }
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            setTimeout(() => errorMessage.classList.add('fade'), 5000);
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
