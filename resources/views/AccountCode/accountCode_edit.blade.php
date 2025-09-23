@extends('layouts.app')

@section('content')
@section('title', 'Chart of Account')

<style>
    /* Enhanced Button Styles */
    .save-button {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .save-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        background: linear-gradient(45deg, #0056b3, #003d80);
    }

    .cancel-button {
        background: linear-gradient(45deg, #6c757d, #5a6268);
        border: none;
        color: white;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .cancel-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        background: linear-gradient(45deg, #5a6268, #4b5156);
    }

    /* Form Enhancements */
    .form-control,
    .form-select {
        border-radius: 6px;
        transition: border-color 0.2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }

    /* Alert Animations */
    .alert {
        animation: fadeIn 0.5s;
        border-radius: 8px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Breadcrumb Styling */
    .breadcrumb {
        background: linear-gradient(90deg, #f8f9fa, #e9ecef);
        border-radius: 6px;
        padding: 10px 15px;
    }

    .breadcrumb-item a {
        color: #007bff;
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        text-decoration: underline;
    }

    /* Required Field Indicator */
    .required:after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }
</style>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('account_page') }}">Chart of Account</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Akun</li>
    </ol>
</nav>

<h2>Edit Akun: {{ $account->account_name }}</h2>

@if (session('success'))
    <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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

<div class="mt-4">
    <form id="accountFormEdit" action="{{ route('account_update', $account->account_code) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label for="account_type" class="form-label required">Tipe Akun</label>
                <select class="form-select" id="account_type" name="account_type" disabled data-bs-toggle="tooltip"
                    title="Tipe akun tidak dapat diubah setelah dibuat">
                    <option value="{{ $account->account_type }}">{{ $account->account_type }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="account_section" class="form-label required">Bagian Akun</label>
                <select class="form-select" id="account_section" name="account_section" disabled
                    data-bs-toggle="tooltip" title="Bagian akun tidak dapat diubah setelah dibuat">
                    <option value="{{ $account->account_section }}">{{ $account->account_section }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="account_subsection" class="form-label required">Anak Bagian Akun</label>
                <select class="form-select" id="account_subsection" name="account_subsection" disabled
                    data-bs-toggle="tooltip" title="Anak bagian akun tidak dapat diubah setelah dibuat">
                    <option value="{{ $account->account_subsection }}">{{ $account->account_subsection }}</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="account_name" class="form-label required">Nama Akun</label>
                <input type="text" class="form-control" id="account_name" name="account_name"
                    value="{{ $account->account_name }}" required placeholder="Masukkan nama akun"
                    data-bs-toggle="tooltip" title="Masukkan nama akun yang deskriptif">
            </div>
        </div>
        <div class="modal-footer mt-4">
            <a href="{{ route('account_page') }}" class="btn cancel-button me-2" data-bs-toggle="tooltip"
                title="Kembali ke daftar akun">Tutup</a>
            <button type="submit" class="btn save-button" id="saveAccountBtn" data-bs-toggle="tooltip"
                title="Simpan perubahan akun">Simpan Akun</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
