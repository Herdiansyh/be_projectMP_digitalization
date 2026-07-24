<h1>{{ $subject->name }}</h1>
<div class="meta">NPK {{ $subject->npk }} — {{ ucfirst($type) }}</div>

<table class="detail">
    <tr>
        <td class="label">Department</td>
        <td>{{ $subject->department?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Section</td>
        <td>{{ $subject->section?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Area / Line / Station</td>
        <td>{{ $subject->area?->name ?? '-' }} / {{ $subject->line?->name ?? '-' }} /
            {{ $subject->station?->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Join Date</td>
        <td>{{ optional($subject->join_date)->format('d F Y') ?? '-' }}</td>
    </tr>
    <tr>
        <td class="label">Contract</td>
        <td>{{ optional($subject->start_contract)->format('d F Y') ?? '-' }} —
            {{ optional($subject->end_contract)->format('d F Y') ?? '-' }}</td>
    </tr>
</table>

<h2>Competency by Station</h2>

@if (empty($stationSummary))
    <p style="color:#94a3b8;">No approved assessment yet.</p>
@else
    <div class="stations">
        @foreach ($stationSummary as $s)
            @php $filled = min(4, max(0, round($s['final_score']))); @endphp
            <div class="station-card">
                @include('print.partials.donut-svg', ['filled' => $filled])
                <div style="font-size:11px; font-weight:600; margin-top:4px;">{{ $s['station_name'] }}</div>
                <div style="font-size:10px; color:#94a3b8;">{{ $s['period_label'] }}</div>
                <div style="font-size:11px; font-weight:700; color:#1A5EA8;">{{ $filled }}/4</div>
            </div>
        @endforeach
    </div>
@endif
