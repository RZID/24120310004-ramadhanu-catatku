@props(['title' => null])

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title !== null ? $title.' — '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col justify-center px-4 py-12 text-slate-900 antialiased">
    <div class="mx-auto w-full max-w-sm">
        <div class="flex items-center justify-center gap-2.5">
            <span class="grid size-10 place-items-center rounded-lg bg-accent-600 text-lg font-bold text-white">C</span>
            <span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span>
        </div>

        <div class="mt-8 space-y-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <x-flash />

            {{ $slot }}
        </div>
    </div>
</body>
</html>
