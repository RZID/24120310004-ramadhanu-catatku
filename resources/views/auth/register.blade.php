<x-layouts.guest title="Daftar">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Daftar</h1>
        <p class="mt-1 text-sm text-slate-500">Buat akun untuk mulai mencatat keuangan.</p>
    </div>

    <form
        method="POST"
        action="{{ route('register') }}"
        class="space-y-4"
        x-data="{ submitting: false }"
        @submit="submitting = true"
    >
        @csrf

        <x-input
            name="name"
            label="Nama"
            :value="old('name')"
            autocomplete="name"
            autofocus
            required
        />

        <x-input
            name="email"
            label="Email"
            type="email"
            :value="old('email')"
            autocomplete="username"
            required
        />

        <x-input
            name="password"
            label="Kata sandi"
            type="password"
            autocomplete="new-password"
            required
        />
        <p class="-mt-2 text-xs text-slate-500">Minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka.</p>

        <x-input
            name="password_confirmation"
            label="Ulangi kata sandi"
            type="password"
            autocomplete="new-password"
            required
        />

        <x-button type="submit" class="w-full" x-bind:disabled="submitting">
            Daftar
        </x-button>
    </form>

    <p class="text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-accent-700 transition hover:text-accent-800">Masuk</a>
    </p>
</x-layouts.guest>
