<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>MP Detail - {{ $subject->name }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            padding: 24px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .meta {
            color: #64748b;
            margin-bottom: 16px;
        }

        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.detail td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        table.detail td.label {
            color: #64748b;
            width: 160px;
            font-weight: 600;
        }

        .stations {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .station-card {
            text-align: center;
            width: 100px;
        }

        .station-card svg {
            display: block;
            margin: 0 auto;
        }

        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>

<body>
    @include('print.partials.manpower-content', [
        'subject' => $subject,
        'type' => $type,
        'stationSummary' => $stationSummary,
    ])

    <script>
        window.print();
    </script>
</body>

</html>
