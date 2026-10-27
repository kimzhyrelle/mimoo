@props(['seed' => 'MIMOO', 'size' => 96])
@php
  /*
   * Decorative 25×25 QR-style matrix, deterministic per $seed. It is a visual
   * placeholder only — swap for a real QR generator (e.g. simplesoftwareio/
   * simple-qrcode) when parcels need machine-readable labels.
   */
  $n = 25;
  $state = abs(crc32((string) $seed)) ?: 1;
  $modules = '';
  for ($y = 0; $y < $n; $y++) {
    for ($x = 0; $x < $n; $x++) {
      $inFinder = ($x < 8 && $y < 8) || ($x >= $n - 8 && $y < 8) || ($x < 8 && $y >= $n - 8);
      if ($inFinder) { continue; }
      $state = ($state * 1103515245 + 12345) & 0x7fffffff;
      if ((($state >> 16) & 1) === 1) { $modules .= 'M'.$x.' '.$y.'h1v1h-1z'; }
    }
  }
  $finders = [[0, 0], [$n - 7, 0], [0, $n - 7]];
@endphp
<svg {{ $attributes->merge(['width' => $size, 'height' => $size, 'viewBox' => '0 0 25 25', 'fill' => 'currentColor', 'shape-rendering' => 'crispEdges']) }}>
  <path d="{{ $modules }}"/>
  @foreach ($finders as $f)
    <path fill-rule="evenodd" d="M{{ $f[0] }} {{ $f[1] }}h7v7h-7zM{{ $f[0] + 1 }} {{ $f[1] + 1 }}v5h5v-5z"/>
    <rect x="{{ $f[0] + 2 }}" y="{{ $f[1] + 2 }}" width="3" height="3"/>
  @endforeach
</svg>
