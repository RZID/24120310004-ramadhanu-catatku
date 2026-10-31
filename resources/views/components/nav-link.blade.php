@props(['route', 'label', 'icon'])

@php
    $active = request()->routeIs($route);
@endphp

<a
    href="{{ route($route) }}"
    @if ($active) aria-current="page" @endif
    @class([
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
        'bg-accent-50 text-accent-700' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active,
    ])
>
    <x-icon :name="$icon" class="size-5 shrink-0" />
    {{ $label }}
</a>
