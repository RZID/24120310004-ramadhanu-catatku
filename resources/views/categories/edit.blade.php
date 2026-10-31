<x-layouts.app title="Ubah Kategori">
    <x-page-header title="Ubah kategori" :description="'Mengubah kategori '.$category->name.'.'" />

    <x-card>
        @include('categories.partials.form', ['category' => $category])
    </x-card>
</x-layouts.app>
