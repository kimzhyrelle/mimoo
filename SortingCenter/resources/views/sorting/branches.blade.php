@extends('layouts.sorting')

@section('title', 'Branches & Checklists')

@section('content')

  <x-page-head eyebrow="Facility Operations" title="Branches & Operational Checklists" sub="Regional network node hierarchy and mandatory shift checklists.">
    <x-slot:meta>• Shift: {{ $shift['label'] }} - {{ $shift['date']->format('M j, Y') }}</x-slot:meta>
    <span class="card flex items-center gap-2 px-4 py-2.5 text-[12px] font-semibold"><span class="size-2 rounded-full bg-emerald-500"></span>Regional Sync: Nominal</span>
    <a href="{{ route('dashboard') }}" class="btn-soft"><x-icon name="history" :size="15" />Audit Logs</a>
  </x-page-head>

  @include('partials.flash')

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1fr_1.25fr]">

    {{-- ═════════ Branch hierarchy ═════════ --}}
    <div class="space-y-5">
      <section class="card p-6">
        <div class="mb-5 flex items-start justify-between">
          <div>
            <h2 class="h-display text-[19px]">Branch Network Hierarchy</h2>
            <p class="text-[12px] text-muted-foreground">Southern Luzon Cluster Structure</p>
          </div>
          <span class="flex size-9 items-center justify-center rounded-xl bg-secondary text-primary"><x-icon name="network" :size="16" /></span>
        </div>

        <ol class="relative space-y-4">
          <span class="absolute bottom-6 left-[19px] top-6 w-0.5 bg-accent"></span>
          @foreach ($branches as $b)
            <li class="relative flex gap-4">
              <span class="relative z-10 flex size-10 shrink-0 items-center justify-center rounded-full text-white {{ $b['tier'] === 1 ? 'bg-ink' : ($b['tier'] === 4 ? 'bg-ink' : 'bg-primary') }}"><x-icon :name="$b['icon']" :size="16" /></span>
              <div class="flex-1 rounded-2xl p-4 {{ $b['tier'] === 1 ? 'bg-accent/70' : 'bg-card shadow-[0_1px_6px_rgba(15,42,95,.08)]' }}">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] font-bold {{ $b['tier'] === 4 ? 'text-muted-foreground' : 'text-primary' }}">{{ $b['code'] }} · Tier {{ $b['tier'] }} • {{ $b['label'] }}</span>
                </div>
                <h3 class="h-display mt-1 text-[16px] leading-snug">{{ $b['code'] }} · {{ $b['name'] }}</h3>
                <p class="text-[12px] text-muted-foreground">{{ $b['sub'] }}</p>
                <div class="mt-3 flex items-center justify-between text-[12px]">
                  <span class="text-muted-foreground">Max Throughput</span>
                  <span class="rounded-lg bg-card px-2.5 py-1 font-bold shadow-sm">{{ $b['capacity'] }} pkgs/day</span>
                </div>
              </div>
            </li>
          @endforeach
        </ol>
      </section>

      <section class="flex items-start gap-4 rounded-2xl bg-ink p-5 text-ink-foreground">
        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white/10"><x-icon name="qr" :size="20" /></span>
        <div>
          <div class="h-display text-[16px]">Strict Matrix Standard</div>
          <p class="mt-1 text-[12px] leading-relaxed text-white/70">All nodes strictly enforce 2D QR validation. Linear 1D barcode inputs are disabled across all branches.</p>
        </div>
      </section>
    </div>

    {{-- ═════════ Checklists ═════════ --}}
    <div class="space-y-5">
      <form method="POST" action="{{ route('branches.checklist.submit') }}" id="checklistForm" class="card p-6">
        @csrf
        <input type="hidden" name="list" id="fList" value="inbound">
        <input type="hidden" name="completed" id="fDone" value="0">
        <input type="hidden" name="total" id="fTotal" value="1">

        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="h-display text-[19px]">Operational Checklists</h2>
            <p class="max-w-xs text-[12px] text-muted-foreground">Mandatory procedure verification per shift sequence</p>
          </div>
          <div class="flex rounded-xl bg-secondary p-1 text-[12.5px] font-semibold">
            @foreach (['inbound' => 'Inbound Checklist', 'outbound' => 'Outbound Checklist'] as $key => $label)
              <button type="button" data-list-tab="{{ $key }}" class="list-tab rounded-lg px-4 py-2 transition">{{ $label }}</button>
            @endforeach
          </div>
        </div>

        @foreach ($checklists as $key => $list)
          @php
            $doneCount = collect($list['items'])->where('done', true)->count();
            $itemCount = count($list['items']);
            $isSubmitted = $list['submitted'];
          @endphp
          <div data-list="{{ $key }}" class="hidden"
               data-submitted="{{ $isSubmitted ? '1' : '0' }}"
               data-submitted-by="{{ $list['submitted_by'] }}"
               data-submitted-at="{{ optional($list['submitted_at'])->toIso8601String() }}">
            <div class="tile mb-5 flex flex-wrap items-center gap-4 rounded-2xl">
              <span class="flex size-11 items-center justify-center rounded-full bg-ink text-[13px] font-bold text-white">{{ $supervisor['initials'] }}</span>
              <div class="flex-1">
                <div class="h-display text-[15px]">{{ $supervisor['name'] }}</div>
                <div class="text-[12px] text-muted-foreground">{{ $supervisor['title'] }}</div>
              </div>
              <div class="flex gap-6 text-[12px]">
                <div><div class="label">Station</div><div class="font-bold">{{ $list['station'] }}</div></div>
                <div><div class="label">Shift</div><div class="font-bold">{{ $list['shift'] }}</div></div>
              </div>
            </div>

            <div class="mb-2 flex items-center justify-between text-[12.5px] font-bold">
              <span>{{ $list['title'] }}</span>
              <span class="text-primary"><span data-progress-text>{{ $doneCount }} of {{ $itemCount }} Completed ({{ $itemCount ? round($doneCount / $itemCount * 100) : 0 }}%)</span></span>
            </div>
            <div class="mb-5 h-2.5 overflow-hidden rounded-full bg-accent"><div data-progress-bar class="h-full rounded-full bg-primary transition-all" style="width:{{ $itemCount ? round($doneCount / $itemCount * 100) : 0 }}%"></div></div>

            <div class="space-y-3">
              @foreach ($list['items'] as $item)
                <label class="flex cursor-pointer items-start gap-4 rounded-2xl bg-secondary p-4 transition hover:bg-accent/60">
                  <input type="checkbox" class="peer sr-only" data-item data-key="{{ $item['key'] }}" {{ $item['done'] ? 'checked' : '' }} {{ $isSubmitted ? 'disabled' : '' }}>
                  <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-md border-2 border-muted-foreground/40 bg-card text-white transition peer-checked:border-primary peer-checked:bg-primary"><x-icon name="check" :size="13" :stroke="3.5" /></span>
                  <span class="flex-1">
                    <span class="flex flex-wrap items-center gap-2"><span class="h-display text-[15px]">{{ $item['title'] }}</span></span>
                    <span class="mt-1 block text-[12.5px] leading-relaxed text-muted-foreground">{{ $item['body'] }}</span>
                    {{-- Note + photo upload per item --}}
                    <span class="mt-2 flex flex-wrap items-center gap-2">
                      <input type="text" placeholder="Add note..." value="{{ $item['note'] }}" class="checklist-note" onclick="event.stopPropagation()" maxlength="200" {{ $isSubmitted ? 'disabled' : '' }}>
                      <button type="button" class="checklist-photo-btn" title="Upload photo" {{ $isSubmitted ? 'disabled' : '' }}>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        Photo
                      </button>
                      <input type="file" accept="image/*" class="checklist-photo-input hidden" data-key="{{ $item['key'] }}">
                      @if ($item['photo_path'])
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['photo_path']) }}" target="_blank" class="checklist-photo-link text-[11px] font-semibold text-primary hover:underline" onclick="event.stopPropagation()">View photo</a>
                      @else
                        <span data-photo-name class="text-[11px] text-muted-foreground"></span>
                      @endif
                    </span>
                  </span>
                </label>
              @endforeach
            </div>
          </div>
        @endforeach

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
          <span class="flex items-center gap-2 text-[12px] text-muted-foreground"><x-icon name="shield" :size="14" />Recorded to Regional Ledger ({{ $hub->name }} Node)</span>
          <button type="button" id="checklistSubmitBtn" onclick="confirmSubmitChecklist()" class="btn-ink px-6 py-3.5"><x-icon name="check-circle" :size="16" />Submit Checklist</button>
        </div>
      </form>

      <div class="grid grid-cols-2 gap-4">
        <div class="card p-5">
          <div class="flex items-center justify-between"><span class="label">Shift Compliance</span><x-icon name="check-circle" :size="17" class="text-primary" /></div>
          <div class="h-display mt-2 text-[34px] leading-none">98.4%</div>
          <div class="mt-2 text-[12px] font-semibold text-emerald-600">+1.2% vs yesterday</div>
          {{-- Trend sparkline --}}
          <svg class="mt-3 h-8 w-full" viewBox="0 0 100 24" preserveAspectRatio="none">
            <polyline points="0,18 12,15 25,16 37,12 50,10 62,8 75,6 87,5 100,4" fill="none" stroke="#16a34a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="100" cy="4" r="2" fill="#16a34a" />
          </svg>
        </div>
        <div class="card p-5">
          <div class="flex items-center justify-between"><span class="label">QR Matrix Scan Rate</span><x-icon name="qr" :size="17" class="text-primary" /></div>
          <div class="h-display mt-2 text-[34px] leading-none">100%</div>
          <div class="mt-2 flex items-center gap-1 text-[12px] font-semibold text-emerald-600"><x-icon name="check" :size="12" />Zero Barcode Fallbacks</div>
          {{-- Trend sparkline --}}
          <svg class="mt-3 h-8 w-full" viewBox="0 0 100 24" preserveAspectRatio="none">
            <polyline points="0,6 12,5 25,5 37,4 50,4 62,4 75,3 87,3 100,2" fill="none" stroke="#1d4ed8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="100" cy="2" r="2" fill="#1d4ed8" />
          </svg>
        </div>
      </div>

      {{-- Submitted state — shows after checklist is submitted --}}
      <div id="submittedState" class="hidden rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-500/10">
        <div class="flex items-center gap-3">
          <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-500 text-white"><x-icon name="check-circle" :size="18" /></span>
          <div>
            <div class="h-display text-[15px] text-emerald-700 dark:text-emerald-300">Checklist Submitted</div>
            <div class="text-[12px] text-muted-foreground" id="submittedBy">Submitted</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Confirm: Submit Checklist --}}
  <x-confirm-modal id="confirmChecklist" title="Submit Checklist?" confirmLabel="Submit" requireCheck>
    <ul class="confirm-summary-list" id="checklistSummary">
      <li>Checklist will be recorded to the Regional Ledger</li>
      <li>Submitted checklist cannot be unsubmitted</li>
    </ul>
  </x-confirm-modal>

