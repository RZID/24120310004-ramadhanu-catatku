@php($transaction ??= null)

<form
    method="POST"
    action="{{ $transaction === null ? route('transactions.store') : route('transactions.update', $transaction) }}"
    class="space-y-4"
    x-data="{ submitting: false }"
    @submit="submitting = true"
>
    @csrf

    @if ($transaction !== null)
        @method('PUT')
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <x-select
            name="type"
            label="Tipe"
            :options="['Pemasukan' => 'income', 'Pengeluaran' => 'expense']"
            :selected="old('type', $transaction?->type->value ?? 'expense')"
            required
        />

        <x-input
            name="transacted_on"
            label="Tanggal"
            type="date"
            :value="old('transacted_on', $transaction?->transacted_on->toDateString() ?? now()->toDateString())"
            required
        />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-select
            name="category_id"
            label="Kategori"
            :options="$categories"
            :selected="old('category_id', $transaction?->category_id)"
            required
        />

        <x-select
            name="wallet_id"
            label="Dompet"
            :options="$wallets"
            :selected="old('wallet_id', $transaction?->wallet_id)"
            required
        />
    </div>

    <div>
        <x-input
            name="amount"
            label="Jumlah (Rp)"
            inputmode="numeric"
            pattern="[0-9]*"
            placeholder="1250000"
            :value="old('amount', $transaction?->amount)"
            required
        />
        <p class="mt-1 text-xs text-slate-500">Tulis angkanya saja, tanpa titik atau koma. Contoh: 1250000.</p>
    </div>

    <x-input
        name="note"
        label="Catatan (opsional)"
        maxlength="255"
        autocomplete="off"
        :value="old('note', $transaction?->note)"
    />

    <x-form-actions
        :cancel="route('transactions.index')"
        :label="$transaction === null ? 'Simpan transaksi' : 'Simpan perubahan'"
    />
</form>
