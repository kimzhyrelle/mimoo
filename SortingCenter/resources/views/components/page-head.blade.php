{{--
  <x-page-head> — slim action bar.
  The large h1 + description block has been replaced by the breadcrumb in the top bar.
  This component now renders only the eyebrow pill (optional) and the action buttons slot.
  The title & sub props are kept so existing calls don't break, but they are no longer rendered
  as visible headings. The page title is shown in the top-bar breadcrumb instead.
--}}
@props(['eyebrow' => null, 'title' => '', 'sub' => null])

@if ($eyebrow || $slot->isNotEmpty())
  <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2">
      @if ($eyebrow)
        <span class="eyebrow"><span class="size-1.5 rounded-full bg-primary"></span>{{ $eyebrow }}</span>
      @endif
      @isset($meta)<span class="text-[11px] font-medium text-muted-foreground">{{ $meta }}</span>@endisset
    </div>
    @if ($slot->isNotEmpty())
      <div class="flex flex-wrap items-center gap-2.5">{{ $slot }}</div>
    @endif
  </div>
@endif
