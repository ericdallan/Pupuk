@extends('layouts.app')

@section('title', 'Perhitungan Zakat')

@section('content')
<div class="container mx-auto p-6">
    <!-- Form untuk filter -->
    <form action="{{ route('zakat.calculate') }}" method="POST" class="mb-3">
        @csrf
        <div class="row">
            <!-- Tahun -->
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun:</label>
                <select name="year" id="year" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = date('Y'); $i >= 1900; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <!-- Bulan -->
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan:</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Semua</option>
                    @for ($i = 1; $i <= 12; $i++) <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->format('F') }}</option>
                        @endfor
                </select>
            </div>

            <!-- Metode Perhitungan -->
            <div class="col-md-3">
                <label for="calculation_method" class="form-label">Metode Perhitungan:</label>
                <select name="calculation_method" id="calculation_method" class="form-select">
                    <option value="cara1" {{ $calculation_method == 'cara1' ? 'selected' : '' }}>Cara 1 (Neraca Keuangan)</option>
                    <option value="cara2" {{ $calculation_method == 'cara2' ? 'selected' : '' }}>Cara 2 (Laba Rugi)</option>
                </select>
            </div>

            <!-- Tombol Filter dan Export -->
            <div class="col-md-3" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary me-2 filter-button">Filter</button>
                <a href="{{ route('zakat.export') . '?year=' . request()->input('year', date('Y')) . '&month=' . request()->input('month', '') }}" class="btn btn-success">Export to Excel</a>
            </div>
        </div>
    </form>

    <!-- Tampilkan hasil jika ada -->
    @if (isset($zakatCara1) || isset($zakatCara2))
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="card-title text-2xl font-semibold mb-4">Hasil Perhitungan Zakat</h2>

            @if ($calculation_method == 'cara1')
            <!-- Tampilkan hasil Cara 1 -->
            <div class="space-y-2">
                <p><strong>Aktiva Lancar:</strong> {{ number_format($totalAktivaLancar, 2) }}</p>
                <p><strong>Hutang Lancar:</strong> {{ number_format($totalHutangLancar, 2) }}</p>
                <p><strong>Selisih (Aktiva Lancar - Hutang Lancar):</strong> {{ number_format($selisih, 2) }}</p>
                <p><strong>Zakat (2.5% x Selisih):</strong> {{ number_format($zakatCara1, 2) }}</p>
            </div>
            @elseif ($calculation_method == 'cara2')
            <!-- Tampilkan hasil Cara 2 -->
            <div class="space-y-2">
                <p><strong>Laba Bersih:</strong> {{ number_format($labaBersih, 2) }}</p>
                <p><strong>Zakat (2.5% x Laba Bersih):</strong> {{ number_format($zakatCara2, 2) }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Tampilkan error jika ada -->
    @if ($errors->has('error'))
    <div class="alert alert-danger mt-4">
        {{ $errors->first('error') }}
    </div>
    @endif
</div>
@endsection