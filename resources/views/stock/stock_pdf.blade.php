<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pemindahan Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 20px;
            /* Use px for Dompdf consistency */
            color: #000;
        }

        .container {
            width: 100%;
            max-width: 595pt;
            /* A4 width in points */
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header .logo {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .header .company-info {
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
            margin-bottom: 15px;
        }

        .header .form-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px;
        }

        .form-details {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            font-size: 10pt;
            padding: 8px;
            border-radius: 4px;
        }

        .form-details-row {
            display: table-row;
        }

        .form-details-cell {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }

        .form-details p {
            margin: 0;
            padding: 4px 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-details p strong {
            width: 40%;
            font-weight: bold;
        }

        .form-details p span {
            width: 60%;
            text-align: left;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid rgb(0, 0, 0);
            padding: 5px 8px;
            font-size: 9pt;
            text-align: left;
        }

        th {
            font-weight: bold;
            text-align: center;
        }

        .signature {
            display: table;
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table-row;
        }

        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 9pt;
            padding: 0 10px;
        }

        .signature p.label {
            margin: 0;
            padding: 4px;
            border-radius: 2px;
        }

        .signature p.name {
            margin: 4px 0 0;
            min-height: 20px;
            /* Ensure space for empty names */
        }

        .signature hr {
            border: none;
            border-top: 1px solid #000;
            width: 85%;
            margin: 75px auto 6px;
            /* Reduced for better spacing */
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            @if ($companyLogo)
            <div class="logo"><img src="{{ public_path('storage/' . $companyLogo) }}" alt="{{ $company->company_name }} Logo" style="max-width: 125px; height: 100px;"></div>
            @endif
            <div class="company-info">
                <div>{{ strtoupper($company->company_name ?? '') }}</div>
                <div>{{ $company->address ?? '' }}</div>
                <div>No. Telp {{ $company->phone ?? '' }} | Email: {{ $company->email ?? '' }}</div>
            </div>
            <div class="form-title">Formulir Pemindahan Barang</div>
        </div>

        <div class="form-details">
            <div class="form-details-row">
                <div class="form-details-cell">
                    <p><strong>Nomor Dokumen:</strong></p>
                    <p><strong>Tanggal:</strong></p>
                </div>
                <div class="form-details-cell">
                    <p><strong>Dari Gudang:</strong></p>
                    <p><strong>Kepada:</strong></p>
                </div>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Ukuran/Satuan</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= 15; $i++) <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $items[$i-1]['name'] ?? '' }}</td>
                    <td>{{ $items[$i-1]['unit'] ?? '' }}</td>
                    <td>{{ $items[$i-1]['quantity'] ?? '' }}</td>
                    <td>{{ $items[$i-1]['notes'] ?? '' }}</td>
                    </tr>
                    @endfor
            </tbody>
        </table>

        <div class="signature">
            <div class="signature-row">
                <div class="signature-cell">
                    <p class="label">Disetujui oleh:</p>
                    <hr>
                    <p class="name"></p>
                </div>
                <div class="signature-cell">
                    <p class="label">Dibuat oleh:</p>
                    <hr>
                    <p class="name"></p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>