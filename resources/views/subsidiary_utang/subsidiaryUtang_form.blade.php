<div class="modal fade" id="createSubsidiaryModal" tabindex="-1" aria-labelledby="createSubsidiaryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubsidiaryModalLabel">Buat Buku Besar Pembantu Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session('success'))
                    <div id="success-message" class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
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

                <form id="subsidiaryForm" action="{{ route('subsidiaries.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_account_name" class="form-label required">Nama Akun</label>
                            <select class="form-select @error('account_name') is-invalid @enderror"
                                id="modal_account_name" name="account_name" required data-bs-toggle="tooltip"
                                title="Pilih tipe akun untuk buku besar pembantu">
                                @if (request()->routeIs('subsidiary_utang'))
                                    <option value="Utang Usaha" selected>Utang Usaha (2.1.01.01)</option>
                                @elseif (request()->routeIs('subsidiary_piutang'))
                                    <option value="Piutang Usaha" selected>Piutang Usaha (1.1.03.01)</option>
                                @else
                                    <option value="Piutang Usaha">Piutang Usaha (1.1.03.01)</option>
                                    <option value="Utang Usaha">Utang Usaha (2.1.01.01)</option>
                                @endif
                            </select>
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="modal_store_name" class="form-label required">Nama Toko</label>
                            <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                id="modal_store_name" name="store_name" required placeholder="Masukkan nama toko"
                                data-bs-toggle="tooltip" title="Masukkan nama toko untuk akun buku besar pembantu">
                            @if (request()->routeIs('subsidiary_piutang'))
                                <small class="form-text text-muted">Nama toko akan menjadi "Piutang [Nama Toko]" untuk
                                    akun Piutang Usaha (1.1.03.01)</small>
                            @elseif (request()->routeIs('subsidiary_utang'))
                                <small class="form-text text-muted">Nama toko akan menjadi "Utang [Nama Toko]" untuk
                                    akun Utang Usaha (2.1.01.01)</small>
                            @else
                                <small class="form-text text-muted">Nama toko akan menjadi "Piutang [Nama Toko]" atau
                                    "Utang [Nama Toko]" berdasarkan pilihan akun</small>
                            @endif
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="account_name_preview" class="form-label">Pratinjau Nama Akun</label>
                            <input type="text" class="form-control" id="account_name_preview" readonly
                                data-bs-toggle="tooltip" title="Pratinjau nama akun berdasarkan input">
                        </div>
                    </div>
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn cancel-button" data-bs-dismiss="modal"
                            data-bs-toggle="tooltip" title="Tutup tanpa menyimpan">Tutup</button>
                        <button type="submit" class="btn save-button" data-bs-toggle="tooltip"
                            title="Simpan buku besar pembantu baru">Simpan Buku Besar Pembantu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

    /* Modal Styling */
    .modal-content {
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(90deg, #343a40, #212529);
        color: white;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
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

    /* Required Field Indicator */
    .required:after {
        content: '*';
        color: #dc3545;
        margin-left: 4px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountNameSelect = document.getElementById('modal_account_name');
        const storeNameInput = document.getElementById('modal_store_name');
        const accountNamePreview = document.getElementById('account_name_preview');

        // Determine the current route (injected from server-side)
        const currentRoute =
            "{{ request()->routeIs('subsidiary_utang') ? 'subsidiary_utang' : (request()->routeIs('subsidiary_piutang') ? 'subsidiary_piutang' : '') }}";

        // Remove options based on route
        if (currentRoute === 'subsidiary_utang') {
            for (let i = 0; i < accountNameSelect.options.length; i++) {
                if (accountNameSelect.options[i].value === 'Piutang Usaha') {
                    accountNameSelect.remove(i);
                    break;
                }
            }
        } else if (currentRoute === 'subsidiary_piutang') {
            for (let i = 0; i < accountNameSelect.options.length; i++) {
                if (accountNameSelect.options[i].value === 'Utang Usaha') {
                    accountNameSelect.remove(i);
                    break;
                }
            }
        }

        // Function to update account name preview
        function updateAccountNamePreview() {
            const accountName = accountNameSelect.value;
            const storeName = storeNameInput.value.trim();
            let previewText = '';

            if (storeName) {
                if (accountName === 'Piutang Usaha') {
                    previewText = `Piutang ${storeName}`;
                } else if (accountName === 'Utang Usaha') {
                    previewText = `Utang ${storeName}`;
                }
            }

            accountNamePreview.value = previewText;
        }

        // Add event listeners to update preview
        accountNameSelect.addEventListener('change', updateAccountNamePreview);
        storeNameInput.addEventListener('input', updateAccountNamePreview);

        // Initialize preview on page load
        updateAccountNamePreview();

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Fade out alerts after a delay
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            setTimeout(() => successMessage.classList.add('fade'), 3000);
        }
        const errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            setTimeout(() => errorMessage.classList.add('fade'), 5000);
        }
    });
</script>
