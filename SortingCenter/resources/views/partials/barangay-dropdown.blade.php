{{--
  Reusable barangay filter dropdown.
  Usage: @include('partials.barangay-dropdown', ['allAreas' => $allAreas, 'area_id' => $area_id])
  The surrounding <form method="GET"> must include this partial.
--}}
<div style="position:relative;display:inline-block;">
  <select
    name="area_id"
    onchange="this.form.submit()"
    style="appearance:none;-webkit-appearance:none;border:1px solid var(--line);border-radius:8px;padding:7px 32px 7px 12px;font-size:12.5px;font-family:inherit;background:#fff;color:var(--text-900);cursor:pointer;outline:none;min-width:160px;font-weight:600;">
    <option value="">All barangays</option>
    @foreach ($allAreas ?? $areas ?? [] as $a)
      <option value="{{ $a->id }}" {{ (isset($area_id) && $area_id == $a->id) ? 'selected' : '' }}>
        {{ $a->name }}
      </option>
    @endforeach
  </select>
  <svg style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#9a93ac;" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
</div>
