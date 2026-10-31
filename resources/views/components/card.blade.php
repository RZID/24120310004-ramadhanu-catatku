@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200']) }}>
    @if ($title !== null)
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>

                @if ($description !== null)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class(['mt-4' => $title !== null])>
        {{ $slot }}
    </div>
</section>
