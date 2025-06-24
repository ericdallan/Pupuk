@extends('layouts/app')
@section('content')
@section('title', 'Neraca Keuangan')

<style>
    .balance-sheet-container {
        width: 100%;
        margin: 20px auto;
    }

    .balance-sheet-table {
        width: 100%;
        border-collapse: collapse;
    }

    .balance-sheet-table th,
    .balance-sheet-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .balance-sheet-table th {
        background-color: #f2f2f2;
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .account-group {
        font-weight: bold;
        background-color: #e9ecef;
    }

    .total-row td {
        border-top: 2px solid #ddd;
        font-weight: bold;
    }

    .half-width {
        width: 50%;
    }
</style>

<div class="mt-4">
    <div class="d-flex align-items-end">
        <form action="{{ route('balanceSheet_page') }}" method="GET" class="row g-3 align-items-end flex-grow-1">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $start_date }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $end_date }}">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
            <div class="col-md-auto">
                <a href="{{ route('export_BalanceSheet', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-success">
                    Export as Excel
                </a>
            </div>
        </form>
    </div>

    <div class="balance-sheet-container">
        <table class="balance-sheet-table table table-bordered table-striped table-hover">
            <thead>
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
                @for ($i = 0; $i < $maxRows; $i++) <tr>
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
                            {{ number_format($saldo, 2, ',', '.') }}
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
                            {{ number_format($saldo, 2, ',', '.') }}
                            @endif
                    </td>
                    </tr>
                    @endfor
                    <!-- Total Aset Lancar and Total Kewajiban -->
                    <tr class="total-row">
                        <td class="font-weight-bold">Total Aset Lancar</td>
                        <td class="text-right font-weight-bold">{{ number_format($totalAsetLancar, 2, ',', '.') }}</td>
                        <td class="font-weight-bold">Total Kewajiban</td>
                        <td class="text-right font-weight-bold">{{ number_format($totalKewajiban, 2, ',', '.') }}</td>
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
                    @for ($i = 0; $i < $maxRows; $i++) <tr>
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
                                {{ number_format($saldo, 2, ',', '.') }}
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

                                if ($subsection && is_object($ekuitasData) && method_exists($ekuitasData, 'get')) {
                                $saldoObject = $ekuitasData->get($subsection);
                                $saldo = $saldoObject->saldo ?? 0; // Asumsi get() mengembalikan objek
                                } elseif ($subsection && is_array($ekuitasData) && isset($ekuitasData[$subsection]['saldo'])) {
                                $saldo = $ekuitasData[$subsection]['saldo'] ?? 0; // Jika $ekuitasData adalah array
                                }

                                $totalEkuitas += $saldo;
                                @endphp
                                {{ number_format($saldo, 2, ',', '.') }}
                                @endif
                        </td>
                        </tr>
                        @endfor
                        <!-- Total Aset Tetap and Total Ekuitas -->
                        <tr class="total-row">
                            <td class="font-weight-bold">Total Aset Tetap</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalAsetTetap, 2, ',', '.') }}</td>
                            <td class="font-weight-bold">Total Ekuitas</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalEkuitas, 2, ',', '.') }}</td>
                        </tr>

                        <!-- Total Aset and Total Kewajiban + Ekuitas -->
                        <tr class="total-row">
                            <td class="font-weight-bold">TOTAL ASET</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalAsetLancar + $totalAsetTetap, 2, ',', '.') }}</td>
                            <td class="font-weight-bold">TOTAL KEWAJIBAN + EKUITAS</td>
                            <td class="text-right font-weight-bold">{{ number_format($totalKewajiban + $totalEkuitas, 2, ',', '.') }}</td>
                        </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection