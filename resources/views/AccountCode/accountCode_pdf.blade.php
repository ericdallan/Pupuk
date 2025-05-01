<!DOCTYPE html>
<html>

<head>
    <title>Chart of Accounts</title>
    <style>
        /* Basic styling for the PDF */
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <h1>Chart of Accounts</h1>

    <table>
        <thead>
            <tr>
                <th>Account Type</th>
                <th>Account Section</th>
                <th>Account Subsection</th>
                <th>Account Name</th>
                <th>Account Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hierarkiAkun as $accountType => $accountSections)
            @php
            $formattedAccountType = str_replace('_', ' ', $accountType);
            $firstSection = true;
            @endphp
            @foreach ($accountSections as $accountSection => $accountSubsections)
            @php
            $firstSubsection = true;
            @endphp
            @if (is_array($accountSubsections))
            @foreach ($accountSubsections as $accountSubsection => $accountNames)
            @foreach ($accountNames as $accountName)
            <tr>
                @if ($firstSection)
                <td style="font-weight: bold; color: black; text-transform: uppercase;">{{ $formattedAccountType }}</td>
                @php
                $firstSection = false;
                @endphp
                @else
                <td></td>
                @endif
                @if ($firstSubsection)
                <td style="font-weight: bold;">{{ $accountSection }}</td>
                @php
                $firstSubsection = false;
                @endphp
                @else
                <td></td>
                @endif
                @if (is_array($accountNames))
                @if (is_array($accountName))
                <td>{{ $accountSubsection }}</td>
                <td>{{ $accountName[0] }}</td>
                <td>{{ $accountName[1] }}</td>
                @else
                <td>{{ $accountSubsection }}</td>
                <td>{{ $accountName }}</td>
                <td></td>
                @endif
                @else
                <td>{{ $accountName }}</td>
                <td></td>
                @endif
            </tr>
            @endforeach
            @endforeach
            @else
            @foreach ($accountSubsections as $accountName)
            <tr>
                @if ($firstSection)
                <td style="font-weight: bold; color: black; text-transform: uppercase;">{{ $formattedAccountType }}</td>
                @php
                $firstSection = false;
                @endphp
                @else
                <td></td>
                @endif
                @if ($firstSubsection)
                <td style="font-weight: bold;">{{ $accountSection }}</td>
                @php
                $firstSubsection = false;
                @endphp
                @else
                <td></td>
                @endif
                @if (is_array($accountName))
                <td></td>
                <td>{{ $accountName[0] }}</td>
                <td>{{ $accountName[1] }}</td>
                @else
                <td></td>
                <td>{{ $accountName }}</td>
                <td></td>
                @endif
            </tr>
            @endforeach
            @endif
            @endforeach
            @endforeach
        </tbody>
    </table>
</body>

</html>