<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="companyModalLabel">Edit Company Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Nama Perusahaan</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $company->company_name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="director" class="form-label">Direktur </label>
                        <input type="text" class="form-control" id="director" name="director" value="{{ $company->director ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required>{{ $company->address ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Nomor Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="{{ $company->phone ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $company->email ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo Perusahaan</label>
                        <input type="file" class="form-control" id="logo" name="logo">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>