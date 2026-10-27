<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mimoo Sorting Center — @yield('title', 'Dashboard')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
{{-- Apply saved theme before first paint to prevent flash --}}
<script>
  (function() {
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  })();
</script>
@stack('styles')
</head>
<body class="font-sans antialiased">
@php $isPreview = request()->routeIs('preview.*'); @endphp

{{-- ── Global scan modal ── --}}
<div id="scanModal" class="scan-modal-backdrop hidden" role="dialog" aria-modal="true" aria-label="Scan or search">
  <div class="scan-modal-box" id="scanModalBox">
    <div class="scan-modal-head">
      <span class="scan-modal-label">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h.01M18 14h.01M14 18h.01M18 18h.01M14 21h4M21 14v4"/></svg>
        Scan Parcel QR
      </span>
      <div class="flex items-center gap-2">
        <kbd class="scan-kbd">Ctrl K</kbd>
        <button type="button" onclick="closeScanModal()" class="scan-modal-close" aria-label="Close">✕</button>
      </div>
    </div>
    <div class="scan-modal-input-wrap">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="scan-modal-icon" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input id="scanInput"
             type="text"
             placeholder="Type parcel ID (e.g. #1011) or buyer name..."
             class="scan-modal-input"
             data-autofocus
             autocomplete="off"
             onkeydown="handleScanKey(event)"
             oninput="handleScanInput(this.value)">
    </div>
    <div id="scanResults" class="scan-modal-results hidden"></div>
    <div class="scan-modal-footer">
      <span><kbd class="scan-kbd">↵</kbd> Go to parcel</span>
      <span><kbd class="scan-kbd">Esc</kbd> Close</span>
      <span class="ml-auto"><kbd class="scan-kbd">S</kbd> Scan incoming</span>
    </div>
  </div>
</div>

<div class="flex min-h-screen overflow-x-hidden bg-background text-foreground">

  @include('partials.sidebar')

  <div class="app-shell-content flex min-w-0 flex-1 flex-col overflow-x-hidden">

    {{-- ── Top bar ── --}}
    <header class="topbar sticky top-0 z-30 flex items-center gap-2 border-b border-border bg-card/90 px-4 py-2.5 backdrop-blur sm:gap-3 sm:px-5 sm:py-3">

      {{-- Mobile navigation trigger --}}
      <button type="button" id="sidebarToggle" aria-label="Open navigation" aria-controls="appSidebar" aria-expanded="false"
              class="flex size-9 shrink-0 items-center justify-center rounded-full text-foreground transition-colors hover:bg-secondary lg:hidden">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      {{-- Breadcrumb --}}
      <nav class="flex min-w-0 flex-1 items-center gap-1.5 text-[12.5px] font-medium text-muted-foreground" aria-label="Breadcrumb">
        <span class="hidden shrink-0 font-semibold text-foreground sm:inline">{{ $hubProfile->name ?? config('sorting.hub_name') }}</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden shrink-0 opacity-40 sm:block" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        <span class="truncate font-semibold text-foreground">@yield('title', 'Dashboard')</span>
      </nav>

      <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">

        {{-- Scan / Search trigger --}}
        <button onclick="openScanModal()" aria-label="Scan QR or search (Ctrl+K)"
                class="scan-trigger-btn hidden items-center gap-2 rounded-xl border border-border bg-secondary px-3 py-2 text-[12.5px] font-medium text-muted-foreground transition hover:border-ring hover:bg-card hover:text-foreground md:flex">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <span>Search / Scan</span>
          <kbd class="scan-kbd ml-1">Ctrl K</kbd>
        </button>
        {{-- Mobile icon-only scan button --}}
        <button onclick="openScanModal()" aria-label="Scan QR or search"
                class="flex size-9 items-center justify-center rounded-full text-muted-foreground hover:bg-secondary hover:text-foreground md:hidden">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h.01M18 14h.01M14 18h.01M18 18h.01M14 21h4M21 14v4"/></svg>
        </button>

        {{-- Dark mode toggle --}}
        <button id="darkToggle" onclick="toggleDark()" aria-label="Toggle dark mode"
                class="flex size-9 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">
          <x-icon name="sun" id="iconSun" :size="17" class="hidden" />
          <x-icon name="moon" id="iconMoon" :size="17" />
        </button>

        {{-- Notification bell --}}
        <div class="relative" id="notifWrapper">
          <button id="notifBtn" onclick="toggleNotifDropdown()" aria-label="Notifications"
                  class="relative flex size-9 items-center justify-center rounded-full text-foreground transition-colors hover:bg-secondary">
            <x-icon name="bell" :size="18" />
            <span id="notifBadge" class="{{ ($unreadNotifCount ?? 0) > 0 ? '' : 'hidden' }} absolute right-1.5 top-1.5 size-2 rounded-full bg-red-500 ring-2 ring-card"></span>
          </button>

          <div id="notifDropdown" class="notif-dropdown absolute right-0 top-11 z-50 hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-border bg-card shadow-xl">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
              <div class="text-[13px] font-bold">Notifications</div>
              <button onclick="markAllRead()" class="text-[11.5px] font-semibold text-primary hover:underline">Mark all as read</button>
            </div>
            {{-- Grouped sections --}}
            <div id="notifList" class="max-h-[26rem] divide-y divide-border overflow-y-auto">
              <div class="flex items-center justify-center py-8 text-[12.5px] text-muted-foreground" id="notifEmpty">Loading…</div>
            </div>
            <div class="border-t border-border px-4 py-2.5 text-center">
              <a href="{{ route('dashboard') }}" class="text-[12px] font-semibold text-primary hover:underline">View all activity on Dashboard</a>
            </div>
          </div>
        </div>

        {{-- Account avatar — always shows "Laguna Hub" --}}
        <a href="{{ $isPreview ? route('preview.account.index') : route('account.index') }}" aria-label="Account — {{ $hubProfile->name ?? config('sorting.hub_name') }}"
           class="flex size-10 items-center justify-center rounded-full bg-ink text-ink-foreground shadow-sm transition hover:opacity-90"
           title="{{ $hubProfile->name ?? config('sorting.hub_name') }}">
          <x-icon name="user" :size="18" />
        </a>
      </div>
    </header>

    <main class="app-main min-w-0 flex-1 overflow-x-hidden">
      @yield('content')
    </main>
  </div>

