@extends('layouts.sorting')

@section('title', 'Account Management')
@section('subtitle', 'Manage your sorting staff profile, hub documents, and preferences.')

@php
  $input = 'w-full rounded-md border border-input bg-background px-3.5 py-2.5 text-[13.5px] outline-none focus:border-ring focus:ring-3 focus:ring-ring/20';
  $inputDisabled = 'w-full rounded-md border border-input bg-muted/40 px-3.5 py-2.5 text-[13.5px] text-muted-foreground outline-none cursor-not-allowed';
  $label = 'mb-1.5 block text-[12px] font-semibold text-foreground';
@endphp

@push('styles')
<style>
@keyframes slideUp {
  from { opacity:0; transform:translateY(16px); }
  to   { opacity:1; transform:translateY(0); }
}
</style>
@endpush

@section('content')

@if (session('status'))
  <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-[13px] text-emerald-700">{{ session('status') }}</div>
@endif

<div class="flex gap-6 items-start">

  {{-- Left: Profile card --}}
  <div class="w-56 shrink-0 rounded-xl border border-border bg-card p-5 text-center shadow-sm">

    {{-- Avatar --}}
    <div class="mx-auto mb-3 flex size-20 items-center justify-center rounded-full bg-primary text-[22px] font-bold text-primary-foreground">
      LG
    </div>

    <div class="text-[14.5px] font-bold text-foreground">Liza Garcia</div>
    <div class="mt-0.5 text-[11.5px] text-muted-foreground">Sorting Staff · Santa Cruz Hub</div>

    <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-[11.5px] font-semibold text-emerald-700">
      <span class="size-1.5 rounded-full bg-emerald-500"></span>Account verified
    </div>

    <button class="mt-2.5 text-[12px] font-semibold text-primary hover:underline">Change photo</button>

    <div class="mt-4 border-t border-border pt-4 text-left space-y-2.5">
      <div class="flex items-center justify-between text-[12px]">
        <span class="text-muted-foreground">Hub</span>
        <span class="font-semibold">Santa Cruz, Laguna</span>
      </div>
      <div class="flex items-center justify-between text-[12px]">
        <span class="text-muted-foreground">Employee ID</span>
        <span class="font-bold font-mono">SC-0042</span>
      </div>
      <div class="flex items-center justify-between text-[12px]">
        <span class="text-muted-foreground">Joined</span>
        <span class="font-semibold">Jan 2026</span>
      </div>
      <div class="flex items-center justify-between text-[12px]">
        <span class="text-muted-foreground">Barangays covered</span>
        <span class="font-bold">{{ $areas->count() }}</span>
      </div>
    </div>
  </div>

  {{-- Right: Tabbed panel --}}
  <div class="flex-1 min-w-0 rounded-xl border border-border bg-card shadow-sm overflow-hidden">

    {{-- Tabs --}}
    <div class="flex border-b border-border px-5 pt-4 gap-1" id="accountTabs">
      @foreach (['profile' => 'Profile', 'documents' => 'Documents', 'security' => 'Security', 'notifications' => 'Notifications'] as $key => $label)
        <button
          onclick="switchTab('{{ $key }}')"
          id="tab-{{ $key }}"
          class="tab-btn rounded-md px-4 py-2 text-[13px] font-semibold mb-[-1px] border border-transparent transition-colors
            {{ $key === 'profile' ? 'bg-primary text-primary-foreground border-primary' : 'text-muted-foreground hover:text-foreground' }}">
          {{ $label }}
        </button>
      @endforeach
    </div>

    {{-- Profile Tab --}}
    <div id="tabpanel-profile" class="tab-panel p-6">
      <div class="mb-5">
        <div class="text-[14px] font-bold">Personal information</div>
        <div class="text-[12px] text-primary mt-0.5">This information appears on your staff badge and internal reports</div>
      </div>

      <form method="POST" action="{{ route('account.update') }}">
        @csrf

        <div class="grid grid-cols-2 gap-x-5 gap-y-4">

          <div>
            <label class="{{ $label }}">Last name</label>
            <input type="text" name="last_name" value="{{ old('last_name', 'Garcia') }}" class="{{ $input }}">
          </div>
          <div>
            <label class="{{ $label }}">First name</label>
            <input type="text" name="first_name" value="{{ old('first_name', 'Liza') }}" class="{{ $input }}">
          </div>

          <div>
            <label class="{{ $label }}">Middle initial</label>
            <input type="text" name="middle_initial" value="{{ old('middle_initial', 'P.') }}" class="{{ $input }}" maxlength="3">
          </div>
          <div>
            <label class="{{ $label }}">Sex</label>
            <select name="sex" class="{{ $input }}">
              <option value="female" selected>Female</option>
              <option value="male">Male</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="{{ $label }}">Birthday</label>
            <input type="date" name="birthday" value="{{ old('birthday', '1996-04-12') }}" class="{{ $input }}">
          </div>
          <div>
            <label class="{{ $label }}">Age</label>
            <input type="text" value="30" class="{{ $inputDisabled }}" disabled>
          </div>

          <div>
            <label class="{{ $label }}">Email address</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $hub->contact_email ?? 'liza.garcia@santacruzhub.ph') }}" class="{{ $input }}">
          </div>
          <div>
            <label class="{{ $label }}">Contact number</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone', $hub->contact_phone ?? '09171234567') }}" class="{{ $input }}">
          </div>

          <div class="col-span-2">
            <label class="{{ $label }}">Street / house number</label>
            <input type="text" name="address" value="{{ old('address', $hub->address ?? '12 Rizal St., Purok 3') }}" class="{{ $input }}">
          </div>

          <div>
            <label class="{{ $label }}">Province</label>
            <input type="text" value="Laguna" class="{{ $inputDisabled }}" disabled placeholder="Laguna">
          </div>
          <div>
            <label class="{{ $label }}">Municipality</label>
            <input type="text" value="Santa Cruz" class="{{ $inputDisabled }}" disabled placeholder="Santa Cruz">
          </div>

        </div>

        <div class="mt-6 flex justify-end gap-2.5">
          <button type="reset" class="rounded-md border border-border bg-background px-4 py-2.5 text-[13px] font-semibold text-foreground hover:bg-muted transition-colors">Cancel</button>
          <button type="submit" class="rounded-md bg-primary px-5 py-2.5 text-[13px] font-semibold text-primary-foreground hover:bg-primary/90 transition-colors">Save changes</button>
        </div>

      </form>
    </div>

    {{-- Documents Tab --}}
    <div id="tabpanel-documents" class="tab-panel p-6 hidden">
      <div class="mb-5">
        <div class="text-[14px] font-bold">Verification documents</div>
        <div class="text-[12px] text-muted-foreground mt-0.5">Required for <span class="text-primary font-semibold">admin</span> approval of this hub account</div>
      </div>

      <div class="divide-y divide-border rounded-lg border border-border overflow-hidden">

        {{-- Government-issued ID --}}
        <div class="flex items-center justify-between px-5 py-4 bg-background hover:bg-muted/20 transition-colors">
          <div class="flex items-center gap-3.5">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="9" cy="11" r="2.5"/><path d="M13 9h4M13 13h4M5 17c0-2 1.8-3 4-3s4 1 4 3"/></svg>
            </div>
            <div>
              <div class="text-[13.5px] font-semibold">Government-issued ID</div>
              <div class="text-[11.5px] text-muted-foreground mt-0.5">Uploaded Jan 14, 2026 · <span class="font-mono">JPG</span></div>
            </div>
          </div>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[12px] font-semibold text-emerald-700">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>Verified
          </span>
        </div>

        {{-- Business / DTI permit --}}
        <div class="flex items-center justify-between px-5 py-4 bg-background hover:bg-muted/20 transition-colors">
          <div class="flex items-center gap-3.5">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="text-[13.5px] font-semibold">Business / DTI permit</div>
              <div class="text-[11.5px] text-muted-foreground mt-0.5">Uploaded Jan 14, 2026 · <span class="font-mono">PDF</span></div>
            </div>
          </div>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-[12px] font-semibold text-emerald-700">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>Verified
          </span>
        </div>

        {{-- Hub facility permit renewal --}}
        <div class="flex items-center justify-between px-5 py-4 bg-background hover:bg-muted/20 transition-colors">
          <div class="flex items-center gap-3.5">
            <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
            </div>
            <div>
              <div class="text-[13.5px] font-semibold">Hub facility permit renewal</div>
              <div class="text-[11.5px] text-muted-foreground mt-0.5">Not yet uploaded</div>
            </div>
          </div>
          <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-[12px] font-semibold text-amber-700">
            <span class="size-1.5 rounded-full bg-amber-500"></span>Action needed
          </span>
        </div>

      </div>

      <div class="mt-4 flex justify-end">
        <button type="button" class="rounded-md bg-primary px-5 py-2.5 text-[13px] font-semibold text-primary-foreground hover:bg-primary/90 transition-colors">Upload document</button>
      </div>
    </div>

    {{-- Security Tab --}}
    <div id="tabpanel-security" class="tab-panel p-6 hidden">

      {{-- Change password --}}
      <div class="mb-6">
        <div class="text-[14px] font-bold">Security settings</div>
        <div class="text-[12px] text-muted-foreground mt-0.5">Manage your password and account access</div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="{{ $label }}">Current password</label>
          <input type="password" name="current_password" class="{{ $input }}" placeholder="••••••••">
        </div>
        <div>
          <label class="{{ $label }}">New password</label>
          <input type="password" name="new_password" class="{{ $input }}" placeholder="••••••••">
        </div>
        <div>
          <label class="{{ $label }}">Confirm new password</label>
          <input type="password" name="new_password_confirmation" class="{{ $input }}" placeholder="••••••••">
        </div>
        <div class="flex justify-end gap-2.5 pt-1">
          <button type="button" class="rounded-md border border-border bg-background px-4 py-2.5 text-[13px] font-semibold text-foreground hover:bg-muted transition-colors">Cancel</button>
          <button type="button" class="rounded-md bg-primary px-5 py-2.5 text-[13px] font-semibold text-primary-foreground hover:bg-primary/90 transition-colors">Update password</button>
        </div>
      </div>

      {{-- Divider --}}
      <div class="my-7 border-t border-border"></div>

      {{-- Account deactivation --}}
      <div class="rounded-lg border border-destructive/30 bg-destructive/5 p-5">
        <div class="flex items-start gap-4">
          <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-destructive/10 text-destructive mt-0.5">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-[13.5px] font-bold text-destructive">Deactivate this account</div>
            <p class="mt-1 text-[12px] text-muted-foreground leading-relaxed">
              Deactivating will immediately suspend all access to this sorting hub. Parcel data and rider assignments are preserved. Only an admin can reactivate the account.
            </p>
            <div class="mt-4">
              <button type="button" onclick="openDeactModal()"
                class="rounded-md border border-destructive/50 bg-background px-4 py-2.5 text-[13px] font-semibold text-destructive hover:bg-destructive/10 transition-colors">
                Deactivate account
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Deactivation Modal ── --}}
      <div id="deact-modal-backdrop"
           class="fixed inset-0 z-50 hidden flex items-center justify-center"
           style="background:rgba(0,0,0,.45);"
           onclick="backdropClose(event)">

        <div id="deact-modal"
             class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden"
             style="animation:slideUp .2s ease-out;">

          {{-- Modal header --}}
          <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <div class="flex items-center gap-2.5">
              <div class="flex size-8 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
              </div>
              <span class="text-[14.5px] font-bold text-destructive">Deactivate this account?</span>
            </div>
            <button onclick="closeDeactModal()" class="rounded-full p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          {{-- Panel: Step 1 — Consequences --}}
          <div id="modal-step-1">
            <div class="px-6 pt-5 pb-4">
              <p class="text-[13px] text-muted-foreground leading-relaxed mb-4">
                Before you continue, here's what will happen to your hub account:
              </p>
              <ul class="space-y-2.5 mb-5">
                @foreach ([
                  'All staff logins will be <strong>immediately disabled</strong>.',
                  'Active deliveries will be <strong>paused</strong> and riders notified.',
                  'All hub data and parcel history will be <strong>preserved</strong>.',
                  'Only a <strong>platform admin</strong> can reactivate this account.',
                ] as $item)
                  <li class="flex items-start gap-2.5 text-[12.5px] text-foreground">
                    <svg class="shrink-0 mt-0.5 text-destructive" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{!! $item !!}</span>
                  </li>
                @endforeach
              </ul>
              <p class="text-[12.5px] font-semibold text-foreground">Do you want to continue?</p>
            </div>
            <div class="flex gap-3 border-t border-border px-6 py-4">
              <button onclick="closeDeactModal()"
                class="flex-1 rounded-lg border border-border bg-background py-2.5 text-[13px] font-semibold text-foreground hover:bg-muted transition-colors">
                No, keep my account
              </button>
              <button onclick="modalGoStep(2)"
                class="flex-1 rounded-lg bg-destructive py-2.5 text-[13px] font-semibold text-white hover:bg-red-700 transition-colors">
                Yes, continue
              </button>
            </div>
          </div>

          {{-- Panel: Step 2 — Password verification --}}
          <div id="modal-step-2" class="hidden">
            <div class="px-6 pt-5 pb-4">
              <div class="mb-4 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3">
                <svg class="shrink-0 mt-0.5" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p class="text-[12px] font-semibold text-amber-700 leading-relaxed">
                  For your security, enter your password to confirm this action.
                </p>
              </div>
              <label class="mb-1.5 block text-[12.5px] font-semibold">Current password</label>
              <div class="relative">
                <input type="password" id="modal-password"
                  class="w-full rounded-lg border border-input bg-background px-3.5 py-2.5 text-[13.5px] outline-none focus:border-destructive focus:ring-3 focus:ring-destructive/20 pr-10"
                  placeholder="Enter your password"
                  oninput="modalPasswordInput()">
                <button type="button" onclick="toggleModalPw()" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                  <svg id="modal-eye" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <p id="modal-pw-error" class="mt-1.5 hidden text-[12px] text-destructive font-semibold">Password is required to continue.</p>
            </div>
            <div class="flex gap-3 border-t border-border px-6 py-4">
              <button onclick="modalGoStep(1)"
                class="flex-1 rounded-lg border border-border bg-background py-2.5 text-[13px] font-semibold text-foreground hover:bg-muted transition-colors">
                ← Go back
              </button>
              <button id="modal-confirm-btn" onclick="modalVerifyAndConfirm()"
                class="flex-1 rounded-lg bg-destructive py-2.5 text-[13px] font-semibold text-white hover:bg-red-700 transition-colors opacity-40 cursor-not-allowed"
                disabled>
                <span id="modal-confirm-label">Confirm deactivation</span>
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>

    {{-- Notifications Tab --}}
    <div id="tabpanel-notifications" class="tab-panel p-6 hidden">
      <div class="mb-5 pb-4 border-b border-border">
        <div class="text-[14px] font-bold">Notification preferences</div>
        <div class="text-[12px] text-muted-foreground mt-0.5">Choose what you're alerted about <span class="text-primary font-semibold">in real time</span></div>
      </div>

      <div class="divide-y divide-border">

        @php
          $notifications = [
            ['key' => 'new_parcel_arrivals',    'label' => 'New parcel arrivals',      'desc' => 'Alert when a rider drops off parcels at the hub',             'on' => true ],
            ['key' => 'pickup_cutoff_warnings', 'label' => 'Pickup cutoff warnings',   'desc' => 'Alert when a barangay route is nearing its ready-by cutoff',  'on' => true ],
            ['key' => 'failed_delivery_scans',  'label' => 'Failed delivery scans',    'desc' => 'Alert when a rider scans a parcel as undelivered',            'on' => true ],
            ['key' => 'new_chat_messages',      'label' => 'New chat messages',        'desc' => 'Alert for messages from riders, sellers, and admin',          'on' => true ],
            ['key' => 'weekly_report_reminders','label' => 'Weekly report reminders',  'desc' => 'Reminder to generate and submit reports',                     'on' => false],
          ];
        @endphp

        @foreach ($notifications as $n)
          <div class="flex items-center justify-between py-4 gap-6">
            <div>
              <div class="text-[13.5px] font-semibold">{{ $n['label'] }}</div>
              <div class="text-[12px] text-muted-foreground mt-0.5">{{ $n['desc'] }}</div>
            </div>
            {{-- iOS-style toggle --}}
            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
              <input type="checkbox" name="{{ $n['key'] }}" class="peer sr-only" {{ $n['on'] ? 'checked' : '' }}>
              <div class="h-6 w-11 rounded-full border border-border bg-muted transition-colors peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-focus:ring-2 peer-focus:ring-emerald-500/30"></div>
              <div class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
            </label>
          </div>
        @endforeach

      </div>

      <div class="mt-5 flex justify-end gap-2.5">
        <button type="button" class="rounded-md border border-border bg-background px-4 py-2.5 text-[13px] font-semibold text-foreground hover:bg-muted transition-colors">Cancel</button>
        <button type="button" class="rounded-md bg-primary px-5 py-2.5 text-[13px] font-semibold text-primary-foreground hover:bg-primary/90 transition-colors">Save preferences</button>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(active) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(b => {
    b.classList.remove('bg-primary', 'text-primary-foreground', 'border-primary');
    b.classList.add('text-muted-foreground');
  });
  document.getElementById('tabpanel-' + active).classList.remove('hidden');
  const btn = document.getElementById('tab-' + active);
  btn.classList.add('bg-primary', 'text-primary-foreground', 'border-primary');
  btn.classList.remove('text-muted-foreground');
}

