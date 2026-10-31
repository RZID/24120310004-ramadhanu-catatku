@props(['cancel', 'label' => 'Simpan'])

<div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 pt-4">
    <x-button as="a" :href="$cancel" variant="secondary">Batal</x-button>
    <x-button type="submit">{{ $label }}</x-button>
</div>
