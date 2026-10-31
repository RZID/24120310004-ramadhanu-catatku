<x-layouts.app title="Kelola Pengguna">
    <x-page-header
        title="Kelola pengguna"
        description="Daftar semua akun. Transaksi pengguna lain tetap privat, termasuk untuk admin."
    />

    <x-table :head="['Nama', 'Email', 'Peran', 'Transaksi', 'Status', 'Aksi']">
        @foreach ($users as $user)
            <x-tr>
                <x-td label="Nama" class="font-medium text-slate-900">
                    {{ $user->name }}
                    @if ($user->id === $currentUserId)
                        <span class="text-xs font-normal text-slate-500">(kamu)</span>
                    @endif
                </x-td>

                <x-td label="Email" class="text-slate-600">{{ $user->email }}</x-td>

                <x-td label="Peran" class="text-slate-600">{{ $user->role->label() }}</x-td>

                <x-td label="Transaksi" class="text-slate-600">{{ $user->transactions_count }} transaksi</x-td>

                <x-td label="Status">
                    @if ($user->isDeactivated())
                        <x-badge tone="neutral">
                            <x-icon name="minus-circle" class="size-3" />
                            Nonaktif
                        </x-badge>
                    @else
                        <x-badge tone="income">
                            <x-icon name="check-circle" class="size-3" />
                            Aktif
                        </x-badge>
                    @endif
                </x-td>

                <x-td label="Aksi">
                    @if ($user->id === $currentUserId)
                        <span class="text-xs text-slate-500">Akun kamu</span>
                    @else
                        <div class="flex items-center justify-end gap-2">
                            <form
                                method="POST"
                                action="{{ route($user->isDeactivated() ? 'admin.users.activate' : 'admin.users.deactivate', $user) }}"
                            >
                                @csrf

                                <x-button type="submit" variant="secondary" class="px-3 py-1.5 text-xs">
                                    {{ $user->isDeactivated() ? 'Aktifkan' : 'Nonaktifkan' }}
                                </x-button>
                            </form>

                            <x-confirm-delete
                                :action="route('admin.users.destroy', $user)"
                                title="Hapus akun ini?"
                                :description="'Akun '.$user->name.' beserta seluruh dompet, kategori dan transaksinya akan dihapus permanen.'"
                            />
                        </div>
                    @endif
                </x-td>
            </x-tr>
        @endforeach
    </x-table>
</x-layouts.app>
