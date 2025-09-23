@extends('layouts.app')

@section('title', 'Perhitungan Zakat')

@section('content')
<style>
    /* Button Styles */
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

    /* Card Styling */
    .card {
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
    }

    /* Form Styling */
    .form-select,
    .form-control {
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #343a40;
    }

    /* Alert Styling */
    .alert {
        animation: fadeIn 0.5s;
        border-radius: 4px;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Result Styling */
    .result-item {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .result-item strong {
        color: #343a40;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }

        .col-md-3 {
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }

    /* Tooltip Styling */
    .tooltip-inner {
        background-color: #343a40;
        color: white;
        border-radius: 4px;
    }

    .tooltip .tooltip-arrow::before {
        border-top-color: #343a40;
    }
</style>

<div class="container-fluid mt-2">
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Perhitungan Zakat</h5>
        </div>
        <div class="card-body">
            @php
                // Default to current year, month, and Cara 1
                $currentYear = \Carbon\Carbon::now()->year;
                $currentMonth = \Carbon\Carbon::now()->month;
                $year = $year ?? $currentYear;
                $month = $month ?? $currentMonth;
                $calculation_method = $calculation_method ?? 'cara1';
            @endphp
            <form action="{{ route('zakat.calculate') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label for="year" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Pilih tahun untuk perhitungan zakat">Tahun</label>
                    <select name="year" id="year" class="form-select">
                        <option value="">Semua</option>
                        @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="month" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Pilih bulan untuk perhitungan zakat">Bulan</label>
                    <select name="month" id="month" class="form-select">
                        <option value="">Semua</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="calculation_method" class="form-label" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Pilih metode perhitungan zakat">Metode Perhitungan</label>
                    <select name="calculation_method" id="calculation_method" class="form-select">
                        <option value="cara1" {{ $calculation_method == 'cara1' ? 'selected' : '' }}>
                            Cara 1 (Neraca Keuangan)
                        </option>
                        <option value="cara2" {{ $calculation_method == 'cara2' ? 'selected' : '' }}>
                            Cara 2 (Laba Rugi)
                        </option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn filter-button" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Terapkan filter untuk menghitung zakat">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('zakat.export') . '?year=' . $year . '&month=' . $month . '&calculation_method=' . $calculation_method }}"
                        class="btn export-button" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Ekspor hasil perhitungan ke Excel">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tampilkan error jika ada -->
    @if ($errors->has('error'))
        <div id="error-message" class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
            {{ $errors->first('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tampilkan hasil untuk Cara 1 pada load awal atau sesuai metode -->
    @if (isset($zakatCara1) && $calculation_method == 'cara1')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Hasil Perhitungan Zakat (Cara 1 - Neraca Keuangan)</h5>
            </div>
            <div class="card-body">
                <div class="space-y-2">
                    <p class="result-item"><strong>Aktiva Lancar:</strong> {{ number_format($totalAktivaLancar, 2, ',', '.') }}</p>
                    <p class="result-item"><strong>Hutang Lancar:</strong> {{ number_format($totalHutangLancar, 2, ',', '.') }}</p>
                    <p class="result-item"><strong>Selisih (Aktiva Lancar - Hutang Lancar):</strong> {{ number_format($selisih, 2, ',', '.') }}</p>
                    @if ($zakatWajibCara1)
                        <p class="result-item"><strong>Status Zakat:</strong> <span class="text-success">Wajib (Selisih ≥ Rp 85,000,000)</span></p>
                        <p class="result-item"><strong>Zakat (2.5% x Selisih):</strong> {{ number_format($zakatCara1, 2, ',', '.') }}</p>
                    @else
                        <p class="result-item"><strong>Status Zakat:</strong> <span class="text-danger">Tidak Wajib (Selisih < Rp 85,000,000)</span></p>
                        <p class="result-item"><strong>Zakat:</strong> Rp 0,00</p>
                    @endif
                </div>
            </div>
        </div>
    @elseif (isset($zakatCara2) && $calculation_method == 'cara2')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Hasil Perhitungan Zakat (Cara 2 - Laba Rugi)</h5>
            </div>
            <div class="card-body">
                <div class="space-y-2">
                    <p class="result-item"><strong>Laba Bersih:</strong> {{ number_format($labaBersih, 2, ',', '.') }}</p>
                    @if ($zakatWajibCara2)
                        <p class="result-item"><strong>Status Zakat:</strong> <span class="text-success">Wajib (Laba Bersih ≥ Rp 85,000,000)</span></p>
                        <p class="result-item"><strong>Zakat (2.5% x Laba Bersih):</strong> {{ number_format($zakatCara2, 2, ',', '.') }}</p>
                    @else
                        <p class="result-item"><strong>Status Zakat:</strong> <span class="text-danger">Tidak Wajib (Laba Bersih < Rp 85,000,000)</span></p>
                        <p class="result-item"><strong>Zakat:</strong> Rp 0,00</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Auto-dismiss error alerts
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                setTimeout(() => errorMessage.classList.add('fade'), 5000);
            }
        });
    </script>
@endpush
@endsection