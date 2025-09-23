@extends('layouts.app')

@section('content')
@section('title', 'Neraca Keuangan')

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
    .balance-sheet-table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
        border-collapse: collapse;
    }

    .balance-sheet-table thead {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .balance-sheet-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .balance-sheet-table tbody tr:hover {
        background-color: #e9ecef;
        transition: background-color 0.2s;
    }

    .balance-sheet-table th,
    .balance-sheet-table td {
        border: 1px solid #dee2e6;
        padding: 0.75rem;
        vertical-align: middle;
    }

    .balance-sheet-table th {
        text-align: center;
    }

    .text-right {
        text-align: right;
        white-space: nowrap;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .account-group {
        font-weight: bold;
        background: linear-gradient(90deg, #e9ecef, #dee2e6);
    }

    .total-row td {
        border-top: 2px solid #6c757d;
        font-weight: bold;
    }

    .half-width {
        width: 50%;
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

<div class="container-fluid mt-2">
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Neraca Keuangan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('balanceSheet_page') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-bold">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                        value="{{ $start_date }}" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Pilih tanggal mulai untuk filter neraca">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-bold">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                        value="{{ $end_date }}" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Pilih tanggal akhir untuk filter neraca">
                </div>
                <div class="col-md-auto d-flex align-items-end gap-2">
                    <button type="submit" class="btn filter-button" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Terapkan filter">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('export_BalanceSheet', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                        class="btn export-button" data-bs-toggle="tooltip" data-bs-placement="bottom"
                        title="Unduh neraca keuangan sebagai Excel">
                        <i class="fas fa-file-excel me-2"></i>Export as Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Balance Sheet Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Neraca Keuangan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="balance-sheet-table table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th class="half-width" colspan="2">ASET</th>
                            <th class="half-width" colspan="2">KEWAJIBAN DAN EKUITAS</th>
                        </tr>
                        <tr>
                            <th>Akun</th>
                            <th class="text-right">Jumlah (Rp)</th>
                            <th>Akun</th>
                            <th class="text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aset Lancar and Kewajiban -->
                        <tr>
                            <td colspan="2" class="account-group">ASET LANCAR</td>
                            <td colspan="2" class="account-group">KEWAJIBAN</td>
                        </tr>
                        @php
                            $totalAsetLancar = 0;
                            $totalKewajiban = 0;
                            $asetLancarSubsections = $allAset['Aset Lancar'] ?? collect([]);
                            $kewajibanSubsections = $allKewajiban;
                            $maxRows = max($asetLancarSubsections->count(), $kewajibanSubsections->count());
                        @endphp
                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr>
                                <!-- Aset Lancar -->
                                <td>
                                    @if ($i < $asetLancarSubsections->count())
                                        {{ $asetLancarSubsections[$i]->account_name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($i < $asetLancarSubsections->count())
                                        @php
                                            $subsection = $asetLancarSubsections[$i]->account_name;
                                            $saldo = $asetLancarData->get($subsection)->saldo ?? 0;
                                            $totalAsetLancar += $saldo;
                                        @endphp
                                        <span class="{{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($saldo), 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <!-- Kewajiban -->
                                <td>
                                    @if ($i < $kewajibanSubsections->count())
                                        {{ $kewajibanSubsections[$i]->account_name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($i < $kewajibanSubsections->count())
                                        @php
                                            $subsection = $kewajibanSubsections[$i]->account_name;
                                            $saldo = $kewajibanData->get($subsection)->saldo ?? 0;
                                            $totalKewajiban += $saldo;
                                        @endphp
                                        <span class="{{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($saldo), 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                        <!-- Total Aset Lancar and Total Kewajiban -->
                        <tr class="total-row">
                            <td class="font-weight-bold">Total Aset Lancar</td>
                            <td class="text-right font-weight-bold">
                                <span class="{{ $totalAsetLancar < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalAsetLancar), 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="font-weight-bold">Total Kewajiban</td>
                            <td class="text-right font-weight-bold">
                                <span class="{{ $totalKewajiban < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalKewajiban), 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        <!-- Aset Tetap and Ekuitas -->
                        <tr>
                            <td colspan="2" class="account-group">ASET TETAP</td>
                            <td colspan="2" class="account-group">EKUITAS</td>
                        </tr>
                        @php
                            $totalAsetTetap = 0;
                            $totalEkuitas = 0;
                            $asetTetapSubsections = $allAset['Aset Tetap'] ?? collect([]);
                            $ekuitasSubsections = $allEkuitas;
                            $maxRows = max($asetTetapSubsections->count(), $ekuitasSubsections->count());
                        @endphp
                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr>
                                <!-- Aset Tetap -->
                                <td>
                                    @if ($i < $asetTetapSubsections->count())
                                        {{ $asetTetapSubsections[$i]->account_name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($i < $asetTetapSubsections->count())
                                        @php
                                            $subsection = $asetTetapSubsections[$i]->account_name;
                                            $saldo = $asetTetapData->get($subsection)->saldo ?? 0;
                                            $totalAsetTetap += $saldo;
                                        @endphp
                                        <span class="{{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($saldo), 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <!-- Ekuitas -->
                                <td>
                                    @if ($i < $ekuitasSubsections->count())
                                        {{ $ekuitasSubsections[$i]->account_name }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($i < $ekuitasSubsections->count())
                                        @php
                                            $subsection = $ekuitasSubsections[$i]->account_name ?? null;
                                            $saldo = 0;
                                            if (
                                                $subsection &&
                                                is_object($ekuitasData) &&
                                                method_exists($ekuitasData, 'get')
                                            ) {
                                                $saldoObject = $ekuitasData->get($subsection);
                                                $saldo = $saldoObject->saldo ?? 0;
                                            } elseif (
                                                $subsection &&
                                                is_array($ekuitasData) &&
                                                isset($ekuitasData[$subsection]['saldo'])
                                            ) {
                                                $saldo = $ekuitasData[$subsection]['saldo'] ?? 0;
                                            }
                                            $totalEkuitas += $saldo;
                                        @endphp
                                        <span class="{{ $saldo < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format(abs($saldo), 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                        <!-- Total Aset Tetap and Total Ekuitas -->
                        <tr class="total-row">
                            <td class="font-weight-bold">Total Aset Tetap</td>
                            <td class="text-right font-weight-bold">
                                <span class="{{ $totalAsetTetap < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalAsetTetap), 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="font-weight-bold">Total Ekuitas</td>
                            <td class="text-right font-weight-bold">
                                <span class="{{ $totalEkuitas < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalEkuitas), 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        <!-- Total Aset and Total Kewajiban + Ekuitas -->
                        <tr class="total-row">
                            <td class="font-weight-bold">TOTAL ASET</td>
                            <td class="text-right font-weight-bold">
                                <span
                                    class="{{ $totalAsetLancar + $totalAsetTetap < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalAsetLancar + $totalAsetTetap), 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="font-weight-bold">TOTAL KEWAJIBAN + EKUITAS</td>
                            <td class="text-right font-weight-bold">
                                <span
                                    class="{{ $totalKewajiban + $totalEkuitas < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($totalKewajiban + $totalEkuitas), 2, ',', '.') }}
                                </span>
                            </td>
                        </tr>
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
