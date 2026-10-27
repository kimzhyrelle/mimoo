{{--
  Reusable barangay filter dropdown.
  Usage: @include('partials.barangay-dropdown', ['allAreas' => $allAreas, 'area_id' => $area_id])
  The surrounding <form method="GET"> must include this partial.
--}}
<select name="area_id" onchange="this.form.submit()" class="field w-auto min-w-44 cursor-pointer py-2.5 pr-8 text-[13px]">
  <option value="">All barangays</option>
  @foreach ($allAreas ?? $areas ?? [] as $a)
    <option value="{{ $a->id }}" {{ (isset($area_id) && $area_id == $a->id) ? 'selected' : '' }}>{{ $a->name }}</option>
  @endforeach
</select>
