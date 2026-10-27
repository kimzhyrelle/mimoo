@props([
    'tone' => 'slate',
    'icon' => null,
])

@php
    $tones = ['blue', 'green', 'amber', 'red', 'slate'];
    $tone = in_array($tone, $tones, true) ? $tone : 'slate';
@endphp

<span {{ $attributes->merge(['class' => "pill-{$tone}"]) }}>
  @if (filled($icon))
    <x-icon :name="$icon" :size="12" />
  @endif
  {{ $slot }}
</span>
