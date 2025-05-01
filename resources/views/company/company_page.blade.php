@extends('layouts.app')

@section('content')
@section('title', 'Profil Perusahaan')

<div class="text-end mb-3">
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#companyModal">
        Edit Data Perusahaan
    </button>
    @extends('company/company_form')
</div>
<div class="container mt-4">
    @if ($company && $company->logo)
    <div class="mb-3 text-center">
        <img src="{{ asset('storage/' . $company->logo) }}" alt="Profile Logo" style="max-width: 100px; max-height: 100px;" class="rounded-circle me-2">
    </div>
    @else
    <div class="mb-3 text-center">
        <p>Tidak ada logo</p>
    </div>
    @endif
    <div class="mb-3">
        <label for="nama_perusahaan" class="form-label">Nama Perusahaan</label>
        <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="{{ $company->company_name ?? '' }}" readonly>
    </div>
    <div class="mb-3">
        <label for="director" class="form-label">Direktur</label>
        <input type="text" class="form-control" id="director" name="director" value="{{ $company->director ?? '' }}" readonly>
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Alamat</label>
        <textarea class="form-control" id="address" name="address" required>{{ $company->address ?? '' }}</textarea>
    </div>
    <div class="mb-3">
        <label for="telepon" class="form-label">Nomor Telepon</label>
        <input type="tel" class="form-control" id="telepon" name="telepon" value="{{ $company->phone ?? '' }}" readonly>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $company->email ?? '' }}" readonly>
    </div>
</div>
@endsection