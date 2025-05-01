<!DOCTYPE html>
<html>

<head>
    <title>Laporan Buku Besar Pembantu - Utang Usaha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        h1,
        h2 {
            text-align: center;
        }

        .invoice-row {
            font-weight: bold;
        }

        .payment-row td {
            padding-left: 20px;
            /* Indent payment rows */
            background-color: #f8f9fa;
            /* Light background */
        }

        .invoice-section {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <h1>Laporan Buku Besar Pembantu</h1>
    <h2>Utang Usaha - {{$storeName}}</h2>

    @if ($is_all_invoices)
    @php
    $groupedTransactions = $transactions->groupBy('invoice_id');
    @endphp
    @forelse ($groupedTransactions as $invoiceId => $trans)
    <div class="invoice-section">
        <h3>Invoice: {{ $trans->first()->invoice ?: 'Tanpa Invoice' }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Nomor Invoice</th>
                    <th>Nomor Voucher Pembelian</th>
                    <th>Nomor Voucher Pelunasan</th>
                    <th>Tanggal</th>
                    <th>Saldo Awal</th>
                    <th>Saldo Akhir</th>
                    <th>Sisa Saldo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($trans as $transaction)
                <tr class="{{ $transaction->type === 'invoice' ? 'invoice-row' : 'payment-row' }}">
                    <td>{{ $transaction->invoice ?? '-' }}</td>
                    <td>{{ $transaction->nomor_voucher_pembelian ?? '-' }}</td>
                    <td>{{ $transaction->nomor_voucher_pelunasan ?? '-' }}</td>
                    <td>{{ $transaction->voucher_date ? \Carbon\Carbon::parse($transaction->voucher_date)->format('d F Y') : '-' }}</td>
                    <td>{{ number_format($transaction->saldo_awal ?? 0, 2, ',', '.') }}</td>
                    <td>{{ $transaction->saldo_akhir === '-' ? '-' : number_format($transaction->saldo_akhir, 2, ',', '.') }}</td>
                    <td>{{ number_format($transaction->sisa_saldo ?? 0, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <p>Tidak ada transaksi ditemukan.</p>
    @endforelse
    @else
    <table>
        <thead>
            <tr>
                <th>Nomor Invoice</th>
                <th>Nomor Voucher Pembelian</th>
                <th>Nomor Voucher Pelunasan</th>
                <th>Tanggal</th>
                <th>Saldo Awal</th>
                <th>Saldo Akhir</th>
                <th>Sisa Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
            <tr class="{{ $transaction->type === 'invoice' ? 'invoice-row' : 'payment-row' }}">
                <td>{{ $transaction->invoice ?? '-' }}</td>
                <td>{{ $transaction->nomor_voucher_pembelian ?? '-' }}</td>
                <td>{{ $transaction->nomor_voucher_pelunasan ?? '-' }}</td>
                <td>{{ $transaction->voucher_date ? \Carbon\Carbon::parse($transaction->voucher_date)->format('d F Y') : '-' }}</td>
                <td>{{ number_format($transaction->saldo_awal ?? 0, 2, ',', '.') }}</td>
                <td>{{ $transaction->saldo_akhir === '-' ? '-' : number_format($transaction->saldo_akhir, 2, ',', '.') }}</td>
                <td>{{ number_format($transaction->sisa_saldo ?? 0, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Tidak ada transaksi ditemukan untuk invoice {{ $invoice_number }}.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</body>

</html>