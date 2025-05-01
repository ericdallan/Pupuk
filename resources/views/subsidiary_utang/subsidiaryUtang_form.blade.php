<div class="modal fade" id="createSubsidiaryModal" tabindex="-1" aria-labelledby="createSubsidiaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubsidiaryModalLabel">Buat Buku Besar Pembantu Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <form id="subsidiaryForm" action="{{ route('subsidiaries.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_account_name" class="form-label">Nama Akun</label>
                        <select class="form-select @error('account_name') is-invalid @enderror" id="modal_account_name" name="account_name" required>
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
                    <div class="mb-3">
                        <label for="modal_store_name" class="form-label">Nama Toko</label>
                        <input type="text" class="form-control @error('store_name') is-invalid @enderror" id="modal_store_name" name="store_name" required>
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
                        <label for="account_name_preview" class="form-label">Pratinjau Nama Akun</label>
                        <input type="text" class="form-control" id="account_name_preview" readonly>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Buku Besar Pembantu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountNameSelect = document.getElementById('modal_account_name');
        const storeNameInput = document.getElementById('modal_store_name');
        const accountNamePreview = document.getElementById('account_name_preview');

        // Determine the current route (injected from server-side)
        const currentRoute = "{{ request()->routeIs('subsidiary_utang') ? 'subsidiary_utang' : (request()->routeIs('subsidiary_piutang') ? 'subsidiary_piutang' : '') }}";

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
    });
</script>