@extends('layouts.sorting')

@section('title', 'Account')

@php
  $isPreview = request()->routeIs('preview.*');
  $initials = \Illuminate\Support\Str::of($hub->name ?? 'Hub')->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
  $contactFields = ['first_name', 'last_name', 'middle_initial', 'staff_title', 'birthday'];
  $contactHasError = collect($contactFields)->contains(fn ($f) => $errors->has($f));
  $entityHasError = $errors->has('entity_name');
  $docs = [
    ['SEC Registration Certificate', 'Corporate'],
    ['BIR Certificate of Registration (2303)', 'Tax'],
    ["Mayor's / Business Permit", 'Local Government'],
    ['Fire Safety Inspection Certificate', 'Facility'],
  ];
  $nodeId = 'MIM-'.strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $hub->name ?? 'HUB'), 0, 3)).'-'.str_pad((string) ($hub->id ?? 1), 3, '0', STR_PAD_LEFT);
@endphp

@section('content')

  {{-- Flash banner — cleared when switching tabs --}}
  <div id="acctFlash">@include('partials.flash')</div>

  {{-- ── Identity header ── --}}
  <section class="card mb-5 p-6">
    <div class="flex flex-wrap items-center gap-6">
      <div class="relative">
        <span class="flex size-20 items-center justify-center rounded-2xl bg-primary font-display text-[28px] font-bold text-white shadow-lg shadow-primary/25">{{ $initials }}</span>
        <span class="absolute -bottom-1 -right-1 flex size-6 items-center justify-center rounded-full bg-primary text-white ring-2 ring-card"><x-icon name="check" :size="13" :stroke="3.5" /></span>
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2.5">
          <h1 class="h-display text-[26px] leading-tight">{{ $hub->name ?? 'Sorting Hub' }}</h1>
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-muted-foreground">
          <span>Registered Logistics Hub Partner</span>
          <span class="flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-muted-foreground/50"></span>NODE ID: <b class="text-foreground">{{ $nodeId }}</b></span>
          <span class="flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-muted-foreground/50"></span>Accredited since {{ ($hub->created_at ?? now())->format('M j, Y') }}</span>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="rounded-xl bg-secondary px-4 py-2.5 text-[12px]"><span class="flex items-center gap-1.5 text-muted-foreground"><span class="size-1.5 rounded-full bg-primary"></span>Hub Operational Tier</span><span class="font-bold">Priority Sorter • Class A</span></div>
        <button type="button" onclick="window.print()" class="btn-soft"><x-icon name="download" :size="15" />Certificate</button>
      </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2" role="tablist">
      @foreach ([['company', 'Company Profile', 'building', null], ['contact', 'Contact Person', 'account', null], ['documents', 'Documents', 'folder', count($docs)], ['security', 'Security', 'lock', null]] as [$key, $label, $icon, $badge])
        <button type="button" data-acct-tab="{{ $key }}" class="acct-tab inline-flex items-center gap-2 rounded-xl px-5 py-3 text-[13.5px] font-semibold transition">
          <x-icon :name="$icon" :size="15" />{{ $label }}
          @if ($badge)<span class="rounded-full bg-accent px-2 py-0.5 text-[10.5px] text-accent-foreground">{{ $badge }}</span>@endif
        </button>
      @endforeach
    </div>
  </section>

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.9fr_1fr]">

    {{-- ═════════ Forms / panels ═════════ --}}
    <form method="POST" action="{{ $isPreview ? '#' : route('account.update') }}" class="card p-6" id="accountForm">
      @csrf
      <input type="hidden" name="confirm" id="accountConfirm" value="0">

      <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 class="h-display text-[20px]" id="panelTitle">Registration &amp; Facility Info</h2>
          <p class="max-w-xs text-[12px] text-muted-foreground" id="panelSub">Manage your corporate credentials and authorized operational locations.</p>
        </div>
        <div class="flex items-center gap-2.5" id="formActions">
          <button type="button" id="editBtn" class="btn-soft" data-edit-only="hide"><x-icon name="pencil" :size="14" />Edit</button>
          <button type="button" id="saveBtn" onclick="saveAccount()" class="btn-primary" {{ $isPreview ? 'disabled' : '' }} data-edit-only="show" disabled><x-icon name="check" :size="15" />Save Changes</button>
          <button type="button" id="cancelBtn" onclick="cancelEdit()" class="btn-ghost" data-edit-only="show">Cancel</button>
        </div>
      </div>

      {{-- Company profile --}}
      <div data-acct-panel="company" class="space-y-4">
        <label class="block"><span class="label">Registered Corporate Entity Name</span>
          <input type="text" name="entity_name" value="{{ old('entity_name', $hub->name ?? config('sorting.hub_name')) }}" class="field acct-input mt-1.5 {{ $errors->has('entity_name') ? 'field-error' : '' }}" maxlength="255" readonly>
          @if ($errors->has('entity_name'))<span class="field-error-msg">{{ $errors->first('entity_name') }}</span>@else<span class="field-hint">Changing this name requires confirmation.</span>@endif</label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="block"><span class="label">Contact Email</span>
            <input type="email" name="contact_email" value="{{ old('contact_email', $hub->contact_email ?? '') }}" class="field acct-input mt-1.5 {{ $errors->has('contact_email') ? 'field-error' : '' }}" readonly>
            @if ($errors->has('contact_email'))<span class="field-error-msg">{{ $errors->first('contact_email') }}</span>@endif</label>
          <label class="block"><span class="label">Contact Phone</span>
            <input type="text" name="contact_phone" id="phoneInput" value="{{ old('contact_phone', $hub->contact_phone ?? '') }}" placeholder="0917 123 4567 or (049) 123-4567" class="field acct-input mt-1.5 {{ $errors->has('contact_phone') ? 'field-error' : '' }}" readonly>
            @if ($errors->has('contact_phone'))<span class="field-error-msg">{{ $errors->first('contact_phone') }}</span>@else<span class="field-hint">PH mobile (09XX XXX XXXX) or landline (049) 123-4567.</span>@endif</label>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 rounded-xl bg-secondary px-4 py-3 text-[12px] font-semibold">
          <x-icon name="network" :size="15" class="text-primary" />Hierarchy Mapping:
          <span class="text-muted-foreground">Region → Province → City / Municipality → Barangay</span>
        </div>

        <label class="block"><span class="label">Street Address &amp; Landmark</span>
          <input type="text" name="address" value="{{ old('address', $hub->address ?? '') }}" class="field acct-input mt-1.5 {{ $errors->has('address') ? 'field-error' : '' }}" maxlength="255" readonly>
          @if ($errors->has('address'))<span class="field-error-msg">{{ $errors->first('address') }}</span>@endif</label>

        <label class="block"><span class="label">Coverage Note</span>
          <textarea name="coverage_note" rows="3" class="field acct-input mt-1.5 resize-none {{ $errors->has('coverage_note') ? 'field-error' : '' }}" maxlength="1000" readonly>{{ old('coverage_note', $hub->coverage_note ?? '') }}</textarea>
          @if ($errors->has('coverage_note'))<span class="field-error-msg">{{ $errors->first('coverage_note') }}</span>@endif</label>

        <div>
          <div class="mb-1.5 flex items-center justify-between"><span class="label">Authorized Regional Service Areas</span><span class="text-[11px] font-bold text-primary">{{ $areas->count() }} Areas Active</span></div>
          <div class="flex max-h-32 flex-wrap gap-2 overflow-y-auto rounded-xl bg-secondary p-3">
            @foreach ($areas as $a)
              <span class="inline-flex items-center gap-2 rounded-xl bg-card px-3 py-1.5 text-[12px] font-semibold shadow-sm"><span class="size-2 rounded-full" style="background: {{ $a->chart_color ?? '#1d4ed8' }}"></span>{{ $a->name }}</span>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Contact person --}}
      <div data-acct-panel="contact" class="hidden space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <label class="block"><span class="label">First Name</span>
            <input type="text" name="first_name" value="{{ old('first_name', $hub->first_name ?? \Illuminate\Support\Str::before($hub->staff_name ?? 'Sorting Staff', ' ')) }}" class="field acct-input mt-1.5 {{ $errors->has('first_name') ? 'field-error' : '' }}" maxlength="50" readonly>
            @if ($errors->has('first_name'))<span class="field-error-msg">{{ $errors->first('first_name') }}</span>@endif</label>
          <label class="block"><span class="label">Last Name</span>
            <input type="text" name="last_name" value="{{ old('last_name', $hub->last_name ?? (\Illuminate\Support\Str::contains($hub->staff_name ?? '', ' ') ? \Illuminate\Support\Str::after($hub->staff_name, ' ') : 'Staff')) }}" class="field acct-input mt-1.5 {{ $errors->has('last_name') ? 'field-error' : '' }}" maxlength="50" readonly>
            @if ($errors->has('last_name'))<span class="field-error-msg">{{ $errors->first('last_name') }}</span>@endif</label>
          <label class="block"><span class="label">Middle Initial</span>
            <input type="text" name="middle_initial" value="{{ old('middle_initial', $hub->middle_initial ?? '') }}" class="field acct-input mt-1.5 {{ $errors->has('middle_initial') ? 'field-error' : '' }}" maxlength="5" readonly>
            @if ($errors->has('middle_initial'))<span class="field-error-msg">{{ $errors->first('middle_initial') }}</span>@endif</label>
          <label class="block"><span class="label">Position / Title</span>
            <input type="text" name="staff_title" value="{{ old('staff_title', $hub->staff_title ?? '') }}" placeholder="e.g. Operations Manager" class="field acct-input mt-1.5 {{ $errors->has('staff_title') ? 'field-error' : '' }}" maxlength="100" readonly>
            @if ($errors->has('staff_title'))<span class="field-error-msg">{{ $errors->first('staff_title') }}</span>@endif</label>
          <label class="block"><span class="label">Birthday</span>
            <input type="date" name="birthday" id="birthdayInput" value="{{ old('birthday', optional($hub->birthday ?? null)->format('Y-m-d')) }}" max="{{ now()->subYears(18)->toDateString() }}" class="field acct-input mt-1.5 {{ $errors->has('birthday') ? 'field-error' : '' }}" readonly onchange="checkBirthday()">
            @if ($errors->has('birthday'))<span class="field-error-msg">{{ $errors->first('birthday') }}</span>@else<span class="field-hint" id="birthdayHint">The contact person must be at least 18 years old.</span>@endif</label>
        </div>
      </div>

      {{-- Documents --}}
      <div data-acct-panel="documents" class="hidden space-y-3">
        @foreach ($docs as [$title, $kind])
          <div class="flex items-center gap-4 rounded-2xl bg-secondary p-4">
            <span class="flex size-10 items-center justify-center rounded-xl bg-card text-primary"><x-icon name="file" :size="18" /></span>
            <div class="flex-1">
              <div class="h-display text-[14.5px]">{{ $title }}</div>
              <div class="text-[12px] text-muted-foreground">{{ $kind }}</div>
              {{-- Last updated date --}}
              <div class="mt-1 text-[11px] text-muted-foreground">Last updated: {{ ($hub->updated_at ?? now())->format('M j, Y') }}</div>
            </div>
          </div>
        @endforeach
        <p class="text-[12px] text-muted-foreground">Document uploads aren't connected to storage yet — these entries are placeholders. Last updated: {{ ($hub->updated_at ?? now())->format('M j, Y \a\t h:i A') }}</p>
      </div>

      {{-- Security --}}
      <div data-acct-panel="security" class="hidden space-y-4">
        <div class="flex items-start gap-4 rounded-2xl bg-secondary p-5">
          <span class="flex size-10 items-center justify-center rounded-xl bg-card text-primary"><x-icon name="shield" :size="18" /></span>
          <div><div class="h-display text-[15px]">Strict Matrix Standard</div><p class="mt-1 text-[12.5px] text-muted-foreground">Every parcel handled at this hub must be verified with a 2D QR scan. Linear barcodes are rejected.</p></div>
        </div>
        <div class="flex items-start gap-4 rounded-2xl bg-secondary p-5">
          <span class="flex size-10 items-center justify-center rounded-xl bg-card text-primary"><x-icon name="lock" :size="18" /></span>
          <div><div class="h-display text-[15px]">Sign-in is turned off</div><p class="mt-1 text-[12.5px] text-muted-foreground">This build runs without login. Password and session controls will appear here once authentication is restored.</p></div>
        </div>
      </div>
    </form>

    {{-- ═════════ Side cards ═════════ --}}
    <div class="space-y-5">
      <section class="card p-5">
        <div class="mb-4 flex items-center justify-between"><h2 class="h-display text-[16px]">Key Operations Lead</h2></div>
        <div class="tile rounded-2xl">
          <div class="flex items-center gap-3">
            <span class="flex size-12 items-center justify-center rounded-xl bg-ink text-[14px] font-bold text-white">{{ $hub->display_initials ?? 'SC' }}</span>
            <div><div class="h-display text-[16px] leading-tight">{{ $hub->staff_name ?? 'Sorting Staff' }}</div><div class="text-[12px] text-muted-foreground">{{ $hub->staff_title ?? 'Sorting Staff' }}</div></div>
          </div>
          <div class="mt-4 space-y-2 text-[12.5px] font-medium">
            <div class="flex items-center gap-2.5"><x-icon name="mail" :size="15" class="text-primary" /><span class="truncate">{{ $hub->contact_email ?? '—' }}</span></div>
            <div class="flex items-center gap-2.5"><x-icon name="phone" :size="15" class="text-primary" />{{ $hub->contact_phone ?? '—' }}</div>
          </div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3">
          <div class="tile rounded-2xl"><div class="text-[11px] text-muted-foreground">Daily Capacity</div><div class="h-display text-[20px]">12,500</div><div class="text-[11px] font-bold text-primary">Parcels / Day</div></div>
          <div class="tile rounded-2xl"><div class="text-[11px] text-muted-foreground">Sort S.L.A.</div><div class="h-display text-[20px]">99.4%</div><div class="text-[11px] font-bold text-primary">Met this month</div></div>
        </div>
      </section>

      <section class="card p-5 text-center">
        <div class="flex items-center justify-between"><h2 class="h-display text-[16px]">Sorting Bay Token</h2><span class="text-[11px] font-bold text-primary">Live Scanner</span></div>
        <x-qr :seed="$nodeId.'-QR'" :size="150" class="mx-auto my-5 text-primary" />
        <div class="label">Fast Dock Check-In</div>
        <div class="h-display text-[16px]">{{ $nodeId }}-QR</div>
        <p class="mx-auto mt-1 max-w-[16rem] text-[12px] text-muted-foreground">Present at bay entry for automated gate clearance and intake ledger.</p>
      </section>
    </div>
  </div>

  {{-- Confirm: Save Account Changes --}}
  <x-confirm-modal id="confirmAccountSave" title="Save Account Changes?" confirmLabel="Save Changes">
    <ul class="confirm-summary-list" id="accountSaveSummary">
      <li>Your account settings will be updated</li>
    </ul>
  </x-confirm-modal>

