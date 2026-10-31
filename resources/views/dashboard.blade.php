<x-layouts.app title="Dashboard">
    <x-page-header
        title="Dashboard"
        :description="'Halo, '.auth()->user()->name.'. Ini ringkasan data yang kamu catat.'"
    />

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Dompet" :value="$walletCount" icon="wallet" />
        <x-stat-card label="Kategori" :value="$categoryCount" icon="tag" />
        <x-stat-card label="Transaksi" :value="$transactionCount" icon="list" />
    </div>

    @if ($transactionCount === 0)
        <x-card title="Belum ada transaksi">
            <p class="text-sm text-slate-500">
                Setelah dompet, kategori, dan transaksi ditambahkan, ringkasan bulanan akan muncul di halaman ini.
            </p>
        </x-card>
    @endif
</x-layouts.app>
