@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'income' => 'bg-emerald-50 text-income ring-emerald-200',
        'expense' => 'bg-rose-50 text-expense ring-rose-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset '.$tones[$tone]]) }}>
    {{ $slot }}
</span>
