@extends('layouts.sorting')

@section('title', 'Help Centre')
@section('subtitle', 'Guides, FAQs, and contact support for the Santa Cruz Sorting Hub.')

@section('content')

@php
  $panel    = 'rounded-lg border border-border bg-card overflow-hidden mb-4';
  $panelHead = 'flex items-center justify-between gap-3 border-b border-border p-4';
@endphp

{{-- Quick links --}}
<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
  <div class="flex items-start gap-3 rounded-lg border border-border bg-card p-4 shadow-sm">
    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
    </div>
    <div>
      <div class="text-sm font-semibold">User Guide</div>
      <div class="mt-0.5 text-xs text-muted-foreground">Step-by-step instructions for every page</div>
    </div>
  </div>

  <div class="flex items-start gap-3 rounded-lg border border-border bg-card p-4 shadow-sm">
    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <div>
      <div class="text-sm font-semibold">Contact Support</div>
      <div class="mt-0.5 text-xs text-muted-foreground">Reach the Mimoo operations team</div>
    </div>
  </div>

  <div class="flex items-start gap-3 rounded-lg border border-border bg-card p-4 shadow-sm">
    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
    </div>
    <div>
      <div class="text-sm font-semibold">Report an Issue</div>
      <div class="mt-0.5 text-xs text-muted-foreground">Log a bug or operational problem</div>
    </div>
  </div>
</div>

{{-- FAQ --}}
<div class="{{ $panel }}">
  <div class="{{ $panelHead }}">
    <div>
      <h2 class="text-sm font-semibold">Frequently asked questions</h2>
      <div class="mt-0.5 text-xs text-muted-foreground">Common questions about the sorting hub system</div>
    </div>
  </div>
  <div class="divide-y divide-border">
    @foreach ([
      ['q' => 'How do I scan an incoming parcel?', 'a' => 'Go to Incoming Parcels, find the parcel in the drop-off queue, and click "Details" then "Scan parcel — mark as arrived". You can also use "Scan all arrived" to process the entire queue at once.'],
      ['q' => 'How do I assign a barangay to a parcel?', 'a' => 'Go to Sort Parcels, click "Details" on the parcel, then click "Determine barangay area →". The system will assign it to the correct route based on the delivery address.'],
      ['q' => 'How do I assign a rider to a barangay?', 'a' => 'Go to Rider Management, find the rider in the Active Riders table, click "Manage", then select the barangay from the dropdown and save. Each barangay can only have one rider at a time.'],
      ['q' => 'What does "Dispatch" mean?', 'a' => 'Dispatching sends all sorted parcels for a barangay to its assigned rider in one action, changing their status to "Assigned". The rider can then start deliveries.'],
      ['q' => 'How do I mark a parcel as delivered?', 'a' => 'Go to Delivery Monitoring, find the parcel and click "Details". If the parcel is out for delivery, you can mark it as delivered from the modal.'],
      ['q' => 'A rider is not showing on the board — why?', 'a' => 'The rider may not have an assigned barangay yet, or their account may not be approved. Check Rider Management to verify their status and barangay assignment.'],
    ] as $faq)
    <div class="group p-4 cursor-pointer select-none" onclick="this.querySelector('.faq-ans').classList.toggle('hidden')">
      <div class="flex items-center justify-between gap-3">
        <div class="text-sm font-medium">{{ $faq['q'] }}</div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-muted-foreground"><path d="m6 9 6 6 6-6"/></svg>
      </div>
      <div class="faq-ans hidden mt-2 text-[13px] text-muted-foreground leading-relaxed">{{ $faq['a'] }}</div>
    </div>
    @endforeach
  </div>
</div>

{{-- Contact --}}
<div class="{{ $panel }}">
  <div class="{{ $panelHead }}">
    <div>
      <h2 class="text-sm font-semibold">Contact & support</h2>
      <div class="mt-0.5 text-xs text-muted-foreground">Get in touch with the Mimoo operations team</div>
    </div>
  </div>
  <div class="grid grid-cols-1 gap-0 divide-y divide-border sm:grid-cols-2 sm:divide-x sm:divide-y-0">
    <div class="p-5">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-3">Operations hotline</div>
      <div class="text-sm font-semibold">+63 49 000 0000</div>
      <div class="mt-0.5 text-xs text-muted-foreground">Mon – Sat, 7:00 AM – 7:00 PM</div>
    </div>
    <div class="p-5">
      <div class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-3">Email support</div>
      <div class="text-sm font-semibold">ops@mimoo.com</div>
      <div class="mt-0.5 text-xs text-muted-foreground">Response within 1 business day</div>
    </div>
  </div>
</div>

@endsection
