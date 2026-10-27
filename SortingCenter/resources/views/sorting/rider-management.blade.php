@extends('layouts.sorting')

@section('title', 'Riders')

@php
  $isPreview = request()->routeIs('preview.*');
  $startTab  = $pending->count() > 0 ? 'pending' : 'active';
  $portraits = ['images/design/rider-1.jpg', 'images/design/rider-2.jpg'];
  $docs      = [['images/design/orcr-1.jpg', 'images/design/license-1.jpg'], ['images/design/orcr-2.jpg', 'images/design/license-2.jpg']];
@endphp

@section('content')

  <x-page-head eyebrow="Fleet Verification">
    <x-slot:meta>{{ $hubProfile->name ?? 'Hub' }}</x-slot:meta>
    <div class="card flex items-center divide-x divide-border text-[12.5px] font-semibold">
      <span class="flex items-center gap-2 px-4 py-2.5"><span class="size-2 rounded-full bg-primary"></span>Queue: {{ $pending->count() }} Pending</span>
      <span class="flex items-center gap-2 px-4 py-2.5"><x-icon name="check-circle" :size="14" class="text-primary" />{{ $onShift }} On Duty</span>
    </div>
  </x-page-head>

  @include('partials.flash')

  {{-- ── Tabs — Pending Applications only shown when there are items ── --}}
  <div class="card mb-6 flex gap-8 px-6" role="tablist">
    @if ($pending->count() > 0)
      <button type="button" data-tab="pending" class="rider-tab -mb-px flex items-center gap-2 border-b-2 py-4 text-[14px] font-semibold">
        Pending Applications
        <span class="rounded-full bg-red-500 px-2 py-0.5 text-[11px] text-white">{{ $pending->count() }}</span>
      </button>
    @endif
    <button type="button" data-tab="active" class="rider-tab -mb-px flex items-center gap-2 border-b-2 py-4 text-[14px] font-semibold">
      Active Riders
      <span class="rounded-full bg-accent px-2 py-0.5 text-[11px] text-accent-foreground">{{ $riders->count() }}</span>
    </button>
  </div>

  {{-- ═════════ Pending Applications tab ═════════ --}}
  @if ($pending->count() > 0)
    <div data-panel="pending" class="space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="h-display flex items-center gap-2 text-[19px]">
          <x-icon name="checklist" :size="18" class="text-primary" />Pending Verification Requests
        </h2>
        <span class="text-[12px] font-semibold text-muted-foreground">Priority Order: Submission Time</span>
      </div>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @foreach ($pending as $app)
          @php
            $checklist  = $app->checklistValue();
            $kyc        = $app->kyc_status === 'verified';
            $canApprove = $app->canBeApproved();
            $photo      = $portraits[$loop->index] ?? null;
            $doc        = $docs[$loop->index % 2];
            $daysAgo    = $app->applied_on ? (int) $app->applied_on->diffInDays(today()) : 0;
            $loopId     = 'app-' . $app->id;
          @endphp
          <article class="card p-5"
                   data-application-card="{{ $loopId }}"
                   data-app-id="{{ $app->id }}"
                   data-can-approve="{{ $canApprove ? '1' : '0' }}"
                   data-verify-url="{{ route('riders.applications.verification', $app) }}"
                   data-otp-send-url="{{ route('riders.applications.otp.send', $app) }}"
                   data-otp-verify-url="{{ route('riders.applications.otp.verify', $app) }}"
                   data-approve-url="{{ route('riders.applications.approve', $app) }}">
            <div class="flex flex-wrap items-center gap-3">
              @if ($photo)
                <img src="{{ asset($photo) }}" alt="{{ $app->name }}" class="size-14 rounded-2xl object-cover">
              @else
                <span class="avatar size-14 rounded-2xl text-[16px]">{{ \Illuminate\Support\Str::of($app->name)->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
              @endif
              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="h-display text-[18px]">{{ $app->name }}</h3>
                </div>
                <div class="text-[12px] text-muted-foreground">Application #{{ $app->id }}</div>
              </div>
              <div class="rounded-xl bg-secondary px-3 py-1.5 text-right text-[11px]">
                <span class="block text-muted-foreground">Applied</span>
                <span class="font-bold">{{ $daysAgo === 0 ? 'Today' : ($daysAgo === 1 ? 'Yesterday' : $app->applied_on?->format('M j')) }}</span>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 rounded-2xl bg-secondary p-4">
              <div><div class="label">Vehicle</div><div class="mt-0.5 text-[13px] font-bold leading-tight">{{ $app->vehicle_type ?: '—' }}</div></div>
              <div><div class="label">Plate No.</div><div class="mt-0.5 text-[13px] font-bold text-primary">{{ $app->plate_no ?: '—' }}</div></div>
              <div><div class="label">Preferred Area</div><div class="mt-0.5 text-[13px] font-bold leading-tight">Set on approval</div></div>
            </div>

            <div class="label mt-4 mb-2">Submitted Credentials</div>
            <div class="grid grid-cols-2 gap-3">
              @foreach ([[$doc[0], 'OR/CR Document', $checklist['id_checked']], [$doc[1], 'Pro License & ID', $checklist['license_checked']]] as [$img, $cap, $ok])
                <a href="{{ asset($img) }}" target="_blank" rel="noopener"
                   class="relative block overflow-hidden rounded-2xl bg-secondary"
                   title="Open {{ $cap }} in a new tab">
                  <img src="{{ asset($img) }}" alt="{{ $cap }}" class="h-28 w-full object-cover transition hover:opacity-90">
                  <span class="absolute bottom-2 left-2 rounded-md bg-card/95 px-2 py-0.5 text-[10.5px] font-bold shadow-sm">{{ $cap }}</span>
                  <span class="absolute right-2 top-2 flex size-5 items-center justify-center rounded-full {{ $ok ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-white' }}">
                    <x-icon :name="$ok ? 'check' : 'clock'" :size="11" :stroke="3" />
                  </span>
                </a>
              @endforeach
            </div>

            {{-- Verification checklist — Approve disabled until all ticked --}}
            <div class="mt-4 rounded-2xl border border-border p-4" data-verify-card="{{ $loopId }}">
              <div class="mb-2 flex items-center justify-between gap-3">
                <div class="label">Verification Checklist</div>
              </div>
              <div class="space-y-2" data-verify-checklist="{{ $loopId }}">
                <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-secondary px-3 py-2 text-[12.5px] font-medium">
                  <input type="checkbox" class="row-checkbox verify-check" data-check-key="id_checked" data-check-for="{{ $loopId }}" onchange="saveVerification('{{ $loopId }}')" {{ $isPreview ? 'disabled' : '' }} title="Check after reviewing the submitted ID" {{ $checklist['id_checked'] ? 'checked' : '' }}>
                  <x-icon name="badge-check" :size="14" class="text-primary" /> ID checked
                </label>
                <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-secondary px-3 py-2 text-[12.5px] font-medium">
                  <input type="checkbox" class="row-checkbox verify-check" data-check-key="license_checked" data-check-for="{{ $loopId }}" onchange="saveVerification('{{ $loopId }}')" {{ $isPreview ? 'disabled' : '' }} title="Check after reviewing the submitted license" {{ $checklist['license_checked'] ? 'checked' : '' }}>
                  <x-icon name="check-circle" :size="14" class="text-primary" /> License checked
                </label>

                <div class="rounded-xl bg-secondary px-3 py-2">
                  <label class="flex items-center gap-3 text-[12.5px] font-medium">
                    <input type="checkbox" class="row-checkbox verify-check" data-check-key="phone_verified" data-check-for="{{ $loopId }}" {{ $checklist['phone_verified'] ? 'checked' : '' }} disabled>
                    <x-icon name="phone" :size="14" class="text-primary" /> Phone verified
                  </label>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <input type="tel" id="phone-{{ $loopId }}" value="{{ $app->phone }}" class="field min-w-40 flex-1 py-1.5 text-[12.5px]" placeholder="0917 123 4567" aria-label="Rider mobile number">
                    <button type="button" class="btn-soft px-3 py-1.5 text-[12px]" onclick="sendOtp('{{ $loopId }}')">Send code</button>
                  </div>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <input type="text" inputmode="numeric" maxlength="6" id="otp-{{ $loopId }}" class="field w-32 py-1.5 text-[12.5px] tracking-[0.3em]" placeholder="000000" aria-label="6-digit OTP">
                    <button type="button" class="btn-ghost px-3 py-1.5 text-[12px]" onclick="verifyOtp('{{ $loopId }}')">Verify code</button>
                  </div>
                  <p class="mt-1.5 text-[11px] text-muted-foreground" data-otp-hint="{{ $loopId }}">
                    {{ $checklist['phone_verified'] ? 'Phone verified '.$app->phone_verified_at?->diffForHumans().'.' : 'OTP expires in 10 minutes. Local codes are written to storage/logs/laravel.log.' }}
                  </p>
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-secondary px-3 py-2 text-[12.5px] font-medium">
                  <input type="checkbox" class="row-checkbox verify-check" data-check-key="docs_not_expired" data-check-for="{{ $loopId }}" disabled title="Calculated from the OR/CR and license expiry dates" {{ $checklist['docs_not_expired'] ? 'checked' : '' }}>
                  <x-icon name="clock" :size="14" class="text-primary" /> Documents not expired
                </label>

                <div class="grid grid-cols-2 gap-2 pt-1">
                  <label>
                    <span class="label text-[10px]">OR/CR expiry</span>
                    <input type="date" id="or-exp-{{ $loopId }}" value="{{ $app->or_cr_expires_on?->format('Y-m-d') }}" class="field mt-1 py-1.5 text-[12px]" onchange="saveVerification('{{ $loopId }}')">
                  </label>
                  <label>
                    <span class="label text-[10px]">License expiry</span>
                    <input type="date" id="lic-exp-{{ $loopId }}" value="{{ $app->license_expires_on?->format('Y-m-d') }}" class="field mt-1 py-1.5 text-[12px]" onchange="saveVerification('{{ $loopId }}')">
                  </label>
                </div>
              </div>
              <p class="field-error-msg mt-2 hidden" data-verify-error="{{ $loopId }}"></p>
            </div>

            @unless ($isPreview)
              <div class="mt-5 grid grid-cols-[1.6fr_1fr] gap-3">
                <form method="POST" action="{{ route('riders.applications.approve', $app) }}" id="approve-form-{{ $loopId }}" onsubmit="return submitApprove(event, '{{ $loopId }}')">@csrf
                  <button class="btn-ink w-full py-3" id="approve-btn-{{ $loopId }}" {{ $canApprove ? '' : 'disabled' }}>
                    <x-icon name="check-circle" :size="15" />Approve Application
                  </button>
                </form>
                <button type="button" onclick="openConfirmModal('disapprove-{{ $loopId }}')" class="btn-ghost w-full py-3">Disapprove</button>
              </div>
            @endunless
          </article>

          {{-- Disapprove modal with reason dropdown --}}
          <x-disapprove-modal id="disapprove-{{ $loopId }}" :action="route('riders.applications.disapprove', $app)" />
        @endforeach
      </div>
    </div>
  @endif

  {{-- ═════════ Active Riders tab ═════════ --}}
  <div data-panel="active" class="{{ $pending->count() > 0 ? 'hidden' : '' }} space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="h-display text-[19px]">Active Riders</h2>
        <p class="text-[12px] text-muted-foreground">One rider per barangay — {{ $covered }} covered, {{ $unassigned }} unassigned</p>
      </div>
    </div>

    {{-- Searchable toolbar --}}
    <x-list-toolbar
      placeholder="Search by name, initials, phone or plate…"
      :areas="$areas ?? collect()"
      :statuses="['active' => 'Active', 'inactive' => 'Inactive / Off shift']"
      :sorts="['name' => 'Name A–Z', 'newest' => 'Newest first', 'oldest' => 'Oldest first', 'checked_in' => 'Recently checked in']"
      show-dates
    />

    <section class="card overflow-hidden">
      <div class="table-scroll-region">
        <table class="tbl min-w-[760px]" data-row-details="off">
          <thead>
            <tr>
              <th>Rider Details</th>
              <th>Plate &amp; Transport</th>
              <th>Assigned Route Area</th>
              <th class="text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            @include('partials.rider-rows', ['rows' => $riders])
          </tbody>
        </table>
      </div>
      @if ($riders->isEmpty())
        <div class="py-8 text-center text-[13px] text-muted-foreground">No riders match your search.</div>
      @endif
    </section>

    @foreach ($riders as $r)
      @include('partials.rider-view-modal', ['r' => $r, 'areas' => $areas, 'isPreview' => $isPreview])
    @endforeach
  </div>

@endsection

@push('scripts')
<script>
  const initialTab = new URLSearchParams(location.search).get('tab')
    || sessionStorage.getItem('riderTab')
    || '{{ $startTab }}';

  function showTab(name) {
    sessionStorage.setItem('riderTab', name);
    document.querySelectorAll('[data-panel]').forEach(p => p.classList.toggle('hidden', p.dataset.panel !== name));
    document.querySelectorAll('.rider-tab').forEach(t => {
      const on = t.dataset.tab === name;
      t.classList.toggle('border-primary', on);
      t.classList.toggle('text-primary', on);
      t.classList.toggle('border-transparent', !on);
      t.classList.toggle('text-muted-foreground', !on);
    });
  }

  document.querySelectorAll('.rider-tab').forEach(t => t.addEventListener('click', () => showTab(t.dataset.tab)));
  document.querySelectorAll('[data-goto]').forEach(b => b.addEventListener('click', () => showTab(b.dataset.goto)));
  showTab(initialTab);

  // -- Rider KYC / approval wiring -------------------------------------------
  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken(),
      },
      body: JSON.stringify(payload || {}),
    }).then(async response => {
      const data = await response.json().catch(() => ({}));
      return { ok: response.ok, status: response.status, data };
    });
  }

  function firstError(data) {
    if (data && data.errors) {
      const first = Object.values(data.errors)[0];
      if (Array.isArray(first) && first.length) return first[0];
    }
    return (data && data.message) || 'Something went wrong. Please try again.';
  }

  function setVerifyError(id, message) {
    const el = document.querySelector('[data-verify-error="' + id + '"]');
    if (!el) return;
    el.textContent = message || '';
    el.classList.toggle('hidden', !message);
  }

  function syncApproveButton(id) {
    const card = document.querySelector('[data-application-card="' + id + '"]');
    const btn = document.getElementById('approve-btn-' + id);
    if (!card || !btn) return;
    btn.disabled = card.dataset.canApprove !== '1';
  }

  function applyVerificationState(id, data) {
    const card = document.querySelector('[data-application-card="' + id + '"]');
    if (!card) return;

    card.dataset.canApprove = data.can_approve ? '1' : '0';

    if (data.checklist) {
      Object.entries(data.checklist).forEach(([key, value]) => {
        const cb = card.querySelector('[data-check-key="' + key + '"]');
        if (cb) cb.checked = !!value;
      });
    }

    syncApproveButton(id);
    setVerifyError(id, '');
  }

  function saveVerification(id) {
    const card = document.querySelector('[data-application-card="' + id + '"]');
    if (!card) return;

    const payload = {
      id_checked: card.querySelector('[data-check-key="id_checked"]')?.checked || false,
      license_checked: card.querySelector('[data-check-key="license_checked"]')?.checked || false,
      or_cr_expires_on: document.getElementById('or-exp-' + id)?.value || null,
      license_expires_on: document.getElementById('lic-exp-' + id)?.value || null,
    };

    postJson(card.dataset.verifyUrl, payload).then(({ ok, data }) => {
      if (!ok) {
        const message = firstError(data);
        setVerifyError(id, message);
        showToast(message, { type: 'error' });
        setTimeout(() => location.reload(), 1400);
        return;
      }
      applyVerificationState(id, data);
      showToast(data.message || 'Verification checklist saved.', { type: 'success' });
    }).catch(() => showToast('Could not save the checklist. Check your connection and try again.', { type: 'error' }));
  }

  function sendOtp(id) {
    const card = document.querySelector('[data-application-card="' + id + '"]');
    if (!card) return;
    const phoneInput = document.getElementById('phone-' + id);
    const phone = phoneInput?.value.trim() || '';

    postJson(card.dataset.otpSendUrl, { phone }).then(({ ok, data }) => {
      if (!ok) {
        const message = firstError(data);
        setVerifyError(id, message);
        showToast(message, { type: 'error' });
        return;
      }
      if (phoneInput && data.phone) phoneInput.value = data.phone;
      const hint = document.querySelector('[data-otp-hint="' + id + '"]');
      if (hint && data.dev_hint) hint.textContent = data.dev_hint;
      showToast(data.message, { type: 'success' });
    }).catch(() => showToast('Could not send the verification code.', { type: 'error' }));
  }

  function verifyOtp(id) {
    const card = document.querySelector('[data-application-card="' + id + '"]');
    if (!card) return;
    const code = document.getElementById('otp-' + id)?.value.trim() || '';

    postJson(card.dataset.otpVerifyUrl, { code }).then(({ ok, data }) => {
      if (!ok) {
        const message = firstError(data);
        setVerifyError(id, message);
        showToast(message, { type: 'error' });
        return;
      }
      applyVerificationState(id, data);
      const hint = document.querySelector('[data-otp-hint="' + id + '"]');
      if (hint) hint.textContent = 'Phone verified just now.';
      showToast(data.message, { type: 'success' });
    }).catch(() => showToast('Could not verify the code.', { type: 'error' }));
  }

  function submitApprove(event, id) {
    event.preventDefault();
    const card = document.querySelector('[data-application-card="' + id + '"]');
    const btn = document.getElementById('approve-btn-' + id);
    if (!card || !btn) return false;

    btn.disabled = true;
    const original = btn.innerHTML;
    btn.textContent = 'Approving...';

    postJson(card.dataset.approveUrl, {}).then(({ ok, data }) => {
      if (!ok) {
        showToast(firstError(data), { type: 'error' });
        btn.innerHTML = original;
        syncApproveButton(id);
        return;
      }
      showToast(data.message, { type: 'success' });
      setTimeout(() => location.reload(), 900);
    }).catch(() => {
      showToast('Could not approve the application. Please try again.', { type: 'error' });
      btn.innerHTML = original;
      syncApproveButton(id);
    });

    return false;
  }

  document.querySelectorAll('[data-disapprove-form]').forEach(form => {
    form.addEventListener('submit', event => {
      event.preventDefault();
      const confirmBtn = form.querySelector('button[type="submit"]');
      if (confirmBtn) confirmBtn.disabled = true;

      postJson(form.action, {
        reason: form.querySelector('[name="reason"]')?.value || '',
        reason_detail: form.querySelector('[name="reason_detail"]')?.value || '',
      }).then(({ ok, data }) => {
        if (!ok) {
          showToast(firstError(data), { type: 'error' });
          if (confirmBtn) confirmBtn.disabled = false;
          return;
        }
        const modal = form.closest('.confirm-modal-backdrop');
        if (modal && typeof window.closeModal === 'function') window.closeModal(modal);
        showToast(data.message, { type: 'success' });
        setTimeout(() => location.reload(), 900);
      }).catch(() => {
        showToast('Could not disapprove this application. Please try again.', { type: 'error' });
        if (confirmBtn) confirmBtn.disabled = false;
      });
    });
  });

  // Initialize approve buttons from the server-side verification state.
  @foreach ($pending as $app)
    syncApproveButton('app-{{ $app->id }}');
  @endforeach
</script>
@endpush
