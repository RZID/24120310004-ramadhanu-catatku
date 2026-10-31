<x-layouts.app title="Transaksi">
    <x-page-header
        title="Transaksi"
        description="Semua pemasukan dan pengeluaran yang kamu catat."
    >
        <x-slot:actions>
            <x-button as="a" :href="route('transactions.create')">
                <x-icon name="plus" class="size-4" />
                Catat transaksi
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card title="Filter">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid gap-4 sm:grid-cols-3">
            <x-input name="month" label="Bulan" type="month" :value="request('month')" />

            <x-select
                name="type"
                label="Tipe"
                :options="['Semua tipe' => '', 'Pemasukan' => 'income', 'Pengeluaran' => 'expense']"
                :selected="request('type')"
            />

            <x-select
                name="category_id"
                label="Kategori"
                :options="['Semua kategori' => ''] + $categories->mapWithKeys(fn ($category) => [$category->name => $category->id])->all()"
                :selected="request('category_id')"
            />

            <div class="flex items-center gap-2 sm:col-span-3">
                <x-button type="submit">Terapkan filter</x-button>

                @if (request()->hasAny(['month', 'type', 'category_id']))
                    <x-button as="a" :href="route('transactions.index')" variant="secondary">Hapus filter</x-button>
                @endif
            </div>
        </form>
    </x-card>

    @if ($transactions->isEmpty())
        <x-empty-state
            icon="list"
            title="{{ request()->hasAny(['month', 'type', 'category_id']) ? 'Tidak ada transaksi yang cocok' : 'Belum ada transaksi' }}"
            description="{{ request()->hasAny(['month', 'type', 'category_id']) ? 'Coba ubah atau hapus filternya.' : 'Catat pemasukan atau pengeluaran pertama kamu.' }}"
        >
            <x-slot:action>
                @if (request()->hasAny(['month', 'type', 'category_id']))
                    <x-button as="a" :href="route('transactions.index')" variant="secondary">Hapus filter</x-button>
                @else
                    <x-button as="a" :href="route('transactions.create')">Catat transaksi</x-button>
                @endif
            </x-slot:action>
        </x-empty-state>
    @else
        <x-table :head="['Tanggal', 'Kategori', 'Dompet', 'Catatan', 'Jumlah', 'Aksi']">
            @foreach ($transactions as $transaction)
                <x-tr>
                    <x-td label="Tanggal" class="whitespace-nowrap text-slate-600">
                        <x-date :value="$transaction->transacted_on" />
                    </x-td>

                    <x-td label="Kategori">
                        <span class="inline-flex items-center gap-2">
                            <x-badge :tone="$transaction->type->value">
                                <x-icon :name="$transaction->type->icon()" class="size-3" />
                                {{ $transaction->type->label() }}
                            </x-badge>
                            <span class="font-medium text-slate-900">{{ $transaction->category->name }}</span>
                        </span>
                    </x-td>

                    <x-td label="Dompet" class="text-slate-600">{{ $transaction->wallet->name }}</x-td>

                    <x-td label="Catatan" class="text-slate-600">
                        {{ $transaction->note ?? '—' }}
                    </x-td>

                    <x-td label="Jumlah">
                        <x-amount :amount="$transaction->amount" :type="$transaction->type" />
                    </x-td>

                    <x-td label="Aksi">
                        <div class="flex items-center justify-end gap-2">
                            <x-button
                                as="a"
                                :href="route('transactions.edit', $transaction)"
                                variant="secondary"
                                class="px-3 py-1.5 text-xs"
                            >
                                <x-icon name="pencil" class="size-4" />
                                Ubah
                            </x-button>

                            <x-confirm-delete
                                :action="route('transactions.destroy', $transaction)"
                                title="Hapus transaksi ini?"
                                :description="'Transaksi '.$transaction->category->name.' sebesar '.\App\Support\Money::rupiah($transaction->amount).' akan dihapus.'"
                            />
                        </div>
                    </x-td>
                </x-tr>
            @endforeach
        </x-table>

        <x-pagination :paginator="$transactions" />
    @endif
</x-layouts.app>
