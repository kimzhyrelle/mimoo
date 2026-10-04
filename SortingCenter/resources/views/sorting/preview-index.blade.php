<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sorting Center — UI Preview</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-background p-6 font-sans antialiased">
  <div class="w-full max-w-sm rounded-lg border border-border bg-card p-6 shadow-sm">
    <h1 class="mb-1 text-lg font-semibold">UI Preview</h1>
    <p class="mb-5 text-[13px] text-muted-foreground">No login, no database — fake data only.</p>
    <div class="flex flex-col gap-2">
      @foreach ([
        ['Dashboard', 'preview.dashboard'],
        ['Incoming Parcels', 'preview.parcels.incoming'],
        ['Sort Parcels', 'preview.parcels.sort'],
        ['Delivery Monitoring', 'preview.delivery.monitoring'],
        ['Rider Management', 'preview.riders.index'],
        ['Account', 'preview.account.index'],
      ] as [$label, $routeName])
        <a href="{{ route($routeName) }}" class="rounded-md border border-border px-3.5 py-2.5 text-[13.5px] font-medium hover:bg-accent hover:text-accent-foreground">{{ $label }}</a>
      @endforeach
    </div>
  </div>
</body>
</html>
