@if ($errors->any())
  <div class="mb-5 flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
    <x-icon name="alert" :size="16" />{{ $errors->first() }}
  </div>
@elseif (session('status') || session('error'))
  <div class="mb-5 flex items-center gap-2.5 rounded-xl border px-4 py-3 text-[13px] font-medium {{ session('error') ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300' : 'border-accent bg-accent/60 text-accent-foreground' }}">
    <x-icon :name="session('error') ? 'alert' : 'check-circle'" :size="16" />{{ session('error') ?? session('status') }}
  </div>
@endif
