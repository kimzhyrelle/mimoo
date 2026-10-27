@extends('layouts.sorting')

@section('title', 'Zones & Shipping Rates')

@section('content')
  <x-page-head eyebrow="Checkout Configuration" title="Zones & Shipping Rates" sub="Buyer and seller checkout quotes come from these serviceable zones and weight tiers.">
    <a href="{{ route('support.index') }}" class="btn-soft"><x-icon name="headset" :size="16" />Support</a>
  </x-page-head>

  @include('partials.flash')

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_1.6fr]">
    <section class="card h-fit p-6">
      <h2 class="h-display mb-4 text-[17px]">Add Zone</h2>
      <form method="POST" action="{{ route('zones.store') }}" class="space-y-3">
        @csrf
        <label class="block text-[12px] font-semibold text-muted-foreground">Zone name
          <input name="name" required maxlength="80" class="field mt-1 w-full" placeholder="e.g. Metro Manila">
        </label>
        <label class="block text-[12px] font-semibold text-muted-foreground">Region
          <input name="region" maxlength="120" class="field mt-1 w-full" placeholder="e.g. NCR">
        </label>
        <label class="block text-[12px] font-semibold text-muted-foreground">Areas / barangays
          <textarea name="areas" rows="3" class="field mt-1 w-full" placeholder="Comma-separated: Makati, Taguig, Pasig"></textarea>
        </label>
        <label class="flex items-center gap-2 text-[12px] text-muted-foreground"><input type="checkbox" name="is_serviceable" value="1" checked class="rounded border-border"> Serviceable at checkout</label>
        <button type="submit" class="btn-primary w-full">Add Zone</button>
      </form>
    </section>

    <div class="space-y-5">
      @forelse ($zones as $zone)
        <section class="card p-6">
          <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
              <h2 class="h-display text-[18px]">{{ $zone->name }}</h2>
              <p class="text-[12px] text-muted-foreground">{{ $zone->region ?: 'No region' }} — {{ implode(', ', $zone->areas ?? []) ?: 'No areas listed' }}</p>
            </div>
            <span class="pill-{{ $zone->is_serviceable ? 'green' : 'slate' }} px-2.5 py-1 text-[11px] font-semibold">{{ $zone->is_serviceable ? 'Serviceable' : 'Not serviceable' }}</span>
          </div>

          <form method="POST" action="{{ route('zones.update', $zone) }}" class="mb-5 grid gap-3 sm:grid-cols-2">
            @csrf
            <label class="text-[11.5px] font-semibold text-muted-foreground">Name<input name="name" value="{{ $zone->name }}" required class="field mt-1 w-full"></label>
            <label class="text-[11.5px] font-semibold text-muted-foreground">Region<input name="region" value="{{ $zone->region }}" class="field mt-1 w-full"></label>
            <label class="text-[11.5px] font-semibold text-muted-foreground sm:col-span-2">Areas<input name="areas" value="{{ implode(', ', $zone->areas ?? []) }}" class="field mt-1 w-full"></label>
            <label class="flex items-center gap-2 text-[11.5px] text-muted-foreground"><input type="checkbox" name="is_serviceable" value="1" @checked($zone->is_serviceable) class="rounded border-border"> Serviceable</label>
            <div class="flex justify-end"><button type="submit" class="btn-soft">Save Zone</button></div>
          </form>

          <div class="overflow-x-auto rounded-xl border border-border">
            <table class="tbl w-full text-left text-[12.5px]" data-row-details="off">
              <thead><tr><th>Weight tier</th><th>Base fee</th><th>Per kg</th><th>Sample 3 kg</th><th></th></tr></thead>
              <tbody>
                @forelse ($zone->rates as $rate)
                  <tr>
                    <td>{{ rtrim(rtrim(number_format((float) $rate->min_weight_kg, 2, '.', ''), '0'), '.') }}–{{ $rate->max_weight_kg ? rtrim(rtrim(number_format((float) $rate->max_weight_kg, 2, '.', ''), '0'), '.') : '∞' }} kg</td>
                    <td>₱{{ number_format((float) $rate->base_fee, 2) }}</td>
                    <td>₱{{ number_format((float) $rate->per_kg_fee, 2) }}</td>
                    <td class="font-semibold">₱{{ number_format($rate->feeForWeight(3), 2) }}</td>
                    <td class="text-right">
                      <form method="POST" action="{{ route('rates.update', [$zone, $rate]) }}" class="flex items-center gap-1">
                        @csrf
                        <input name="base_fee" type="number" step="0.01" min="0" value="{{ $rate->base_fee }}" class="field w-20 py-1" aria-label="Base fee">
                        <input name="per_kg_fee" type="number" step="0.01" min="0" value="{{ $rate->per_kg_fee }}" class="field w-20 py-1" aria-label="Per kg fee">
                        <input name="min_weight_kg" type="number" step="0.01" min="0.01" value="{{ $rate->min_weight_kg }}" class="field w-20 py-1" aria-label="Min kg">
                        <input name="max_weight_kg" type="number" step="0.01" min="0.01" value="{{ $rate->max_weight_kg }}" placeholder="No max" class="field w-20 py-1" aria-label="Max kg">
                        <button type="submit" class="btn-soft px-2 py-1">Save</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="py-4 text-center text-muted-foreground">No rate tiers yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <form method="POST" action="{{ route('rates.store', $zone) }}" class="mt-3 grid gap-2 sm:grid-cols-[1fr_1fr_1fr_1fr_auto]">
            @csrf
            <input name="base_fee" type="number" step="0.01" min="0" required placeholder="Base ₱" class="field py-1.5">
            <input name="per_kg_fee" type="number" step="0.01" min="0" required placeholder="Per kg ₱" class="field py-1.5">
            <input name="min_weight_kg" type="number" step="0.01" min="0.01" required placeholder="Min kg" class="field py-1.5">
            <input name="max_weight_kg" type="number" step="0.01" min="0.01" placeholder="Max kg" class="field py-1.5">
            <button type="submit" class="btn-primary">Add Tier</button>
          </form>
        </section>
      @empty
        <section class="card p-10 text-center text-muted-foreground">No zones yet. Add one to enable checkout quotes.</section>
      @endforelse
    </div>
  </div>
@endsection
