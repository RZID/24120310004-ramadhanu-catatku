<x-layouts.app title="Dompet">
    <x-page-header
        title="Dompet"
        description="Tempat uangmu disimpan: tunai, rekening bank, atau e-wallet."
    >
        <x-slot:actions>
            <x-button as="a" :href="route('wallets.create')">
                <x-icon name="plus" class="size-4" />
                Tambah dompet
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($wallets->isEmpty())
        <x-empty-state
            icon="wallet"
            title="Belum ada dompet"
            description="Tambahkan dompet pertama supaya transaksi bisa dicatat."
        >
            <x-slot:action>
                <x-button as="a" :href="route('wallets.create')">Tambah dompet</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <x-table :head="['Nama', 'Transaksi', 'Aksi']">
            @foreach ($wallets as $wallet)
                <x-tr>
                    <x-td label="Nama" class="font-medium text-slate-900">{{ $wallet->name }}</x-td>

                    <x-td label="Transaksi" class="text-slate-600">
                        {{ $wallet->transactions_count }} transaksi
                    </x-td>

                    <x-td label="Aksi">
                        <div class="flex items-center justify-end gap-2">
                            <x-button
                                as="a"
                                :href="route('wallets.edit', $wallet)"
                                variant="secondary"
                                class="px-3 py-1.5 text-xs"
                            >
                                <x-icon name="pencil" class="size-4" />
                                Ubah
                            </x-button>

                            <x-confirm-delete
                                :action="route('wallets.destroy', $wallet)"
                                title="Hapus dompet ini?"
                                :description="'Dompet '.$wallet->name.' akan dihapus. Dompet yang masih punya transaksi tidak bisa dihapus.'"
                            />
                        </div>
                    </x-td>
                </x-tr>
            @endforeach
        </x-table>
    @endif
</x-layouts.app>
