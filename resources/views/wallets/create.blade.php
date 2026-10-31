<x-layouts.app title="Tambah Dompet">
    <x-page-header
        title="Tambah dompet"
        description="Beri nama yang mudah dikenali, misalnya Dompet Tunai atau BCA."
    />

    <x-card>
        @include('wallets.partials.form')
    </x-card>
</x-layouts.app>
