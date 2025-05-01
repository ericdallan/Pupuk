<!DOCTYPE html>
<html>

<head>
    <title>{{ $headingText }} - {{ $voucher->voucher_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 98%;
            margin: 5px auto;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 0.8em;
            line-height: 1.2;
        }

        .clearfix-2 {
            text-align: center;
            margin: 10px auto 10px auto;
            padding: 5px;
        }

        .slip-title {
            text-align: center;
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            padding-top: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        .info-table td,
        .info-table th {
            padding: 2px 5px;
            font-size: 0.7em;
            border-style: solid;
            border-width: 0.5px;
            border-color: blue;
            word-break: break-word;
        }

        .info-table th {
            font-weight: bold;
            text-align: left;
        }

        .transaction-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-style: solid;
            border-width: 0.5px;
            border-color: blue;
        }

        .transaction-details-table th,
        .transaction-details-table td {
            border-style: solid;
            border-width: 0.5px;
            border-color: blue;
            padding: 3px;
            font-size: 0.8em;
            text-align: left;
        }

        .transaction-details-table th {
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            text-align: right;
            font-size: 0.8em;
            font-weight: bold;
            padding-right: 5px;
        }

        .accounting-section-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .accounting-section-table td {
            width: 25%;
            text-align: center;
            padding-top: 70px;
            /* Reduced padding-top */
            font-size: 0.8em;
            vertical-align: top;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 5px 20px;
            /* Added margin-top/bottom */
        }

        .signature-label {
            margin-top: 5px;
            font-size: 1em;
            /* Reduced font size */
        }

        .left-section {
            margin-top: 5px;
            width: 55%;
            /* Adjusted width */
            float: left;
            padding-right: 10px;
        }

        .right-section {
            margin-top: 5px;
            width: 45%;
            /* Adjusted width */
            float: right;
            padding-left: 10px;
        }

        .clearfix::after {
            /* This is the crucial part */
            content: "";
            display: table;
            clear: both;
        }

        .amount-label {
            font-weight: bold;
        }

        .account-code-table {
            width: 100%;
            border-collapse: collapse;
            border-style: solid;
            border-width: 0.5px;
            border-color: blue;
            margin-top: 15px;
        }

        .account-code-table th,
        .account-code-table td {      
            border-style: solid;
            border-width: 0.5px;
            border-color: blue;
            padding: 3px;
            font-size: 0.8em;
            text-align: left;
        }

        .account-code-table th {
            text-align: center;
        }

        .account-code-total th {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="clearfix-2">
            @if ($companyLogo)
            <div class="logo"><img src="{{ public_path('storage/' . $companyLogo) }}" alt="{{ $company->company_name }} Logo" style="max-width: 125px; height: 100px;"></div>
            @endif
            <div class="company-info">
                <div>{{ strtoupper($company->company_name ?? '') }}</div>
                <div>SUPPLIER & TRADING</div>
                <div>{{ $company->address ?? '' }}</div>
                <div>No. Telp {{ $company->phone ?? '' }} Email: {{ $company->email ?? '' }}</div>
            </div>
        </div>

        <div class="slip-title">ACCOUNTING SLIP / SLIP AKUNTANSI</div>

        <table class="info-table">
            <tr>
                <th>Transaction Approval /<br>Persetujuan Transaksi</th>
                <td>{{ $voucher->transaction_approval ?? '' }}</td>
                <th>Date / Tanggal</th>
                <td>{{ $voucher->voucher_date ? \Carbon\Carbon::parse($voucher->voucher_date)->format('d M Y') : '' }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th>Checked By / Yang Memeriksa</th>
                <td>{{ $voucher->approved_by }}</td>
                <th>Director / Direktur</th>
                <td>{{ $company->director }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th>Department</th>
                <td>{{ $voucher->department ?? 'Finance' }}</td>
                <th>Voucher No / No. Bukti</th>
                <td>{{ $voucher->voucher_number }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th>Prepared By / Dibuat oleh</th>
                <td>{{ $voucher->prepared_by }}</td>
                <th>Approved By / Disetujui Oleh</th>
                <td>{{ $company->director ?? '' }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th>Given To / Diberikan kepada</th>
                <td>{{ $voucher->given_to ?? '' }}</td>
                <th>Approved Date / Tanggal Disetujui</th>
                <td>{{ $voucher->voucher_date ? \Carbon\Carbon::parse($voucher->voucher_date)->format('d/m/Y') : '' }}</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <th>Transaction Name / Nama Transaksi</th>
                <td>{{ $voucher->transaction ?? '' }}</td>
                <th>Store Name / Nama Toko</th>
                <td>{{ $voucher->store ?? '' }}</td>
            </tr>
        </table>

        <div><strong style="font-size: 0.9em;">Transaction Details</strong></div>
        <table class="transaction-details-table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Quantity</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction as $trans)
                <tr>
                    <td>{{ $trans->description }}</td>
                    <td style="text-align: center;">{{ $trans->quantity }}</td>
                    <td style="text-align: right;">Rp. {{ number_format($trans->nominal, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="2" style="text-align: right;">Total</th>
                    <th style="text-align: right;">Rp. {{ number_format($transaction->sum(function ($t) { return $t->quantity * $t->nominal; }), 2) }}</th>
                </tr>
            </tbody>
        </table>

        <div class="clearfix">
            <div class="left-section">
                <div><strong>Accounting Section</strong></div>
                <table class="accounting-section-table">
                    <tr>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-label">Created by<br>{{ $voucher->prepared_by }}</div>
                        </td>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-label">Received by<br>{{ $voucher->given_to ?? '' }}</div>
                        </td>
                        <td>
                            <div class="signature-line"></div>
                            <div class="signature-label">Checked and Approved by<br>{{ $company->director ?? '' }}</div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="right-section">
                <div><strong>Account Code</strong></div>
                <table class="account-code-table">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $detail)
                        <tr>
                            <td>{{ $detail->account_code }}</td>
                            <td>{{ $detail->account_name }}</td>
                            <td style="text-align: right;">{{ number_format($detail->debit, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($detail->credit, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="account-code-total">
                            <th colspan="2">Total</th>
                            <th style="text-align: right;">{{ number_format($details->sum('debit'), 2) }}</th>
                            <th style="text-align: right;">{{ number_format($details->sum('credit'), 2) }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>