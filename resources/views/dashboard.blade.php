<x-layouts.app title="Dashboard">
    <x-page-header
        title="Dashboard"
        :description="'Halo, '.auth()->user()->name.'. Ringkasan keuanganmu.'"
    >
        <x-slot:actions>
            <form method="GET" action="{{ route('dashboard') }}" class="flex items-end gap-2">
                <x-input name="month" label="Bulan" type="month" :value="$month" />
                <x-button type="submit">Tampilkan</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Pemasukan"
            :value="\App\Support\Money::rupiah($summary->income)"
            tone="income"
            icon="arrow-up"
        />
        <x-stat-card
            label="Pengeluaran"
            :value="\App\Support\Money::rupiah($summary->expense)"
            tone="expense"
            icon="arrow-down"
        />
        <x-stat-card
            :label="'Saldo '.$monthLabel"
            :value="\App\Support\Money::rupiah($summary->balance())"
        />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-card
            class="lg:col-span-2"
            title="Pengeluaran per kategori"
            :description="'Bulan '.$monthLabel"
        >
            @if ($summary->expenseByCategory->isEmpty())
                <p class="py-10 text-center text-sm text-slate-500">Belum ada pengeluaran pada bulan ini.</p>
            @else
                <div class="h-64">
                    <canvas
                        data-chart="expense-by-category"
                        data-chart-data="{{ json_encode($chart, JSON_THROW_ON_ERROR) }}"
                        role="img"
                        aria-label="Grafik pengeluaran per kategori bulan {{ $monthLabel }}"
                    ></canvas>
                </div>

                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach ($summary->expenseByCategory as $row)
                        <li class="flex items-center justify-between gap-4">
                            <span class="text-slate-600">{{ $row['name'] }}</span>
                            <span class="font-medium tabular-nums text-slate-900">
                                {{ \App\Support\Money::rupiah($row['total']) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Saldo per dompet" description="Pemasukan dikurangi pengeluaran.">
            @if ($wallets->isEmpty())
                <p class="text-sm text-slate-500">
                    Belum ada dompet.
                    <a href="{{ route('wallets.create') }}" class="font-semibold text-accent-700 hover:text-accent-800">Tambah dompet</a>.
                </p>
            @else
                <ul class="divide-y divide-slate-200">
                    @foreach ($wallets as $wallet)
                        <li class="flex items-center justify-between gap-4 py-2.5 text-sm">
                            <span class="text-slate-600">{{ $wallet['name'] }}</span>
                            <span class="font-medium tabular-nums {{ $wallet['balance'] < 0 ? 'text-expense' : 'text-slate-900' }}">
                                {{ \App\Support\Money::rupiah($wallet['balance']) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    <x-card title="Transaksi terakhir" description="Lima transaksi terbaru.">
        <x-slot:actions>
            <x-button as="a" :href="route('transactions.index')" variant="secondary" class="px-3 py-1.5 text-xs">
                Lihat semua
            </x-button>
        </x-slot:actions>

        @if ($recent->isEmpty())
            <div class="py-6 text-center">
                <p class="text-sm font-medium text-slate-900">Belum ada transaksi</p>
                <p class="mt-1 text-sm text-slate-500">Catat pemasukan atau pengeluaran pertama kamu.</p>

                <x-button as="a" :href="route('transactions.create')" class="mt-4">
                    Catat transaksi
                </x-button>
            </div>
        @else
            <x-table :head="['Tanggal', 'Kategori', 'Dompet', 'Catatan', 'Jumlah']">
                @foreach ($recent as $transaction)
                    <x-tr>
                        <x-td label="Tanggal" class="whitespace-nowrap text-slate-600">
                            <x-date :value="$transaction->transacted_on" />
                        </x-td>

                        <x-td label="Kategori">
                            <span class="font-medium text-slate-900">{{ $transaction->category->name }}</span>
                        </x-td>

                        <x-td label="Dompet" class="text-slate-600">{{ $transaction->wallet->name }}</x-td>

                        <x-td label="Catatan" class="text-slate-600">{{ $transaction->note ?? '—' }}</x-td>

                        <x-td label="Jumlah">
                            <x-amount :amount="$transaction->amount" :type="$transaction->type" />
                        </x-td>
                    </x-tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-layouts.app>