@endsection

@push('scripts')
<script>
  const form = document.getElementById('checklistForm');
  const submitBtn = document.getElementById('checklistSubmitBtn');
  let activeList = 'inbound';

  function panelFor(name) {
    return document.querySelector('[data-list="' + name + '"]');
  }

  function collectItems(panel) {
    return [...panel.querySelectorAll('[data-item]')].map(cb => {
      const label = cb.closest('label');
      const note = label.querySelector('.checklist-note');
      return {
        key: cb.dataset.key,
        checked: cb.checked,
        note: note ? note.value : '',
      };
    });
  }

  function collectPhotos(panel) {
    const photos = {};
    panel.querySelectorAll('.checklist-photo-input').forEach(input => {
      if (input.files && input.files[0]) {
        photos[input.dataset.key] = input.files[0];
      }
    });
    return photos;
  }

  function refresh(panel) {
    if (!panel) return;
    const boxes = [...panel.querySelectorAll('[data-item]')];
    const done = boxes.filter(b => b.checked).length;
    const pct = boxes.length ? Math.round(done / boxes.length * 100) : 0;
    panel.querySelector('[data-progress-text]').textContent = `${done} of ${boxes.length} Completed (${pct}%)`;
    panel.querySelector('[data-progress-bar]').style.width = pct + '%';
    document.getElementById('fDone').value = done;
    document.getElementById('fTotal').value = boxes.length;
  }

  function updateSubmitState(panel) {
    const isSubmitted = panel && panel.dataset.submitted === '1';
    submitBtn.disabled = !!isSubmitted;
    submitBtn.innerHTML = isSubmitted
      ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg> Submitted'
      : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg> Submit Checklist';

    const banner = document.getElementById('submittedState');
    if (isSubmitted) {
      banner.classList.remove('hidden');
      const when = panel.dataset.submittedAt
        ? new Date(panel.dataset.submittedAt).toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' })
        : 'this shift';
      document.getElementById('submittedBy').textContent =
        'Submitted by ' + (panel.dataset.submittedBy || 'Sorting Staff') + ' at ' + when;
    } else {
      banner.classList.add('hidden');
    }
  }

  function lockPanel(panel, data) {
    panel.dataset.submitted = '1';
    if (data) {
      panel.dataset.submittedBy = data.submitted_by || '';
      panel.dataset.submittedAt = data.submitted_at || '';
    }
    panel.querySelectorAll('[data-item], .checklist-note, .checklist-photo-btn, .checklist-photo-input').forEach(el => {
      el.disabled = true;
    });
    updateSubmitState(panel);
  }

  function submitChecklist() {
    const panel = panelFor(activeList);
    if (!panel || panel.dataset.submitted === '1') return;

    const body = new FormData();
    body.append('_token', document.querySelector('meta[name=csrf-token]').content);
    body.append('list', activeList);
    body.append('items', JSON.stringify(collectItems(panel)));

    const photos = collectPhotos(panel);
    Object.keys(photos).forEach(key => body.append('photos[' + key + ']', photos[key]));

    fetch(form.action, {
      method: 'POST',
      body,
      headers: { 'Accept': 'application/json' },
    })
      .then(async response => {
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unable to submit checklist.');
        return data;
      })
      .then(data => {
        lockPanel(panel, data);
        if (window.showToast) window.showToast(data.message);
      })
      .catch(error => {
        if (window.showToast) window.showToast(error.message, { type: 'error' });
        else alert(error.message);
      });
  }

  function confirmSubmitChecklist() {
    const panel = panelFor(activeList);
    if (!panel || panel.dataset.submitted === '1') return;

    const done = panel.querySelectorAll('[data-item]:checked').length;
    const total = panel.querySelectorAll('[data-item]').length;
    const pct = total ? Math.round(done / total * 100) : 0;

    document.getElementById('checklistSummary').innerHTML =
      '<li>' + done + ' of ' + total + ' items completed (' + pct + '%)</li>' +
      '<li>Checklist will be recorded to the Regional Ledger</li>' +
      '<li>Submitted checklist cannot be unsubmitted</li>';

    document.getElementById('confirmChecklist-confirm').onclick = function() {
      closeConfirmModal('confirmChecklist');
      submitChecklist();
    };

    openConfirmModal('confirmChecklist');
  }

  function showList(name) {
    activeList = name;
    document.getElementById('fList').value = name;
    document.querySelectorAll('[data-list]').forEach(p => p.classList.toggle('hidden', p.dataset.list !== name));
    document.querySelectorAll('.list-tab').forEach(t => {
      const on = t.dataset.listTab === name;
      t.classList.toggle('bg-ink', on); t.classList.toggle('text-ink-foreground', on);
      t.classList.toggle('text-muted-foreground', !on);
    });
    const panel = panelFor(name);
    refresh(panel);
    updateSubmitState(panel);
  }

  document.querySelectorAll('.list-tab').forEach(t => t.addEventListener('click', () => showList(t.dataset.listTab)));
  document.querySelectorAll('[data-item]').forEach(b => b.addEventListener('change', () => refresh(b.closest('[data-list]'))));
  document.querySelectorAll('[data-list]').forEach(panel => {
    refresh(panel);
    if (panel.dataset.submitted === '1') {
      lockPanel(panel);
    }
  });

  document.querySelectorAll('.checklist-photo-btn').forEach(btn => {
    btn.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      const input = btn.parentElement.querySelector('.checklist-photo-input');
      if (input && !input.disabled) input.click();
    });
  });

  document.querySelectorAll('.checklist-photo-input').forEach(input => {
    input.addEventListener('click', event => event.stopPropagation());
    input.addEventListener('change', event => {
      event.stopPropagation();
      const name = input.files && input.files[0] ? input.files[0].name : '';
      const label = input.parentElement.querySelector('[data-photo-name]');
      if (label) label.textContent = name;
    });
  });

  showList('inbound');
</script>
@endpush