</div>
<div id="rowDetailsModal" class="confirm-modal-backdrop hidden" role="dialog" aria-modal="true"
     aria-labelledby="rowDetailsModalTitle" aria-hidden="true">
  <section class="confirm-modal-box row-details-modal">
    <div class="confirm-modal-head">
      <h2 id="rowDetailsModalTitle" class="h-display text-[18px]">View row</h2>
      <button type="button" class="confirm-modal-close" data-modal-close aria-label="Close details">&#10005;</button>
    </div>
    <div class="confirm-modal-body">
      <dl id="rowDetailsModalContent" class="row-details-list"></dl>
      <div id="rowDetailsModalActions" class="row-details-actions hidden">
        <h3 class="label">Available actions</h3>
        <div id="rowDetailsModalActionButtons" class="mt-2 flex flex-wrap gap-2"></div>
      </div>
    </div>
    <div class="confirm-modal-foot">
      <button type="button" class="btn-ghost px-5 py-2.5" data-modal-close>Close</button>
    </div>
  </section>
</div>
@stack('scripts')
<script>
// ── Dark mode ──
function syncDarkIcons() {
  const dark = document.documentElement.classList.contains('dark');
  document.getElementById('iconSun').classList.toggle('hidden', !dark);
  document.getElementById('iconMoon').classList.toggle('hidden', dark);
}
function toggleDark() {
  const isDark = document.documentElement.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
  syncDarkIcons();
}
syncDarkIcons();

// ── Notification dropdown ──
let notifOpen = false;

function toggleNotifDropdown() {
  notifOpen ? closeNotifDropdown() : openNotifDropdown();
}

function openNotifDropdown() {
  notifOpen = true;
  document.getElementById('notifDropdown').classList.remove('hidden');
  loadNotifications();
}

function closeNotifDropdown() {
  notifOpen = false;
  document.getElementById('notifDropdown').classList.add('hidden');
}

document.addEventListener('click', function (e) {
  if (notifOpen && !document.getElementById('notifWrapper').contains(e.target)) {
    closeNotifDropdown();
  }
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    closeNotifDropdown();
    closeScanModal();
  }
  // Ctrl+K → open scan modal
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    openScanModal();
  }
  // "S" key when not in an input → open scan modal (navigate to incoming parcels)
  if (e.key === 's' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
    e.preventDefault();
    openScanModal();
  }
});

function loadNotifications() {
  fetch('{{ route("notifications.index") }}')
    .then(r => r.json())
    .then(data => renderNotifications(data))
    .catch(() => {
      // TODO(antigravity): wire $notifications when endpoint is ready
      renderNotificationsFallback();
    });
}

function renderNotifications(data) {
  const list = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge');
  badge.classList.toggle('hidden', !(data.unread_count > 0));

  if (!data.notifications || !data.notifications.length) {
    list.innerHTML = `<div class="flex items-center justify-center py-8 text-[12.5px] text-muted-foreground">No notifications yet.</div>`;
    return;
  }

  // Group by type: overdue, unassigned, kyc
  const groups = {
    overdue:    { label: 'Overdue Parcels',  icon: 'alert',       items: [] },
    unassigned: { label: 'Unassigned',       icon: 'box',         items: [] },
    kyc:        { label: 'KYC Pending',      icon: 'account',     items: [] },
    general:    { label: 'Activity',         icon: 'bell',        items: [] },
  };
  data.notifications.forEach(n => {
    const g = groups[n.type] ? n.type : 'general';
    groups[g].items.push(n);
  });

  list.innerHTML = Object.values(groups).filter(g => g.items.length).map(g => `
    <div>
      <div class="notif-group-label">${g.label}</div>
      ${g.items.map(n => `
        <a href="${n.url || '#'}" class="notif-item ${n.read ? '' : 'notif-item-unread'}" id="notif-${n.id || n.type}"
           ${n.id ? `onclick="markRead(${n.id})"` : ''}>
          <div class="notif-dot ${n.icon_classes || 'bg-secondary text-muted-foreground'}">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">${n.icon_svg || ''}</svg>
          </div>
          <div class="notif-body">
            <div class="notif-text">${n.description}</div>
            <div class="notif-time">${n.time}</div>
          </div>
          ${!n.read ? `<span class="notif-unread-pip"></span>` : ''}
        </a>
      `).join('')}
    </div>
  `).join('');
}

