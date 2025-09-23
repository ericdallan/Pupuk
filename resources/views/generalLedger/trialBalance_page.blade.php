@extends('layouts.app')

@section('content')
@section('title', 'Neraca Saldo')

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

    /* Card Styling */
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        border-radius: 8px 8px 0 0;
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
</style>

<div class="container mt-2">
    <!-- Card for Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Neraca Saldo</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('trialBalance_page') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label fw-bold">Bulan:</label>
                        <select name="month" id="month" class="form-select" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Pilih bulan untuk filter neraca saldo">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label fw-bold">Tahun:</label>
                        <select name="year" id="year" class="form-select" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Pilih tahun untuk filter neraca saldo">
                            @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn filter-button" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Terapkan filter">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <a href="{{ route('export_neraca_saldo', ['month' => $month, 'year' => $year, 'columns' => 'code_name_total']) }}"
                            class="btn export-button" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Unduh neraca saldo sebagai Excel">
                            <i class="fas fa-file-excel me-2"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card for Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Neraca Saldo</h5>
            <span class="badge bg-info">Periode: {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Kode Akun</th>
                            <th scope="col">Nama Akun</th>
                            <th scope="col">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($accountBalances->isEmpty())
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Tidak ada data ditemukan untuk periode yang dipilih.
                                </td>
                            </tr>
                        @else
                            @php
                                $totalBalance = 0;
                            @endphp
                            @foreach ($accountBalances as $accountCode => $balance)
                                <tr>
                                    <td>{{ $accountCode }}</td>
                                    <td>{{ $accountNames[$accountCode] ?? 'Tidak Ada Nama Akun' }}</td>
                                    <td class="{{ $balance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($balance, 2, ',', '.') }}
                                        @php $totalBalance += $balance; @endphp
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
