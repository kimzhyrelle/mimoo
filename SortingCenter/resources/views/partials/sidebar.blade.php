<aside class="flex w-60 shrink-0 flex-col border-r border-sidebar-border bg-sidebar px-3 py-5 text-sidebar-foreground">

    {{-- Logo --}}
    <div class="mb-5 px-3 pb-4 border-b border-sidebar-border">
      <div class="text-[13px] font-bold tracking-[.18em] text-white uppercase" style="font-family:'Syncopate',sans-serif">Sorting Center</div>
    </div>

    <nav class="flex flex-1 flex-col gap-0.5 overflow-y-auto">
      @php
        $navItem  = 'flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium transition-colors';
        $active   = 'bg-primary text-white';
        $inactive = 'text-white/65 hover:text-white hover:bg-white/10';
        $isPreview = request()->routeIs('preview.*');
        $section  = 'mt-4 mb-1 px-3 text-[10px] font-bold tracking-[.12em] uppercase text-white/35';
      @endphp

      <div class="{{ $section }}">Overview</div>

      <a href="{{ $isPreview ? route('preview.dashboard') : route('dashboard') }}"
         class="{{ $navItem }} {{ request()->routeIs('dashboard') || request()->routeIs('preview.dashboard') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard
      </a>

      <div class="{{ $section }}">Parcels</div>

      <a href="{{ $isPreview ? route('preview.parcels.incoming') : route('parcels.incoming') }}"
         class="{{ $navItem }} {{ request()->routeIs('parcels.incoming') || request()->routeIs('preview.parcels.incoming') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M21 8v8a2 2 0 0 1-1 1.73l-7 4a2 2 0 0 1-2 0l-7-4A2 2 0 0 1 3 16V8"/><path d="M3.27 6.96 12 12l8.73-5.04"/><path d="M12 22V12"/></svg>
        Incoming Parcels
      </a>

      <a href="{{ $isPreview ? route('preview.parcels.sort') : route('parcels.sort') }}"
         class="{{ $navItem }} {{ request()->routeIs('parcels.sort') || request()->routeIs('preview.parcels.sort') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        Sort Parcels
      </a>

      <a href="{{ route('delivery.assignment') }}"
         class="{{ $navItem }} {{ request()->routeIs('delivery.assignment') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-4 0v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
        Delivery Assignment
      </a>

      <a href="{{ $isPreview ? route('preview.delivery.monitoring') : route('delivery.monitoring') }}"
         class="{{ $navItem }} {{ request()->routeIs('delivery.monitoring') || request()->routeIs('preview.delivery.monitoring') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0 0 21 18.618V7.618a1 1 0 0 0-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        Delivery Monitoring
      </a>

      <div class="{{ $section }}">Team</div>

      <a href="{{ $isPreview ? route('preview.riders.index') : route('riders.index') }}"
         class="{{ $navItem }} {{ request()->routeIs('riders.index') || request()->routeIs('preview.riders.index') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><circle cx="8" cy="8" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"/><circle cx="17.5" cy="9.5" r="2.5"/><path d="M22 21v-1.5a4 4 0 0 0-3-3.87"/></svg>
        Rider Management
      </a>

      <a href="{{ route('reports.index') }}"
         class="{{ $navItem }} {{ request()->routeIs('reports.index') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M4 21V8a2 2 0 0 1 2-2h5l2 2h5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg>
        Reports
      </a>

      <a href="{{ route('chat.index') }}"
         class="{{ $navItem }} {{ request()->routeIs('chat.index') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Chat / Messaging
      </a>

      <a href="{{ $isPreview ? route('preview.account.index') : route('account.index') }}"
         class="{{ $navItem }} {{ request()->routeIs('account.index') || request()->routeIs('preview.account.index') ? $active : $inactive }}">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        Account
      </a>

      @if ($isPreview)
        <a href="{{ route('preview.index') }}" class="{{ $navItem }} {{ $inactive }}">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-80"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
          Back to preview
        </a>
      @endif
    </nav>

    {{-- User info --}}
    <div class="mt-4 flex items-center gap-2.5 border-t border-sidebar-border px-2 pt-4">
      <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-white">LG</div>
      <div class="leading-tight min-w-0">
        <div class="text-[12.5px] font-semibold text-white truncate">Laguna Hub</div>
        <div class="text-[11px] text-white/50">Sorting Staff</div>
      </div>
    </div>

</aside>