function renderNotificationsFallback() {
  // TODO(antigravity): wire $notifications — showing static fallback
  const list = document.getElementById('notifList');
  @php
    $notifFallback = $notifications ?? [];
  @endphp
  @if (!empty($notifFallback))
    const items = @json($notifFallback);
    if (items.length) {
      list.innerHTML = items.map(n => `
        <a href="${n.url || '#'}" class="notif-item notif-item-unread">
          <div class="notif-dot bg-blue-100 text-blue-600"></div>
          <div class="notif-body">
            <div class="notif-text">${n.title}</div>
            <div class="notif-time">${n.created_at || ''}</div>
          </div>
        </a>
      `).join('');
      return;
    }
  @endif
  list.innerHTML = `<div class="flex items-center justify-center py-8 text-[12.5px] text-muted-foreground">No notifications yet.</div>`;
}

function markRead(id) {
  fetch(`/notifications/${id}/read`, { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content} })
    .then(() => loadNotifications());
}

function markAllRead() {
  fetch('{{ route("notifications.mark-all-read") }}', { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content} })
    .then(() => loadNotifications());
}

// ── Global Scan / Search modal ──
let scanDebounce = null;

function openScanModal() {
  if (typeof window.openModal !== 'function') return;
  window.openModal('scanModal', document.activeElement);
}

function closeScanModal() {
  if (typeof window.closeModal !== 'function') return;
  window.closeModal('scanModal');
  document.getElementById('scanInput').value = '';
  document.getElementById('scanResults').classList.add('hidden');
  document.getElementById('scanResults').innerHTML = '';
}

function handleScanKey(e) {
  if (e.key === 'Enter') {
    const val = document.getElementById('scanInput').value.trim();
    if (val) {
      const dest = '{{ $isPreview ? route("preview.parcels.incoming") : route("parcels.incoming") }}';
      window.location.href = dest + '?tracking=' + encodeURIComponent(val);
    }
  }
}

function handleScanInput(val) {
  clearTimeout(scanDebounce);
  const box = document.getElementById('scanResults');
  if (!val.trim()) { box.classList.add('hidden'); return; }
  scanDebounce = setTimeout(() => {
    fetch('{{ route("search") }}?q=' + encodeURIComponent(val), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'fetch' }
    })
      .then(response => response.json())
      .then(data => {
        const groups = [
          ['Parcels', data.parcels || []],
          ['Riders', data.riders || []],
          ['Sellers', data.sellers || []],
          ['Branches & Hubs', data.branches || []],
        ].filter(([, items]) => items.length);

        if (!groups.length) {
          box.innerHTML = `<div class="px-4 py-6 text-center text-[12.5px] text-muted-foreground">No matches for <strong>${escHtmlScan(val)}</strong>.</div>`;
          box.classList.remove('hidden');
          return;
        }

        box.innerHTML = groups.map(([label, items]) => `
          <div class="scan-result-group">${label}</div>
          ${items.map(item => `
            <a href="${item.url}" class="scan-result-row" onclick="closeScanModal()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
              <span style="min-width:0;">
                <strong>${escHtmlScan(item.title)}</strong>
                ${item.subtitle ? `<span style="display:block;font-size:11px;opacity:.65;">${escHtmlScan(item.subtitle)}</span>` : ''}
              </span>
            </a>
          `).join('')}
        `).join('');

        box.classList.remove('hidden');
      })
      .catch(() => {
        box.innerHTML = `
          <div class="scan-result-group">Quick actions</div>
          <a href="{{ $isPreview ? route("preview.parcels.incoming") : route("parcels.incoming") }}?tracking=${encodeURIComponent(val)}"
             class="scan-result-row" onclick="closeScanModal()">Look up <strong>${escHtmlScan(val)}</strong> in Incoming Parcels</a>
          <a href="{{ $isPreview ? route("preview.delivery.monitoring") : route("delivery.monitoring") }}?q=${encodeURIComponent(val)}"
             class="scan-result-row" onclick="closeScanModal()">Search <strong>${escHtmlScan(val)}</strong> in Delivery Monitoring</a>
        `;
        box.classList.remove('hidden');
      });
  }, 250);
}

function escHtmlScan(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Confirm modal (global) ──
</script>

{{-- Toast container — available on every page --}}
<x-toast-container />

@stack('schedule-data')
</body>
</html>
