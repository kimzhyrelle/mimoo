@extends('layouts.sorting')

@section('title', 'COD & Remittance')

@section('content')
  <x-page-head eyebrow="Cash Operations" title="COD Reconciliation & Remittance" sub="Match collected cash on delivery, record rider remittances and reconcile balances.">
    <a href="{{ route('reports.index') }}" class="btn-soft"><x-icon name="chart" :size="16" />Reports</a>
  </x-page-head>

  @include('partials.flash')

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.4fr_1fr]">
    <section class="card overflow-hidden">
      <div class="border-b border-border p-5">
        <h2 class="h-display text-[17px]">Unremitted COD</h2>
        <p class="text-[12px] text-muted-foreground">Grouped by rider. Expected is the sum of cash collected on delivered parcels.</p>
      </div>
      <div class="max-h-[70vh] divide-y divide-border overflow-y-auto">
        @forelse ($groups as $group)
          <div class="p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="font-semibold">{{ $group['rider']->name }}</div>
                <div class="text-[11.5px] text-muted-foreground">{{ $group['rider']->plate_no }} – {{ $group['parcels']->count() }} parcel(s)</div>
              </div>
              <div class="text-right">
                <div class="text-[11px] text-muted-foreground">Expected</div>
                <div class="h-display text-[18px]">₱{{ number_format($group['expected'], 2) }}</div>
              </div>
            </div>
            <form method="POST" action="{{ route('cod.remittances.store') }}" class="mt-3 grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
              @csrf
              <input type="hidden" name="rider_id" value="{{ $group['rider']->id }}">
              <label class="text-[11px] font-semibold text-muted-foreground">Collected ₱
                <input name="collected" type="number" step="0.01" min="0" required value="{{ number_format($group['expected'], 2, '.', '') }}" class="field mt-1 w-full py-1.5">
              </label>
              <label class="text-[11px] font-semibold text-muted-foreground">Reference
                <input name="reference" required minlength="3" maxlength="80" class="field mt-1 w-full py-1.5" placeholder="OR / GCash ref">
              </label>
              <button type="submit" class="btn-primary self-end">Record</button>
            </form>
          </div>
        @empty
          <div class="p-8 text-center text-[13px] text-muted-foreground">No unremitted COD collections. All cash is reconciled.</div>
        @endforelse
      </div>
      @if ($unassigned->isNotEmpty())
        <div class="border-t border-border bg-amber-50 p-4 text-[12px] text-amber-800">
          {{ $unassigned->count() }} delivered COD parcel(s) have no rider. Assign a rider before remitting.
        </div>
      @endif
    </section>

    <section class="card overflow-hidden">
      <div class="border-b border-border p-5">
        <h2 class="h-display text-[17px]">Recent Remittances</h2>
        <p class="text-[12px] text-muted-foreground">Confirming updates the rider COD balance and writes an audit entry.</p>
      </div>
      <div class="divide-y divide-border">
        @forelse ($remittances as $remittance)
          <div class="flex flex-wrap items-center justify-between gap-3 p-4">
            <div>
              <div class="text-[13px] font-semibold">{{ $remittance->rider?->name }}</div>
              <div class="text-[11.5px] text-muted-foreground">{{ $remittance->reference }} · {{ $remittance->created_at->format('M j, g:i A') }}</div>
              <div class="text-[11.5px] text-muted-foreground">Expected ₱{{ number_format((float) $remittance->amount, 2) }} · Collected ₱{{ number_format((float) $remittance->collected, 2) }}</div>
            </div>
            <div class="text-right">
              @if ($remittance->status !== 'confirmed')
                <form method="POST" action="{{ route('cod.remittances.confirm', $remittance) }}" class="mt-2">
                  @csrf
                  <button type="submit" class="btn-soft px-2 py-1 text-[11px]">Confirm</button>
                </form>
              @else
                <div class="mt-1 text-[10.5px] text-muted-foreground">{{ optional($remittance->confirmed_at)->format('M j, g:i A') }}</div>
              @endif
            </div>
          </div>
        @empty
          <div class="p-8 text-center text-[13px] text-muted-foreground">No remittances recorded yet.</div>
        @endforelse
      </div>
    </section>
  </div>
@endsection
