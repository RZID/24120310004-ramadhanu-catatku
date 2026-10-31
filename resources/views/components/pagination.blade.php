@props(['paginator'])

@if ($paginator->hasPages())
    <nav
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white px-4 py-3 text-sm shadow-sm ring-1 ring-slate-200"
        aria-label="Navigasi halaman"
    >
        <p class="text-slate-500">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <x-button variant="secondary" as="span" class="cursor-not-allowed px-3 py-1.5 text-xs opacity-50">
                    <x-icon name="chevron-left" class="size-4" />
                    Sebelumnya
                </x-button>
            @else
                <x-button
                    as="a"
                    :href="$paginator->previousPageUrl()"
                    variant="secondary"
                    rel="prev"
                    class="px-3 py-1.5 text-xs"
                >
                    <x-icon name="chevron-left" class="size-4" />
                    Sebelumnya
                </x-button>
            @endif

            <span class="px-1 text-xs text-slate-500">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <x-button
                    as="a"
                    :href="$paginator->nextPageUrl()"
                    variant="secondary"
                    rel="next"
                    class="px-3 py-1.5 text-xs"
                >
                    Berikutnya
                    <x-icon name="chevron-right" class="size-4" />
                </x-button>
            @else
                <x-button variant="secondary" as="span" class="cursor-not-allowed px-3 py-1.5 text-xs opacity-50">
                    Berikutnya
                    <x-icon name="chevron-right" class="size-4" />
                </x-button>
            @endif
        </div>
    </nav>
@endif
