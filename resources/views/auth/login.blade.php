<x-layouts.guest title="Masuk">
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-slate-900">Masuk</h1>
        <p class="mt-1 text-sm text-slate-500">Catat pemasukan dan pengeluaranmu dengan rapi.</p>
    </div>

    <form
        method="POST"
        action="{{ route('login') }}"
        class="space-y-4"
        x-data="{ submitting: false }"
        @submit="submitting = true"
    >
        @csrf

        <x-input
            name="email"
            label="Email"
            type="email"
            :value="old('email')"
            autocomplete="username"
            autofocus
            required
        />

        <x-input
            name="password"
            label="Kata sandi"
            type="password"
            autocomplete="current-password"
            required
        />

        <x-button type="submit" class="w-full" x-bind:disabled="submitting">
            Masuk
        </x-button>
    </form>

    <p class="text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-accent-700 transition hover:text-accent-800">Daftar</a>
    </p>
</x-layouts.guest>
