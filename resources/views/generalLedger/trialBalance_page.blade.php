@extends('layouts/app')

@section('content')
@section('title', 'Neraca Saldo')

<div class="container">
    <!-- Card for Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Neraca Saldo</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('trialBalance_page') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label fw-bold">Bulan:</label>
                        <select name="month" id="month" class="form-select">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label fw-bold">Tahun:</label>
                        <select name="year" id="year" class="form-select">
                            @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                        <a href="{{ route('export_neraca_saldo', ['month' => $month, 'year' => $year, 'columns' => 'code_name_total']) }}"
                            class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Export as Excel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card for Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Neraca Saldo</h5>
            <span class="badge bg-info">Periode: {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr class="text-center">
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
                                <tr class="text-center">
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

@endsection
