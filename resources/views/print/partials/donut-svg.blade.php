@php
    $size = 64;
    $center = $size / 2;
    $radius = $size / 2 - 2;
    $paths = [
        "M $center,$center L $center," .
        ($center - $radius) .
        " A $radius,$radius 0 0,1 " .
        ($center + $radius) .
        ",$center Z",
        "M $center,$center L " .
        ($center + $radius) .
        ",$center A $radius,$radius 0 0,1 $center," .
        ($center + $radius) .
        ' Z',
        "M $center,$center L $center," .
        ($center + $radius) .
        " A $radius,$radius 0 0,1 " .
        ($center - $radius) .
        ",$center Z",
        "M $center,$center L " .
        ($center - $radius) .
        ",$center A $radius,$radius 0 0,1 $center," .
        ($center - $radius) .
        ' Z',
    ];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
    @foreach ($paths as $i => $d)
        <path d="{{ $d }}" fill="{{ $i < $filled ? '#1A5EA8' : '#f1f5f9' }}" stroke="#ffffff"
            stroke-width="1.5" />
    @endforeach
    <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="#cbd5e1"
        stroke-width="1.5" />
</svg>
