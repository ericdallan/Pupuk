@extends('layouts.app')

@section('title', 'Voucher Akuntansi')

@section('content')
<div class="container">
    <h2 class="text-center">Rincian {{ $headingText }} Voucher</h2>
    <div class="row mb-3">
        <label for="voucherNumber" class="col-sm-3 col-form-label">Nomor Voucher:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="voucherNumber" value="{{ $voucher->voucher_number }}" readonly>
        </div>
    </div>

    <div class="row mb-3">
        <label for="companyName" class="col-sm-3 col-form-label">Nama Perusahaan:</label>
        <div class="col-sm-9">
            @if ($company)
            <input type="text" class="form-control" id="companyName" value="{{ $company->company_name }}" readonly>
            @else
            <input type="text" class="form-control" id="companyName" value="Not Found" readonly>
            <small class="text-danger">Nama Perusahaan belum ditemukan.</small>
            @endif
        </div>
    </div>
    <div class="row mb-3">
        <label for="voucherType" class="col-sm-3 col-form-label">Tipe Voucher:</label>
        <div class="col-sm-3">
            <input type="text" class="form-control" id="voucherType" value="{{ $voucher->voucher_type }}" readonly>
        </div>
        <label for="voucherDate" class="col-sm-2 col-form-label">Tanggal:</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="voucherDate" value="{{ \Carbon\Carbon::parse($voucher->voucher_date)->isoFormat('dddd, DD MMMM') }}" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="preparedBy" class="col-sm-3 col-form-label">Disiapkan Oleh:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="preparedBy" value="{{ $voucher->prepared_by }}" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="approvedBy" class="col-sm-3 col-form-label">Disetujui Oleh:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="approvedBy" value="{{ $voucher->approved_by }}" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="givenTo" class="col-sm-3 col-form-label">Diberikan Kepada:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="given_to" value="{{ $voucher->given_to }}" readonly>
        </div>
    </div>
    <div class="row mb-3">
        <label for="description" class="col-sm-3 col-form-label">Transaksi:</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="description" value="{{ $voucher->transaction }}" readonly>
        </div>
    </div>
    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered" id="transactionTable">
                <thead>
                    <tr class="text-center">
                        <th colspan="4">Rincian Transaksi</th>
                    </tr>
                    <tr class="text-center">
                        <th>Deskripsi</th>
                        <th>Ukuran</th>
                        <th>Quantitas</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($voucherTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->size }}</td>
                        <td>{{ $transaction->quantity }}</td>
                        <td>{{ number_format($transaction->nominal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered" id="voucherDetailsTable">
            <thead>
                <tr class="text-center">
                    <th colspan="4">Rincian Voucher</th>
                </tr>
                <tr class="text-center">
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
            </thead>
            <tbody class="text-center">
                @foreach($voucherDetails as $detail)
                <tr>
                    <td>{{ $detail->account_code }}</td>
                    <td>{{ $detail->account_name }}</td>
                    <td>{{ number_format($detail->debit, 2) }}</td>
                    <td>{{ number_format($detail->credit, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row mb-3">
        <label for="totalDebit" class="col-sm-3 col-form-label">Total Debit:</label>
        <div class="col-sm-3">
            <input type="text" class="form-control" id="totalDebit" value="{{ number_format($voucher->total_debit, 2) }}" readonly>
        </div>
        <label for="totalCredit" class="col-sm-3 col-form-label">Total Credit:</label>
        <div class="col-sm-3">
            <input type="text" class="form-control" id="totalCredit" value="{{ number_format($voucher->total_credit, 2) }}" readonly>
        </div>
    </div>
    <table class="table table-bordered" style="margin-left: auto; width: 70%;">
        <tr class="text-center">
            <th>Dibuat Oleh</th>
            <th>Diberikan Kepada</th>
            <th>Diperiksa dan Disetujui Oleh</th>
        </tr>
        <tr>
            <td style="width: 25%; height: 100px;"></td>
            <td style="width: 25%; height: 100px;"></td>
            <td style="width: 25%; height: 100px;"></td>
        </tr>
        <tr class="text-center" style="font-size: smaller; font-style: bold;">
            <td>{{ $voucher->prepared_by }}</td>
            <td>{{ $voucher->given_to }}</td>
            <td>{{ $company->director }}</td>
        </tr>
    </table>
</div>
@endsection