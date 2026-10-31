<x-layouts.app title="Ubah Transaksi">
    <x-page-header
        title="Ubah transaksi"
        :description="'Mengubah transaksi '.\App\Support\Money::rupiah($transaction->amount).' pada '.$transaction->transacted_on->locale('id')->translatedFormat('j M Y').'.'"
    />

    <x-card>
        @include('transactions.partials.form', ['transaction' => $transaction])
    </x-card>
</x-layouts.app>
