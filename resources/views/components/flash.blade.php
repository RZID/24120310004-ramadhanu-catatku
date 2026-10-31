@if (session('status'))
    <div
        class="flex items-start gap-3 rounded-xl bg-accent-50 px-4 py-3 text-sm text-accent-900 ring-1 ring-accent-200 ring-inset"
        role="status"
    >
        <x-icon name="check-circle" class="mt-px size-5 shrink-0 text-accent-600" />
        <p>{{ session('status') }}</p>
    </div>
@endif

@if (session('error'))
    <div
        class="flex items-start gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-900 ring-1 ring-rose-200 ring-inset"
        role="alert"
    >
        <x-icon name="alert" class="mt-px size-5 shrink-0 text-rose-600" />
        <p>{{ session('error') }}</p>
    </div>
@endif
