<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Stock Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20mm;
        }

        .header {
            text-align: center;
            margin-bottom: 20mm;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20mm;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 20mm;
        }

        .signature div {
            text-align: center;
        }

        .signature hr {
            width: 100%;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Transfer Stock Form</h1>
        <p>Date: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Size</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transferStockData as $stock)
            <tr>
                <td>{{ $stock->id }}</td>
                <td>{{ $stock->item }}</td>
                <td>{{ $stock->size }}</td>
                <td>{{ $stock->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>
            <p>Prepared by:</p>
            <hr>
        </div>
        <div>
            <p>Approved by:</p>
            <hr>
        </div>
    </div>
</body>

</html>