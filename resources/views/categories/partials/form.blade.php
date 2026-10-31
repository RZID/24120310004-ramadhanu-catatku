@php($category ??= null)

<form
    method="POST"
    action="{{ $category === null ? route('categories.store') : route('categories.update', $category) }}"
    class="space-y-4"
    x-data="{ submitting: false }"
    @submit="submitting = true"
>
    @csrf

    @if ($category !== null)
        @method('PUT')
    @endif

    <x-input
        name="name"
        label="Nama kategori"
        :value="old('name', $category?->name)"
        maxlength="100"
        autocomplete="off"
        autofocus
        required
    />

    <x-select
        name="type"
        label="Tipe"
        :options="['Pemasukan' => 'income', 'Pengeluaran' => 'expense']"
        :selected="$category?->type->value"
        required
    />

    <x-form-actions :cancel="route('categories.index')" :label="$category === null ? 'Simpan kategori' : 'Simpan perubahan'" />
</form>
