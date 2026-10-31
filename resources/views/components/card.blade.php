@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200']) }}>
    @if ($title !== null)
        <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>

        @if ($description !== null)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endif
    @endif

    <div @class(['mt-4' => $title !== null])>
        {{ $slot }}
    </div>
</section>
