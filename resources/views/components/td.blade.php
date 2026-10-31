@props(['label' => null])

<td {{ $attributes->merge(['class' => 'px-4 py-3 align-middle max-sm:flex max-sm:items-center max-sm:justify-between max-sm:gap-4 max-sm:px-0 max-sm:py-1']) }}>
    @if ($label !== null)
        <span class="hidden text-xs font-medium tracking-wide text-slate-500 uppercase max-sm:block">{{ $label }}</span>
    @endif

    <span class="max-sm:text-right">{{ $slot }}</span>
</td>
