@extends('layouts/app')

@section('content')
@section('title', 'Neraca Saldo')

<form action="{{ route('trialBalance_page') }}" method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <label for="month" class="form-label">Bulan:</label>
            <select name="month" class="form-select">
                @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label for="year" class="form-label">Tahun:</label>
            <select name="year" class="form-select">
                @for ($i = date('Y') - 5; $i <= date('Y') + 5; $i++) <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
            </select>
        </div>
        <div class="col-md-3" style="display: flex; align-items: flex-end;">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="{{ route('export_neraca_saldo', ['month' => $month, 'year' => $year, 'columns' => 'code_name_total']) }}" class="btn btn-success">Export as Excel</a>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if($accountBalances->isEmpty())
            <tr>
                <td colspan="3" class="text-center">Tidak ada data ditemukan untuk periode yang dipilih.</td>
            </tr>
            @else
            @php
            $totalBalance = 0;
            @endphp
            @foreach($accountBalances as $accountCode => $balance)
            <tr class="text-center">
                <td>{{ $accountCode }}</td>
                <td>{{ $accountNames[$accountCode] ?? 'Tidak Ada Nama Akun' }}</td>
                <td>
                    {{ number_format($balance, 2) }}
                    @php $totalBalance += $balance; @endphp
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</div>

@endsection