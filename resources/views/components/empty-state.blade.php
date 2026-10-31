@props(['title', 'description' => null, 'icon' => 'list'])

<div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
    <span class="mx-auto grid size-12 place-items-center rounded-full bg-slate-100 text-slate-500">
        <x-icon :name="$icon" class="size-6" />
    </span>

    <h2 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h2>

    @if ($description !== null)
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5 flex justify-center">{{ $action }}</div>
    @endisset
</div>