// ── Deactivation modal ──
function openDeactModal() {
  modalGoStep(1);
  document.getElementById('deact-modal-backdrop').classList.remove('hidden');
  document.getElementById('deact-modal-backdrop').classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function closeDeactModal() {
  document.getElementById('deact-modal-backdrop').classList.add('hidden');
  document.getElementById('deact-modal-backdrop').classList.remove('flex');
  document.body.style.overflow = '';
  // Reset state
  document.getElementById('modal-password').value = '';
  document.getElementById('modal-pw-error').classList.add('hidden');
  const btn = document.getElementById('modal-confirm-btn');
  btn.disabled = true;
  btn.classList.add('opacity-40','cursor-not-allowed');
  document.getElementById('modal-confirm-label').textContent = 'Confirm deactivation';
}

function backdropClose(e) {
  if (e.target === document.getElementById('deact-modal-backdrop')) closeDeactModal();
}

function modalGoStep(n) {
  document.getElementById('modal-step-1').classList.toggle('hidden', n !== 1);
  document.getElementById('modal-step-2').classList.toggle('hidden', n !== 2);
  if (n === 2) setTimeout(() => document.getElementById('modal-password').focus(), 50);
}

function modalPasswordInput() {
  const val = document.getElementById('modal-password').value;
  const btn = document.getElementById('modal-confirm-btn');
  document.getElementById('modal-pw-error').classList.add('hidden');
  document.getElementById('modal-password').style.borderColor = '';
  if (val.length > 0) {
    btn.disabled = false;
    btn.classList.remove('opacity-40','cursor-not-allowed');
  } else {
    btn.disabled = true;
    btn.classList.add('opacity-40','cursor-not-allowed');
  }
}

function toggleModalPw() {
  const input = document.getElementById('modal-password');
  input.type = input.type === 'password' ? 'text' : 'password';
}

function modalVerifyAndConfirm() {
  const pw = document.getElementById('modal-password').value;
  if (!pw) {
    document.getElementById('modal-pw-error').classList.remove('hidden');
    document.getElementById('modal-password').style.borderColor = '#c0432f';
    document.getElementById('modal-password').focus();
    return;
  }
  // Run countdown then submit
  const btn = document.getElementById('modal-confirm-btn');
  const lbl = document.getElementById('modal-confirm-label');
  btn.disabled = true;
  btn.classList.add('opacity-60','cursor-not-allowed');
  let secs = 3;
  lbl.textContent = `Deactivating... (${secs}s)`;
  const timer = setInterval(() => {
    secs--;
    if (secs > 0) {
      lbl.textContent = `Deactivating... (${secs}s)`;
    } else {
      clearInterval(timer);
      closeDeactModal();
      const toast = document.createElement('div');
      toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-destructive/30 bg-white px-5 py-3.5 shadow-2xl text-[13px] font-semibold text-destructive';
      toast.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Deactivation request submitted — pending admin review.';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 6000);
    }
  }, 1000);
}

// Close modal on Escape key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeDeactModal();
});
</script>
@endpush
