<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSubsidiaryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Akun Pembantu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session('success'))
                        <div id="success-message" class="alert alert-success alert-dismissible fade show"
                            role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div id="error-message" class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <input type="hidden" id="subsidiary_id" name="subsidiary_id">
                    <input type="hidden" id="edit_account_name" name="account_name">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_store_name" class="form-label required">Nama Toko</label>
                            <input type="text" class="form-control @error('store_name') is-invalid @enderror"
                                id="edit_store_name" name="store_name" required placeholder="Masukkan nama toko"
                                data-bs-toggle="tooltip" title="Masukkan nama toko untuk akun pembantu">
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
                            <label for="edit_account_name_preview" class="form-label">Pratinjau Nama Akun</label>
                            <input type="text" class="form-control" id="edit_account_name_preview" readonly
                                data-bs-toggle="tooltip" title="Pratinjau nama akun berdasarkan input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-button" data-bs-dismiss="modal" data-bs-toggle="tooltip"
                        title="Tutup tanpa menyimpan">Batal</button>
                    <button type="submit" class="btn save-button" form="editSubsidiaryForm" data-bs-toggle="tooltip"
                        title="Simpan perubahan akun pembantu">Simpan Perubahan</button>
                </div>
            </form>
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
        const editModal = document.getElementById('editModal');
        const editStoreNameInput = document.getElementById('edit_store_name');
        const editForm = document.getElementById('editSubsidiaryForm');
        const editAccountNamePreview = document.getElementById('edit_account_name_preview');
        const editButtons = document.querySelectorAll('.edit-button');
        const currentRoute =
            "{{ request()->routeIs('subsidiary_utang') ? 'subsidiary_utang' : (request()->routeIs('subsidiary_piutang') ? 'subsidiary_piutang' : '') }}";
        const accountPrefix = currentRoute === 'subsidiary_utang' ? 'Utang' : 'Piutang';
        const bootstrapModal = new bootstrap.Modal(editModal);

        function updateEditAccountNamePreview(storeName) {
            const trimmedStoreName = storeName ? storeName.trim() : '';
            editAccountNamePreview.value = trimmedStoreName ? `${accountPrefix} ${trimmedStoreName}` : '';
            document.getElementById('edit_account_name').value = editAccountNamePreview.value;
        }

        if (editStoreNameInput) {
            editStoreNameInput.addEventListener('input', () => {
                const storeName = editStoreNameInput.value;
                updateEditAccountNamePreview(storeName);
            });
        }

        if (editButtons) {
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const subsidiaryId = this.getAttribute('data-subsidiary-id');
                    const storeName = this.getAttribute('data-store-name');
                    const accountName = this.getAttribute('data-account-name') || '';

                    document.getElementById('subsidiary_id').value = subsidiaryId || '';
                    document.getElementById('edit_account_name').value = accountName;
                    editStoreNameInput.value = storeName || '';
                    updateEditAccountNamePreview(storeName);

                    bootstrapModal.show();
                });
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const subsidiaryId = document.getElementById('subsidiary_id').value;
                let formAction;
                if (currentRoute === 'subsidiary_utang') {
                    formAction = `{{ route('utangUpdate', '') }}/${subsidiaryId}`;
                } else {
                    formAction = `{{ route('piutangUpdate', '') }}/${subsidiaryId}`;
                }
                this.action = formAction;

                if (!editStoreNameInput.value.trim()) {
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                    errorAlert.role = 'alert';
                    errorAlert.innerHTML = `
                    Nama Toko tidak boleh kosong.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                    editForm.querySelector('.modal-body').prepend(errorAlert);
                    setTimeout(() => errorAlert.classList.add('fade'), 3000);
                    return;
                }

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').content,
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ||
                            `Gagal menyimpan perubahan: ${response.statusText}`);
                    }

                    if (data.success) {
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success alert-dismissible fade show';
                        successAlert.role = 'alert';
                        successAlert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                        editForm.querySelector('.modal-body').prepend(successAlert);
                        setTimeout(() => {
                            successAlert.classList.add('fade');
                            bootstrapModal.hide();
                            window.location.reload();
                        }, 2000);
                    } else {
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                        errorAlert.role = 'alert';
                        errorAlert.innerHTML = `
                        ${data.message || 'Terjadi kesalahan saat menyimpan.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                        editForm.querySelector('.modal-body').prepend(errorAlert);
                        setTimeout(() => errorAlert.classList.add('fade'), 3000);
                    }
                } catch (error) {
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                    errorAlert.role = 'alert';
                    errorAlert.innerHTML = `
                    Terjadi kesalahan: ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                    editForm.querySelector('.modal-body').prepend(errorAlert);
                    setTimeout(() => errorAlert.classList.add('fade'), 3000);
                }
            });
        }

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
