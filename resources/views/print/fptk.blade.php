<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak FPTK - {{ $requisition->no_req }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 20px;
        }
        .container-fluid {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-borderless td, .table-borderless th {
            border: 0;
            padding: 8px;
        }
        .table-bordered {
            border: 1px solid #000;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000;
            padding: 8px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .line-top {
            border: 0;
            border-top: 2px solid #000;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .flex-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .col-label { width: 40%; font-weight: bold; }
        .col-colon { width: 5%; text-align: center; font-weight: bold; }
        .col-value { width: 55%; }
        
        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 5px 0;
        }
        .section-hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 5px 0 10px 0;
        }
        
        @media print {
            body { padding: 0; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <section id="header-kop">
        <div class="container-fluid">
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td class="text-left" width="20%">
                            <!-- If logo is missing, just show text -->
                            <h2>AVI</h2>
                        </td>
                        <td class="text-center" width="60%" style="font-size: 16px;">
                            <b>MANPOWER REQUISITION FORM</b><br>
                            <div style="margin-top: 5px; font-size: 14px;">
                                AVI/FRM/PROC.SP.01/01/00/{{ $requisition->no_req }}
                            </div>
                        </td>
                        <td class="text-right" width="20%"></td>
                    </tr>
                </tbody>
            </table>
            <hr class="line-top" />
        </div>
    </section>

    <section id="body-of-report">
        <div class="container-fluid">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <!-- Requirement Column -->
                        <td style="width: 50%; vertical-align: top; padding: 10px;">
                            <div class="section-title">REQUIREMENT</div>
                            <hr class="section-hr">
                            
                            <div class="flex-row">
                                <div class="col-label">Type</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->type }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Group</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->group }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Department</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->department }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Section</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->section }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Position</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->position }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Status</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->status ?: '-' }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Duration (contract/intern)</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->duration }} Month(s)</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Level</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->level }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Number Of Employee</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->cost_employee }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Start Date Required</div><div class="col-colon">:</div>
                                <div class="col-value">{{ $requisition->fulfilment_time ? \Carbon\Carbon::parse($requisition->fulfilment_time)->format('d-F-Y') : '-' }}</div>
                            </div>
                            
                            <br>
                            <div class="section-title">MAN SPECIFICATION</div>
                            <hr class="section-hr">
                            
                            <div class="flex-row">
                                <div class="col-label">Education / Major</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->education }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Maximum Age</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->max_age }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Min. Experience</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->min_experience }} Years</div>
                            </div>
                            
                            <br>
                            <div class="section-title">DETAIL REQUIREMENT</div>
                            <hr class="section-hr">
                            
                            <div class="flex-row">
                                <div class="col-label">Employee Cost Center</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->cost_center }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Requisition Object</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->objective }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Replacement Reason</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->reason }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Name Of Employee Out</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->employee_out }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Manpower Plan</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->manpower_plan }}</div>
                            </div>
                            <div class="flex-row">
                                <div class="col-label">Unplanned Reason</div><div class="col-colon">:</div><div class="col-value">{{ $requisition->unplanned_reason ?: '-' }}</div>
                            </div>
                        </td>

                        <!-- Job Specification Column -->
                        <td style="width: 50%; vertical-align: top; padding: 10px;">
                            <div class="section-title">JOB SPECIFICATION</div>
                            <hr class="section-hr">
                            
                            <div class="flex-row">
                                <div class="col-label" style="width: 30%">Technical Skill</div><div class="col-colon" style="width: 5%">:</div>
                                <div class="col-value" style="width: 65%">
                                    @if($requisition->technical_skill)
                                        @foreach($requisition->technical_skill as $index => $skill)
                                            {{ range('a', 'z')[$index] ?? '-' }}.) {{ trim($skill) }}<br>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <br>
                            <div class="flex-row">
                                <div class="col-label" style="width: 30%">Soft Skill</div><div class="col-colon" style="width: 5%">:</div>
                                <div class="col-value" style="width: 65%">
                                    @if($requisition->soft_skill)
                                        @foreach($requisition->soft_skill as $index => $skill)
                                            {{ range('a', 'z')[$index] ?? '-' }}.) {{ trim($skill) }}<br>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <br><br>
                            
                            <div class="section-title">JOB DESCRIPTION</div>
                            <hr class="section-hr">
                            
                            <div class="flex-row">
                                <div class="col-label" style="width: 30%">Description</div><div class="col-colon" style="width: 5%">:</div>
                                <div class="col-value" style="width: 65%">{{ $requisition->description }}</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h5 style="margin-top: 20px; font-size: 13px;">Cibinong, {{ $requisition->request_date ? \Carbon\Carbon::parse($requisition->request_date)->format('d-F-Y') : '-' }}</h5>
            
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td style="width: 33.33%; padding: 10px; text-align: center;">
                            <div style="font-weight: bold; margin-bottom: 20px;">Requested By,</div>
                            <div style="font-weight: bold; margin-top: 40px;">{{ $requisition->requester_name }}</div>
                            <div style="margin-top: 5px;">Date: {{ $requisition->request_date ? \Carbon\Carbon::parse($requisition->request_date)->format('d-m-Y') : '-' }}</div>
                        </td>
                        <td style="width: 33.33%; padding: 10px; text-align: center;">
                            <div style="font-weight: bold; margin-bottom: 20px;">Acknowledged By,</div>
                            <div style="font-weight: bold; margin-top: 40px;">{{ $requisition->manager ?: '-' }}</div>
                            <div style="margin-top: 5px;">Date: {{ $requisition->updated_at ? \Carbon\Carbon::parse($requisition->updated_at)->format('d-m-Y') : '-' }}</div>
                        </td>
                        <td style="width: 33.33%; padding: 10px; text-align: center;">
                            <div style="font-weight: bold; margin-bottom: 20px;">Approved By,</div>
                            <div style="font-weight: bold; margin-top: 40px;">{{ $requisition->division ?: '-' }}</div>
                            <div style="margin-top: 5px;">Date: {{ $requisition->updated_at ? \Carbon\Carbon::parse($requisition->updated_at)->format('d-m-Y') : '-' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td style="width: 50%; padding: 10px; text-align: center;">
                            <div style="font-weight: bold; margin-bottom: 20px;">Approved By,</div>
                            <div style="font-weight: bold; margin-top: 40px;">{{ $requisition->director ?: '-' }}</div>
                            <div style="margin-top: 5px;">Date: {{ $requisition->updated_at ? \Carbon\Carbon::parse($requisition->updated_at)->format('d-m-Y') : '-' }}</div>
                        </td>
                        <td style="width: 50%; padding: 10px; text-align: center;">
                            <div style="font-weight: bold; margin-bottom: 20px;">Approved By,</div>
                            <div style="font-weight: bold; margin-top: 40px;">HRD</div>
                            <div style="margin-top: 5px;">Date: {{ $requisition->updated_at ? \Carbon\Carbon::parse($requisition->updated_at)->format('d-m-Y') : '-' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 30px; font-size: 11px; font-style: italic;">
                *Form ini dicetak oleh sistem dan tidak memerlukan tanda tangan basah atau pengesahan lain jika sudah berstatus Approved.
            </div>
        </div>
    </section>

    <script type="text/javascript">
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
