@extends('layouts.sorting')

@section('title', 'New Issue Report')

@section('content')
  <x-page-head eyebrow="Help Desk" title="New Issue Report" sub="Describe the problem. We keep every reply and status change on the ticket timeline.">
    <a href="{{ route('support.index') }}" class="btn-soft"><x-icon name="arrow-right" :size="16" class="rotate-180" />Back</a>
  </x-page-head>

  @include('partials.flash')

  <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data" class="card max-w-3xl space-y-5 p-6">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
      <label class="text-[12px] font-semibold text-muted-foreground">Category
        <select name="category" required class="field mt-1 w-full">
          @foreach ($categories as $key => $label)
            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
          @endforeach
        </select>
        @error('category')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
      </label>
      <label class="text-[12px] font-semibold text-muted-foreground">Priority
        <select name="priority" class="field mt-1 w-full">
          @foreach ($priorities as $key => $label)
            <option value="{{ $key }}" @selected(old('priority', 'Normal') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </label>
    </div>

    <label class="block text-[12px] font-semibold text-muted-foreground">Subject
      <input name="subject" required minlength="5" maxlength="150" value="{{ old('subject') }}" class="field mt-1 w-full" placeholder="Short summary of the issue">
      @error('subject')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
    </label>

    <label class="block text-[12px] font-semibold text-muted-foreground">Description
      <textarea name="description" required minlength="20" maxlength="3000" rows="6" class="field mt-1 w-full" placeholder="What happened? Include parcel or rider references.">{{ old('description') }}</textarea>
      @error('description')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
    </label>

    <div class="grid gap-4 sm:grid-cols-3">
      <label class="text-[12px] font-semibold text-muted-foreground">Parcel / rider reference
        <input name="related_reference" value="{{ old('related_reference') }}" maxlength="40" class="field mt-1 w-full" placeholder="e.g. MIM-1024">
      </label>
      <label class="text-[12px] font-semibold text-muted-foreground">Contact email
        <input type="email" name="contact_email" value="{{ old('contact_email') }}" maxlength="150" class="field mt-1 w-full">
      </label>
      <label class="text-[12px] font-semibold text-muted-foreground">Contact phone
        <input name="contact_phone" value="{{ old('contact_phone') }}" maxlength="20" class="field mt-1 w-full" placeholder="0917 555 0000">
      </label>
    </div>

    <label class="block text-[12px] font-semibold text-muted-foreground">Attachments (JPG, PNG or PDF, up to 3 files / 5 MB each)
      <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="field mt-1 w-full">
      @error('attachments.*')<span class="mt-1 block text-[11.5px] text-red-600">{{ $message }}</span>@enderror
    </label>

    <div class="flex justify-end gap-2">
      <a href="{{ route('support.index') }}" class="btn-ghost">Cancel</a>
      <button type="submit" class="btn-primary">Submit Report</button>
    </div>
  </form>
@endsection
