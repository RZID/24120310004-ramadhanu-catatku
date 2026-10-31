@props(['amount', 'type'])

@php
    $income = $type === \App\Enums\TransactionType::Income;
@endphp

<span
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-end gap-1 font-medium tabular-nums '.($income ? 'text-income' : 'text-expense'),
    ]) }}
>
    <x-icon :name="$type->icon()" class="size-4 shrink-0" />
    <span class="sr-only">{{ $type->label() }}</span>
    <span>{{ $income ? '+' : '-' }} {{ \App\Support\Money::rupiah($amount) }}</span>
</span>
