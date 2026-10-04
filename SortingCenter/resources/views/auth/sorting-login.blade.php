<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sorting Center — Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
<div class="flex min-h-screen">

  <div class="relative hidden flex-1 basis-[46%] flex-col justify-between overflow-hidden bg-primary p-11 text-primary-foreground md:flex">
    <div class="pointer-events-none absolute -top-24 -right-24 size-72 rounded-full border border-white/10"></div>
    <div class="pointer-events-none absolute -top-10 -right-10 size-52 rounded-full border border-white/10"></div>

    <div class="z-10 flex items-center gap-3">
      <img src="{{ asset('images/mimoo-logo.svg') }}" alt="Mimoo Logo" class="h-10 w-auto">
    </div>

    <div class="z-10 max-w-sm">
      <h1 class="mb-3.5 text-[28px] leading-tight font-extrabold tracking-tight" style="font-family:'Syncopate',sans-serif">Every parcel, sorted by barangay and on its way.</h1>
      <p class="text-[13.5px] leading-relaxed text-primary-foreground/70">Sign in to receive, scan, sort, and hand off parcels to the right rider — covering Santa Cruz, Laguna, barangay by barangay.</p>
      <div class="mt-6 flex flex-wrap gap-2">
        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11.5px] font-semibold">Receive &amp; Scan</span>
        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11.5px] font-semibold">Sort by Barangay</span>
        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11.5px] font-semibold">Rider Assignment</span>
        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11.5px] font-semibold">Live Monitoring</span>
      </div>
    </div>

    <div class="z-10 text-[11.5px] text-primary-foreground/50">© 2026 Marketplace ERP · Logistics / Sorting Center Module</div>
  </div>

  <div class="flex flex-1 basis-[54%] items-center justify-center bg-background px-6 py-10">
    <div class="w-full max-w-sm">
      <div class="mb-2 text-xs font-bold tracking-wide text-muted-foreground uppercase">Logistics Access</div>
      <h2 class="mb-1.5 text-[22px] font-extrabold tracking-tight" style="font-family:'Syncopate',sans-serif">Sign in to your hub</h2>
      <div class="mb-7 text-[13px] text-muted-foreground">Enter the credentials given after admin approval.</div>

      <div class="mb-5.5 flex rounded-md border border-border bg-card p-1">
        <label class="flex-1 cursor-pointer rounded px-1.5 py-2 text-center text-xs font-semibold {{ old('role', 'staff') === 'staff' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground' }} role-opt">
          <input type="radio" name="role" value="staff" form="loginForm" class="hidden" {{ old('role', 'staff') === 'staff' ? 'checked' : '' }}>
          Sorting Staff
        </label>
        <label class="flex-1 cursor-pointer rounded px-1.5 py-2 text-center text-xs font-semibold {{ old('role') === 'rider' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground' }} role-opt">
          <input type="radio" name="role" value="rider" form="loginForm" class="hidden" {{ old('role') === 'rider' ? 'checked' : '' }}>
          Rider
        </label>
      </div>

      @if ($errors->any())
        <div class="mb-4 flex items-center gap-2 rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2.5 text-[12.5px] text-destructive">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form id="loginForm" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
          <label for="email" class="mb-1.5 block text-[12.5px] font-semibold">Email address</label>
          <div class="flex items-center gap-2.5 rounded-md border border-input bg-card px-3.5 py-2.5 focus-within:border-ring focus-within:ring-3 focus-within:ring-ring/20">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-muted-foreground"><path d="M22 6 12 13 2 6"/><path d="M2 6h20v12H2z"/></svg>
            <input type="email" id="email" name="email" placeholder="you@santacruzhub.ph" value="{{ old('email') }}" required autofocus class="w-full border-none bg-transparent text-[13.5px] outline-none placeholder:text-muted-foreground">
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="mb-1.5 block text-[12.5px] font-semibold">Password</label>
          <div class="flex items-center gap-2.5 rounded-md border border-input bg-card px-3.5 py-2.5 focus-within:border-ring focus-within:ring-3 focus-within:ring-ring/20">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-muted-foreground"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" id="password" name="password" placeholder="••••••••" required class="w-full border-none bg-transparent text-[13.5px] outline-none placeholder:text-muted-foreground">
            <svg class="shrink-0 cursor-pointer text-muted-foreground" id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
          </div>
        </div>

        <div class="mb-5.5 flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-[12.5px] text-muted-foreground"><input type="checkbox" name="remember" value="1" class="accent-primary">Keep me signed in</label>
          <a class="cursor-pointer text-[12.5px] font-semibold text-foreground hover:underline">Forgot password?</a>
        </div>

        <button type="submit" class="w-full rounded-md bg-primary py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">Sign in</button>
      </form>

      <div class="my-5.5 flex items-center gap-3 text-[11.5px] text-muted-foreground">
        <span class="h-px flex-1 bg-border"></span>or<span class="h-px flex-1 bg-border"></span>
      </div>
      <div class="text-center text-[12.5px] text-muted-foreground">Not registered yet? <a class="cursor-pointer font-semibold text-foreground hover:underline">Apply as Rider / Sorting Staff</a></div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll(".role-opt").forEach(el => {
  el.addEventListener("click", () => {
    document.querySelectorAll(".role-opt").forEach(o => o.classList.remove("bg-primary", "text-primary-foreground"));
    document.querySelectorAll(".role-opt").forEach(o => o.classList.add("text-muted-foreground"));
    el.classList.remove("text-muted-foreground");
    el.classList.add("bg-primary", "text-primary-foreground");
    el.querySelector('input[type=radio]').checked = true;
  });
});
document.getElementById("eyeIcon").addEventListener("click", () => {
  const pw = document.getElementById("password");
  pw.type = pw.type === "password" ? "text" : "password";
});
</script>
</body>
</html>
