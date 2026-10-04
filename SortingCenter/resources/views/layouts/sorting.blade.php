<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sorting Center — @yield('title', 'Dashboard')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Syne:wght@400;500;600;700;800&family=Poppins:wght@700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')
</head>
<body class="font-sans antialiased">
<div class="flex min-h-screen bg-background text-foreground">

  @include('partials.sidebar')

  <div class="flex min-w-0 flex-1 flex-col">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border bg-card px-7 py-4">
      <div>
        <h1 class="text-lg font-semibold tracking-tight" style="font-family:'Syncopate',sans-serif">@yield('title', 'Dashboard')</h1>
        <p class="mt-0.5 text-[13px] text-muted-foreground">@yield('subtitle', 'Santa Cruz, Laguna Hub')</p>
      </div>
      <div class="flex items-center gap-2.5">
        <div class="rounded-full border border-border bg-background px-2.5 py-1.5 font-mono text-[11.5px] text-muted-foreground">
          {{ now()->format('D, M j') }} · <b class="text-foreground">{{ now()->format('h:i A') }}</b>
        </div>

        {{-- ── Notification bell ── --}}
        <div class="relative" id="notifWrapper">
          <button
            id="notifBtn"
            onclick="toggleNotifDropdown()"
            class="relative flex size-8.5 items-center justify-center rounded-md border border-border bg-background hover:bg-muted transition-colors"
            aria-label="Notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
              <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
            {{-- Unread badge --}}
            @if (($unreadNotifCount ?? 0) > 0)
              <span id="notifBadge"
                class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[9px] font-bold text-white leading-none">
                {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
              </span>
            @else
              <span id="notifBadge" class="hidden absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[9px] font-bold text-white leading-none"></span>
            @endif
          </button>

          {{-- Dropdown panel --}}
          <div id="notifDropdown"
               class="hidden absolute right-0 top-10 z-50 w-80 rounded-xl border border-border bg-card shadow-xl overflow-hidden"
               style="animation: slideDown .15s ease-out;">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
              <div class="text-[13px] font-bold">Notifications</div>
              <button onclick="markAllRead()" class="text-[11.5px] font-semibold text-primary hover:underline">
                Mark all as read
              </button>
            </div>

            {{-- List --}}
            <div id="notifList" class="max-h-80 overflow-y-auto divide-y divide-border">
              <div class="flex items-center justify-center py-8 text-[12.5px] text-muted-foreground" id="notifEmpty">
                Loading…
              </div>
            </div>

            {{-- Footer --}}
            <div class="border-t border-border px-4 py-2.5 text-center">
              <a href="{{ route('dashboard') }}" class="text-[12px] font-semibold text-primary hover:underline">
                View all activity on Dashboard
              </a>
            </div>
          </div>
        </div>
        {{-- ── End bell ── --}}

      </div>
    </div>

    <div class="px-7 py-6">
      @yield('content')
    </div>
  </div>

</div>
@stack('scripts')
<style>
@keyframes slideDown {
  from { opacity:0; transform:translateY(-6px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
<script>
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

// Close when clicking outside
document.addEventListener('click', function (e) {
  if (notifOpen && !document.getElementById('notifWrapper').contains(e.target)) {
    closeNotifDropdown();
  }
});

// Close on Escape
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeNotifDropdown();
});

function loadNotifications() {
  fetch('{{ route("notifications.index") }}')
    .then(r => r.json())
    .then(data => renderNotifications(data));
}

function renderNotifications(data) {
  const list = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge');

  // Update badge
  if (data.unread_count > 0) {
    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
    badge.classList.remove('hidden');
    badge.classList.add('flex');
  } else {
    badge.classList.add('hidden');
    badge.classList.remove('flex');
  }

  if (!data.notifications.length) {
    list.innerHTML = `<div class="flex items-center justify-center py-8 text-[12.5px] text-muted-foreground">No notifications yet.</div>`;
    return;
  }

  list.innerHTML = data.notifications.map(n => `
    <div class="flex items-start gap-3 px-4 py-3 transition-colors ${n.read ? 'bg-background' : 'bg-primary/5'}"
         id="notif-${n.id}">
      <div class="flex size-7 shrink-0 items-center justify-center rounded-full mt-0.5 ${n.icon_classes}">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">${n.icon_svg}</svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-[12.5px] leading-snug">${n.description}</div>
        <div class="mt-0.5 text-[11px] text-muted-foreground">${n.time}</div>
      </div>
      ${!n.read ? `<button onclick="markRead(${n.id})" title="Mark as read"
        class="shrink-0 mt-0.5 size-2 rounded-full bg-primary hover:bg-primary/70 transition-colors"></button>` : ''}
    </div>
  `).join('');
}

function markRead(id) {
  fetch(`/notifications/${id}/read`, { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content} })
    .then(() => loadNotifications());
}

function markAllRead() {
  fetch('{{ route("notifications.mark-all-read") }}', { method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content} })
    .then(() => loadNotifications());
}
</script>
</body>
</html>
