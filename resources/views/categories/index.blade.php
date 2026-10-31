<x-layouts.app title="Kategori">
    <x-page-header
        title="Kategori"
        description="Kelompokkan transaksi sebagai pemasukan atau pengeluaran."
    >
        <x-slot:actions>
            <x-button as="a" :href="route('categories.create')">
                <x-icon name="plus" class="size-4" />
                Tambah kategori
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($categories->isEmpty())
        <x-empty-state
            icon="tag"
            title="Belum ada kategori"
            description="Kategori dipakai untuk mengelompokkan transaksi dan menghitung pengeluaran per kategori."
        >
            <x-slot:action>
                <x-button as="a" :href="route('categories.create')">Tambah kategori</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        <x-table :head="['Nama', 'Tipe', 'Transaksi', 'Aksi']">
            @foreach ($categories as $category)
                <x-tr>
                    <x-td label="Nama" class="font-medium text-slate-900">{{ $category->name }}</x-td>

                    <x-td label="Tipe">
                        <x-badge :tone="$category->type->value">
                            <x-icon :name="$category->type->icon()" class="size-3" />
                            {{ $category->type->label() }}
                        </x-badge>
                    </x-td>

                    <x-td label="Transaksi" class="text-slate-600">
                        {{ $category->transactions_count }} transaksi
                    </x-td>

                    <x-td label="Aksi">
                        <div class="flex items-center justify-end gap-2">
                            <x-button
                                as="a"
                                :href="route('categories.edit', $category)"
                                variant="secondary"
                                class="px-3 py-1.5 text-xs"
                            >
                                <x-icon name="pencil" class="size-4" />
                                Ubah
                            </x-button>

                            <x-confirm-delete
                                :action="route('categories.destroy', $category)"
                                title="Hapus kategori ini?"
                                :description="'Kategori '.$category->name.' akan dihapus. Kategori yang masih dipakai transaksi tidak bisa dihapus.'"
                            />
                        </div>
                    </x-td>
                </x-tr>
            @endforeach
        </x-table>
    @endif
</x-layouts.app>
