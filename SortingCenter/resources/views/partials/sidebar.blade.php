@php
  $isPreview = request()->routeIs('preview.*');

  // label, icon, route, preview route (null = no preview), extra routes that keep the item highlighted
  $nav = [
    ['Dashboard',             'dashboard', 'dashboard',           'preview.dashboard',           []],
    ['Incoming Parcels',      'inbox',     'parcels.incoming',    'preview.parcels.incoming',    []],
    ['Sort & Assign',         'split',     'parcels.sort',        'preview.parcels.sort',        ['delivery.assignment']],
    ['Delivery Monitoring',   'route',     'delivery.monitoring', 'preview.delivery.monitoring', []],
    ['Riders',                'bike',      'riders.index',        'preview.riders.index',        []],
    ['Branches & Checklists', 'checklist', 'branches.index',      null,                          []],
    ['Zones & Rates',         'sliders',   'zones.index',         null,                          []],
    ['COD & Remittance',      'chart',     'cod.index',           null,                          []],
    ['Reports',               'chart',     'reports.index',       null,                          []],
    ['Support',               'headset',   'support.index',       null,                          ['support.create']],
    ['Messages',              'message',   'chat.index',          null,                          []],
    ['Account',               'account',   'account.index',       'preview.account.index',       []],
  ];
@endphp
<div id="sidebarBackdrop" class="sidebar-backdrop"></div>
<aside id="appSidebar" aria-label="Sidebar" class="app-sidebar flex h-screen w-64 shrink-0 flex-col bg-sidebar px-4 py-5 text-sidebar-foreground">

  {{-- Brand --}}
  <div class="mb-7 flex items-center justify-between gap-2">
    <a href="{{ $isPreview ? route('preview.dashboard') : route('dashboard') }}" class="flex items-center gap-3 px-2">
      <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-black/20">
        <x-icon name="truck" :size="20" />
      </span>
      <span class="leading-tight">
        <span class="block font-display text-[17px] font-bold tracking-tight text-white">Mimoo</span>
        <span class="block text-[10px] font-semibold uppercase tracking-[.16em] text-white/60">Sorting Center</span>
      </span>
    </a>
    <button type="button" id="sidebarClose" aria-label="Close navigation"
            class="flex size-9 shrink-0 items-center justify-center rounded-full text-white/70 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- Navigation --}}
  <nav class="flex flex-1 flex-col gap-1 overflow-y-auto" aria-label="Main navigation">
    @foreach ($nav as [$label, $icon, $route, $previewRoute, $also])
      @php
        $active  = request()->routeIs($route) || ($previewRoute && request()->routeIs($previewRoute)) || request()->routeIs($also);
        $href    = ($isPreview && $previewRoute) ? route($previewRoute) : route($route);
      @endphp
      <a href="{{ $href }}"
         @if ($active) aria-current="page" @endif
         class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[13.5px] font-medium transition-colors
                {{ $active ? 'sidebar-active bg-primary text-white shadow-sm' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
        <x-icon :name="$icon" :size="17" class="shrink-0 opacity-90" />
        <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
      </a>
    @endforeach

    @if ($isPreview)
      <a href="{{ route('preview.index') }}" class="mt-3 flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[13px] font-medium text-white/60 hover:bg-white/10 hover:text-white">
        <x-icon name="arrow-right" :size="15" class="rotate-180" /> Back to preview
      </a>
    @endif
  </nav>

  {{-- Logout (visual only — sign-in is turned off in this build) --}}
  <button type="button" title="Sign-in is turned off in this build"
          onclick="if (window.showToast) window.showToast('Sign-in is turned off in this build.', { type: 'info' })"
          class="mt-4 flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[13.5px] font-medium text-white/75 transition-colors hover:bg-white/10 hover:text-white">
    <x-icon name="logout" :size="17" class="opacity-90" /> Logout
  </button>
</aside>
