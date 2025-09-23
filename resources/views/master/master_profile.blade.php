@extends('layouts.app')

@section('title', 'Master Profile')

@section('content')
    <style>
        .content-wrapper {
            padding: 20px;
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
            position: relative;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            padding: 15px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 0.25rem;
            color: #343a40;
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
            transition: border-color 0.2s;
        }

        .form-control-plaintext:focus {
            border-bottom-color: #007bff;
            outline: none;
        }

        .edit-button {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .edit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-title {
            font-weight: bold;
            color: #343a40;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #007bff;
        }

        .progress {
            height: 20px;
            font-size: 0.8em;
            margin-top: 10px;
        }

        .alert {
            cursor: pointer;
            margin-bottom: 15px;
            animation: fadeIn 0.5s;
        }

        .alert:hover {
            opacity: 0.9;
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

        @if (isset($master))
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Profil Master</h4>
                </div>
                <div class="card-body">
                    <!-- Summary Section -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <h6><i class="fas fa-user-tie me-2"></i>Peran</h6>
                                <p class="fw-bold">Master Admin</p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-clock me-2"></i>Terakhir Diperbarui</h6>
                                <p class="fw-bold">01:56 PM WIB, Sabtu, 20 September 2025</p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="fas fa-check-circle me-2"></i>Kelasifikasi Profil</h6>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $master->name && $master->email ? '100%' : '50%' }}"
                                        aria-valuenow="{{ $master->name && $master->email ? 100 : 50 }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                        {{ $master->name && $master->email ? 'Lengkap (100%)' : 'Sebagian Lengkap (50%)' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama:</label>
                        <input type="text" class="form-control-plaintext" id="name" value="{{ $master->name }}"
                            disabled readonly data-bs-toggle="tooltip" data-bs-placement="top" title="Nama Master Admin">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="text" class="form-control-plaintext" id="email" value="{{ $master->email }}"
                            disabled readonly data-bs-toggle="tooltip" data-bs-placement="top" title="Email Master Admin">
                    </div>
                </div>
                <button type="button" class="btn btn-primary edit-button" data-bs-toggle="modal"
                    data-bs-target="#editProfileModal" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Ubah profil Master Admin">
                    <i class="fas fa-pen me-2"></i>Edit
                </button>
            </div>

            <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('master_update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Kata Sandi Saat Ini:</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" required>
                                        <span class="password-toggle" id="toggleCurrentPassword">
                                            <i class="fas fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Nama:</label>
                                    <input type="text" class="form-control" id="edit_name" name="name"
                                        value="{{ $master->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label">Email:</label>
                                    <input type="email" class="form-control" id="edit_email" name="email"
                                        value="{{ $master->email }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Kata Sandi Baru:</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="new_password"
                                            name="new_password">
                                        <span class="password-toggle" id="toggleNewPassword">
                                            <i class="fas fa-eye-slash"></i>
                                        </span>
                                    </div>
                                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah kata
                                        sandi.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Konfirmasi Kata Sandi
                                        Baru:</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="new_password_confirmation"
                                            name="new_password_confirmation">
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
            <p class="text-center text-muted">Data master tidak tersedia.</p>
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

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection
