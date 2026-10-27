@props(['label', 'value', 'icon', 'tone' => 'blue'])
@php
  $tones = [
    'blue'  => 'bg-accent text-accent-foreground',
    'green' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
    'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
    'red'   => 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-300',
  ];
@endphp
<div {{ $attributes->class(['card p-5']) }}>
  <div class="flex items-start justify-between gap-3">
    <div class="label pt-1">{{ $label }}</div>
    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['blue'] }}"><x-icon :name="$icon" :size="17" /></span>
  </div>
  <div class="h-display mt-3 text-[34px] leading-none tabular-nums">{{ $value }}</div>
  @if ($slot->isNotEmpty())<div class="mt-3">{{ $slot }}</div>@endif
</div>
