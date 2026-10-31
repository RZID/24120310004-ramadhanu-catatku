@props(['label', 'value', 'icon' => null, 'tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'text-slate-900',
        'income' => 'text-income',
        'expense' => 'text-expense',
    ];
@endphp

<div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <p class="flex items-center gap-2 text-sm font-medium text-slate-500">
        @if ($icon !== null)
            <x-icon :name="$icon" class="size-4" />
        @endif
        {{ $label }}
    </p>

    <p class="mt-2 text-2xl font-semibold tabular-nums {{ $tones[$tone] }}">{{ $value }}</p>
</div>
