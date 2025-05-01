<div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
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
                    <input type="hidden" id="subsidiary_id" name="subsidiary_id">
                    <input type="hidden" id="edit_account_name" name="account_name">
                    <div class="mb-3">
                        <div class="mb-3">
                            <label for="edit_store_name" class="form-label">Nama Toko:</label>
                            <input type="text" class="form-control @error('store_name') is-invalid @enderror" id="edit_store_name" name="store_name" required>
                            @if (request()->routeIs('subsidiary_piutang'))
                            <small class="form-text text-muted">Nama toko akan menjadi "Piutang [Nama Toko]" untuk akun Piutang Usaha (1.1.03.01)</small>
                            @elseif (request()->routeIs('subsidiary_utang'))
                            <small class="form-text text-muted">Nama toko akan menjadi "Utang [Nama Toko]" untuk akun Utang Usaha (2.1.01.01)</small>
                            @else
                            <small class="form-text text-muted">Nama toko akan menjadi "Piutang [Nama Toko]" atau "Utang [Nama Toko]" berdasarkan pilihan akun</small>
                            @endif
                            @error('store_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_account_name_preview" class="form-label">Pratinjau Nama Akun</label>
                            <input type="text" class="form-control" id="edit_account_name_preview" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" form="editSubsidiaryForm">Simpan Perubahan</button>
                    </div>
            </form>
        </div>
    </div>
</div>
<script>
 document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');
    const editStoreNameInput = document.getElementById('edit_store_name');
    const editForm = document.getElementById('editSubsidiaryForm');
    const editAccountNamePreview = document.getElementById('edit_account_name_preview');
    const editButtons = document.querySelectorAll('.btn-warning.detail-btn');
    const currentRoute = "{{ request()->routeIs('subsidiary_utang') ? 'subsidiary_utang' : (request()->routeIs('subsidiary_piutang') ? 'subsidiary_piutang' : '') }}";
    const accountPrefix = currentRoute === 'subsidiary_utang' ? 'Utang' : 'Piutang';
    const bootstrapModal = new bootstrap.Modal(editModal);
    const notificationArea = document.getElementById('notification-area');
    const notificationMessage = document.getElementById('notification-message');

    function showNotification(message, isSuccess) {
        if (notificationArea && notificationMessage) {
            notificationMessage.textContent = message;
            notificationArea.classList.remove('d-none', 'alert-success', 'alert-danger');
            notificationArea.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
            // Auto-hide after 3 seconds (adjust as needed)
            setTimeout(() => {
                notificationArea.classList.add('d-none');
            }, 2000); // Changed to 3 seconds for visibility
        } else {
            alert(message); // Fallback to alert if notification area is missing
        }
    }

    function updateEditAccountNamePreview(storeName) {
        const trimmedStoreName = storeName ? storeName.trim() : '';
        editAccountNamePreview.value = trimmedStoreName ? `${accountPrefix} ${trimmedStoreName}` : '';
    }

    if (editStoreNameInput) {
        editStoreNameInput.addEventListener('input', () => {
            const storeName = editStoreNameInput.value;
            updateEditAccountNamePreview(storeName);
            document.getElementById('edit_account_name').value = editAccountNamePreview.value;
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
                editAccountNamePreview.value = accountName;
                updateEditAccountNamePreview(storeName);

                bootstrapModal.show();
            });
        });
    }

    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Tombol Simpan Perubahan ditekan!');

            const subsidiaryId = document.getElementById('subsidiary_id').value;
            let formAction;
            if (currentRoute === 'subsidiary_utang') {
                formAction = `{{ route('subsidiary_utang.update', '') }}/${subsidiaryId}`;
            } else {
                formAction = `{{ route('subsidiary_piutang.update', '') }}/${subsidiaryId}`;
            }
            this.action = formAction;

            if (!editStoreNameInput.value.trim()) {
                showNotification('Nama Toko tidak boleh kosong.', false);
                return;
            }

            console.log('Validasi lulus, mencoba mengirim data ke:', this.action);

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();
                console.log('Respon dari server:', data);

                if (!response.ok) {
                    throw new Error(data.message || `Gagal menyimpan perubahan: ${response.statusText}`);
                }

                if (data.success) {
                    showNotification(data.message, true);
                    bootstrapModal.hide();
                    // Delay page reload to allow notification to be visible for 3 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000); // Match the notification duration
                } else {
                    showNotification(data.message || 'Terjadi kesalahan saat menyimpan.', false);
                }
            } catch (error) {
                console.error('Error saat mengirim data:', error);
                showNotification(`Terjadi kesalahan: ${error.message}`, false);
            }
        });
    }

    // --- Existing CREATE modal logic (unchanged) ---
    const createAccountNameSelect = document.getElementById('modal_account_name');
    const createStoreNameInput = document.getElementById('modal_store_name');
    const createAccountNamePreview = document.getElementById('account_name_preview');

    function updateCreateAccountNamePreview() {
        const accountName = createAccountNameSelect.value;
        const storeName = createStoreNameInput.value.trim();
        let previewText = '';

        if (storeName) {
            if (accountName === 'Piutang Usaha') {
                previewText = `Piutang ${storeName}`;
            } else if (accountName === 'Utang Usaha') {
                previewText = `Utang ${storeName}`;
            }
        }

        createAccountNamePreview.value = previewText;
    }

    if (createAccountNameSelect && createStoreNameInput && createAccountNamePreview) {
        createAccountNameSelect.addEventListener('change', updateCreateAccountNamePreview);
        createStoreNameInput.addEventListener('input', updateCreateAccountNamePreview);
        updateCreateAccountNamePreview();
    }
});
</script>