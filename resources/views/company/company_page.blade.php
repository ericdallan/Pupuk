@extends('layouts.app')

@section('content')
@section('title', 'Profil Perusahaan')

<style>
    .container {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .logo-container {
        border: 2px solid #dee2e6;
        border-radius: 50%;
        padding: 5px;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        background: linear-gradient(45deg, #0056b3, #003d80);
    }

    .progress {
        height: 20px;
        font-size: 0.8em;
    }

    .form-label {
        font-weight: bold;
        color: #343a40;
    }
</style>

<div class="container py-4">
    <div class="text-end mb-3">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#companyModal"
            data-bs-toggle="tooltip" data-bs-placement="top" title="Ubah data perusahaan">
            <i class="fas fa-edit me-2"></i>Edit Data Perusahaan
        </button>
    </div>

    <!-- Summary Card -->
    <div class="card mb-4 p-3">
        <div class="row">
            <div class="col-md-4">
                <h6><i class="fas fa-info-circle me-2"></i>Status Perusahaan</h6>
                <p class="fw-bold">{{ $company->status ?? 'Inactive' }}</p>
            </div>
            <div class="col-md-4">
                <h6><i class="fas fa-clock me-2"></i>Terakhir Diperbarui</h6>
                <p class="fw-bold">01:52 PM WIB, Sabtu, 20 September 2025</p>
            </div>
        </div>
    </div>

    <div class="card p-3">
        <div class="mb-3 text-center">
            @if ($company && $company->logo)
                <div class="logo-container">
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Profile Logo"
                        style="max-width: 100px; max-height: 100px;" class="rounded-circle">
                </div>
            @else
                <div class="logo-container">
                    <div style="max-width: 100px; max-height: 100px;"
                        class="rounded-circle d-flex align-items-center justify-content-center bg-light">
                        <p class="text-muted mt-2">Tidak ada logo</p>
                    </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan"
                    value="{{ $company->company_name ?? '' }}" readonly data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Nama resmi perusahaan">
            </div>
            <div class="col-md-6 mb-3">
                <label for="director" class="form-label">Direktur</label>
                <input type="text" class="form-control" id="director" name="director"
                    value="{{ $company->director ?? '' }}" readonly data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Nama direktur perusahaan">
            </div>
            <div class="col-12 mb-3">
                <label for="address" class="form-label">Alamat</label>
                <textarea class="form-control" id="address" name="address" rows="3" readonly data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Alamat lengkap perusahaan">{{ $company->address ?? '' }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label for="telepon" class="form-label">Nomor Telepon</label>
                <input type="tel" class="form-control" id="telepon" name="telepon"
                    value="{{ $company->phone ?? '' }}" readonly data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Nomor kontak perusahaan">
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ $company->email ?? '' }}" readonly data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Email resmi perusahaan">
            </div>
        </div>

        <!-- Data Completeness Progress -->
        <div class="mb-3">
            <label class="form-label">Kelasifikasi Data</label>
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar"
                    style="width: {{ $company && $company->company_name && $company->director && $company->address && $company->phone && $company->email ? '100%' : ($company ? '50%' : '0%') }}"
                    aria-valuenow="{{ $company && $company->company_name && $company->director && $company->address && $company->phone && $company->email ? 100 : ($company ? 50 : 0) }}"
                    aria-valuemin="0" aria-valuemax="100">
                    {{ $company && $company->company_name && $company->director && $company->address && $company->phone && $company->email ? 'Lengkap (100%)' : ($company ? 'Sebagian Lengkap (50%)' : 'Tidak Lengkap (0%)') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modal -->
@include('company.company_form')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
