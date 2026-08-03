<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Competence Matrix</title>
    <style>
        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            padding: 10px;
        }

        .cm-page {
            page-break-after: always;
        }

        .cm-page:last-child {
            page-break-after: auto;
        }

        .cm-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .cm-header .logo {
            height: 40px;
        }

        .cm-header .doc-no {
            font-size: 11px;
            text-align: right;
        }

        .cm-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 2px 0 10px;
        }

        .cm-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .cm-meta table td {
            padding: 1px 6px 1px 0;
            vertical-align: top;
        }

        .cm-meta table td.label {
            width: 90px;
        }

        table.matrix {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.matrix th,
        table.matrix td {
            border: 1px solid #94a3b8;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
        }

        table.matrix thead th.station-band {
            background: #326735;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
        }

        table.matrix thead th.std-row {
            background: #ffe066;
            font-weight: bold;
        }

        table.matrix thead th.label-cell {
            font-size: 10px;
            writing-mode: horizontal-tb;
        }

        table.matrix thead th.corner {
            background: #cfe8cf;
        }

        table.matrix th.no-col {
            width: 26px;
        }

        table.matrix td.name-col,
        table.matrix th.name-col {
            text-align: left;
            width: 150px;
        }

        table.matrix td.npk-col,
        table.matrix th.npk-col {
            width: 55px;
        }

        table.matrix td.group-col,
        table.matrix th.group-col {
            width: 40px;
        }

        table.matrix td.sta-col,
        table.matrix th.sta-col {
            width: 30px;
        }

        table.matrix tbody td {
            height: 28px;
        }

        table.matrix td.remarks-col,
        table.matrix th.remarks-col {
            text-align: left;
            width: 140px;
        }

        table.matrix svg {
            display: block;
            margin: 0 auto;
        }

        .cm-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            font-size: 10px;
        }

        .cm-legend {
            width: 60%;
        }

        .cm-legend .legend-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 2px;
        }

        .cm-signoff table {
            border-collapse: collapse;
        }

        .cm-signoff td {
            border: 1px solid #94a3b8;
            padding: 4px 10px;
            text-align: center;
            font-size: 10px;
        }

        .cm-signoff .sign-space {
            height: 50px;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>

<body>
    @foreach ($lineGroups as $group)
        <div class="cm-page">
            <div class="cm-header">
                <img class="logo" src="{{ asset('favicon.png') }}" alt="Astra Visteon">
                <div class="doc-no">Doc. No : 08 - PROD - 001</div>
            </div>
            <div class="cm-title">COMPETENCE MATRIX</div>

            <div class="cm-meta">
                <table>
                    <tr>
                        <td class="label">DEPARTEMEN</td>
                        <td>: {{ $group['department']?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">SEKSI</td>
                        <td>: {{ $group['section']?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">LINE</td>
                        <td>: {{ $group['line']->name }}</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td class="label">TGL. EVALUASI</td>
                        <td>: {{ $evaluatedAt->format('d-M-y') }}</td>
                    </tr>
                </table>
            </div>

            <table class="matrix">
                <thead>
                    <tr>
                        <th class="no-col corner" rowspan="3">NO</th>
                        <th class="name-col corner" rowspan="3">NAMA</th>
                        <th class="npk-col corner" rowspan="3">NRP</th>
                        <th class="group-col corner" rowspan="3">Group</th>
                        <th class="sta-col corner"></th>
                        <th class="station-band" colspan="{{ $group['stations']->count() }}">STATION</th>
                        <th class="remarks-col corner" rowspan="3">Remarks</th>
                    </tr>
                    <tr>
                        <th class="sta-col label-cell">STA</th>
                        @foreach ($group['stations'] as $station)
                            <th class="sta-col">{{ $station->name }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th class="sta-col label-cell std-row">STD</th>
                        @foreach ($group['stations'] as $station)
                            <th class="sta-col std-row">
                                @include('print.partials.donut-svg', [
                                    'filled' => $group['std_level'],
                                    'size' => 28,
                                ])
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group['rows'] as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="name-col">{{ $row['subject']->name }}</td>
                            <td>{{ $row['subject']->npk }}</td>
                            <td>{{ $row['subject']->group ?? '-' }}</td>
                            <td>{{ $row['line_abbr'] }}</td>
                            @foreach ($row['cells'] as $cell)
                                <td>
                                    @include('print.partials.donut-svg', [
                                        'filled' => $cell['filled'] ?? 1,
                                        'size' => 28,
                                    ])
                                </td>
                            @endforeach
                            <td class="remarks-col"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cm-footer">
                <div class="cm-legend">
                    <strong>Keterangan:</strong>
                    <div class="legend-row">
                        @include('print.partials.donut-svg', ['filled' => 0, 'size' => 18])
                        <span>1. Operator baru. Belum memiliki informasi apapun untuk menjalankan station</span>
                    </div>
                    <div class="legend-row">
                        @include('print.partials.donut-svg', ['filled' => 1, 'size' => 18])
                        <span>2. Mengetahui product dan process knowledge. Sudah bisa mengoperasikan station sesuai
                            dengan WI</span>
                    </div>
                    <div class="legend-row">
                        @include('print.partials.donut-svg', ['filled' => 2, 'size' => 18])
                        <span>3. Bisa mengidentifikasi sendiri critical part dalam proses di stationnya</span>
                    </div>
                    <div class="legend-row">
                        @include('print.partials.donut-svg', ['filled' => 3, 'size' => 18])
                        <span>4. Mampu mencapai 90% target yang ditentukan</span>
                    </div>
                    <div class="legend-row">
                        @include('print.partials.donut-svg', ['filled' => 4, 'size' => 18])
                        <span>5. Mampu mencapai target kuantitas dan juga bisa mengajari operator lain</span>
                    </div>
                </div>

                <div class="cm-signoff">
                    <table>
                        <tr>
                            <td style="font-weight:bold;">Approved by</td>
                            <td style="font-weight:bold;">Prepared by</td>
                        </tr>
                        <tr>
                            <td class="sign-space"></td>
                            <td class="sign-space"></td>
                        </tr>
                        <tr>
                            <td>Dept. Head</td>
                            <td>Sect. Head</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        window.print();
    </script>
</body>

</html>
