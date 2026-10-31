@props(['variant' => 'primary', 'type' => 'button', 'as' => 'button'])

@php
    $variants = [
        'primary' => 'bg-accent-600 text-white shadow-sm hover:bg-accent-700 focus-visible:outline-accent-600',
        'secondary' => 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-300 ring-inset hover:bg-slate-50 focus-visible:outline-slate-400',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700 focus-visible:outline-rose-600',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-slate-400',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60 '.$variants[$variant];
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@elseif ($as === 'span')
    <span {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </span>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
