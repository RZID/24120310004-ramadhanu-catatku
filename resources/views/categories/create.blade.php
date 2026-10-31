<x-layouts.app title="Tambah Kategori">
    <x-page-header
        title="Tambah kategori"
        description="Pilih tipe yang sesuai: pemasukan untuk uang masuk, pengeluaran untuk uang keluar."
    />

    <x-card>
        @include('categories.partials.form')
    </x-card>
</x-layouts.app>
