<x-layouts.app title="Ubah Dompet">
    <x-page-header title="Ubah dompet" :description="'Mengubah nama '.$wallet->name.'.'" />

    <x-card>
        @include('wallets.partials.form', ['wallet' => $wallet])
    </x-card>
</x-layouts.app>