@endsection

@push('scripts')
<script>
  const meta = {
    company:   ['Registration & Facility Info', 'Manage your corporate credentials and authorized operational locations.', true],
    contact:   ['Contact Person', 'The person responsible for this hub. Must be 18 or older.', true],
    documents: ['Compliance Documents', 'Accreditation paperwork on file for this hub.', false],
    security:  ['Security', 'Access standards for this sorting center.', false],
  };
  function showAcctTab(name) {
    document.querySelectorAll('[data-acct-panel]').forEach(p => p.classList.toggle('hidden', p.dataset.acctPanel !== name));
    document.querySelectorAll('.acct-tab').forEach(t => {
      const on = t.dataset.acctTab === name;
      t.classList.toggle('bg-ink', on); t.classList.toggle('text-ink-foreground', on);
      t.classList.toggle('text-muted-foreground', !on); t.classList.toggle('hover:bg-secondary', !on);
    });
    document.getElementById('panelTitle').textContent = meta[name][0];
    document.getElementById('panelSub').textContent = meta[name][1];
    document.getElementById('formActions').classList.toggle('hidden', !meta[name][2]);
    // Clear flash banner when switching tabs
    const flash = document.getElementById('acctFlash');
    if (flash) flash.innerHTML = '';
  }
  document.querySelectorAll('.acct-tab').forEach(t => t.addEventListener('click', () => showAcctTab(t.dataset.acctTab)));
  document.querySelectorAll('[data-acct-goto]').forEach(b => b.addEventListener('click', () => showAcctTab(b.dataset.acctGoto)));

  // ── Edit mode toggling ──
  let isEditing = false;
  let formChanged = false;

  function setEditing(on) {
    isEditing = on;
    formChanged = false;
    document.querySelectorAll('.acct-input').forEach(i => {
      i.readOnly = !on;
      i.classList.toggle('bg-card', on);
      i.classList.toggle('border-border', on);
    });
    // Toggle Edit/Save/Cancel visibility
    document.querySelectorAll('[data-edit-only]').forEach(el => {
      const mode = el.dataset.editOnly;
      el.classList.toggle('hidden', mode === 'show' ? !on : on);
    });
    // Save button disabled until a field changes
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.disabled = !on || !formChanged;
  }

  function cancelEdit() {
    // Reset form to original values
    document.getElementById('accountForm').reset();
    setEditing(false);
  }

  document.getElementById('editBtn').addEventListener('click', () => {
    setEditing(true);
    document.querySelector('[data-acct-panel]:not(.hidden) .acct-input')?.focus();
  });

  // ── Detect field changes to enable Save ──
  document.getElementById('accountForm').addEventListener('input', () => {
    formChanged = true;
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) saveBtn.disabled = !isEditing;
  });

  // ── Phone auto-format as typed ──
  const phoneInput = document.getElementById('phoneInput');
  if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
      let val = e.target.value.replace(/\D/g, '');
      if (val.startsWith('09')) {
        val = val.slice(0, 11);
        if (val.length > 7) {
          e.target.value = val.slice(0, 4) + ' ' + val.slice(4, 7) + ' ' + val.slice(7);
        } else if (val.length > 4) {
          e.target.value = val.slice(0, 4) + ' ' + val.slice(4);
        } else {
          e.target.value = val;
        }
      } else {
        val = val.slice(0, 10);
        if (val.length > 6) {
          e.target.value = '(' + val.slice(0, 3) + ') ' + val.slice(3, 6) + '-' + val.slice(6);
        } else if (val.length > 3) {
          e.target.value = '(' + val.slice(0, 3) + ') ' + val.slice(3);
        } else if (val.length > 0) {
          e.target.value = '(' + val;
        }
      }
    });
  }

  // ── Birthday validation: must be at least 18 ──
  function checkBirthday() {
    const input = document.getElementById('birthdayInput');
    const hint = document.getElementById('birthdayHint');
    if (!input || !input.value) return;
    const birth = new Date(input.value);
    const age = (Date.now() - birth.getTime()) / (365.25 * 24 * 60 * 60 * 1000);
    if (age < 18 && hint) {
      hint.textContent = 'Must be at least 18 years old.';
      hint.className = 'field-error-msg';
    } else if (hint) {
      hint.textContent = 'The contact person must be at least 18 years old.';
      hint.className = 'field-hint';
    }
  }

  // ── Scroll to first error on load ──
  document.addEventListener('DOMContentLoaded', () => {
    const firstError = document.querySelector('.field-error-msg');
    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });

  // Inline save for normal edits; confirmation modal only for entity-name changes.
  function saveAccount() {
    const form = document.getElementById('accountForm');
    const summary = document.getElementById('accountSaveSummary');
    const saveBtn = document.getElementById('saveBtn');
    const changes = [];

    form.querySelectorAll('.acct-input').forEach(input => {
      const original = input.defaultValue || input.getAttribute('value') || '';
      if (input.value !== original && input.name) {
        const label = form.querySelector(`[for="${input.id}"] .label, label:has([name="${input.name}"]) .label`);
        const labelText = label ? label.textContent : input.name;
        changes.push(`<li><strong>${labelText}</strong> will be updated</li>`);
      }
    });

    const nameInput = form.querySelector('[name="entity_name"]');
    const entityChanged = !!(nameInput && nameInput.value !== nameInput.defaultValue);

    if (!changes.length && !entityChanged) {
      if (window.showToast) window.showToast('No changes to save.', { type: 'info' });
      return;
    }

    document.getElementById('accountConfirm').value = entityChanged ? '1' : '0';

    if (!entityChanged) {
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.dataset.loading = '1';
      }
      form.submit();
      return;
    }

    changes.push('<li><strong>Entity name</strong> is changing - this affects all downstream records. Please confirm this is intentional.</li>');
    summary.innerHTML = changes.join('');

    document.getElementById('confirmAccountSave-confirm').onclick = function() {
      closeConfirmModal('confirmAccountSave');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.dataset.loading = '1';
      }
      form.submit();
    };

    openConfirmModal('confirmAccountSave');
  }

  showAcctTab('{{ $entityHasError ? 'company' : ($contactHasError ? 'contact' : 'company') }}');
  setEditing({{ $errors->any() ? 'true' : 'false' }});

  // Phone: strip spaces so it matches the 09XXXXXXXXX rule the server validates
  document.querySelector('[name=contact_phone]')?.addEventListener('blur', e => { e.target.value = e.target.value.replace(/\s+/g, ''); });
</script>
@endpush
