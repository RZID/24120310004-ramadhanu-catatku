@props(['code', 'title', 'description'])

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} — {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="flex min-h-full items-center justify-center px-4 py-16 text-slate-900 antialiased">
    <div class="w-full max-w-md text-center">
        <p class="text-6xl font-bold tracking-tight text-accent-600 tabular-nums">{{ $code }}</p>

        <h1 class="mt-4 text-xl font-semibold text-slate-900">{{ $title }}</h1>

        <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
            <x-button as="a" href="{{ url('/') }}">Kembali ke beranda</x-button>
            <x-button as="a" href="{{ route('login') }}" variant="secondary">Masuk</x-button>
        </div>
    </div>
</body>
</html>
