@props(['action', 'title', 'description', 'label' => 'Hapus'])

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="inline-flex">
    <x-button variant="danger" type="button" @click="open = true">
        <x-icon name="trash" class="size-4" />
        {{ $label }}
    </x-button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/40 p-4 sm:items-center"
        role="dialog"
        aria-modal="true"
        :aria-label="$el.querySelector('h2')?.textContent"
        @click.self="open = false"
    >
        <div
            x-show="open"
            x-transition
            class="w-full max-w-sm rounded-xl bg-white p-5 shadow-lg ring-1 ring-slate-200"
        >
            <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>

            <div class="mt-5 flex justify-end gap-2">
                <x-button variant="secondary" type="button" @click="open = false">Batal</x-button>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method('DELETE')

                    <x-button variant="danger" type="submit">{{ $label }}</x-button>
                </form>
            </div>
        </div>
    </div>
</div>
