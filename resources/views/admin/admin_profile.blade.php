@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<style>
    .card {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: relative;
        /* To position the edit button */
    }

    .form-label {
        font-weight: bold;
        margin-bottom: 0.25rem;
    }

    .form-control-plaintext {
        display: block;
        width: 100%;
        padding: 0.375rem 0;
        margin-bottom: 0.5rem;
        line-height: 1.5;
        color: #495057;
        background-color: transparent;
        border: solid transparent 1px;
        border-bottom-color: #ced4da;
        border-radius: 0;
    }

    .edit-button {
        position: absolute;
        bottom: 15px;
        right: 15px;
    }

    .modal-title {
        font-weight: bold;
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }
</style>
<div class="content-wrapper">
    @if (session('success'))
    <div id="success-message" class="alert alert-success" style="cursor: pointer;">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @elseif (session('message'))
    <div id="error-message" class="alert alert-danger" style="cursor: pointer;">
        {{ session('message') }}
    </div>
    @endif

    @if (isset($admin))
    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Nama:</label>
                <input type="text" class="form-control-plaintext" id="name" value="{{ $admin->name }}" disabled readonly>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="text" class="form-control-plaintext" id="email" value="{{ $admin->email }}" disabled readonly>
            </div>
        </div>
        <button type="button" class="btn btn-primary edit-button" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            Edit
        </button>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin_update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Kata Sandi Saat Ini:</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <span class="password-toggle" id="toggleCurrentPassword">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nama:</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $admin->name }}">
                        </div>

                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="edit_email" name="email" value="{{ $admin->email }}">
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Kata Sandi Baru:</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <span class="password-toggle" id="toggleNewPassword">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>

                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah kata sandi.</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru:</label>
                            <div class="position-relative">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                                <span class="password-toggle" id="toggleConfirmPassword">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @else
    <p>Data admin tidak tersedia.</p>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
        const currentPasswordInput = document.getElementById('current_password');

        toggleCurrentPassword.addEventListener('click', function() {
            const type = currentPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            currentPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const newPasswordInput = document.getElementById('new_password');

        toggleNewPassword.addEventListener('click', function() {
            const type = newPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            newPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.addEventListener('click', function() {
                this.style.display = 'none';
            });
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }

        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.addEventListener('click', function() {
                this.style.display = 'none';
            });
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }
    });
</script>
@endsection