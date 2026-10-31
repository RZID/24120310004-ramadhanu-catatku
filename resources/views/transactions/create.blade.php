<x-layouts.app title="Catat Transaksi">
    <x-page-header
        title="Catat transaksi"
        description="Tipe transaksi harus sama dengan tipe kategorinya."
    />

    <x-card>
        @include('transactions.partials.form')
    </x-card>
</x-layouts.app>
