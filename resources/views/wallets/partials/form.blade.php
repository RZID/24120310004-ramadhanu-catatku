@php($wallet ??= null)

<form
    method="POST"
    action="{{ $wallet === null ? route('wallets.store') : route('wallets.update', $wallet) }}"
    class="space-y-4"
    x-data="{ submitting: false }"
    @submit="submitting = true"
>
    @csrf

    @if ($wallet !== null)
        @method('PUT')
    @endif

    <x-input
        name="name"
        label="Nama dompet"
        :value="old('name', $wallet?->name)"
        maxlength="100"
        autocomplete="off"
        autofocus
        required
    />

    <x-form-actions :cancel="route('wallets.index')" :label="$wallet === null ? 'Simpan dompet' : 'Simpan perubahan'" />
</form>
