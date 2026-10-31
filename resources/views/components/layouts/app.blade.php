@props(['title' => null])

@php
    $user = auth()->user();

    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title !== null ? $title.' — '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-900 antialiased">
    <div class="min-h-full lg:pl-64">
        <aside class="fixed inset-y-0 left-0 hidden w-64 flex-col border-r border-slate-200 bg-white lg:flex">
            <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-slate-200 px-6">
                <span class="grid size-9 place-items-center rounded-lg bg-accent-600 text-base font-bold text-white">C</span>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi utama">
                @foreach ($navItems as $item)
                    <x-nav-link :route="$item['route']" :label="$item['label']" :icon="$item['icon']" />
                @endforeach
            </nav>

            <p class="border-t border-slate-200 px-6 py-4 text-xs text-slate-400">
                Pencatatan keuangan pribadi
            </p>
        </aside>

        <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 lg:px-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 lg:hidden">
                <span class="grid size-9 place-items-center rounded-lg bg-accent-600 text-base font-bold text-white">C</span>
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
            </a>

            <div class="hidden lg:block"></div>

            <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-haspopup="true"
                    class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                >
                    <span class="grid size-8 place-items-center rounded-full bg-accent-100 text-sm font-semibold text-accent-700">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>
                    <span class="hidden sm:block">{{ $user->name }}</span>
                    <x-icon name="chevron-down" class="size-4 text-slate-400" />
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition.origin.top.right
                    @click.outside="open = false"
                    class="absolute right-0 z-30 mt-2 w-56 rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-slate-200"
                >
                    <div class="px-3 py-2">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                        >
                            <x-icon name="logout" class="size-4" />
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl space-y-6 px-4 py-8 lg:px-8">
            <x-flash />

            {{ $slot }}
        </main>
    </div>
</body>
</html>
